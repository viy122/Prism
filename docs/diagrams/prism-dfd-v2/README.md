# PRISM DFD ? revised data stores

Eight processes, exactly three logical data stores, and one box for each of the six user roles.

- **D1 ? Proposal and Market Records:** proposals, item requirements, approval status, market results, and selected price references.
- **D2 ? Procurement Records:** procurement plans, PRs, canvassing, POs, schedules, delivery and receipt records, payment details and evidence.
- **D3 ? Review and Report Records:** review decisions, routing and signature history, and archived report snapshots.

The 32 external arrows retain the exact labels, endpoints, and directions of v1 and the approved context table. All 63 flows are retained; data store endpoints are consolidated. External roles are Office Head / Dean, Budget Office, Procurement Office, Chancellor, Vice Chancellor, and Cashier, with no duplicated entity boxes.

`prism-dfd.png` is the image; `prism-dfd.svg` is the zoomable vector version. `flows.json` and `prism-dfd.dot` contain the editable flow definitions. `render.cjs` uses the existing temporary Viz.js renderer.
