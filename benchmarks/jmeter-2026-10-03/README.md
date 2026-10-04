# PRISM Chapter 3 JMeter benchmark artifacts

Test date: October 3, 2026 (Asia/Manila, UTC+08:00).

This directory contains testing artifacts only. No application functionality, server configuration, database configuration, or application optimization was changed for this benchmark. Normal application session/cache writes, logs, and the demo account's last-login timestamp occur during requests.

## Transaction and authentication

The measured transaction is one HTTP GET to `http://prism.test/procurement-office?year=2026`, the Procurement Office dashboard. It retrieves PR/AOC/PO data, tracking summaries, and dashboard chart data. Initial inspection found 36 non-deleted FY 2026 purchase requests and 20 non-deleted FY 2026 budget proposals in the existing local database.

Each JMeter thread obtains its own Laravel session through the existing non-production `GET /demo-login/procurement-office` route, using the existing `procurement` demo account. This invokes the application's normal `Auth::login`; no account, authentication bypass, or application change was added. These are independent sessions, not 50 different account identities. Login throughput/password verification is not measured.

In the final plan, a JMeter Critical Section Controller serializes login and one dashboard warm-up per thread. A Synchronizing Timer then releases all authenticated threads together. Only requests labelled `MEASURED - Procurement dashboard FY2026` are included in the tables. Each response must be HTTP 200 and contain the actual Procurement Office dashboard title; redirects are not followed by measured samplers.

## Configuration

- Apache JMeter 5.6.3, non-GUI; existing OpenJDK 21.0.10 runtime bundled with Android Studio.
- Java heap: initial 128 MB, maximum 512 MB; maximum metaspace 192 MB.
- Host: `prism.test`, HTTP port 80, through the existing Apache virtual host (not `php artisan serve`).
- HTTPClient4, keep-alive enabled, embedded resources disabled, no HTTP retries, no think-time timer.
- Connect timeout 10 seconds; response timeout 60 seconds.
- Thread startup ramp: 5 seconds. Unmeasured setup is serialized; the measurement barrier timeout is 120 seconds.
- Normal runs: 1, 5, 10, 30, and 50 threads, exactly 10 measured iterations each, sequential load levels, one final run per level.
- Sustained run: 50 threads, a common 300-second measurement deadline after the startup barrier. In-flight requests finish afterward; the result records the actual first-request-to-last-completion span.
- Normal and sustained tests use the same transaction and assertions.
- Existing FY 2026 data is read; no new procurement records are created or reset by the tests.

## Environment observed

- Windows 11 Home Single Language, build 10.0.26200.
- Intel Core i5-1155G7, 4 physical cores / 8 logical processors.
- OS-visible RAM: 7.79 GiB; approximately 0.58 GiB free at initial inspection. Per-run memory observations are included.
- Apache 2.4.58 Win64; PHP 8.2.12; Laravel 12.61.0; MariaDB 10.4.32.
- Laravel environment `local`, debug enabled; database-backed sessions and cache.
- Client, Apache, database, and other existing services share the same PC. This is a localhost benchmark with background workloads and memory pressure, not a hosted-capacity or network-latency test.

## Calculations

All elapsed times come from JMeter HTTP sampler results, including failed measured requests.

```text
Average response time = sum(elapsed_ms) / measured sample count
Measurement span = max(timestamp + elapsed) - min(timestamp)
Throughput (RPS) = measured sample count / measurement span in seconds
Error rate (%) = failed measured samples / measured samples * 100
Degradation (%) = (average_at_N_users - average_at_1_user) / average_at_1_user * 100
```

Throughput is total attempted-request throughput, not successful-only throughput. Measurements exclude setup and warm-up but include the measured workload's start/end tail. The short fixed-iteration tests reach the specified concurrency initially and taper as threads complete; only the duration-based run maintains threads until its common deadline. There is no agreed pass/fail performance threshold, so these results do not establish that performance is acceptable or unacceptable.

## Files and reproducibility

- `dashboard.jmx`: final test plan.
- `benchmark.properties`: JMeter result settings; response bodies and session headers are not saved.
- `run-benchmark.ps1`: executes one run and validates setup, per-thread iteration counts, and participating users.
- `inspect-environment.php`: read-only application configuration/data-count diagnostic.
- `summarize.ps1`: calculates tables from final JTLs and emits raw-file hashes.
- `final-01`, `final-05`, `final-10`, `final-30`, `final-50`: final normal runs.
- `sustained-50`: separate duration-based run.
- Each completed run includes raw `samples.jtl`, measured-only CSV, logs, summary JSON, and memory observations. Generated HTML reports use only measured samples.

To repeat a run without overwriting evidence, use a new run name:

```powershell
./run-benchmark.ps1 -Users 10 -Iterations 10 -RunName rerun-10
./run-benchmark.ps1 -Users 50 -Duration 300 -RunName rerun-sustained-50
```

JMeter CLI/report generation follows the [Apache JMeter manual](https://jmeter.apache.org/usermanual/get-started.html).

## Preliminary attempts retained, not used in final tables

- `smoke.jtl`: Java 24 could not execute the measurement-start Groovy script (`Unsupported class file major version 68`); there were no measured samples. The application was not changed; the existing Java 21 runtime was selected instead.
- `smoke-java21.jtl`: successful two-iteration validation, not part of the baseline.
- `normal-01`, `normal-05`, `normal-10`: preliminary runs with concurrent unmeasured session setup. The 10-user preliminary run recorded 3 HTTP 500 responses out of 100 measured samples.
- `normal-30`: stopped during failed session setup, before any measured workload. Some socket failures in this aborted attempt resulted from stopping JMeter and are not presented as measured dashboard results.
- The final five-load series was run anew with the same serialized unmeasured setup at every level. Its errors are retained in full; the final series is not an error-free selection.
- The preliminary 1-user runner could not retrieve its subprocess exit code and raised a wrapper error after saving complete samples. Its JMeter log shows completion. This does not affect the separate final baseline.

Application logs during load contained missing application encryption-key and missing SQLite database errors. These are observations, not a proven root-cause diagnosis. No fix, configuration cache command, or other performance tuning was applied.
