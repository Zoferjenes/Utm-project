# Arcade Team Contributions

This document records the Arcade-only contribution split for the final project.

## Members

| Member | Matric | Poll Role | Main Contribution |
| --- | --- | --- | --- |
| Fiyandha Ceyllo Fathurrahman | A24CS4012 | Member 3: Mobile, Tokens & Global Validation | Full-stack integration, GitHub repository setup, backend/frontend connection, JWT/login flow, validation, deployment/mobile preparation, documentation and test plan |
| Ishal Siddique | A24CS4047 | Member 2: Server Routing & Database | Project coordination, proposal preparation, backend/database direction, early schema/ERD direction, task split planning, presentation material support |
| Md Rejwanul Islam Rejve | A24CS4086 | Member 1: Web Interface & Forms | Web interface/form direction, API contract discussion, admin frontend mock, admin dashboard/category-management UI direction |

## Poll-Based Role Split

Arcade used an internal WhatsApp poll on 16 June 2026 to divide the project work using the "Everyone Has It Easy" split:

| Poll Option | Person Who Took It | Role Scope |
| --- | --- | --- |
| Mem 1 | Md Rejwanul Islam Rejve | Web interface, admin screens, forms, tables, approve/reject actions, visual layout |
| Mem 2 | Ishal Siddique | Server routing, database tables, PDO connection, CRUD actions, password hashing |
| Mem 3 | Fiyandha Ceyllo Fathurrahman | Capacitor/mobile setup, input validation, JWT tokens, final cross-platform checks |

The poll is treated as role ownership evidence. It does not mean one member completed an entire layer alone; the final project was integrated into one working Vue/Slim/MySQL application.

## How Fiyandha's Member 3 Role Was Used

Member 3 covered the cross-platform and security layer in the final build:

```text
JWT authentication flow
frontend and backend validation checks
role-based access control verification
Capacitor Android project configuration
deployment/mobile proof documentation
GitHub repository setup and final integration
```

## How Ishal's Coordination Was Used

Ishal took Member 2 in the poll and helped keep Arcade moving when the project scope was still confusing between Arcade and Top GS. His WhatsApp coordination and proposal work established the Arcade team direction:

```text
confirmed Arcade should work on Problem 5
created the early work split for frontend, backend, database/devops/mobile
helped prepare and share the project proposal
kept the group aligned on needing one GitHub repository
supported presentation/demo planning
provided backend/database planning direction
```

The final project uses that direction as the base structure:

```text
Vue frontend module
Slim PHP backend module
MySQL schema and seed data
Capacitor/mobile preparation
documentation for demo, deployment, API, testing, and team work
```

This means Ishal's contribution is represented as project leadership, planning, proposal preparation, and coordination evidence rather than only source-code commits.

## How Rejve's Frontend Mock Was Used

Rejve took Member 1 in the poll and later shared `fixit-frontend zip.zip` in the Arcade WhatsApp group on 21 June 2026. The zip contained a Vue admin mock with:

```text
admin login screen
dashboard overview cards
provider verification table
service category CRUD panel
static provider/category data
```

The mock was not copied directly because the final app already uses Vue Router, Pinia, Axios, Slim API, JWT, and MySQL. Instead, every usable project-specific part was integrated into the real app:

```text
FixIt Admin / secure login wording and layout direction
Admin portal side navigation
Admin overview cards connected to /admin/overview
Provider verification table layout with approve/reject actions
Category add/edit/deactivate management
Mock provider examples: John Doe Plumbing Ltd and Sarah Quick Sparks
Mock category examples: Plumbing & Repair, Electrical Systems, Home Cleaning, Carpentry Work
```

Template-only leftovers were intentionally not copied:

```text
node_modules folder
default Vite README text
default Vite/Vue demo component and unused assets
hard-coded fake login state
hard-coded fake provider/category arrays
```

This keeps Rejve's contribution represented while preserving the working full-stack architecture and real backend data flow.

## Final Arcade Scope

Arcade submits FixIt independently as Problem 5. The temporary combined group with Top GS was treated as coordination/evidence only, not as the final submission team.
