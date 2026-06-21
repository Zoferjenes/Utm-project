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
4. Filter by category, location/search text, and max rate.
5. Create a booking/job request.
6. Open Bookings and show the job status timeline.
7. Send a job message to the provider.
8. After provider completion, confirm the final cost and submit a review.

### Provider Flow

1. Login as `provider@fixit.test` / `password`.
2. Open Bookings.
3. Accept or update a customer job status.
4. Open job activity and reply to the customer message.
5. Save the final job cost.
6. Open Provider Profile and update profile/category/KYC reference.
7. Show provider dashboard statistics.

### Admin Flow

1. Login as `admin@fixit.test` / `password`.
2. Open Admin.
3. Show Rejve-inspired admin portal sidebar and real overview cards from `/admin/overview`.
4. Verify pending providers such as `John Doe Plumbing Ltd` or `Sarah Quick Sparks`.
5. Add, edit, or deactivate a service category from the CRUD panel.
6. Show all jobs from the admin account.

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
- Job messages and status timeline are scoped by role, so unrelated users cannot inspect another job.
- Admin category management uses real API-backed create, update, and soft-delete actions.
- Admin dashboard cards, provider verification table, and category CRUD panel integrate Rejve's frontend mock into the real Vue/Slim/MySQL app.

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
