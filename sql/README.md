# Database Setup (All Modules)

Run this file to create the full schema for:

- Dashboard support data
- CRM
- Account Executive
- Passport & Visa
- Schedule & Rates
- Facilities & Reservation
- Financial
- Messages
- Notifications

## Main Script

- `sql/install_all_modules.sql`
- `sql/seed_all_modules.sql`

## Run via MySQL CLI

```sql
SOURCE c:/xampp/htdocs/programming Original/sql/install_all_modules.sql;
```

## Run via phpMyAdmin

1. Open phpMyAdmin.
2. Go to the SQL tab.
3. Paste the content of `sql/install_all_modules.sql`.
4. Execute.

The script creates `beyond_the_map` (if it does not exist) and all required tables.

## Seed Demo Data

After schema install, run:

```sql
SOURCE c:/xampp/htdocs/programming Original/sql/seed_all_modules.sql;
```

This inserts demo records for:

- Users and staff profiles
- Customers and CRM interactions
- Passport applications/documents
- Tours, guests, bookings
- Facilities reservations/coordination
- Payments/reminders
- Messages and notifications
