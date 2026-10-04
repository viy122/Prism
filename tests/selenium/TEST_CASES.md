# Functional Correctness (UI) test plan

Expected behavior below is an assertion contract, not a claim that every case
has passed. Mutating cases require `-AllowMutations` and disposable test records.

| ID | Module | Scenario and expected result | Mutating |
|---|---|---|---|
| FC-01 | Login | Blank submission shows both required-field messages | No |
| FC-02 | Access | Guest redirected from Budget Proposal, Purchase Orders and User Management | No |
| FC-03 | Access | Valid login, restricted Admin URL redirected, logout protects dashboard | No |
| FC-04 | Login | Wrong password rejected with specific error | No |
| FC-05 | Budget Proposal | Blank description triggers required validation | No |
| FC-06 | Budget Proposal | Zero quantity triggers minimum validation | No |
| FC-07 | Budget Proposal | Add one item with quantity 2 at 125; edit quantity to 3; remove; totals persist | Yes |
| FC-08 | Budget Proposal | Missing source reference disables submission | No |
| FC-09 | Proposal Review | Finance return and remarks persist for originating Office Head | Yes |
| FC-10 | Budget Approval | Empty return remarks rejected; proposal remains pending after refresh | No |
| FC-11 | Budget Approval | Return archives exact proposal; status and remarks persist | Yes |
| FC-12 | Planning workflow | Submit, endorse, approve across three roles; approved proposal becomes read-only | Yes |
| FC-13 | PR Management | Known PR details and timeline survive refresh | No |
| FC-14 | AOC | Known AOC/parent PR details and supplier quotations match fixture | No |
| FC-15 | Purchase Orders | Known PO/AOC/supplier/amount details survive refresh | No |
| FC-16 | PR Management | Exact search results, empty state and office-filter ID set | No |
| FC-17 | AOC | Exact search results, empty state and office-filter ID set | No |
| FC-18 | Purchase Orders | Exact search results, empty state and office-filter ID set | No |
| FC-19 | PR Creation | Actual PDF extraction matches expected fields/items/total; unique PR saved | Yes |
| FC-20 | AOC Creation | Eligible PR creates one correct AOC and loses duplicate eligibility | Yes |
| FC-21 | PO Creation | AOC creates one PO with persisted supplier/address/amount | Yes |
| FC-22 | For My Signature | Other role's stage is disabled with not-your-stage message | No |
| FC-23 | Signature handoff | Stage and one log persist; next role receives actionable document | Yes |
| FC-24 | Item Receiving | Excess quantity and future date rejected by browser validation | No |
| FC-25 | Item Receiving | Quantity/status and one receipt history with date/recipient/remark persist | Yes |
| FC-26 | User Management | Missing name/username/email generate validation messages | No |
| FC-27 | User Management | Create/edit/deactivate disposable user; inactive login is rejected | Yes |
| FC-28 | Reports | Office totals, quarterly rows and export-link scope are correct after refresh | No |
| FC-29 | Fiscal Year | Switching year updates URL/selection and independently known totals | No |
| FC-30 | Notifications | Correct destination, persistent read state and decremented count | Yes |

FC-23 additionally checks missing-third-signer validation if its fixture needs
that choice. UI validation coverage does not constitute a full security audit.
