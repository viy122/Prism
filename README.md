# PRISM

PRISM (Procurement, Requisition, Inventory, and Scoping Management) is a budget and procurement workflow system for BSU offices, roles, and approval chains.

## Repository layout

- [`prism/`](prism/) — the Laravel application (backend, Blade views, migrations, tests). This is where day-to-day feature work happens. See [`docs/PROJECT_STRUCTURE_GUIDE.md`](docs/PROJECT_STRUCTURE_GUIDE.md) for where things live inside it.
- [`microservice/`](microservice/) — Python service used for price matching/market scoping data.
- [`docs/`](docs/) — project documentation (structure guide, database structure, system flow, work plan).
- [`postman/`](postman/) — Postman collections, environments, and API specs for manual/API testing.

## Local setup

### Laravel app (`prism/`)

```
cd prism
composer install
npm install
copy .env.example .env   # if you don't already have a .env
php artisan key:generate
php artisan migrate
php artisan storage:link
npm run build
```

The app is served locally via XAMPP/Apache. A vhost is configured to serve it at **http://prism.test** directly from `prism/public` (see `C:/xampp/apache/conf/extra/httpd-vhosts.conf`). This requires a one-time hosts file entry:

```
127.0.0.1 prism.test
```

Add that line to `C:\Windows\System32\drivers\etc\hosts` (requires an elevated/Administrator text editor), then restart Apache from the XAMPP Control Panel. Until that entry is added, the app is still reachable at `http://localhost/Prism/prism/public`.

### Microservice (`microservice/`)

```
cd microservice
pip install -r requirements.txt
start.bat
```

### Everything at once

Run [`start-all.bat`](start-all.bat) from the repo root to launch MySQL, Apache, the price API, and the matcher microservice together.
