# Telnet intern attendance

Laravel 13 API and React manager UI for off-grid intern attendance. The phone app is a separate project. Work is tracked in `task.md`. Contributors follow `CONTRIBUTING.md`.

## Requirements

- PHP 8.3 or newer, with the `pdo_pgsql` extension
- Composer
- Node 24 and npm
- Docker, for Postgres and Redis

This machine does not have the PHP Redis extension. The app uses the Predis client instead.

## Local setup

```bash
docker compose up -d
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
composer run dev
```

The app is at `http://localhost:8000`.

`docker compose` starts Postgres 16 on port 5432 and Redis 7 on port 6379. The database name, user, and password in `.env.example` match that Compose file. They are local placeholders. Replace `SYNC_DEV_TOKEN` before you share a machine.

`composer run dev` starts the PHP server, the queue listener, and Vite together.

## Checks

```bash
php artisan test
```

Tests use an in-memory SQLite database. The running app uses Postgres.

## Layout

- `routes/web.php` — Inertia pages
- `app/Models/Model.php` — base model for later UUID tables
- `app/Models/User.php` — manager account
- `docs/decisions/0001-prototype-stack.md` — locked stack
