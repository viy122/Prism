"""Run five independent Locust processes and compare their raw-sample metrics."""
import csv
import json
import pathlib
import subprocess
import sys

ROOT = pathlib.Path(__file__).resolve().parent
summaries = []
for users in (1, 5, 10, 30, 50):
    output = ROOT / f"final-{users:02}"
    if output.exists():
        raise SystemExit(f"Refusing to overwrite {output}")
    command = [sys.executable, str(ROOT / "run-benchmark.py"), "--users", str(users),
               "--iterations", "10", "--output", str(output)]
    print(f"Running Locust with {users} users...", flush=True)
    with (ROOT / f"final-{users:02}-console.log").open("w", encoding="utf-8") as log:
        result = subprocess.run(command, stdout=log, stderr=subprocess.STDOUT, timeout=660)
    if result.returncode:
        raise SystemExit(f"Invalid/incomplete {users}-user run; inspect its log and summary (exit {result.returncode})")
    summary = json.loads((output / "summary.json").read_text(encoding="utf-8"))
    summaries.append(summary)
    print(f"{users} users: {summary['avg_ms']:.2f} ms, {summary['throughput_rps']:.2f} RPS, "
          f"{summary['failed']}/{summary['samples']} failed", flush=True)

with (ROOT.parent / "jmeter-2026-10-03/table-1-load-results.csv").open(encoding="utf-8-sig", newline="") as handle:
    baseline = {int(row["ConcurrentUsers"]): row for row in csv.DictReader(handle)}
fields = ["ConcurrentUsers", "Samples", "AverageMs", "ThroughputRPS", "FailedRequests",
          "ErrorRatePercent", "P95Ms", "MeasurementSeconds", "JMeterAverageMs",
          "JMeterThroughputRPS", "JMeterErrorRatePercent"]
with (ROOT / "summary.csv").open("w", encoding="utf-8", newline="") as handle:
    writer = csv.DictWriter(handle, fieldnames=fields)
    writer.writeheader()
    for row in summaries:
        old = baseline[row["users"]]
        writer.writerow({
            "ConcurrentUsers": row["users"], "Samples": row["samples"],
            "AverageMs": f"{row['avg_ms']:.2f}",
            "ThroughputRPS": f"{row['throughput_rps']:.2f}",
            "FailedRequests": row["failed"], "ErrorRatePercent": f"{row['error_pct']:.2f}",
            "P95Ms": f"{row['p95_ms']:.2f}",
            "MeasurementSeconds": f"{row['measurement_seconds']:.6f}",
            "JMeterAverageMs": f"{float(old['AverageMs']):.2f}",
            "JMeterThroughputRPS": f"{float(old['ThroughputRPS']):.2f}",
            "JMeterErrorRatePercent": f"{float(old['ErrorRatePercent']):.2f}",
        })
(ROOT / "results.json").write_text(json.dumps(summaries, indent=2), encoding="utf-8")
print(f"Saved comparison: {ROOT / 'summary.csv'}", flush=True)
