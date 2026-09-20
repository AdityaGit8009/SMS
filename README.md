# SMS - Student Management System (Laravel 13 Code Pack)

This code pack is designed to be copied into an existing fresh Laravel 13 project named `SMS`.

## Stack
- Laravel 13
- PHP 8.4+
- MySQL / InnoDB
- Blade
- HTML / CSS / Bootstrap 5
- JavaScript
- Eloquent ORM

## Included modules
- Role-based authentication: Admin, Teacher, Student
- Login / Logout / Forgot Password / Reset Password / Change Password
- Admin dashboard
- Academic Sessions
- Departments
- Courses
- Semesters
- Sections
- Subjects
- Teacher Management
- Student Management
- Student Documents
- Attendance
- Fee Structures / Payments / Printable Receipt
- Exams / Exam Schedules
- Marks / Results / Printable Result
- Assignments / Student Submissions
- Timetable
- Notices
- Leave Management
- Student ID Card with browser-generated QR
- Percentage / Grade / Pass-Fail / credit-weighted SGPA and CGPA starter formula
- Student / Teacher / Attendance / Pending Fee / Exam / Result reports and CSV exports
- System Settings
- Profile Management
- Teacher Dashboard
- Student Dashboard

## IMPORTANT BEFORE INSTALLING
Your WAMP MySQL default engine was MyISAM. Laravel should use InnoDB.

In `config/database.php`, in the `mysql` connection, set:

```php
'engine' => 'InnoDB',
```

Also set WAMP MySQL `default-storage-engine=InnoDB` in `my.ini`, restart WAMP, and verify:

```sql
SHOW VARIABLES LIKE 'default_storage_engine';
```

It should return `InnoDB`.

## Installation
1. Back up your existing SMS project.
2. Copy the folders/files from this pack into `C:\LaravelProjects\SMS` and allow overwrite for matching app/routes/resource files.
3. Keep your existing `.env`, then configure MySQL:

```env
APP_NAME="Student Management System"
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sms_db
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@sms.test"
MAIL_FROM_NAME="${APP_NAME}"
```

4. Run:

```powershell
php artisan optimize:clear
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```

5. Open `http://127.0.0.1:8000`.

## Demo accounts
- Admin: `admin@sms.com` / `Admin@123`
- Teacher: `teacher@sms.com` / `Teacher@123`
- Student: `student@sms.com` / `Student@123`

## Forgot password in local development
With `MAIL_MAILER=log`, the reset link is written to:

`storage/logs/laravel.log`

## Notes
- Printable fee receipt, result and ID card can be saved as PDF using the browser Print -> Save as PDF option.
- The ID card QR is generated in the browser with qrcodejs CDN.
- This pack intentionally contains no `vendor`, `node_modules`, or framework core files.
