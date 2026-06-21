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
│   ├── ERD.md
│   ├── TEAM_CONTRIBUTIONS.md
│   └── TEST_PLAN.md
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
| Pending Provider | omar@fixit.test |
| Pending Provider | john@fixit.test |
| Pending Provider | sarah.spark@fixit.test |

## Team Contribution Summary

| Member | Poll Role | Main Contribution |
| --- | --- | --- |
| Fiyandha Ceyllo Fathurrahman | Member 3: Mobile, Tokens & Global Validation | Full-stack integration, repository setup, JWT/login flow, validation, docs, testing, deployment/mobile preparation |
| Ishal Siddique | Member 2: Server Routing & Database | Project coordination, proposal preparation, backend/database direction, early task split, schema/ERD direction, presentation support |
| Md Rejwanul Islam Rejve | Member 1: Web Interface & Forms | Web interface/form direction, admin frontend mock, admin dashboard/category UI direction |

See [docs/TEAM_CONTRIBUTIONS.md](docs/TEAM_CONTRIBUTIONS.md) for the detailed evidence-friendly breakdown.

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
3. Use the job timeline/messages to show customer-provider communication.
4. Login as provider, update profile, accept/update the job, reply to messages, and set final cost.
5. Login as admin and verify providers/manage categories.
6. Login as customer again to confirm final cost and submit review.
7. Show REST API data coming from MySQL.
8. Build production frontend and sync Capacitor Android wrapper.

See [docs/DEMO_SCRIPT.md](docs/DEMO_SCRIPT.md).
Deployment and mobile proof commands are in [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md).
GitHub publish steps are in [docs/GIT_PUBLISH.md](docs/GIT_PUBLISH.md).
Manual testing coverage is in [docs/TEST_PLAN.md](docs/TEST_PLAN.md).
Team contribution notes are in [docs/TEAM_CONTRIBUTIONS.md](docs/TEAM_CONTRIBUTIONS.md).
