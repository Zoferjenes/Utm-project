# Deployment and Capacitor Proof

This file is for the final video proof requested in class.

## Local Production Build

```bash
cd frontend
npm install
npm run build
npm run preview
```

Preview URL:

```text
http://localhost:4173
```

## Backend Deployment Notes

The Slim backend needs PHP 8.1+, Composer, MySQL, and a web root pointing to:

```text
backend/public
```

Production checklist:

```text
APP_ENV=production
APP_DEBUG=false
DB_HOST=<production-mysql-host>
DB_NAME=<production-database>
DB_USER=<production-user>
DB_PASS=<production-password>
JWT_SECRET=<long-random-secret>
CORS_ALLOWED_ORIGINS=<production-frontend-url>
```

Run:

```bash
cd backend
composer install --no-dev --optimize-autoloader
```

Import:

```bash
mysql -u <user> -p < database/schema.sql
mysql -u <user> -p < database/seed.sql
```

## Frontend Deployment Notes

Set the production API URL:

```text
VITE_API_BASE_URL=https://your-api-domain.example
```

Build:

```bash
cd frontend
npm run build
```

Deploy the `frontend/dist` folder to Netlify, Vercel, Firebase Hosting, or any static host.

## Capacitor Android Proof

```bash
cd frontend
npm install
npm run build
npx cap add android
npx cap sync android
npx cap open android
```

In Android Studio, show:

```text
android app project opens
app name: Arcade FixIt
package id: my.utm.arcade.fixit
web assets loaded from dist
```

## Suggested Video Order

1. Open production frontend URL.
2. Login as customer and create a booking.
3. Login as provider and move status to completed.
4. Confirm final cost and submit review as customer.
5. Show backend API JSON response.
6. Show `npm run build` success.
7. Show `npx cap sync android` success.
8. Open Android project or emulator.
