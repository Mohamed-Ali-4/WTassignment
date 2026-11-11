# Online Admission Portal — Application / Registration Form

A web application built with **HTML5, CSS3, JavaScript, jQuery and PHP**. Applicants fill in
a 3-step registration form; on clicking **Submit**, the data is validated and sent to PHP, which
saves it in a database and returns a formatted acknowledgement (with an application number) that
is displayed in the browser and can be printed or saved as PDF. Staff use a password-protected
**admin panel** to see, search, export and delete applications.

## Front end vs back end

| | Files | Job |
|---|---|---|
| **Front end** (runs in the browser) | `index.html`, `assets/css/style.css`, `assets/js/app.js` | Shows the form, validates as you type, sends data to the server with jQuery AJAX, displays the result |
| **Back end** (runs on the server) | `submit.php`, `includes/*.php`, `admin/*.php` | Re-validates & sanitises, stores in SQLite, generates the application number, renders the acknowledgement, admin panel |
| **Database** | `data/applications.db` (created automatically) | One row per application |

Flow: browser form → jQuery `$.ajax` POST → `submit.php` → validate → `INSERT` into SQLite →
JSON with result HTML → jQuery shows it. Admin: `admin/login.php` → `admin/index.php` (list,
search, stats) → `view.php` / `delete.php` / `export.php`.

## Features

| Layer | What it does |
|---|---|
| **HTML** (`index.html`) | Semantic 3-step form: personal, academic, contact details; photo upload; radio, select, checkbox, textarea, date inputs |
| **CSS** (`assets/css/style.css`) | Modern responsive design, animated stepper and progress bar, dark mode, mobile layout, print stylesheet |
| **JavaScript / jQuery** (`assets/js/app.js`) | Step navigation, live validation with inline messages, photo preview, age calculation, character counter, AJAX submission, animated result display |
| **PHP** (`submit.php`, `includes/`) | Server-side validation & sanitisation (XSS-safe output, whitelisted options, real MIME check on uploads), application number, formatted result page |
| **Database** (`includes/db.php`) | SQLite via PDO with prepared statements; table created automatically |
| **Admin panel** (`admin/`) | Password login, stats (total, today, average score, top program), search & program filter, pagination, view full application, delete, CSV export; CSRF-protected actions |

Works without JavaScript too: the form then posts normally and PHP renders a full result page.

## Project structure

```
index.html                 the form
submit.php                 form handler (JSON for AJAX, full page otherwise)
includes/functions.php     validation, sanitising, rendering helpers
includes/result-template.php  acknowledgement card markup
includes/db.php            SQLite database (PDO)
includes/auth.php          admin login + CSRF protection
includes/layout.php        shared page header/footer
admin/                     admin panel: login, list, view, delete, export, logout
data/                      SQLite database file (not committed, not web-accessible)
assets/css/style.css
assets/js/app.js
assets/vendor/jquery-3.7.1.min.js   local fallback if the CDN is unreachable
Dockerfile, render.yaml    cloud hosting
```

## Run locally

```bash
ADMIN_PASSWORD=choose-a-password php -S localhost:8000
```

Form: http://localhost:8000 — Admin: http://localhost:8000/admin/ (log in with the password above).
The admin panel stays disabled until `ADMIN_PASSWORD` is set.

## Host on the cloud

### Option A — Render (free, deploys straight from GitHub)
1. Sign in at https://render.com with GitHub.
2. **New → Blueprint**, pick this repository (it reads `render.yaml`), click **Apply**.
   *(Or **New → Web Service** → this repo → Runtime **Docker** → Free plan.)*
3. After the build finishes you get a public URL like `https://online-admission-portal.onrender.com`.

4. Admin password: Render generates one. Find it under your service → **Environment** →
   `ADMIN_PASSWORD` (you can change it there). Admin panel: `https://<your-app>.onrender.com/admin/`.

Free Render services sleep after inactivity; the first request after that takes ~30–50 s.
**Note:** the free plan has no permanent disk, so saved applications are wiped whenever the
service restarts, sleeps or redeploys. That's fine for a demo; for permanent storage add a Render
persistent disk (paid) mounted at e.g. `/var/data` and set `DB_PATH=/var/data/applications.db`.

### Option B — InfinityFree / 000webhost-style free PHP hosting
1. Create a free account and a hosting site (e.g. https://infinityfree.com).
2. Open the File Manager (or FTP) and upload everything **into `htdocs/`**:
   `index.html`, `submit.php`, `.htaccess`, `includes/`, `assets/`, `admin/`, `data/`.
3. Shared hosts usually can't set environment variables, so add this line to `.htaccess`
   to set the admin password: `SetEnv ADMIN_PASSWORD your-password`
   (needs the host to support SQLite / `pdo_sqlite`).
4. Visit your site URL.

### Option C — Any Docker host (Railway, Fly.io, Google Cloud Run)
```bash
docker build -t admission-portal .
docker run -p 8080:80 -e ADMIN_PASSWORD=choose-a-password admission-portal
```
The container honours the `$PORT` variable the platform provides.
