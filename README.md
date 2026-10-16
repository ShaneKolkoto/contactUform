# Contact Form
![PHP](https://img.shields.io/badge/php-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white)
![Docker](https://img.shields.io/badge/docker-%230db7ed.svg?style=for-the-badge&logo=docker&logoColor=white)
![MySQL](https://img.shields.io/badge/mysql-%234479A1.svg?style=for-the-badge&logo=mysql&logoColor=white)


A small PHP contact form that validates input, stores submissions in MySQL,
and lists what's been saved. No framework, no Composer, no build step —
just PHP, MySQL, and a light layer of CSS/JS on top.

Built as a portfolio piece to show how I structure a small PHP app:
dependency-free, deployment-agnostic, and tested.

---

## Getting started

### Clone the repository

```bash
git clone https://github.com/ShaneKolkoto/contactUform.git
cd contactUform
```

Replace the URL with your actual repo URL. Then pick one of the three
setup options below — Docker is the fastest if you have it installed.

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
- **Test suite** — zero-dependency runner, 68 assertions
- **Docker** — one-command local setup; also runs on XAMPP and shared hosting

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

Useful commands:

```bash
docker compose logs -f web       # tail PHP + Apache logs
docker compose down              # stop (keeps DB data)
docker compose down -v           # stop and wipe DB (forces init.sql re-run)
docker compose exec web bash     # shell into the web container
```

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
│       ├── css/style.css
│       └── js/app.js
│
├── src/
│   ├── Database.php          # PDO connection factory
│   ├── Validator.php         # all input rules + SA phone normalization
│   └── EntryRepository.php   # SQL for the entries table
│
├── templates/
│   ├── header.php
│   ├── footer.php
│   └── flash.php             # success/error banner component
│
└── tests/
    ├── run.php               # the runner
    ├── TestCase.php          # assertion helpers
    ├── bootstrap.php
    ├── ValidatorTest.php
    └── EntryRepositoryTest.php
```

---

## Running the tests

```bash
docker compose exec web php tests/run.php
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

### What the tests cover

- **`ValidatorTest`** — every field rule, every accepted SA phone format, every rejected input shape
- **`EntryRepositoryTest`** — create/find/list/count against the live MySQL, wrapped in transactions that roll back so the DB stays clean

### What they don't cover (yet)

- The HTTP layer (`submit.php`, CSRF, redirects)
- The templates (no rendered HTML assertions)
- Browser behaviour (no JS tests)

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
The project is deliberately Composer-free. The runner is ~80 lines, prints
a familiar format, and exits with the correct code. Swapping in PHPUnit
later is a 20-minute job if needed.

---

## Things I'd improve with more time

Honest list, in priority order:

### 1. HTTP-layer tests
Currently `submit.php` is only exercised manually. I'd add tests that:
- POST valid data → 303 redirect → row in DB
- POST with missing CSRF → redirect with error flash
- POST with invalid fields → redirect preserving old input
- POST with uploaded files → 400

This needs a small HTTP test harness (maybe using PHP's built-in server
with `php -S`).

### 2. Rate limiting
No protection against someone spamming the form. A simple approach: track
submissions per IP in a `rate_limit` table, reject when a threshold is
exceeded within a window. Or use a sliding-window token bucket in the session.

### 3. Admin authentication
The entries page is currently public. In production, at minimum:
- Basic auth via `.htpasswd`, or
- Session-based login with a bcrypt-hashed password in env

### 4. Pagination on the entries page
`EntryRepository::all()` already supports `limit`/`offset`, but `entries.php`
doesn't yet expose page controls. Easy win.

### 5. Search / filter on entries
Filter by name or email. Requires adding a `WHERE` clause with a bound
search term.

### 6. Honeypot or CAPTCHA on the form
A hidden field that bots fill in but humans don't is a cheap win. Full
CAPTCHA (hCaptcha, Turnstile) is heavier but more effective.

### 7. Accessibility audit
The form uses proper labels, `aria-invalid`, and `role="alert"` on the
flash message. What's missing:
- End-to-end keyboard-only walkthrough
- Screen reader test (NVDA / VoiceOver)
- Colour contrast check on the muted-text palette

### 8. Static analysis
PHPStan at level 6+ would catch type issues the tests miss. Would need
Composer as a dev dependency only.

### 9. Structured logging
Currently logs go to stderr. In production I'd want JSON logs with request
IDs, so failures can be traced across services.

### 10. Email notification
When a new entry is saved, send an email to the site owner. Would use a
transactional provider (Postmark, SES) rather than `mail()`.

### 11. Export
CSV or JSON export of entries for the site owner. A single
`GET /entries.php?format=csv` endpoint.

### 12. Cross-browser testing
Only tested in Chrome + Firefox during development. Would want Safari and
mobile Safari before considering the UI done.

---

## Security notes

- **SQL injection** — every query uses prepared statements, no string interpolation
- **XSS** — all output goes through `e()` (`htmlspecialchars` with `ENT_QUOTES`)
- **CSRF** — form includes a session-backed token; `submit.php` verifies with `hash_equals()`
- **Session fixation** — session ID rotates on successful submission
- **Cookie flags** — `HttpOnly`, `SameSite=Lax`, `Secure` when HTTPS is detected
- **Password handling** — n/a; the app has no user accounts
- **File uploads** — the app rejects any request containing `$_FILES`
- **Sensitive files** — `.env`, `.sql`, config files blocked via `.htaccess` and by living outside `public/`
- **Error leakage** — DB errors log server-side but never appear in the browser

---

## License

MIT — do what you like with it.

---

## Author

Shane Morne Kolkoto. Feedback welcome.