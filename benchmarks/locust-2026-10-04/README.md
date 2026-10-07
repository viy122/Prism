# PRISM localhost Locust benchmark

Test date: October 4, 2026, Asia/Manila. This uses the actual Locust local runner
and HttpUser client through Locust's documented Python library API. It is not a
BlazeMeter test or an internet speed test.

## Workload and comparability

- Target: `http://prism.test` through the existing local Apache virtual host.
- Levels: 1, 5, 10, 30, 50 users, sequential processes, ten measured requests each.
- Measured transaction: `GET /procurement-office?year=2026`, requiring HTTP 200
  and the title `Dashboard | Procurement Office | PRISM`.
- Each user establishes an independent cookie session through the existing
  `GET /demo-login/procurement-office` route, followed by a dashboard warm-up.
  Setup is serialized and excluded from measurements. All sessions share the
  existing procurement demo account identity, as in the JMeter baseline.
- A barrier releases measured traffic only after every user's setup succeeds.
  Barrier timeout: 120 seconds. Spawn rate: users / 5 per second (nominal five
  second ramp); Locust's dispatch batching differs from JMeter's thread ramp.
- No think time, embedded resources, followed redirects, or HTTP retries.
  HTTP connections and cookies are reused independently per user. Explicit
  `Accept-Encoding: identity`; local requests bypass environment proxies.
- Connect/read timeouts: 10/60 seconds. A 600-second overall guard prevents an
  incomplete run from hanging. Invalid setup/counts are recorded as invalid runs.
- Users stop after ten measured requests, so concurrency tapers as users finish.
  This reproduces the short fixed-iteration workload, not sustained capacity.
- No application code/configuration changes or procurement record mutations.
  Normal demo login timestamps, sessions, caches, and logs can be written.

## Evidence and calculations

`final-*/samples.csv` records each Locust request event, including elapsed time,
start timestamp, status code, success, label, and user ID. Response bodies and
cookies are not recorded. `measured.csv` excludes setup. `summary.json` validates
sample counts and records Locust's own average/request/failure statistics beside
the raw-sample calculations. Raw samples have a SHA-256 recorded in each summary.

```text
Mean response time = sum(measured elapsed milliseconds) / measured request count
Span = (last measured completion - first measured start) / 1000
Throughput (RPS) = measured request count / span
Error percentage = 100 * failed measured requests / measured request count
P95 = nearest-rank 95th percentile of measured elapsed times
```

Failed measured requests are included, matching the JMeter baseline. Native
Locust overall throughput can use a wider window including setup, so the
comparison deliberately uses the same measured-span formula as JMeter.
A structurally valid run may still have HTTP failures; errors are reported,
not treated as successful application behavior.

`summary.csv` and `results.json` contain final results; the CSV includes the
October 3 JMeter baseline. `resources.json` records available system RAM, Locust
RSS/CPU and active users once per second. `smoke-02` is a two-user, two-iteration
validation run and is excluded from the final five-level comparison.

## Limitations

The client, Apache, PHP, and database share one Windows PC. RAM and background
workloads differ between the October 3 JMeter and October 4 Locust sessions.
Client implementations, headers, runtime overhead, and ramp batching also differ.
Differences between results cannot be attributed solely to the testing tool.
Only one final run per level is included; this does not establish repeatability.
The test measures one authenticated server-side HTML request, not browser render
time, all Prism features, or hosted deployment capacity. No acceptance threshold
was supplied.

## Reproduce

Locust was installed in an isolated environment outside the application:

```powershell
$locustPython = Join-Path $env:LOCALAPPDATA 'PrismBenchmarkTools\locust-venv\Scripts\python.exe'
& $locustPython benchmarks/locust-2026-10-04/run-benchmark.py --users 10 --iterations 10 --output benchmarks/locust-2026-10-04/rerun-10
```

Use a new output directory for each repeat; the runner refuses overwrites.
`run-suite.py` runs the complete five-level series, also refusing existing final
directories. `requirements-lock.txt` records the installed Python dependencies.

References: [Locust library API](https://docs.locust.io/en/stable/use-as-lib.html)
and [HttpUser/request events](https://docs.locust.io/en/stable/api.html).
