# Arcade FixIt Demo Script

## 1. Problem

Students and young adults often need small home services like plumbing, cleaning, AC servicing, or electrical repair. Finding reliable providers through word of mouth or random chats is not transparent and has no tracking, review, or verification workflow.

## 2. Solution

FixIt connects customers with verified local providers through a cross-platform web app and mobile-ready Capacitor build.

## 3. Demo Flow

### Customer Flow

1. Login as `customer@fixit.test` / `password`.
2. Open Services.
3. Browse service categories and verified providers.
4. Create a booking/job request.
5. Open Bookings and show the job status.
6. After provider completion, confirm the final cost and submit a review.

### Provider Flow

1. Login as `provider@fixit.test` / `password`.
2. Open Bookings.
3. Accept or update a customer job status.
4. Save the final job cost.
5. Open Provider Profile and update profile/category/KYC reference.
6. Show provider dashboard statistics.

### Admin Flow

1. Login as `admin@fixit.test` / `password`.
2. Open Admin.
3. Verify pending provider.
4. Add a service category.
5. Show all jobs from the admin account.

## 4. Technical Points To Mention

- Vue 3 frontend uses router, Pinia store, and Axios API client.
- Slim 4 backend exposes REST JSON endpoints.
- MySQL stores users, providers, categories, jobs, reviews, messages, and status logs.
- Passwords are hashed with `password_hash`.
- JWT is sent in the Authorization header.
- Role checks protect customer/provider/admin actions.
- PDO prepared statements protect against SQL injection.
- Frontend and backend both validate inputs.
- Provider KYC upload accepts only PDF/JPG/PNG and limits file size.

## 5. Production/Mobile Proof

Telegram class instruction on 19 June 2026 says the final proof should show:

- application deployed in production environment
- application wrapped into mobile application using Capacitor
- video evidence of the setup

Suggested proof recording:

1. Show production frontend URL.
2. Login and create a booking.
3. Show API URL responding with JSON.
4. Run `npm run build`.
5. Run `npx cap sync android`.
6. Open Android Studio or emulator/device build if available.

For exact commands, see [DEPLOYMENT.md](DEPLOYMENT.md).
