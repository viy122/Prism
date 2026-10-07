# Locust results: PRISM localhost

Tested October 4, 2026 (Asia/Manila) using Locust 2.46.0 / Python 3.10.11.

| Users | Samples | Average response (ms) | Throughput (RPS) | Failures | P95 (ms) |
| --- | --- | --- | --- | --- | --- |
| 1 | 10 | 300.80 | 3.32 | 0 | 409.17 |
| 5 | 50 | 315.58 | 15.67 | 0 | 336.50 |
| 10 | 100 | 517.14 | 18.35 | 0 | 637.19 |
| 30 | 300 | 1,928.15 | 12.93 | 0 | 3,001.97 |
| 50 | 500 | 3,409.15 | 12.71 | 0 | 5,453.06 |

All 960 measured requests completed with HTTP 200 and the expected dashboard title. Setup/warm-up requests are excluded.

Measured request windows range from 2026-10-04T18:42:36.619706+08:00 to 2026-10-04T18:44:46.507364+08:00. These are five separate runs, not continuous load.

Observed throughput peaked at 10 users and decreased at 30 and 50 users. Mean response time rose to about 3.41 seconds at 50 users. Zero observed failures in these short runs does not establish sustained reliability.

Minimum observed available RAM across final runs: 170.36 MiB. Peak Locust resident memory: 67.03 MiB. Load client and server share this machine.

See [summary.csv](summary.csv) for the side-by-side October 3 JMeter comparison and [README.md](README.md) for methodology. Different run dates, available resources, and HTTP clients mean these are not controlled measurements of the tools themselves.

Verification: all raw-file hashes, two successful setup samples per user, ten measured samples per user, participating user counts, calculated means/throughput, and native Locust failure counts checked. The two-user smoke run is excluded.
