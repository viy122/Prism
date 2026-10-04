# PRISM Apache JMeter benchmark results

Measured on October 3, 2026, Asia/Manila (UTC+08:00). These are actual local measurements, not projected hosting results. No application functionality or performance optimization was changed.

## Table 1. Load Test Results

| Concurrent Users | Samples | Avg Response Time (ms) | Min (ms) | Max (ms) | Throughput (RPS) | Error Rate (%) |
|---:|---:|---:|---:|---:|---:|---:|
| 1 | 10 | 253.10 | 217 | 281 | 3.73 | 0.00 |
| 5 | 50 | 299.02 | 281 | 329 | 15.37 | 0.00 |
| 10 | 100 | 479.44 | 308 | 655 | 19.15 | 1.00 |
| 30 | 300 | 2,018.62 | 289 | 11,843 | 12.22 | 2.33 |
| 50 | 500 | 3,747.46 | 309 | 12,743 | 11.65 | 2.00 |

Failed requests for 1/5/10/30/50 users were respectively 0/0/1/7/10, all HTTP 500. Total: 18 failures out of 960 measured requests (1.875%, rounded to 1.88%). Failed requests remain included in response-time and throughput statistics. Login/warm-up samples are excluded.

## Table 2. Scalability Results

Degradation = ((RTn - RT1) / RT1) * 100; RT1 = 253.10 ms. Calculations use unrounded means.

| Concurrent Users | Avg Response Time (ms) | Performance Degradation (%) |
|---:|---:|---:|
| 1 | 253.10 | 0.00 (baseline) |
| 5 | 299.02 | 18.14 |
| 10 | 479.44 | 89.43 |
| 30 | 2,018.62 | 697.56 |
| 50 | 3,747.46 | 1,380.62 |

## Table 3. Final Apache JMeter Benchmark Summary

| Performance Metric | Result |
|---|---|
| Response Time | Average increased from 253.10 ms at 1 user to 3,747.46 ms at 50 users in the normal-load series. |
| Throughput | 3.73 RPS at 1 user; highest observed normal-load throughput 19.15 RPS at 10 users; 11.65 RPS at 50 users. |
| Scalability | Response-time degradation at 5/10/30/50 users: 18.14% / 89.43% / 697.56% / 1,380.62%, relative to 1 user. |
| Reliability under Load | Normal tests: 18/960 failures (1.88% overall); normal 50-user run: 10/500 (2.00%). Separate sustained 50-user run: 82/3,576 failures (2.29%). |

## Sustained 50-user test

- Configured measurement duration: 300 seconds (5 minutes), not 24 hours.
- Actual first-request-to-last-completion span: 305.126 seconds, including in-flight requests finishing after the common deadline.
- Measurement timestamps: 2026-10-03 21:31:29.534 to 21:36:34.660, UTC+08:00.
- 50 participating threads, each executing 58 to 79 measured requests until the common deadline.
- Samples: 3,576; successful: 3,494; failed: 82 (80 HTTP 500 responses and 2 read timeouts).
- Average response time: 4,207.89 ms; minimum: 627 ms; maximum: 60,019 ms.
- Throughput: 11.72 RPS; error rate: 2.29%.
- Complete JMeter-process run, including setup/startup/shutdown: 331.966 seconds.

## Exact transaction and configuration

One request per measured iteration: `GET http://prism.test/procurement-office?year=2026` (Procurement Office dashboard). The existing local database contained 36 non-deleted FY 2026 purchase requests at inspection.

Authentication was involved: each thread received a separate Laravel cookie session using the application's existing non-production `GET /demo-login/procurement-office` flow for the existing `procurement` account. This is real authenticated dashboard access, but not a password-login benchmark and not 50 distinct account identities.

For every final load level, login and one warm-up request per user were serialized and excluded from measurements. Threads were started over 5 seconds; a synchronization barrier released all users after setup. Each normal-load user then executed exactly 10 iterations. No think time, automatic HTTP retry, redirect following, or embedded-resource downloads were enabled. The response assertion required HTTP 200 and the Procurement Office dashboard title.

