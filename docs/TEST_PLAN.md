# Manual Test Plan

Use this checklist before the final demo recording.

## Setup

```bat
mysql -u root < database\schema.sql
mysql -u root < database\seed.sql
cd backend
composer install
copy .env.example .env
php -S localhost:8000 -t public
```

Second terminal:

```bat
cd frontend
npm install
npm run dev
```

## Auth and Roles

| Test | Expected Result |
| --- | --- |
| Login as `customer@fixit.test` | Customer sees Dashboard, Services, Bookings |
| Login as `provider@fixit.test` | Provider sees Provider Profile and Bookings |
| Login as `admin@fixit.test` | Admin sees Admin Control |
| Open `/admin` as customer | Redirects back to dashboard |

## Services

| Test | Expected Result |
| --- | --- |
| Search `Skudai` | Providers in Skudai/Taman Universiti appear |
| Filter category `Cleaning` | Sara Cleaner appears |
| Set max rate `45` | Providers above RM45 are hidden |
| Click Book | User moves to Bookings screen |

## Booking Workflow

| Test | Expected Result |
| --- | --- |
| Customer creates booking | New job appears as requested |
| Provider accepts job | Status changes to accepted and timeline records it |
| Provider sets final cost | Customer sees final cost awaiting confirmation |
| Customer confirms cost | Final cost badge changes to confirmed |
| Customer submits review | Job status changes to reviewed |

## Messages and Timeline

| Test | Expected Result |
| --- | --- |
| Open View Activity on a job | Timeline and messages load |
| Customer sends message | Message appears on the job |
| Provider replies | Reply appears with provider role/name |
| Unrelated role tries direct API access | Backend returns not found/not allowed |

## Admin

| Test | Expected Result |
| --- | --- |
| Admin opens Overview | Total users, pending providers, and active jobs cards load from `/admin/overview` |
| Admin opens Provider Verification | All providers are listed with approve/reject controls |
| Admin verifies Omar, John, or Sarah Quick Sparks | Provider becomes visible in verified provider browse list |
| Admin adds category | New category appears in Services |
| Admin edits category | Updated name/description appears in Services |
| Admin deactivates category | Category disappears from active category list |

## Mobile Proof

```bat
cd frontend
npm run build
npx cap add android
npx cap sync android
npx cap open android
```

Expected result: Android Studio opens the `Arcade FixIt` Android project.
