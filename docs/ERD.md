# Arcade FixIt ERD

## Tables

```text
users
-----
id PK
name
email UNIQUE
password_hash
role: customer | provider | admin
phone
status
created_at

service_categories
------------------
id PK
name UNIQUE
description
icon
is_active
created_at

provider_profiles
-----------------
id PK
user_id FK -> users.id UNIQUE
bio
location
latitude
longitude
service_radius_km
base_rate
photo_url
is_verified
kyc_doc_url
created_at

provider_categories
-------------------
id PK
provider_id FK -> provider_profiles.id
category_id FK -> service_categories.id

jobs
----
id PK
customer_id FK -> users.id
provider_id FK -> provider_profiles.id
category_id FK -> service_categories.id
status
scheduled_at
address
description
total
final_cost
final_cost_confirmed
created_at
updated_at

job_status_logs
---------------
id PK
job_id FK -> jobs.id
status
changed_by FK -> users.id
changed_at

messages
--------
id PK
job_id FK -> jobs.id
sender_id FK -> users.id
body
sent_at

reviews
-------
id PK
job_id FK -> jobs.id UNIQUE
rating
comment
created_at
```

## Relationships

```text
users 1 -> 0..1 provider_profiles
provider_profiles many -> many service_categories through provider_categories
users(customer) 1 -> many jobs
provider_profiles 1 -> many jobs
service_categories 1 -> many jobs
jobs 1 -> many job_status_logs
jobs 1 -> many messages
jobs 1 -> 0..1 reviews
```
