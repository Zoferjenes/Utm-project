# Arcade FixIt

Independent Arcade group project for SCSM2223 Cross-Platform Application Development.

FixIt is an on-demand local home services marketplace for customers, verified service providers, and administrators.

## Stack

- Frontend: Vue 3, Vite, Vue Router, Pinia, Axios
- Backend: PHP Slim 4, PDO, MySQL, Firebase JWT
- Mobile: Capacitor Android wrapper
- Security: hashed passwords, JWT auth, role-based access, prepared statements, server validation

## Project Structure

```text
Utm-project/
├── backend/
│   ├── public/
│   ├── src/
│   ├── composer.json
│   └── .env.example
├── database/
│   ├── schema.sql
│   └── seed.sql
├── docs/
│   ├── API.md
│   ├── DEPLOYMENT.md
│   ├── DEMO_SCRIPT.md
│   └── ERD.md
└── frontend/
    ├── src/
    ├── package.json
    └── capacitor.config.json
```

## Demo Accounts

All seeded users use password:

```text
password
```

| Role | Email |
| --- | --- |
| Admin | admin@fixit.test |
| Customer | customer@fixit.test |
| Provider | provider@fixit.test |

## Local Setup

### 1. Database

Create and seed the database in Laragon/MySQL:

```bash
mysql -u root < database/schema.sql
mysql -u root < database/seed.sql
```

### 2. Backend

```bash
cd backend
composer install
copy .env.example .env
php -S localhost:8000 -t public
```

### 3. Frontend

```bash
cd frontend
npm install
npm run dev
```

Open:

```text
http://localhost:5173
```

## Required Demo Flow

1. Login as customer and browse service categories/providers.
2. Create a booking/job request.
3. Login as provider, update profile, accept/update the job, and set final cost.
4. Login as admin and verify providers/manage categories.
5. Login as customer again to confirm final cost and submit review.
6. Show REST API data coming from MySQL.
7. Build production frontend and sync Capacitor Android wrapper.

See [docs/DEMO_SCRIPT.md](docs/DEMO_SCRIPT.md).
Deployment and mobile proof commands are in [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md).
GitHub publish steps are in [docs/GIT_PUBLISH.md](docs/GIT_PUBLISH.md).
