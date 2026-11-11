# TechVerse 2027 — Tech Fest Event Registration

**Live site (AWS EC2):** http://13.54.236.227/ &nbsp;·&nbsp; **Admin panel:** http://13.54.236.227/admin/

> Open with `http://` (no SSL certificate is configured for the bare IP address).

An online **event registration** web application for a college technical fest, built with
**HTML5, CSS3, JavaScript, jQuery and PHP**. Participants fill in a 3-step registration form;
on clicking **Register Now** the data is validated, saved to a database by PHP, and a formatted
**event pass** (with registration number and QR code) is displayed in the browser, ready to print
or save as PDF. Organisers use a password-protected **admin panel** to view, search, export and
delete registrations.

## Front end vs back end

| | Files | Job |
|---|---|---|
| **Front end** (browser) | `index.php` (form markup), `assets/css/style.css`, `assets/js/app.js`, `assets/js/qr.js` | Shows the form, validates as you type, limits event choices, manages team members, calculates the fee live, sends the data with jQuery AJAX and shows the event pass |
| **Back end** (server) | `submit.php`, `includes/*.php`, `admin/*.php` | Re-validates & sanitises everything, recalculates the fee, stores the registration in SQLite, generates the registration number, renders the pass, runs the admin panel |
| **Database** | `data/techfest.db` (created automatically) | One row per registration |

**Flow:** form → jQuery `$.ajax` POST → `submit.php` → validate → `INSERT` into SQLite →
JSON containing the pass HTML → jQuery displays it and draws the QR code.

## Features

**Registration form**
- Countdown timer to the fest, 3-step wizard with progress bar
- Step 1 *Participant*: photo (preview), name, email, mobile, date of birth (age shown), gender
- Step 2 *College*: college, roll number, department, year of study, city, state
- Step 3 *Events & Team*: 8 event cards (choose up to 3, the rest lock automatically), solo/team
  entry with add/remove team members, T-shirt size, food preference, hostel stay, **live fee
  calculator**, declaration
- Inline validation messages; works without JavaScript too (plain form POST)

**Event pass (result)**
- Ticket-style pass: participant photo, college, team, chosen events with date & venue,
  registration number, scannable QR code, fee
- Full details tables and fee breakdown; print stylesheet for **Print / Save as PDF**

**Admin panel** (`/admin/`)
- Password login, stats (total, today, fees expected, most popular event)
- Registrations-per-event bar chart, search, filter by event, pagination
- View full pass, delete (CSRF-protected), CSV export for Excel

**Security**
- All output escaped (XSS-safe); only whitelisted choices accepted; fee calculated on the server
- PDO prepared statements; photo checked by real MIME type, never written to disk
- Database and include files blocked from the web (`.htaccess`)

## Project structure

```
index.php                     registration form (front end)
submit.php                    form handler: JSON for AJAX, full page otherwise
includes/config.php           fest name, dates, events, fees, choice lists (edit me!)
includes/functions.php        validation, sanitising, pass rendering
includes/pass-template.php    event pass markup
includes/db.php               SQLite database (PDO)
includes/auth.php             admin login + CSRF protection
includes/layout.php           shared header (hero + countdown) and footer
admin/                        admin panel: login, list, view, delete, export, logout
assets/css/style.css          all styling (responsive, dark mode, print)
assets/js/app.js              jQuery: wizard, validation, events, team, fee, AJAX
assets/js/qr.js               draws QR codes on the pass
assets/vendor/                jQuery (fallback copy) and qrcode-generator (MIT)
data/                         SQLite database file (not committed, not web-accessible)
Dockerfile, render.yaml       cloud hosting
```

To change the fest name, dates, events or fees, edit `includes/config.php` — the form, the
validation and the admin panel all update automatically.

## Run locally

```bash
ADMIN_PASSWORD=choose-a-password php -S localhost:8000
```

Form: http://localhost:8000 — Admin: http://localhost:8000/admin/

The admin panel stays disabled until `ADMIN_PASSWORD` is set.

## Host on the cloud

### Option A — Render (free)
1. Sign in at https://dashboard.render.com with GitHub.
2. **New + → Blueprint**, pick this repository (branch `main`), click **Apply**.
3. Wait for the build (~3–5 min); your URL appears at the top of the service page.
4. Admin password: service → **Environment** → `ADMIN_PASSWORD` (generated; you can change it).

Free Render services sleep when idle (first visit takes ~30–50 s) and have no permanent disk,
so registrations are wiped on restart/redeploy. Fine for a demo; use AWS below to keep data.

### Option B — AWS EC2 (data is kept)
The script `deploy/aws-ec2.sh` installs Docker and Git, downloads this repo, builds and starts
the app on port 80, and keeps registrations in a Docker volume.

1. EC2 → **Launch instance**: Amazon Linux 2023, `t3.micro`/`t2.micro`, tick **Allow HTTP traffic
   from the internet**.
2. Under **Advanced details → User data**, paste the whole of `deploy/aws-ec2.sh`, changing
   `ChangeMe-Admin-2027` to your own admin password. Launch.
3. Wait ~5 minutes, then open `http://<instance-public-ip>/` (form) and `/admin/`.

Or run it by hand on an existing instance (EC2 Instance Connect):

```bash
sudo dnf install -y git && git clone https://github.com/Mohamed-Ali-4/WTassignment.git && sudo bash WTassignment/deploy/aws-ec2.sh 'YourPassword'
```

To update after pushing new code, run `sudo bash /opt/techfest/deploy/aws-ec2.sh 'YourPassword'`
on the instance; registrations are kept.
