# SmartCampus - Backend Architecture & API Overview

## Architecture Overview
This project now uses a PHP + MySQL backend with PDO.

- Core DB helper: `api/db.php`
- Persistent storage: MySQL tables from `scm.sql` (database name: `project`)
- Auth/session model: PHP session stores current logged-in user, while business data is persisted in MySQL

The frontend still communicates through `fetch` calls to `api/*.php` and expects JSON responses.

---

## Environment Configuration

Backend DB connection settings are read from environment variables with defaults:

- `DB_HOST` (default `127.0.0.1`)
- `DB_PORT` (default `3306`)
- `DB_NAME` (default `project`)
- `DB_USER` (default `root`)
- `DB_PASS` (default empty)

Google token audience validation:

- `GOOGLE_CLIENT_ID` (recommended to set explicitly on server)

---

## API Endpoints

All endpoints are located in the `api/` directory.

### 1. Authentication (`api/auth.php`)
- Handles `login`, `google_login`, and `logout`.
- Uses Google `tokeninfo` verification for Google sign-in.
- Persists users into `Users` table via shared DB helper.
- Keeps role mapping for configured Google emails.

### 2. Notifications (`api/notifications.php`)
- `GET`: Returns notification list and unread count for current session user.
- `POST`: Supports `mark_read` and `mark_all_read`.
- Backed by `notifications` table.

### 3. Notices (`api/notices.php`)
- `GET`: Returns all notices.
- `POST`: Faculty/Admin can create or delete notices.
- Writes notifications for impacted roles.

### 4. Complaints (`api/complaints.php`)
- `GET`: Students see own complaints; Faculty/Admin see all.
- `POST`: Students create complaints; Faculty/Admin can update complaint status.
- Emits notifications for submit/update events.

### 5. Dashboard (`api/dashboard.php`)
- Returns role-specific stats.
- For Faculty/Admin, includes complaint trend and feedback distribution data.

### 6. Analytics (`api/analytics.php`)
- Returns monthly complaint totals and resolved counts for chart rendering.

### 7. Feedback (`api/feedback.php`)
- `POST`: Stores feedback ratings in `Feedback` table.
- `GET`: Returns submitted feedback records.

### 8. Lost & Found (`api/lost-found.php`)
- `GET`: Returns reported items.
- `POST`: Stores new reports in `LostFound` table.

### 9. Settings (`api/settings.php`)
- `GET`: Reads campus settings.
- `POST`: Upserts settings row.

### 10. Users (`api/users.php`)
- `GET`: Lists users from `Users` table.
- `POST` (`action=update`): Updates user profile fields.

---

## Deployment Notes

- Import `scm.sql` before running APIs.
- Ensure PHP has PDO MySQL extension enabled.
- Keep API response structure stable to avoid frontend regressions.
- Keep secrets (DB password, OAuth secrets) in environment variables, not in source code.