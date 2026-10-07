"""Run the existing PRISM workload using Locust's local runner (no web UI)."""
from locust import HttpUser, task, constant
from locust.env import Environment
from locust.event import Events
from locust.exception import StopUser
from locust.log import setup_logging

import argparse
import collections
import csv
import datetime
import hashlib
import importlib.metadata
import json
import math
import pathlib
import platform
import statistics
import time

import gevent
from gevent.event import Event
from gevent.lock import Semaphore
import psutil

LABEL = "MEASURED - Procurement dashboard FY2026"
TITLE = "<title>Dashboard | Procurement Office | PRISM</title>"
MANILA = datetime.timezone(datetime.timedelta(hours=8))


class BenchmarkState:
    def __init__(self, users, iterations):
        self.users, self.iterations = users, iterations
        self.lock = Semaphore(1)
        self.ready = Event()
        self.finished = Event()
        self.created = self.authenticated = self.completed = 0
        self.errors = []
        self.rows = []
        self.resources = []

    def abort(self, reason):
        self.errors.append(reason)
        self.finished.set()


class ProcurementUser(HttpUser):
    wait_time = constant(0)

    def context(self):
        return {"user_id": self.user_id}

    def get_checked(self, path, name, login=False):
        valid = False
        with self.client.get(path, name=name, allow_redirects=False,
                             timeout=(10, 60), catch_response=True) as response:
            valid = (
                response.status_code == 302
                and "/procurement-office" in response.headers.get("Location", "")
            ) if login else (response.status_code == 200 and TITLE in response.text)
            if not valid:
                response.failure(f"Expected {'demo login redirect' if login else 'HTTP 200 and authenticated dashboard title'}; HTTP {response.status_code}")
        return valid

    def on_start(self):
        state = self.environment.benchmark
        state.created += 1
        self.user_id = state.created
        self.completed = 0
        # Avoid proxy routing for this localhost-only benchmark; retain cookies
        # independently per HttpUser and the default zero HTTP adapter retries.
        self.client.trust_env = False
        self.client.headers["Accept-Encoding"] = "identity"
        with state.lock:
            if state.errors:
                raise StopUser()
            if not self.get_checked("/demo-login/procurement-office",
                                    "SETUP - existing procurement demo login", login=True):
                state.abort(f"User {self.user_id}: authentication failed")
                raise StopUser()
            if not self.get_checked("/procurement-office?year=2026",
                                    "SETUP - dashboard warm-up"):
                state.abort(f"User {self.user_id}: warm-up failed")
                raise StopUser()
            state.authenticated += 1
            if state.authenticated == state.users:
                state.ready.set()
        if not state.ready.wait(timeout=120):
            state.abort(f"User {self.user_id}: measurement barrier timed out")
            raise StopUser()

    @task
    def dashboard(self):
        state = self.environment.benchmark
        self.get_checked("/procurement-office?year=2026", LABEL)
        self.completed += 1
        if self.completed == state.iterations:
            state.completed += 1
            if state.completed == state.users:
                state.finished.set()
            raise StopUser()


