# Production Deployment: Railway + Netlify

Arcade FixIt is deployed as a monorepo:

- Railway: PHP Slim API and MySQL database
- Netlify: Vue/Vite frontend

Do not deploy course exports, WhatsApp/Telegram data, screenshots, ZIP archives, local `.env` files, `node_modules`, `vendor`, or `dist`.

## Backend: Railway

Railway uses the root `Dockerfile` and `railway.json`.

Required Railway variables for the API service:

```text
APP_ENV=production
APP_DEBUG=false
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_NAME=${{MySQL.MYSQLDATABASE}}
DB_USER=${{MySQL.MYSQLUSER}}
DB_PASS=${{MySQL.MYSQLPASSWORD}}
JWT_SECRET=<strong-random-secret>
JWT_ISSUER=arcade-fixit
JWT_TTL=86400
CORS_ALLOWED_ORIGINS=https://your-netlify-site.netlify.app
```

The backend also supports Railway's native MySQL variable names as fallbacks:

```text
MYSQLHOST
MYSQLPORT
MYSQLDATABASE
MYSQLUSER
MYSQLPASSWORD
```

On container startup, `backend/bin/init-database.php` checks for the required FixIt tables. It imports `database/production_schema.sql` and `database/production_seed.sql` only when none of the required tables exist. Existing initialized databases are skipped.

Public endpoints to verify:

```text
GET /
GET /health
GET /categories
POST /auth/login
```

Expected `/health` shape:

```json
{
  "status": "ok",
  "database": "connected",
  "service": "Arcade FixIt API"
}
```

## Frontend: Netlify

Netlify uses `netlify.toml`:

```text
base directory: frontend
build command: npm run build
publish directory: dist
```

The root `netlify.toml` stores the publish path as `frontend/dist` because the file itself lives at the repository root.

Set this Netlify environment variable:

```text
VITE_API_BASE_URL=https://your-railway-api-domain.up.railway.app
```

`frontend/public/_redirects` contains the SPA fallback:

```text
/* /index.html 200
```

This keeps `/login`, `/services`, `/bookings`, `/admin`, and `/profile` working after refresh.

## Local Verification Commands

Backend:

```bash
cd backend
composer install
php -S 127.0.0.1:8000 -t public public/index.php
```

Frontend:

```bash
cd frontend
npm ci
npm run build
npm run dev -- --host 127.0.0.1 --port 5173
```

API checks:

```bash
curl http://127.0.0.1:8000/health
curl http://127.0.0.1:8000/categories
```

Demo accounts all use password `password`:

```text
admin@fixit.test
customer@fixit.test
provider@fixit.test
```

## Capacitor Android Proof

After the public Railway API and Netlify frontend pass verification, build with the production API URL:

```bash
cd frontend
npm run build
npx cap add android
npx cap sync android
npx cap open android
```

Keep `frontend/capacitor.config.json` using:

```json
{
  "webDir": "dist"
}
```

Do not point Capacitor to the Netlify website as a remote server; package the compiled Vue app from `dist`.
