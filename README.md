# Contact Form

![PHP](https://img.shields.io/badge/php-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white)
![Docker](https://img.shields.io/badge/docker-%230db7ed.svg?style=for-the-badge&logo=docker&logoColor=white)
![MySQL](https://img.shields.io/badge/mysql-%234479A1.svg?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/bootstrap-%238511FA.svg?style=for-the-badge&logo=bootstrap&logoColor=white)
![CI](https://github.com/ShaneKolkoto/contactUform/actions/workflows/ci.yml/badge.svg)

A small PHP contact form that validates input, stores submissions in MySQL,
and lists what's been saved. No framework, no Composer, no build step —
just PHP, MySQL, and a light layer of CSS/JS on top.

Built as a portfolio piece to show how I structure a small PHP app:
dependency-free, deployment-agnostic, and tested.

---

## Screenshots

| Contact form | Entries list |
|---|---|
| _(add `docs/screenshots/contact.png`)_ | _(add `docs/screenshots/entries.png`)_ |

| Mobile contact | Error state |
|---|---|
| _(add `docs/screenshots/contact-mobile.png`)_ | _(add `docs/screenshots/errors.png`)_ |

---

## Getting started

### Clone the repository

```bash
git clone https://github.com/ShaneKolkoto/contactUform.git
cd contactUform
```

### Prerequisites

| Requirement | Version | Notes |
|---|---|---|
| PHP | 8.1+ | Uses typed properties, `readonly`, `never`, match expressions |
| MySQL | 5.7+ or MariaDB 10.4+ | InnoDB, utf8mb4 |
| PHP extensions | `pdo_mysql`, `mbstring` | Both default on most hosts |
| Web server | Apache or Nginx | Apache recommended for `.htaccess` support |
| Docker (optional) | Desktop 4+ or Engine 20+ | Only needed for the Docker path |

Verify what you have:

```bash
php -v
php -m | grep -E 'pdo_mysql|mbstring'
```

---

## What's in here

- **Contact form** — name, email, South African phone number, message
- **Validation** — server-side (source of truth) and client-side (UX polish)
- **Entries page** — lists everything that's been submitted, newest first
- **MySQL storage** — schema + seed data in `db/init.sql`
- **Test suite** — zero-dependency runner; unit tests + HTTP tests
- **Docker** — one-command local setup; also runs on XAMPP and shared hosting
- **CI** — lint, unit tests, HTTP tests, and a Docker smoke test on every PR

---

## Running the project

Three supported setups. Pick whichever matches your environment.

### Option 1 — Docker (recommended)

Everything runs in containers. No local PHP or MySQL install needed.

```bash
cp .env.example .env
docker compose up -d --build
```

Wait ~20 seconds for MySQL's first-boot init, then open:

| URL | What |
|---|---|
| http://localhost:8080 | The app |
| http://localhost:8081 | phpMyAdmin (`appuser` / `apppassword`) |

If you have `make` installed (see the [Makefile](#makefile) section below),
you can use shortcuts: `make up`, `make logs`, `make test`, etc.

---

### Option 2 — XAMPP / WAMP / MAMP

1. Copy the project folder into your web root:
   - XAMPP on Windows: `C:\xampp\htdocs\contactUform\`
   - MAMP on macOS: `/Applications/MAMP/htdocs/contactUform/`

2. Import the schema via phpMyAdmin:
   - Open `http://localhost/phpmyadmin`
   - SQL tab → paste the contents of `db/init.sql` → Go

3. Open `http://localhost/contactUform/`

The root `.htaccess` rewrites requests into `public/`, so no virtual host
is required. Default DB credentials in `config.php` already match a fresh
XAMPP install (`root` with an empty password).

---

### Option 3 — Shared hosting / cPanel

Upload layout:

```
public_html/               ← upload the contents of /public here
├── index.php
├── submit.php
├── entries.php
└── assets/

contactUform/              ← upload the rest here, one level above public_html
├── bootstrap.php
├── config.php
├── src/
├── templates/
└── db/
```

Then in `public/*.php`, change:

```php
require __DIR__ . '/../bootstrap.php';
```

to:

```php
require __DIR__ . '/../contactUform/bootstrap.php';
```

Create a database via cPanel, import `db/init.sql` via phpMyAdmin, and edit
`config.php` with your hosting credentials (or set the `DB_*` environment
variables if your host supports them).

---

## Working with Docker

This section covers the day-to-day Docker commands you'll actually use
after `docker compose up -d --build` has been run once.

### Container status

```bash
docker compose ps
```

Shows all three services: `web` (Apache + PHP), `db` (MySQL), and
`phpmyadmin`. The `db` service shows `(healthy)` when MySQL is ready to
accept connections.

### Logs

```bash
docker compose logs -f web        # tail PHP + Apache access/error logs
docker compose logs -f db         # tail MySQL logs
docker compose logs -f            # tail everything
docker compose logs --tail 50 web # show last 50 lines and exit
```

PHP errors (`error_log()` calls) and uncaught exceptions show up in the
`web` logs — that's where to look when a page returns a 500.

### Opening a shell

```bash
docker compose exec web bash      # shell into the web container
docker compose exec db bash       # shell into the MySQL container
```

Once inside, you can run `php`, `composer` (not installed), `ls`, `cat`,
and so on as if it were a normal Linux shell.

### Restarting services

```bash
docker compose restart web        # restart just the web container
docker compose restart            # restart everything
docker compose up -d --force-recreate web  # recreate after compose file changes
```

**Note:** `restart` does not pick up changes to `docker-compose.yml`. If
you edit the compose file (add a volume, change an env var, etc.), use
`up -d --force-recreate` instead.

### Stopping

```bash
docker compose stop               # stop containers, keep them
docker compose down               # stop + remove containers (DB data kept in volume)
docker compose down -v            # stop + remove + wipe DB volume
```

After `docker compose down -v`, the next `docker compose up -d` re-runs
`db/init.sql` from scratch — which reseeds the 10 rows.

### Rebuilding after Dockerfile changes

```bash
docker compose build web          # rebuild the web image
docker compose build --no-cache web  # rebuild from scratch (no layer cache)
docker compose up -d --force-recreate web
```

You **don't** need to rebuild for normal code changes — the volumes mount
your local files. Rebuild only when you change `docker/php/Dockerfile`.

---

### Database commands

Three ways to talk to MySQL in this project. All are useful — pick whichever
matches what you're doing.

#### 1. phpMyAdmin (GUI)

Open `http://localhost:8081` and log in as `appuser` / `apppassword`.

Best for: browsing the schema, running ad-hoc queries, eyeballing data.

#### 2. MySQL shell (CLI)

```bash
docker compose exec db mysql -uappuser -papppassword contact_form
```

Inside the shell:

```sql
SHOW TABLES;
DESCRIBE entries;
SELECT COUNT(*) FROM entries;
SELECT id, name, phone, created_at FROM entries ORDER BY id DESC LIMIT 5;
EXIT;
```

Best for: running SQL without leaving the terminal, debugging a specific
query.

#### 3. One-shot queries

For a single query, without opening a shell:

```bash
docker compose exec db mysql -uappuser -papppassword contact_form -e "
  SELECT id, name, phone, created_at FROM entries ORDER BY id DESC LIMIT 5;
"
```

Use this in scripts, tests, or when you just want to check something quickly.

---

### Seeding and resetting data

**Check the current row count:**

```bash
docker compose exec db mysql -uappuser -papppassword contact_form -e "
  SELECT COUNT(*) AS total FROM entries;
"
```

**Reseed from scratch** (drops and recreates the table):

```bash
docker compose exec -T db mysql -uappuser -papppassword contact_form < db/init.sql
```

The `-T` flag is required — without it, stdin redirect (`<`) doesn't work
through `docker compose exec`.

**Delete all rows but keep the schema** (useful for testing the empty state
on the entries page):

```bash
docker compose exec db mysql -uappuser -papppassword contact_form -e "
  TRUNCATE TABLE entries;
"
```

Then restore the seed data with the `init.sql` command above.

**Backup the current data to a file:**

```bash
docker compose exec db mysqldump -uappuser -papppassword contact_form entries > backup.sql
```

**Restore from a backup file:**

```bash
docker compose exec -T db mysql -uappuser -papppassword contact_form < backup.sql
```

---

### Common troubleshooting

**"Port 8080 is already in use"**

Something else is listening on 8080. Change `WEB_PORT` in `.env` (e.g. to
`8090`) and restart:

```bash
docker compose down
docker compose up -d
```

The app will be at `http://localhost:8090` instead.

**"Access denied for user 'appuser'"**

The MySQL volume was initialized with a different password. Either:

1. Use the password from `.env` (check `MYSQL_PASSWORD`)
2. Or wipe and rebuild: `docker compose down -v && docker compose up -d`

**"Class 'App\...' not found"**

The file in `src/` is either missing, empty (0 bytes), or has a syntax error.
Check:

```bash
docker compose exec web ls -la /var/www/html/src/
docker compose exec web php -l /var/www/html/src/SomeClass.php
```

**Changes not showing up**

If you edited PHP/CSS/JS and the browser still shows old content:

1. Hard refresh (`Ctrl+Shift+R` / `Cmd+Shift+R`)
2. If the container can't see the files: `docker compose exec web ls /var/www/html/public/`
3. If files are missing, check `docker-compose.yml` has the right volume mount

---

## Makefile

The project includes a `Makefile` — a set of shortcuts for the Docker
commands you'd otherwise type in full. It's optional (the app works fine
without it), but it saves keystrokes on the commands you run most often.

### Why a Makefile?

1. **Discoverability.** Run `make` (or `make help`) and you get a list of
   available commands with descriptions. New contributors don't have to
   read the Docker section to know what to do.
2. **Consistency.** Everyone runs the same commands with the same flags.
   No "did you run it with `-d` or without?" conversations.
3. **Fewer typos.** `make test` is easier to remember than
   `docker compose exec web php tests/run.php`.

### Installing `make`

`make` is preinstalled on macOS and most Linux distributions. On Windows:

- **Git Bash** — usually has it via `mingw32-make`
- **WSL2** — `sudo apt install make` inside your WSL environment
- **Chocolatey** — `choco install make`

If you don't have it and don't want to install it, just use the full
`docker compose` commands from the sections above.

### Available targets

```bash
make help          # List all available targets
```

| Target | What it does |
|---|---|
| `make up` | Start all containers (`docker compose up -d`) |
| `make down` | Stop all containers, keep DB data |
| `make logs` | Tail web + db logs |
| `make shell` | Open a bash shell in the web container |
| `make db` | Open a MySQL shell as `appuser` |
| `make test` | Run the unit test suite |
| `make test-http` | Run the HTTP test suite |
| `make test-all` | Run both test suites |
| `make seed` | Reload `db/init.sql` (wipes and re-seeds) |
| `make rebuild` | Rebuild the web image without cache |
| `make fresh` | Wipe DB volume and rebuild from scratch |
| `make clean` | Same as `make fresh` — alias |

### Example workflow

A typical session looks like:

```bash
# First time
cp .env.example .env
make up            # start the stack
make logs          # watch it boot

# While developing
# ... edit files ... the volumes pick up changes automatically

# Run tests
make test          # unit
make test-http     # HTTP
make test-all      # both at once

# Check something in the DB
make db            # opens MySQL shell
# ... SELECT * FROM entries; ...
# EXIT;

# Done for the day
make down
```

### If `make` isn't available

Every target maps to a plain Docker command. For example:

| `make` target | Equivalent command |
|---|---|
| `make up` | `docker compose up -d` |
| `make down` | `docker compose down` |
| `make test` | `docker compose exec web php tests/run.php` |
| `make test-http` | `docker compose exec web php tests/http/run.php` |
| `make logs` | `docker compose logs -f web db` |
| `make shell` | `docker compose exec web bash` |
| `make db` | `docker compose exec db mysql -uappuser -papppassword contact_form` |
| `make seed` | `docker compose exec -T db mysql -uappuser -papppassword contact_form < db/init.sql` |
| `make fresh` | `docker compose down -v && docker compose up -d` |

So you're never blocked — the Makefile is a convenience layer, not a
dependency.

---

## Configuration

Config lives in `config.php`. Values resolve in this order:

1. **Environment variables** — highest priority (Docker, PaaS, some hosts)
2. **`.env` file** — parsed by `bootstrap.php` on boot
3. **Hard-coded defaults** — matched to XAMPP out of the box

| Variable | Default | Purpose |
|---|---|---|
| `APP_ENV` | `production` | Shown in some error output |
| `APP_DEBUG` | `false` | When `true`, display errors in the browser |
| `DB_HOST` | `127.0.0.1` | MySQL host |
| `DB_PORT` | `3306` | MySQL port |
| `DB_NAME` | `contact_form` | Database name |
| `DB_USER` | `root` | MySQL user |
| `DB_PASS` | *(empty)* | MySQL password |

**Never commit `.env`.** It's gitignored. `.env.example` is the template —
copy it and edit.

---

## Project layout

```
contactUform/
├── bootstrap.php             # env loading, autoloader, session, helpers
├── config.php                # config with env-var override
├── docker-compose.yml        # web + db + phpmyadmin
├── Makefile                  # shortcuts for Docker commands
├── .htaccess                 # rewrites into public/, blocks sensitive files
├── .env.example
│
├── db/
│   └── init.sql              # schema + 10 rows of seed data
│
├── docker/
│   └── php/Dockerfile        # php:8.3-apache + pdo_mysql + mbstring
│
├── public/                   # web root — the only thing served over HTTP
│   ├── index.php
│   ├── submit.php
│   ├── entries.php
│   └── assets/
│       ├── css/
│       │   ├── style.css
│       │   ├── contact-form.css
│       │   └── entries.css
│       ├── js/
│       │   └── app.js
│       ├── favicon.ico
│       ├── favicon-16x16.png
│       ├── favicon-32x32.png
│       ├── apple-touch-icon.png
│       ├── android-chrome-192x192.png
│       ├── android-chrome-512x512.png
│       └── site.webmanifest
│
├── src/
│   ├── Database.php          # PDO connection factory
│   ├── Validator.php         # all input rules + SA phone normalization
│   └── EntryRepository.php   # SQL for the entries table
│
├── templates/
│   ├── header.php
│   ├── footer.php
│   ├── flash.php             # success/error banner
│   └── components/
│       ├── form-field.php    # label + input + hint + error
│       ├── alert.php         # success/error/warning/info
│       ├── button.php        # primary/secondary with optional spinner
│       └── empty-state.php   # icon + message + optional CTA
│
└── tests/
    ├── run.php               # unit test runner
    ├── TestCase.php          # assertion helpers
    ├── bootstrap.php
    ├── ValidatorTest.php
    ├── EntryRepositoryTest.php
    └── http/
        ├── run.php           # HTTP test runner
        ├── HttpTestCase.php  # server lifecycle + HTTP client
        └── SubmitHttpTest.php
```

---

## Running the tests

Two test suites. Run both before pushing.

### Unit tests

```bash
docker compose exec web php tests/run.php
# or: make test
```

Or, without Docker:

```bash
php tests/run.php
```

Output looks like:

```
Contact Form — Test Suite
============================================================

EntryRepositoryTest
  [OK]  all clamps out of range arguments (2 assertions)
  [OK]  all respects limit and offset (3 assertions)
  ...

ValidatorTest
  [OK]  sa phone number normalization (8 assertions)
  [OK]  sa phone rejects junk (9 assertions)
  ...

────────────────────────────────────────────────────────────
18 tests, 74 assertions in 0.183s
All passed
```

Exit code is `0` on success, `1` on any failure — so it works in CI.

### HTTP tests

```bash
docker compose exec web php tests/http/run.php
# or: make test-http
```

Output looks like:

```
HTTP Test Suite
============================================================

SubmitHttpTest
  [OK]  csrf token cannot be reused after success (6 assertions)
  [OK]  get request redirects without inserting (3 assertions)
  [OK]  missing csrf token rejects submission (4 assertions)
  [OK]  tampered csrf token rejects submission (3 assertions)
  [OK]  valid submission inserts row and redirects (6 assertions)
  [OK]  validation failure redirects without inserting (3 assertions)

────────────────────────────────────────────────────────────
6 tests, 25 assertions in 1.8s
All passed
```

These tests spawn PHP's built-in server on a random port, then make real
HTTP requests with cookie support. They prove `submit.php` works end-to-end:
CSRF check → validation → DB insert → 303 redirect.

**Note:** the HTTP tests require the DB to be seeded. If you've truncated
`entries` while testing manually, reload:

```bash
make seed
# or:
docker compose exec -T db mysql -uappuser -papppassword contact_form < db/init.sql
```

### What the tests cover

- **`ValidatorTest`** — every field rule, every accepted SA phone format, every rejected input shape
- **`EntryRepositoryTest`** — create/find/list/count against the live MySQL, wrapped in transactions that roll back so the DB stays clean
- **`SubmitHttpTest`** — `submit.php` end-to-end: successful insert, GET rejection, CSRF validation, DB rollback on error

### What they don't cover (yet)

- Rendered HTML from the templates (no DOM assertions)
- Browser behaviour of `app.js` (no JS tests)
- Full accessibility audit

These are listed under "Things I'd improve" below.

---

## Design decisions

A few choices that might raise questions on a first read:

**Why no framework?**
The brief specified PHP without one. It's also a good exercise — most of
what a framework provides is 200 lines of glue code.

**Why no Composer?**
Zero runtime dependencies keeps shared-hosting deployment simple: upload
files, done. The autoloader in `bootstrap.php` is 12 lines. Composer could
be added for dev-only tooling (PHPUnit, PHPStan) without touching runtime.

**Why is `src/` outside `public/`?**
Only `public/` is the web root, so business logic, config, and `.env` are
physically unreachable over HTTP. On shared hosting where everything sits
in `public_html/`, the root `.htaccess` blocks the sensitive extensions
instead.

**Why store phone numbers in E.164?**
`+27821234567` is unambiguous and country-code agnostic. Formatting for
display happens in the template (`082 123 4567`). If the app ever needs to
send SMS or integrate with a dialler, the data is already in the right shape.

**Why `VARBINARY(16)` for IP addresses?**
Fits both IPv4 (4 bytes) and IPv6 (16 bytes) efficiently, and it's the
"correct" MySQL type for this. Stored via `inet_pton()`, read via `inet_ntop()`.

**Why so few comments?**
Comments explain *why*, not *what*. The code says what it does; comments
say why a decision was made (e.g. why `LIMIT` needs `PDO::PARAM_INT`).

**Why a hand-rolled test runner instead of PHPUnit?**
The project is deliberately Composer-free. Each runner is ~100 lines,
prints a familiar format, and exits with the correct code. Swapping in
PHPUnit later is a 20-minute job if needed.

**Why two test suites instead of one?**
Unit tests run in 0.1s and catch logic errors. HTTP tests run in 2s and
catch glue errors (routing, CSRF, redirects). Merging them means every
`php tests/run.php` invocation pays the HTTP cost — so they stay separate.
CI runs both in parallel jobs.

---

## Things I'd improve with more time

Honest list, in priority order:

### 1. Rate limiting
No protection against someone spamming the form. A simple approach: track
submissions per IP in a `rate_limit` table, reject when a threshold is
exceeded within a window.

### 2. Admin authentication
The entries page is currently public. In production, at minimum:
- Basic auth via `.htpasswd`, or
- Session-based login with a bcrypt-hashed password in env

### 3. Search / filter on entries
Filter by name or email. Requires adding a `WHERE` clause with a bound
search term.

### 4. Honeypot or CAPTCHA on the form
A hidden field that bots fill in but humans don't is a cheap win. Full
CAPTCHA (hCaptcha, Turnstile) is heavier but more effective.

### 5. Accessibility audit
The form uses proper labels, `aria-invalid`, and `role="alert"` on the
flash message. What's missing:
- End-to-end keyboard-only walkthrough
- Screen reader test (NVDA / VoiceOver)
- Colour contrast check on the muted-text palette

### 6. Static analysis
PHPStan at level 6+ would catch type issues the tests miss. Would need
Composer as a dev dependency only.

### 7. Structured logging
Currently logs go to stderr. In production I'd want JSON logs with request
IDs, so failures can be traced across services.

### 8. Email notification
When a new entry is saved, send an email to the site owner. Would use a
transactional provider (Postmark, SES) rather than `mail()`.

### 9. Export
CSV or JSON export of entries for the site owner. A single
`GET /entries.php?format=csv` endpoint.

### 10. Cross-browser testing
Only tested in Chrome + Firefox during development. Would want Safari and
mobile Safari before considering the UI done.

---

## Security notes

- **SQL injection** — every query uses prepared statements, no string interpolation
- **XSS** — all output goes through `e()` (`htmlspecialchars` with `ENT_QUOTES`)
- **CSRF** — form includes a session-backed token; `submit.php` verifies with `hash_equals()` and rotates the token after success
- **Session fixation** — session ID rotates on successful submission
- **Cookie flags** — `HttpOnly`, `SameSite=Lax`, `Secure` when HTTPS is detected
- **Password handling** — n/a; the app has no user accounts
- **File uploads** — the app rejects any request containing `$_FILES`
- **Sensitive files** — `.env`, `.sql`, config files blocked via `.htaccess` and by living outside `public/`
- **Error leakage** — DB errors log server-side but never appear in the browser

## Author

Shane Morne Kolkoto. Feedback welcome.