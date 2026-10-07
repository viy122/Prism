"""Bounded internet throughput test; synthetic payloads only, no project data."""
import concurrent.futures
import datetime
import json
import pathlib
import re
import statistics
import subprocess
import threading
import time
import uuid

import requests

ROOT = pathlib.Path(__file__).resolve().parent
TARGET = "1.1.1.1"
CHUNK = 5_000_000
PHASE_SECONDS = 15
MAX_REQUESTS = 36


def ping():
    proc = subprocess.run(
        ["ping.exe", "-n", "1", "-w", "1500", TARGET],
        capture_output=True, text=True, timeout=4,
    )
    match = re.search(r"time([=<])(\d+)ms", proc.stdout)
    return float(match[2]) if match else None


def summarize(samples):
    valid = [v for v in samples if v is not None]
    diffs = [abs(b - a) for a, b in zip(samples, samples[1:])
             if a is not None and b is not None]
    return {
        "samples_ms": samples,
        "median_ms": statistics.median(valid) if valid else None,
        "mean_ms": statistics.mean(valid) if valid else None,
        "jitter_ms": statistics.mean(diffs) if diffs else None,
        "lost": len(samples) - len(valid),
    }


def phase(direction):
    samples, transfers, errors = [], [], []
    lock, stop = threading.Lock(), threading.Event()
    started = time.perf_counter()
    reserved = 0
    payload = b"0" * CHUNK

    def probe():
        while not stop.is_set():
            samples.append(ping())
            stop.wait(0.2)

    def transfer():
        nonlocal reserved
        with requests.Session() as session:
            while time.perf_counter() - started < PHASE_SECONDS:
                with lock:
                    if reserved >= MAX_REQUESTS:
                        break
                    reserved += 1
                begin = time.perf_counter()
                try:
                    if direction == "download":
                        with session.get(
                            "https://speed.cloudflare.com/__down",
                            params={"bytes": CHUNK, "nonce": uuid.uuid4().hex},
                            headers={"Accept-Encoding": "identity"},
                            stream=True, timeout=(5, 20),
                        ) as response:
                            response.raise_for_status()
                            size = sum(len(chunk) for chunk in response.iter_content(65536))
                        if size != CHUNK:
                            raise RuntimeError(f"Expected {CHUNK} bytes, received {size}")
                    else:
                        response = session.post(
                            "https://speed.cloudflare.com/__up",
                            data=payload,
                            headers={"Content-Type": "application/octet-stream"},
                            timeout=(5, 20),
                        )
                        response.raise_for_status()
                        size = CHUNK
                    with lock:
                        transfers.append({"bytes": size, "seconds": time.perf_counter() - begin})
                except Exception as exc:
                    with lock:
                        errors.append(str(exc))
                    break

    monitor = threading.Thread(target=probe)
    monitor.start()
    with concurrent.futures.ThreadPoolExecutor(max_workers=3) as pool:
        list(pool.map(lambda _: transfer(), range(3)))
    elapsed = time.perf_counter() - started
    stop.set()
    monitor.join()
    total = sum(item["bytes"] for item in transfers)
    return {
        "mbps": total * 8 / elapsed / 1_000_000 if transfers and not errors else None,
        "bytes": total, "elapsed_seconds": elapsed,
        "latency": summarize(samples), "transfers": transfers, "errors": errors,
    }


if __name__ == "__main__":
    result = {
        "timestamp": datetime.datetime.now(datetime.timezone(datetime.timedelta(hours=8))).isoformat(),
        "method": "Custom Cloudflare HTTPS throughput, three concurrent connections; ICMP ping to 1.1.1.1. Throughput includes connection/request overhead. Jitter is mean absolute difference of consecutive successful RTT samples; samples separated by loss are excluded. Not an official Cloudflare or Ookla test.",
        "limits": {"seconds_per_phase": PHASE_SECONDS, "max_bytes_per_direction": MAX_REQUESTS * CHUNK},
    }
    print("Measuring idle ping (20 samples)...", flush=True)
    idle = []
    for _ in range(20):
        idle.append(ping())
        time.sleep(0.2)
    result["idle_latency"] = summarize(idle)
    for direction in ("download", "upload"):
        print(f"Measuring {direction} and loaded jitter...", flush=True)
        result[direction] = phase(direction)
        print(json.dumps({direction: result[direction]["mbps"], "errors": result[direction]["errors"]}), flush=True)
        time.sleep(1)
    (ROOT / "results.json").write_text(json.dumps(result, indent=2), encoding="utf-8")
    print(json.dumps({
        "download_mbps": result["download"]["mbps"],
        "upload_mbps": result["upload"]["mbps"],
        "ping_ms": result["idle_latency"]["median_ms"],
        "download_jitter_ms": result["download"]["latency"]["jitter_ms"],
        "upload_jitter_ms": result["upload"]["latency"]["jitter_ms"],
    }, indent=2), flush=True)
