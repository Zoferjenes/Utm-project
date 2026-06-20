# Arcade FixIt API Contract

Base URL:

```text
http://localhost:8000
```

Protected endpoints require:

```text
Authorization: Bearer <access_token>
```

## Auth

### Register

`POST /auth/register`

```json
{
  "name": "New Customer",
  "email": "new@example.com",
  "password": "password",
  "phone": "011-1234567",
  "role": "customer"
}
```

Roles allowed for self-registration: `customer`, `provider`.

### Login

`POST /auth/login`

```json
{
  "email": "customer@fixit.test",
  "password": "password"
}
```

Response:

```json
{
  "access_token": "JWT_TOKEN",
  "user": {
    "id": 2,
    "name": "Fiyandha Customer",
    "role": "customer"
  }
}
```

### Current User

`GET /auth/me`

## Categories

### List Categories

`GET /categories`

### Create Category

Admin only.

`POST /admin/categories`

```json
{
  "name": "Painting",
  "description": "Wall painting and small touch-up jobs",
  "icon": "paint"
}
```

## Providers

### Browse Providers

`GET /providers`

Optional query:

```text
?category_id=1&q=skudai
```

### Provider Detail

`GET /providers/{id}`

### Own Provider Profile

Provider only.

`GET /providers/profile`

### Create/Update Provider Profile

Provider only.

`POST /providers/profile`

```json
{
  "bio": "Experienced home repair provider",
  "location": "Skudai, Johor",
  "base_rate": 50,
  "photo_url": "/provider-ali.svg",
  "kyc_doc_url": "mock-kyc/provider.pdf",
  "category_ids": [1, 5]
}
```

### Upload Mock KYC Document

Provider only. Multipart form upload.

`POST /providers/kyc`

```text
document=<pdf|jpg|jpeg|png, max 2MB>
```

## Jobs

### List Jobs

`GET /jobs`

Role behavior:

- Customer sees own jobs.
- Provider sees assigned jobs.
- Admin sees all jobs.

### Create Job

Customer only.

`POST /jobs`

```json
{
  "provider_id": 1,
  "category_id": 1,
  "scheduled_at": "2026-06-24 10:00:00",
  "address": "Block A, Student Apartment, Skudai",
  "description": "Kitchen sink pipe is leaking.",
  "total": 50
}
```

### Update Job Status

Provider/admin only.

`PATCH /jobs/{id}/status`

```json
{
  "status": "in_progress"
}
```

Allowed statuses:

```text
requested, accepted, rejected, in_progress, completed, reviewed
```

### Set Final Cost

Provider/admin only.

`PATCH /jobs/{id}/cost`

```json
{
  "final_cost": 85
}
```

### Confirm Final Cost

Customer only. Job must be completed and have a final cost.

`PATCH /jobs/{id}/confirm-cost`

## Reviews

Customer only.

`POST /reviews`

```json
{
  "job_id": 2,
  "rating": 5,
  "comment": "Good service."
}
```

## Admin

### Provider Verification Queue

`GET /admin/providers/pending`

### Verify / Unverify Provider

`PATCH /admin/providers/{id}/verify`

```json
{
  "is_verified": true
}
```
