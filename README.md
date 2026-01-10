# UniPrint (Laravel 12) — Setup & Run Guide

This repository is a Laravel 12 application.

## Requirements

- PHP `^8.2`
- Composer 2
- Node.js `>= 18` + npm
- A database (PostgreSQL recommended)

You can run the built-in checker:

```bash
php scripts/check-requirements.php
```

### Required PHP extensions

At minimum, you must have:

- `pdo`
- `mbstring`
- `openssl`
- `json`
- `tokenizer`
- `xml`
- `curl`
- `zip`
- `bcmath`
- `fileinfo`

Database driver extensions (install at least one):

- **PostgreSQL**: `pdo_pgsql` (and usually `pgsql`)
- **MySQL**: `pdo_mysql`

If you use Supabase/PostgreSQL and see `could not find driver`, it means `pdo_pgsql` is missing.

## First-time setup (fresh clone)

### 1) Install dependencies

```bash
composer install
npm install
```

### 2) Create `.env`

```bash
cp .env.example .env
php artisan key:generate
```

### 3) Configure database

Edit `.env`:

- `DB_CONNECTION` (`pgsql` recommended)
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

### 4) Migrate + seed

```bash
php artisan migrate --seed
```

### 5) Build frontend assets

```bash
npm run build
```

## Run the app

### Option A — Simple run (recommended for debugging)

```bash
php artisan optimize:clear
php artisan serve --host=127.0.0.1 --port=8000 --no-reload
```

Open:

- `http://127.0.0.1:8000`

### Option B — Full dev (Vite)

In two terminals:

Terminal 1:
```bash
php artisan optimize:clear
php artisan serve --host=127.0.0.1 --port=8000 --no-reload
```

Terminal 2:
```bash
npm run dev
```

## Common issues / troubleshooting

### 1) `could not find driver (Connection: pgsql ...)`

Cause: `pdo_pgsql` is not installed/enabled on that machine.

Quick check:

```bash
php -m | grep -E 'pdo_pgsql|pgsql' || true
```

Install the correct package for your OS (examples):

- Ubuntu/Debian: `sudo apt install php-pgsql`
- Arch: `sudo pacman -S php-pgsql`

After installing, restart your PHP process / rerun `php artisan serve`.

### 2) App boots but fails after moving to another PC

If config is cached from a previous environment, Laravel may still use old `.env` values.

Run:

```bash
php artisan optimize:clear
```

### 3) `127.0.0.1 refused to connect` after a refresh

This is not a normal Laravel 500 response. It means the web server process stopped.

Do this to capture the real error:

1. Start the server with:
   ```bash
   php artisan serve --host=127.0.0.1 --port=8000 --no-reload
   ```
2. Keep that terminal open.
3. Refresh the failing page.
4. Copy/paste the **last lines** printed in that terminal.

If the process exits with a signal (e.g. `SIGILL` / `Segmentation fault`), it is usually a PHP build/extension crash. Common workarounds:

- Use a different PHP version (often `8.3.x` is more stable than `8.4.x` for some extension combos)
- Disable JIT / PCRE JIT in your `php.ini` (if enabled)

### 4) Sessions / cache / queue portability

For local development and portability (especially when DB access is unstable), use:

- `SESSION_DRIVER=file`
- `CACHE_STORE=file`
- `QUEUE_CONNECTION=sync`

These are set in `.env.example` as the recommended defaults.

## Notes

- Business user docs:
  - `BUSINESS_USER_GUIDE.md`
  - `BUSINESS_USER_CREDENTIALS.md`
  - `QUICK_START_BUSINESS_USERS.md`

