# PRISM Project Structure Guide

This guide explains where the important parts of the PRISM Laravel application are located and how future developers should organize new work.

## 1. Project Structure Summary

The project follows a standard Laravel structure with PRISM-specific controllers, Blade views, Tailwind assets, routes, and database files.

- `app/` contains backend PHP application code.
- `app/Http/Controllers/` contains the PRISM role controllers.
- `app/Models/` contains Eloquent models.
- `routes/` contains web and console route definitions.
- `resources/` contains Blade views, Tailwind CSS entry files, and JavaScript.
- `public/` contains publicly served files, images, and Vite build output.
- `database/` contains migrations, seeders, factories, and the local SQLite database file.
- `docs/` contains project documentation.
- `tests/` contains Laravel automated tests.
- `config/`, `bootstrap/`, `storage/`, and `vendor/` are standard Laravel framework folders.

## 2. Frontend / UI Files

PRISM user interface files are located mainly under `resources/`.

- `resources/views/` contains Blade templates.
- `resources/views/prism/layout.blade.php` is the shared PRISM layout with the top user switch bar, sidebar, and main content area.
- `resources/views/prism/` contains PRISM role-based pages.
- `resources/views/components/prism/status-badge.blade.php` contains the shared status badge component.
- `resources/css/app.css` is the Tailwind CSS entry file and theme token location.
- `resources/js/app.js` contains PRISM frontend behavior, including dynamic table actions, filters, timelines, status updates, print actions, and mock interaction behavior.
- `resources/js/bootstrap.js` contains Laravel frontend bootstrap setup.
- `public/` contains files served directly by the web server.
- `public/images/bsu-seal.png` contains the BSU seal used by the PRISM layout.
- `public/build/` contains generated Vite build assets.

## 3. Pages / Screens

Role-based PRISM screens are stored under `resources/views/prism/`.

Office Head / Dean pages are in:

- `resources/views/prism/office-head/dashboard.blade.php`
- `resources/views/prism/office-head/budget-proposal.blade.php`
- `resources/views/prism/office-head/my-proposals.blade.php`
- `resources/views/prism/office-head/purchase-requests.blade.php`

Finance Office pages are in:

- `resources/views/prism/finance-office/dashboard.blade.php`
- `resources/views/prism/finance-office/proposal-review.blade.php`
- `resources/views/prism/finance-office/annual-procurement-plan.blade.php`
- `resources/views/prism/finance-office/budget-utilization-report.blade.php`

Procurement Office pages are in:

- `resources/views/prism/procurement-office/dashboard.blade.php`
- `resources/views/prism/procurement-office/purchase-request-management.blade.php`
- `resources/views/prism/procurement-office/procurement-status-tracking.blade.php`
- `resources/views/prism/procurement-office/procurement-reports.blade.php`

Chancellor pages are in:

- `resources/views/prism/chancellor/dashboard.blade.php`
- `resources/views/prism/chancellor/budget-approval.blade.php`
- `resources/views/prism/chancellor/procurement-reports.blade.php`

Vice Chancellor pages are in:

- `resources/views/prism/vice-chancellor/dashboard.blade.php`
- `resources/views/prism/vice-chancellor/division-procurement-status.blade.php`
- `resources/views/prism/vice-chancellor/division-performance-report.blade.php`

Shared or fallback PRISM views:

- `resources/views/prism/layout.blade.php`
- `resources/views/prism/placeholder.blade.php`

## 4. Backend Files

Backend logic is located under `app/` and `routes/`.

- `app/Http/Controllers/PrismOfficeHeadController.php` handles Office Head / Dean pages.
- `app/Http/Controllers/PrismFinanceOfficeController.php` handles Finance Office pages.
- `app/Http/Controllers/PrismProcurementOfficeController.php` handles Procurement Office pages.
- `app/Http/Controllers/PrismChancellorController.php` handles Chancellor pages.
- `app/Http/Controllers/PrismViceChancellorController.php` handles Vice Chancellor pages.
- `app/Http/Controllers/Controller.php` is the base Laravel controller.
- `app/Models/User.php` is the current Eloquent user model.
- `routes/web.php` defines page routes for PRISM.
- `database/migrations/` contains database migration files.
- `database/seeders/` contains database seeders.
- `database/factories/` contains model factories.

This project currently does not include `routes/api.php`.

## 5. Routes

Page navigation and backend route mapping are defined in `routes/web.php`.

Current PRISM route groups:

- `/office-head` uses `PrismOfficeHeadController`.
- `/finance-office` uses `PrismFinanceOfficeController`.
- `/procurement-office` uses `PrismProcurementOfficeController`.
- `/chancellor` uses `PrismChancellorController`.
- `/vice-chancellor` uses `PrismViceChancellorController`.

To add a new page:

1. Create a Blade view under the correct role folder in `resources/views/prism/`.
2. Add a controller method in the matching PRISM controller if the page needs data.
3. Register a route in the matching route group in `routes/web.php`.
4. Add the new page to the role sidebar navigation data in the controller.
5. Pass the required data from the controller to the view.
6. Test the route in the browser and with automated tests when possible.

## 6. Database

Database-related files are located in `database/` and `app/Models/`.

- `database/migrations/` defines database tables and schema changes.
- `database/seeders/DatabaseSeeder.php` is the main database seeder.
- `database/factories/UserFactory.php` defines test or seed data factories.
- `database/database.sqlite` is the local SQLite database file.
- `app/Models/` contains Eloquent models.

Future procurement records, budget proposals, users, purchase requests, APP items, reports, approval timelines, remarks, activity logs, and status updates should be represented through models and migrations instead of hard-coded data.

## 7. Assets

Project assets are stored in these locations:

- `resources/css/app.css` contains the Tailwind CSS entry and PRISM theme tokens.
- `resources/js/app.js` contains role page interactions and UI behavior.
- `resources/js/bootstrap.js` contains Laravel frontend bootstrap setup.
- `public/images/` stores public image assets such as `bsu-seal.png`.
- `public/build/` stores compiled Vite CSS and JavaScript output.
- `public/favicon.ico`, `public/index.php`, and `public/robots.txt` are public web entry/support files.

Uploaded or publicly accessible files should be stored through Laravel storage/public-disk conventions when upload persistence is implemented. Public assets that must be served directly can live under `public/`.

## 8. What Not to Forget

- Role-based access
- User switch/navigation
- Proposal status badges
- PR upload handling
- Market scoping data display
- APP consolidation
- Procurement status updates
- Approval timeline
- Remarks/activity logs
- Reports/export buttons
- Dashboard summaries
- Responsive Tailwind layout

## 9. How to Add a New Page

1. Create the view/page file in the correct folder under `resources/views/prism/`.
2. Add a route in `routes/web.php`.
3. Add a controller method in the matching controller if the page needs backend data.
4. Add the sidebar link in the role navigation data.
5. Connect data from the model/controller to the Blade view.
6. Test page access, role navigation, layout behavior, and any JavaScript interactions.

## 10. Developer Notes

- Use Tailwind CSS only.
- Do not use Bootstrap.
- Keep UI role-based.
- Keep Finance focused on proposal review, APP, and budget utilization.
- Keep Procurement focused on PR processing and procurement status updates.
- Keep Chancellor views campus-wide.
- Keep Vice Chancellor views division-level.
- Keep Office Head / Dean views limited to own-office proposals, PRs, and procurement status.
- Preserve existing route names, IDs, and `data-*` hooks when updating views that are controlled by JavaScript.
- Keep status badges consistent across all roles.