Connection timeout: 10 seconds. Response timeout: 60 seconds. HTTP implementation: HttpClient4 with keep-alive. Barrier timeout: 120 seconds. JMeter JVM: 128 MB initial heap / 512 MB maximum heap, 192 MB maximum metaspace. One final run per normal load level, in ascending order; no application configuration/cache optimization between levels.

Measured normal-run durations (first measured request to last completion):

- 1 user: 2.682 seconds, 21:28:11.642 to 21:28:14.324.
- 5 users: 3.253 seconds, 21:28:22.251 to 21:28:25.504.
- 10 users: 5.223 seconds, 21:28:33.854 to 21:28:39.077.
- 30 users: 24.553 seconds, 21:28:55.879 to 21:29:20.432.
- 50 users: 42.916 seconds, 21:29:47.309 to 21:30:30.225.

All times above are October 3, 2026, Asia/Manila. Initial measured-request spreads were 0, 1, 3, 57, and 114 ms respectively; peak active thread counts matched the requested loads. Short fixed-iteration runs taper as threads finish; they do not hold the full concurrency for a fixed duration.

## Environment and limitations

- Windows 11 Home Single Language, build 10.0.26200; Intel Core i5-1155G7, 4 cores / 8 logical processors; 7.79 GiB OS-visible RAM.
- Apache JMeter 5.6.3 in CLI mode, OpenJDK 21.0.10. The initial Java 24 smoke attempt had a Groovy compatibility error; it produced no measured samples and is not used in these tables.
- Apache 2.4.58 Win64 serving the existing `prism.test` virtual host on HTTP port 80; PHP 8.2.12; Laravel 12.61.0; MariaDB 10.4.32.
- Laravel `local` environment with debug enabled; database-backed sessions and cache. Existing server configuration was retained.
- Load generator, application, database, and background services shared this PC. Initial free RAM was about 0.58 GiB, and the sustained-run monitor observed a low of 47.91 MiB. Host resource contention can materially affect these results; its exact contribution was not isolated.
- This measures one authenticated server-side HTML request, not browser rendering, JavaScript execution, all PRISM features, internet latency, or hosted production capacity.
- Only 10 iterations per user and one final normal run per level were required; the low-load measurement windows are short. No confidence interval or repeatability claim is made.
- Preliminary attempts are retained under `normal-*` and `smoke*`. Preliminary 10-user testing had 3 HTTP 500 failures; preliminary 30-user testing stopped during authentication setup. Final tables use a fresh five-level series with identical serialized setup, not selectively merged samples.
- Application logs during the load included missing encryption-key and missing SQLite database errors. The logs were inspected, but no root-cause fix or configuration change was made. Do not treat these results as an optimized or error-free deployment benchmark.
- Authentication naturally updates session records and the demo account's last-login timestamp; normal logs/caches are also written. The benchmark did not create or alter procurement records.

## Factual interpretation

At 50 users, mean response time was approximately 14.81 times the 1-user baseline. Observed normal-load throughput peaked at 10 users and was lower at 30 and 50 users. HTTP 500 errors occurred in the 10-, 30-, and 50-user normal runs; the sustained run also encountered read timeouts. No acceptance threshold was supplied, so these measurements do not by themselves establish that PRISM is acceptable, unacceptable, scalable, or reliable.

## Evidence

- [Load results CSV](table-1-load-results.csv)
- [Scalability CSV](table-2-scalability.csv)
- [Full numerical results](results.json)
- [Sample-count, status-code, and SHA-256 audit](sample-audit.json)
- [Final 50-user JMeter HTML report](final-50/report/index.html)
- [Sustained JMeter HTML report](sustained-50/report/index.html)
- Raw JTLs and per-run HTML reports are retained in every final run directory.
- Required counts, error counts, averages, and throughput were cross-checked against JMeter's own generated `statistics.json` for all six final runs; they agree with the tables (apart from display rounding).