def run(users, iterations, output):
    output.mkdir(parents=True, exist_ok=False)
    state = BenchmarkState(users, iterations)
    env = Environment(user_classes=[ProcurementUser], host="http://prism.test",
                      events=Events(), stop_timeout=5)
    env.benchmark = state
    runner = env.create_local_runner()
    fields = ["timeStamp", "elapsed", "label", "success", "responseCode",
              "user_id", "failureMessage"]
    raw_handle = (output / "samples.csv").open("w", newline="", encoding="utf-8")
    writer = csv.DictWriter(raw_handle, fieldnames=fields)
    writer.writeheader()

    def record(request_type, name, response_time, context, exception,
               start_time, response=None, **kwargs):
        row = {
            "timeStamp": start_time * 1000, "elapsed": response_time,
            "label": name, "success": exception is None,
            "responseCode": response.status_code if response is not None else 0,
            "user_id": context["user_id"],
            "failureMessage": str(exception) if exception else "",
        }
        state.rows.append(row)
        writer.writerow(row)
        raw_handle.flush()

    def user_error(user_instance, exception, tb, **kwargs):
        state.abort(f"Locust user exception: {type(exception).__name__}: {exception}")

    env.events.request.add_listener(record)
    env.events.user_error.add_listener(user_error)
    process = psutil.Process()
    process.cpu_percent()

    def observe():
        while True:
            memory = psutil.virtual_memory()
            state.resources.append({
                "time": datetime.datetime.now(MANILA).isoformat(),
                "available_ram_mb": memory.available / 1048576,
                "locust_rss_mb": process.memory_info().rss / 1048576,
                "locust_cpu_percent": process.cpu_percent(),
                "active_users": runner.user_count,
            })
            gevent.sleep(1)

    observer = gevent.spawn(observe)
    started = time.perf_counter()
    print(f"START {users} users, {iterations} measured requests each", flush=True)
    try:
        runner.start(users, spawn_rate=users / 5)
        if not state.finished.wait(timeout=600):
            state.abort("Run exceeded the 600-second safety deadline")
    finally:
        runner.quit()
        observer.kill()
        raw_handle.close()
    measured = [row for row in state.rows if row["label"] == LABEL]
    setup = [row for row in state.rows if row["label"].startswith("SETUP")]
    per_user = collections.Counter(row["user_id"] for row in measured)
    if len(setup) != 2 * users or any(not row["success"] for row in setup):
        state.errors.append("Setup sample count or setup success does not match baseline")
    if len(per_user) != users or any(count != iterations for count in per_user.values()):
        state.errors.append("Measured per-user sample counts do not match the workload")
    if state.completed != users:
        state.errors.append("Not all configured users completed")
    entry = env.stats.get(LABEL, "GET")
    if entry.num_requests != len(measured):
        state.errors.append("Raw request count does not match Locust's native statistics")
    summary = {
        "tool": "Locust", "locust_version": importlib.metadata.version("locust"),
        "python_version": platform.python_version(), "host": env.host,
        "users": users, "iterations_per_user": iterations,
        "samples": len(measured), "setup_samples_excluded": len(setup),
        "completed_users": state.completed, "per_user_counts": dict(per_user),
        "spawn_rate": users / 5, "nominal_ramp_seconds": 5,
        "valid_workload": not state.errors, "validation_errors": state.errors,
        "process_wall_seconds": time.perf_counter() - started,
        "min_available_ram_mb": min(row["available_ram_mb"] for row in state.resources),
        "peak_locust_rss_mb": max(row["locust_rss_mb"] for row in state.resources),
        "peak_locust_cpu_percent": max(row["locust_cpu_percent"] for row in state.resources),
        "raw_sha256": hashlib.sha256((output / "samples.csv").read_bytes()).hexdigest(),
    }
    if measured:
        first = min(row["timeStamp"] for row in measured)
        last = max(row["timeStamp"] + row["elapsed"] for row in measured)
        elapsed = sorted(row["elapsed"] for row in measured)
        failures = [row for row in measured if not row["success"]]
        first_by_user = {}
        for row in measured:
            first_by_user.setdefault(row["user_id"], row["timeStamp"])
        summary.update({
            "avg_ms": statistics.mean(elapsed), "min_ms": min(elapsed),
            "max_ms": max(elapsed), "p95_ms": elapsed[math.ceil(.95 * len(elapsed)) - 1],
            "throughput_rps": len(measured) / ((last - first) / 1000),
            "measurement_seconds": (last - first) / 1000,
            "failed": len(failures), "error_pct": 100 * len(failures) / len(measured),
            "failure_codes": dict(collections.Counter(row["responseCode"] for row in failures)),
            "first_request_spread_ms": max(first_by_user.values()) - first,
            "first_request_manila": datetime.datetime.fromtimestamp(first / 1000, MANILA).isoformat(),
            "last_completion_manila": datetime.datetime.fromtimestamp(last / 1000, MANILA).isoformat(),
            "native_locust_avg_ms": entry.avg_response_time,
            "native_locust_requests": entry.num_requests,
            "native_locust_failures": entry.num_failures,
        })
        if not math.isclose(summary["avg_ms"], entry.avg_response_time, rel_tol=1e-9):
            state.errors.append("Raw mean does not match Locust native mean")
            summary["valid_workload"] = False
    with (output / "measured.csv").open("w", newline="", encoding="utf-8") as handle:
        filtered = csv.DictWriter(handle, fieldnames=fields)
        filtered.writeheader()
        filtered.writerows(measured)
    (output / "resources.json").write_text(json.dumps(state.resources, indent=2), encoding="utf-8")
    (output / "summary.json").write_text(json.dumps(summary, indent=2), encoding="utf-8")
    print(json.dumps(summary, indent=2), flush=True)
    return 0 if summary["valid_workload"] else 2


if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument("--users", type=int, required=True, choices=[1, 2, 5, 10, 30, 50])
    parser.add_argument("--iterations", type=int, default=10)
    parser.add_argument("--output", type=pathlib.Path, required=True)
    args = parser.parse_args()
    if args.iterations < 1:
        parser.error("iterations must be positive")
    setup_logging("INFO")
    raise SystemExit(run(args.users, args.iterations, args.output))
