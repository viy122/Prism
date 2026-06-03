# PRISM MVP Database Foundation

This schema is the flexible MVP backend foundation for PRISM using the existing MySQL database `prism_db`. It intentionally avoids overfitting to unknown BSU procurement form columns. Official PR, APP, and procurement report template fields should be added only after the actual school templates are obtained.

## Commands

Use the normal migration command after pulling these files:

```bash
php artisan config:clear
php artisan migrate --seed
```

For this local prototype only, the schema was rebuilt with:

```bash
php artisan migrate:fresh --seed
```

`migrate:fresh` drops all tables, so only use it when local database data can be reset.

## Current Scope

The backend foundation now supports:

- Users, roles, permissions, and offices
- Office Head / Dean budget proposals
- Budget proposal items
- Market scoping references for price validation
- Proposal review and approval timeline
- Annual Procurement Plan headers and items
- Purchase Request upload and tracking
- Procurement status updates and remarks
- Document uploads
- Notifications
- Audit logs

## Main Tables

| Table | Why It Exists |
| --- | --- |
| `users` | Stores institutional PRISM user accounts. Roles are assigned through pivots; there are no separate user tables per role. |
| `roles` | Stores role names such as Office Head / Dean, Finance Office, Procurement Office, Chancellor, and Vice Chancellor. |
| `permissions` | Stores generic permissions for RBAC. |
| `role_user` | Connects users to roles. |
| `permission_role` | Connects permissions to roles. |
| `campuses` | Stores the BSU ARASOF-Nasugbu campus record. |
| `offices` | Stores colleges, departments, finance, procurement, and executive offices. |
| `office_user_assignments` | Tracks office assignments and primary office mapping for users. |
| `budget_proposals` | Stores proposal headers created by Office Head / Dean users. |
| `budget_proposal_items` | Stores proposed procurement items under each budget proposal. |
| `market_scoping_references` | Stores supplier/source price references for budget validation only. Suppliers are not users. |
| `budget_proposal_reviews` | Stores workflow actions such as submitted, returned, endorsed, approved, or disapproved. |
| `annual_procurement_plans` | Stores APP headers in a generic form until the official APP template is available. |
| `annual_procurement_plan_items` | Stores APP line items linked to approved proposal items when applicable. |
| `purchase_requests` | Stores Purchase Request tracking records, upload paths, and extracted PDF metadata. |
| `purchase_request_items` | Stores generic PR line items. |
| `procurement_status_updates` | Stores procurement tracking remarks and status changes. |
| `document_uploads` | Stores file paths and extracted document metadata. Raw files are not stored in MySQL. |
| `prism_notifications` | Stores user notifications. |
| `audit_logs` | Append-oriented action logs for important system activity. |

Laravel default support tables also exist: `migrations`, `password_reset_tokens`, `sessions`, `cache`, `jobs`, `failed_jobs`, and `job_batches`.

## Major Relationships

- `users.office_id` points to the user's primary office.
- `users` have many `roles` through `role_user`.
- `roles` have many `permissions` through `permission_role`.
- `offices` belong to `campuses`.
- `budget_proposals` belong to `offices` and track who created, submitted, reviewed, and approved them.
- `budget_proposals` have many `budget_proposal_items`.
- `budget_proposal_items` have many `market_scoping_references`.
- `budget_proposals` have many `budget_proposal_reviews`.
- `annual_procurement_plans` have many `annual_procurement_plan_items`.
- `annual_procurement_plan_items` may link back to `budget_proposal_items`.
- `purchase_requests` may link to an `annual_procurement_plan`.
- `purchase_request_items` may link to `annual_procurement_plan_items`.
- `procurement_status_updates` may link to a `purchase_request` or APP item.
- `document_uploads` are polymorphic, so documents can attach to proposals, APP records, PRs, or future records.
- `audit_logs` are polymorphic through `auditable_type` and `auditable_id`.

## Generic / Temporary Fields

These fields are intentionally generic until official school templates are available:

| Field | Current Use | Update Later When Templates Arrive |
| --- | --- | --- |
| `code` / `number` | Stores proposal, APP, item, or PR identifiers. | Match official numbering format and validation rules. |
| `title` / `name` | Stores human-readable record labels. | Rename or split if official forms use specific item/request names. |
| `description` | Stores free-form details. | Replace or supplement with official description fields. |
| `category` | Temporary text category for proposal items. | Replace with a controlled lookup table if the school has official categories. |
| `fiscal_year` | Stored directly as a year integer for MVP speed. | Replace with a fiscal year table only if school rules require periods, ceilings, or locked cycles. |
| `target_quarter` | Generic procurement timing. | Align values with official quarterly/monthly APP template. |
| `approved_budget` | Generic approved amount. | Align with official ABC/budget/allotment terminology. |
| `procurement_mode` | Temporary text on APP items. | Replace with a formal procurement mode lookup and override workflow when official rules are confirmed. |
| `status` | Generic workflow state. | Restrict values through app constants or enums once final workflow is approved. |
| `remarks` | Generic comments and notes. | Split into official remarks/recommendation/return reason fields if required. |
| `file_path` | Stores uploaded document path. | Keep as-is; add document classification if required. |
| `form_data_json` | Stores unconfirmed proposal form fields. | Convert stable official fields into real columns later. |
| `specifications_json` | Stores flexible item technical specs. | Convert high-value repeated specs into structured columns only if needed. |
| `source_snapshot_json` | Stores source/reference snapshot data. | Keep flexible unless reporting requires structured fields. |
| `ai_analysis_json` | Stores AI match/explanation payloads. | Keep flexible because AI output can change. |
| `template_data_json` | Stores unconfirmed APP template fields. | Convert official APP columns later. |
| `extracted_fields_json` | Stores OCR/PDF extracted fields. | Map confirmed PR PDF fields into real columns later. |
| `review_data_json` | Stores extra review context. | Convert official review checklist fields later. |
| `update_data_json` | Stores extra procurement update metadata. | Convert common tracking fields after report templates are known. |

## What To Update After Receiving Official Templates

After receiving official PR, APP, and procurement reporting templates:

1. Add confirmed APP columns to `annual_procurement_plans` and `annual_procurement_plan_items`.
2. Add confirmed PR columns to `purchase_requests` and `purchase_request_items`.
3. Add official procurement report tracking fields to `procurement_status_updates` or a new normalized report table.
4. Replace temporary text `category` with a lookup table if official categories are fixed.
5. Replace temporary text `procurement_mode` with a lookup table and override audit flow if required.
6. Lock down allowed `status` values in application constants and validation rules.
7. Map `extracted_fields_json` OCR outputs to confirmed PR fields.
8. Keep file uploads as paths, not database blobs.

## Seeded Defaults

The seeder creates:

- 6 roles
- 9 permissions
- 1 campus
- 5 offices
- 6 demo users

Demo password for all seeded users:

```text
password
```

The Office Head / Dean demo account is:

```text
office.head@prism.test
```

## Assumptions

- `prism_db` already exists in XAMPP phpMyAdmin.
- `.env` uses MySQL with user `root` and empty password.
- Market scoping references are for budget validation only and do not imply checkout, ordering, payment, or supplier selection.
- Uploaded documents are stored in the filesystem; the database stores paths and extracted metadata.
- APP and PR fields are intentionally generic until the school provides official forms.
- Audit logs should be treated as append-oriented records.
