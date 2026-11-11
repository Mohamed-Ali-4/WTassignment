# Online Admission Portal — Application / Registration Form

A web application built with **HTML5, CSS3, JavaScript, jQuery and PHP**. Applicants fill in
a 3-step registration form; on clicking **Submit**, the data is validated and sent to PHP, which
returns a formatted acknowledgement (with an application number) that is displayed in the browser
and can be printed or saved as PDF.

## Features

| Layer | What it does |
|---|---|
| **HTML** (`index.html`) | Semantic 3-step form: personal, academic, contact details; photo upload; radio, select, checkbox, textarea, date inputs |
| **CSS** (`assets/css/style.css`) | Modern responsive design, animated stepper and progress bar, dark mode, mobile layout, print stylesheet |
| **JavaScript / jQuery** (`assets/js/app.js`) | Step navigation, live validation with inline messages, photo preview, age calculation, character counter, AJAX submission, animated result display |
| **PHP** (`submit.php`, `includes/`) | Server-side validation & sanitisation (XSS-safe output, whitelisted options, real MIME check on uploads), application number, formatted result page |

Works without JavaScript too: the form then posts normally and PHP renders a full result page.

## Project structure

```
index.html                 the form
submit.php                 form handler (JSON for AJAX, full page otherwise)
includes/functions.php     validation, sanitising, rendering helpers
includes/result-template.php  acknowledgement card markup
assets/css/style.css
assets/js/app.js
assets/vendor/jquery-3.7.1.min.js   local fallback if the CDN is unreachable
Dockerfile, render.yaml    cloud hosting
```

## Run locally

```bash
php -S localhost:8000
```

Open http://localhost:8000

## Host on the cloud

### Option A — Render (free, deploys straight from GitHub)
1. Sign in at https://render.com with GitHub.
2. **New → Blueprint**, pick this repository (it reads `render.yaml`), click **Apply**.
   *(Or **New → Web Service** → this repo → Runtime **Docker** → Free plan.)*
3. After the build finishes you get a public URL like `https://online-admission-portal.onrender.com`.

Free Render services sleep after inactivity; the first request after that takes ~30–50 s.

### Option B — InfinityFree / 000webhost-style free PHP hosting
1. Create a free account and a hosting site (e.g. https://infinityfree.com).
2. Open the File Manager (or FTP) and upload everything **into `htdocs/`**:
   `index.html`, `submit.php`, `.htaccess`, `includes/`, `assets/`.
3. Visit your site URL.

### Option C — Any Docker host (Railway, Fly.io, Google Cloud Run)
```bash
docker build -t admission-portal .
docker run -p 8080:80 admission-portal
```
The container honours the `$PORT` variable the platform provides.
