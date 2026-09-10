# Central University Business School Association — Web Portal

A full-stack PHP + MySQL prototype for the CU Business School Association,
themed with the maroon/gold colors from the association logo.

## Features

- **Landing page** with Sign Up / Login buttons
- **Sign Up**: department dropdown (full names only — initials hidden),
  Student ID, email, phone, password + confirm password. The Student ID
  must start with the correct department initials (ACC, BKF, HRM, MGT, MKT)
  and must already exist in the `eligible_students` allow-list together
  with the matching email — otherwise sign up is blocked.
- **Email OTP verification** before the account is actually created.
- **Login**: Student ID/email + password + department select. Login is
  blocked unless the Student ID prefix matches the selected department
  and the department on file.
- **Dashboard**: shows all departments; only the student's own department
  is selectable.
- **Department page**: Buy Cloths / Pay Dues actions.
- **Buy Cloths**: choose number of yards (GHS 50/yard), live total, pay
  via Paystack.
- **Pay Dues**: flat GHS 65 fee, pay via Paystack.
- **Paystack** inline checkout + server-side transaction verification.
- **Order History** with a printable/emailed receipt per paid order.
- **Profile page** showing the student's own details (no password shown).
- **Logout** back to the landing page.

## Tech stack

- PHP (vanilla, PDO for MySQL — no framework required)
- MySQL / MariaDB
- Vanilla CSS (no build step) + Paystack Inline JS

## Setup

1. **Create the database**

   ```bash
   mysql -u root -p < database/schema.sql
   ```

   This creates the `cu_business_school` database, all tables, the 5
   departments, and a few **sample eligible students** you can sign up
   with immediately for testing:

   | Student ID    | Email                     | Department |
   |---------------|---------------------------|------------|
   | ACC10012024   | ama.owusu@example.com     | Accounting |
   | BKF10022024   | kojo.mensah@example.com   | Banking & Finance |
   | HRM10032024   | efua.boateng@example.com  | HR Management |
   | MGT10042024   | yaw.asante@example.com    | Management Studies |
   | MKT10052024   | abena.darko@example.com   | Marketing |

   To onboard real students, the school admin simply inserts rows into
   `eligible_students` (student_id, email, department_code).

2. **Configure the database connection**

   Edit `config/db.php` with your MySQL host/user/password.

3. **Configure Paystack**

   Edit `config/paystack.php` and put in your real `pk_test_...` /
   `sk_test_...` (or live) keys from your Paystack dashboard.

4. **Email sending**

   The app uses PHP's built-in `mail()` function to send OTP codes and
   receipts. On most local dev environments this isn't configured, so
   as a fallback every email is also written to `includes/email_log.txt`
   so you can still see the OTP code / receipt while testing. For
   production, swap `send_email()` in `includes/functions.php` for a
   proper SMTP library (e.g. PHPMailer) with your school's mail server.

5. **Run it**

   Point your PHP dev server (or Apache/Nginx + PHP-FPM) at the project
   root:

   ```bash
   php -S localhost:8000
   ```

   Then visit `http://localhost:8000`.

## Project structure

```
cu-business-school/
├── assets/
│   ├── css/style.css        # Maroon & gold theme (from the logo)
│   └── images/logo.png
├── config/
│   ├── db.php                # MySQL connection
│   └── paystack.php          # Paystack API keys
├── database/
│   └── schema.sql             # Full DB schema + seed data
├── includes/
│   ├── functions.php          # Helpers (auth, OTP, pricing, email)
│   ├── header.php / footer.php
│   └── receipt.php            # Shared receipt HTML builder
├── index.php                  # Landing page
├── signup.php
├── verify-otp.php
├── login.php
├── dashboard.php
├── department.php
├── buy-cloths.php
├── pay-dues.php
├── paystack-callback.php      # Verifies payment, saves order, emails receipt
├── order-history.php
├── receipt-view.php
├── profile.php
└── logout.php
```

## Notes for going to production

- Replace `mail()` with an authenticated SMTP sender.
- Serve over HTTPS (required by Paystack and for password safety).
- Add rate-limiting / CAPTCHA on login & OTP requests.
- Consider adding an admin panel for managing `eligible_students`.
- Rotate the Paystack secret key and never expose it client-side (this
  prototype already keeps it server-side only, inside `paystack-callback.php`).
