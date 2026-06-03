# to run the proj
cd C:\xampp\htdocs\Prism
composer run dev

or open 2 terminals 
1. php artisan serveyes do 
2. npm run dev


# PRISM: Procurement Records, Intelligence, Scoping, and Monitoring System for Campus Budget Deliberation and Procurement Compliance

## Main System Purpose

PRISM is a campus administrative system that supports budget deliberation, AI-assisted market scoping, proposal approval, Annual Procurement Plan consolidation, purchase request tracking, procurement monitoring, reporting, and budget utilization monitoring.

The system helps offices prepare budget proposals with itemized procurement needs, attach market price references, route proposals for Finance and Chancellor review, consolidate approved items into procurement planning records, monitor PR and procurement progress, and generate role-based oversight reports for campus leadership.

## User Roles

PRISM is organized around five user roles:

- Office Head / Dean
- Finance Office
- Procurement Office
- Chancellor
- Vice Chancellor

Each role has its own section, pages, and workflow responsibilities.

## Office Head / Dean

### Dashboard

Shows the office's procurement and proposal summary. It displays total proposed items, total proposed budget, approved items, pending approvals, procurement progress for the current quarter, recent approval updates, and PR status changes.

### Budget Proposal

Allows the Office Head / Dean to prepare an annual budget proposal. The page includes proposal details such as office name, fiscal year, date, and total proposed budget. It also includes an item encoder for item descriptions, units, quantities, estimated unit costs, total costs, justifications, target quarters, and AI-assisted market scoping references.

### My Proposals

Lists submitted proposals with proposal title, date submitted, total amount, and status. Users can filter proposals by status and fiscal year. Selecting a proposal shows the approval timeline, timestamps, remarks, and revision actions when a proposal is returned.

### Purchase Requests

Shows approved items that are eligible for purchase request submission. The page allows signed PR PDF uploads per item, displays OCR-extracted PR details, shows PR status, and presents Procurement Office remarks.

## Finance Office

### Dashboard

Provides a Finance Office overview of proposal review workload. It shows proposals awaiting review, proposals endorsed this month, returned proposals, total proposed campus budget, grouped proposal statuses by office, and recent submissions with review links.

### Proposal Review

Displays full proposal details submitted by offices. Finance reviewers can inspect office information, item details, quantities, estimated costs, justifications, target quarters, and AI market scoping references. The page supports item-level remarks, overall proposal remarks, endorsement, and return with remarks.

### Annual Procurement Plan

Consolidates approved items from all offices into an Annual Procurement Plan view. It shows office, item, quantity, ABC amount, target quarter, procurement mode, recommended procurement mode, and override reason fields. Finance can filter by office, quarter, and procurement mode, then export or print the APP.

### Budget Utilization Report

Shows campus budget utilization summaries. It includes total campus budget, total utilized amount, overall utilization percentage, offices at risk, and utilization per office with quarter and office filters.

## Procurement Office

### Dashboard

Shows procurement workload and status summaries. It displays total PRs received, PRs in progress, PRs completed this month, overdue PRs, PR counts per office by status, urgent PRs past target quarter, and a quick filter for PRs due this month.

### Purchase Request Management

Lists all uploaded PRs with office, PR number, item, date submitted, and current status. Selecting a PR opens details including PDF preview, OCR-extracted fields, status update controls, remarks, and a timestamped activity log.

### Procurement Status Tracking

Tracks all approved items currently being processed by Procurement. The page shows office, item, approved amount, target quarter, current status, and remarks. Procurement staff can update item status and remarks, with delayed items clearly highlighted.

### Procurement Reports

Provides quarterly procurement accomplishment reporting. It shows targeted versus procured items per office per quarter, completed purchases, delayed items, and reasons or remarks. Reports can be exported to PDF or printed.

## Chancellor

### Campus Monitoring Dashboard

Provides campus-wide procurement and utilization oversight. It shows total APP items, procured items, pending items, overdue items, campus-wide utilization percentage, procurement status per office across Q1 to Q4, utilization forecasts, risk flags, office rankings, and overdue PR alerts.

### Budget Approval

Shows proposals endorsed by the Finance Office for Chancellor action. The Chancellor can view full proposal details, item costs, justifications, market scoping references, Finance remarks, and approval trail. The Chancellor can approve or return proposals with required remarks.

### Procurement Reports

Shows campus-wide procurement accomplishment reports, targeted versus procured items per office, year-end budget utilization summaries, and delayed items grouped by office. Reports can be exported to PDF or printed.

## Vice Chancellor

### Division Dashboard

Shows procurement and utilization monitoring for offices under the assigned Vice Chancellor. It displays division offices, total APP items, procured count, division utilization percentage, utilization rate per office, delayed or overdue office flags, and pending PR summaries.

### Division Procurement Status

Shows APP items under the assigned division grouped by office. It displays item, target quarter, current status, and remarks. Selecting an item shows the procurement activity timeline, Procurement Office remarks, and a field for Vice Chancellor follow-up notes.

### Division Performance Report

Summarizes office performance within the division. It shows office utilization rates, total APP items, procured count, pending count, utilization percentage, best-performing office, lowest-performing office, and export or print options.

## End-to-End Workflow

1. Office Head / Dean creates a budget proposal.
2. AI market scoping generates supplier price references for proposed items.
3. The proposal is submitted to the Finance Office.
4. Finance reviews the proposal and either endorses it or returns it with remarks.
5. The Chancellor approves the endorsed proposal or returns it with remarks.
6. Finance consolidates approved proposals into the Annual Procurement Plan.
7. Office Head / Dean uploads a signed purchase request.
8. Procurement Office processes the PR and updates procurement status.
9. Chancellor and Vice Chancellor monitor procurement progress, risks, and delays.
10. Reports and budget utilization summaries are generated for monitoring and decision support.

## Important Scope Notes

- Supplier websites are not system users.
- Market scoping results are references only.
- AI recommendations are advisory only.
- PRISM does not replace PhilGEPS.
- PRISM does not handle online purchasing, payment, contract awarding, or full liquidation.
