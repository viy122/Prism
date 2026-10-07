# PRISM BlazeMeter test preparation

Status: prepared; no BlazeMeter cloud test has been run. Empty BlazeMeter columns
in `comparison.csv` are intentional, not zero-valued results.

The original target, `http://prism.test`, resolves to `127.0.0.1` on this PC.
No BlazeMeter connector or BlazeMeter-named credentials were found in the checked
environment variables or project environment file. Account access and a target
reachable from the selected load engine are needed before execution.

## Preserved workload

`dashboard.jmx` copies the October 3 JMeter plan, changing only the HTTP defaults
to accept target host, port, and protocol properties. Each virtual user obtains
an independent session through the existing demo login, warms the FY2026
Procurement Office dashboard, waits for the other users, then issues ten measured
dashboard requests. Users share the existing demo account identity.

Run the levels 1, 5, 10, 30, and 50 sequentially. Expected measured sample counts
are 10, 50, 100, 300, and 500. Keep the 5-second startup ramp, serialized setup,
120-second synchronization timeout, assertions, and no think time. Use one load
engine: the synchronization barrier and its `users` property are engine-local.

This reads existing procurement data but normal login/session/cache/log writes
still occur. A deployed target must support this test authentication flow; do not
enable the demo-login route on production just to make the script work.

## Configure BlazeMeter

1. Choose an existing test deployment reachable from BlazeMeter, or an available
   BlazeMeter Private Location that can reach the local application. Private
   Locations require the appropriate account subscription/permissions. Inside a
   container, `127.0.0.1` refers to that container, not this Windows PC. Configure
   routing/DNS and the Apache virtual host appropriately before testing.
2. Create a Performance test and upload `dashboard.jmx` as the main test file.
3. In JMeter Properties, set the following for each run:

   | Property | Value |
   | --- | --- |
   | `target_host` | Reachable host name only, without scheme or path |
   | `target_protocol` | `http` or `https` |
   | `target_port` | `80` or `443`, as appropriate |
   | `users` | `1`, then `5`, `10`, `30`, `50` in separate runs |
   | `iterations` | `10` |
   | `duration` | `0` |
   | `httpclient4.retrycount` | `0` |

4. Disable load overrides to retain the original script settings where your
   subscription allows it. BlazeMeter documents this option for paid accounts.
   If overrides are required, use matching Total Users, one outer iteration,
   and the same startup ramp, then inspect the modified JMX in the downloaded
   artifacts. The script's inner loop already performs ten dashboard requests;
   setting ten outer iterations repeats login and the entire workload. Do not
   accept results unless sample counts, barrier behavior, and participating
   threads match the baseline. Resolve unsupported settings before running all
   five levels.
5. Select one engine/location, leave RPS limiting off, and use JMeter 5.6.3 with a
   supported compatible Java version where available. Record the actual versions.
6. Validate authentication and reachability with one user before raising load.
   Verify the measured response is the actual dashboard, not a login page or
   redirect. Check load-engine CPU/memory before increasing load.

`benchmark.properties` contains the original local result-saving settings. Merely
uploading this filename does not apply them in BlazeMeter; add needed properties
in the JMeter Properties UI. In particular, keep response bodies, request headers,
response headers, and sampler data out of saved results. Preserve timestamp,
elapsed, label, success, and threadName fields in downloaded raw CSV/JTL samples.

## Calculate the comparison

Use only the label `MEASURED - Procurement dashboard FY2026`. Exclude both setup
labels. Keep failed measured requests in the timing and throughput calculation,
as the baseline did, and report errors separately.

```text
Average response time (ms) = sum(elapsed) / measured sample count
Measurement span (s) = (max(timeStamp + elapsed) - min(timeStamp)) / 1000
Throughput (RPS) = measured sample count / measurement span
Error percentage = 100 * failed measured samples / measured sample count
```

Verify two successful setup requests per user, ten measured requests per thread,
the expected number of threads, and no barrier timeouts before filling in
`comparison.csv`. Preserve each BlazeMeter report link and raw artifacts.
BlazeMeter's aggregate report may include setup or use a different time window;
do not copy its overall total into this comparison without checking.

Cloud-to-app results include network travel and may use a different server from
the original localhost test. Record target, location, engine count, versions,
application/data changes, and test time. The short original runs are not sustained
capacity measurements. A local JMeter smoke run is not a BlazeMeter result.

## Official references

- [Create a JMeter test](https://help.blazemeter.com/docs/guide/performance-create-jmeter-test.html)
- [JMeter properties](https://help.blazemeter.com/docs/guide/performance-jmeter-properties.html)
- [Load configuration and overrides](https://help.blazemeter.com/docs/guide/performance-load-configuration.html)
- [Private Locations](https://help.blazemeter.com/docs/guide/private-locations-intro.html)
- [API authorization](https://help.blazemeter.com/docs/guide/api-authorization.html)
