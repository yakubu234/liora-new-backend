# Project Setup Guide

## Overview

This project is split into 2 parts:

1. `frontend/`
   This is the public-facing standalone frontend application for the main domain.

2. Laravel app root
   This is the backend API and admin dashboard application.

The intended deployment structure is:

- Main website frontend domain: `https://www.lioracityeventcenter.com/`
- API and admin subdomain: `https://dashboard.lioracityeventcenter.com/`

In other words:

- `frontend/` should be served on the main domain.
- The Laravel app in the project root should be served on the subdomain.
- The frontend calls the backend API on the subdomain.

## Folder Structure

- `frontend/index.html`
  Main public website page.

- `frontend/gallery.html`
  Public gallery page.

- `public/`
  Laravel public directory for the backend/admin app.

- `routes/web.php`
  Contains both admin routes and public API routes such as:
  - `/public/calendar-availability`
  - `/public/slider-images`
  - `/public/gallery-images`
  - `/public/testimonials`
  - `/public/messages`

## Server Requirements

- PHP 8.2 or higher
- Composer
- MySQL
- Node.js and npm
- A web server such as Apache or Nginx

## Local Setup

1. Clone or copy the project into your server/workspace.

2. Install PHP dependencies:

```bash
composer install
```

3. Install frontend/build dependencies:

```bash
npm install
```

4. Create the environment file:

```bash
cp .env.example .env
```

On Windows, if needed, create `.env` manually by copying `.env.example`.

5. Generate the Laravel app key:

```bash
php artisan key:generate
```

6. Update the `.env` values.

7. Create the database and import/migrate the schema:

```bash
php artisan migrate
```

If you already have a production SQL dump, you may import that instead of using a fresh migration-only setup.

8. Build frontend assets for Laravel admin if needed:

```bash
npm run build
```

9. Start the Laravel app locally:

```bash
php artisan serve
```

## Production Deployment Structure

### Main Domain

Point the main domain below to the contents of the `frontend/` folder:

- `https://www.lioracityeventcenter.com/`

This frontend is meant to run as a standalone site.

### Dashboard and API Subdomain

Point the subdomain below to the Laravel app's `public/` directory:

- `https://dashboard.lioracityeventcenter.com/`

This subdomain serves:

- Admin login
- Admin dashboard
- Public JSON/API endpoints consumed by the frontend

## Laravel `.env` Configuration

Below are the important values that must be set in `.env`.

### App URLs

```env
APP_NAME="Liora City Admin"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://dashboard.lioracityeventcenter.com
FRONTEND_URLS=https://www.lioracityeventcenter.com,https://www.lioracityeventcenter.com/
```

Notes:

- `APP_URL` must point to the backend/admin subdomain.
- `FRONTEND_URLS` must include the allowed frontend origin(s).
- `FRONTEND_URLS` is used by the public message endpoint for CORS handling.

### Database

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

### Mail

General Laravel mail settings can still be set in `.env`:

```env
MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

Important note:

- The website contact email notification currently uses the SMTP credentials stored in the `mailer_creds` database table from the admin panel.
- So the actual live contact-form email sending depends on the SMTP details saved inside the dashboard, not only on the `.env` mail values.
- The `.env` mail values can still be kept for fallback/general Laravel mail configuration.

### Optional Website Settings

```env
WEBSITE_SLIDER_IMAGE_COUNT=3
WEBSITE_SLIDER_VIDEO_COUNT=1
```

These control the expected slider media counts used by the public frontend API.

## Admin SMTP Setup

After the Laravel backend is running:

1. Open the dashboard on:
   `https://dashboard.lioracityeventcenter.com/`

2. Log in to the admin panel.

3. Go to the SMTP settings page.

4. Set:
   - SMTP email username
   - SMTP password
   - Recipient email address

The contact form email notification uses these saved values from the `mailer_creds` table.

## Frontend Configuration

The frontend is plain HTML and JavaScript inside the `frontend/` folder. It is not automatically reading Laravel `.env`, so you must point it to the correct backend API base URL.

### `frontend/index.html`

This file contains:

```js
const API_BASE_URL = window.LIORACITY_API_BASE_URL || 'http://localhost:8000';
```

and then builds these endpoints from it:

- `/public/calendar-availability`
- `/public/slider-images`
- `/public/gallery-images`
- `/public/testimonials`
- `/public/messages`

For production, the API base URL should resolve to:

```js
https://dashboard.lioracityeventcenter.com
```

### `frontend/gallery.html`

This file contains:

```js
const API_BASE_URL = window.LIORACITY_API_BASE_URL || 'http://localhost:8000';
```

and uses it for:

- `/public/gallery-images`

For production, the API base URL should also resolve to:

```js
https://dashboard.lioracityeventcenter.com
```

## How to Set the Frontend API Constant

You have 2 options.

### Option 1: Hardcode the production value

In both `frontend/index.html` and `frontend/gallery.html`, change:

```js
const API_BASE_URL = window.LIORACITY_API_BASE_URL || 'http://localhost:8000';
```

to:

```js
const API_BASE_URL = window.LIORACITY_API_BASE_URL || 'https://dashboard.lioracityeventcenter.com';
```

### Option 2: Define the global constant before the page script

Add this before the main script block in both `frontend/index.html` and `frontend/gallery.html`:

```html
<script>
  window.LIORACITY_API_BASE_URL = 'https://dashboard.lioracityeventcenter.com';
</script>
```

This option is cleaner if you want to keep the local fallback in the file.

## Links Already Hardcoded in Frontend

The frontend already contains login links pointing to:

```text
https://dashboard.lioracityeventcenter.com/
```

These appear in:

- `frontend/index.html`
- `frontend/gallery.html`

If the dashboard subdomain changes later, those links should be updated too.

## CORS Note

The public contact API uses the frontend origin allow-list from:

```env
FRONTEND_URLS=
```

If the frontend domain is not listed correctly, the contact form may fail due to cross-origin restrictions.

Recommended production value:

```env
FRONTEND_URLS=https://www.lioracityeventcenter.com,https://www.lioracityeventcenter.com/
```

If you also use non-www or staging domains, add them as comma-separated values.

## Common Commands

Install dependencies:

```bash
composer install
npm install
```

Generate app key:

```bash
php artisan key:generate
```

Run migrations:

```bash
php artisan migrate
```

Run local development server:

```bash
php artisan serve
```

Build frontend/admin assets:

```bash
npm run build
```

Run tests:

```bash
php artisan test
```

## Final Deployment Checklist

1. Serve `frontend/` on `https://www.lioracityeventcenter.com/`
2. Serve Laravel `public/` on `https://dashboard.lioracityeventcenter.com/`
3. Set `APP_URL=https://dashboard.lioracityeventcenter.com`
4. Set `FRONTEND_URLS=https://www.lioracityeventcenter.com,https://www.lioracityeventcenter.com/`
5. Set the correct database credentials in `.env`
6. Run migrations or import the production database
7. Save SMTP credentials in the dashboard admin SMTP settings
8. Set `API_BASE_URL` in both `frontend/index.html` and `frontend/gallery.html`
9. Confirm the contact form, gallery, testimonials, slider, and availability calendar can all reach the subdomain API

