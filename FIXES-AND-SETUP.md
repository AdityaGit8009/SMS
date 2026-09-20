# SMS Laravel 13 - Fixed Complete Project

This project is configured for Laravel 13, PHP 8.4, MySQL, Blade, Bootstrap, JavaScript and Eloquent ORM.

## Important fixes applied

- Removed duplicate `role` and `status` definitions from the base users migration. The dedicated SMS users migration now owns these fields.
- Forced MySQL-created framework tables to InnoDB and reduced indexed string lengths where needed for compatibility with the user's WAMP/MySQL configuration.
- Fixed stale/missing password route references and removed unused duplicate admin layout files.
- Fixed teacher search query grouping so department filtering works correctly.
- Added course/semester/section consistency checks for students.
- Added course/semester consistency checks for exams and subjects.
- Added exam schedule validation for date range, times and passing marks.
- Added student ownership checks before marks are saved.
- Added fee structure ownership checks and overpayment prevention.
- Prevented fee structure deletion when payment records exist.
- Added timetable relationship checks and teacher/class overlap validation.
- Improved assignment permission checks, late handling, resubmission handling and file cleanup.
- Added CSV exports for teachers, attendance, exams and results.
- Added current academic session selection to system settings.
- Verified all PHP source files for syntax, all named route references, controller view references and Blade compilation.

## First-time setup on Windows/WAMP

1. Ensure PHP 8.4+, Composer, Node/npm and WAMP MySQL are available.
2. In WAMP MySQL `my.ini`, use:

   `default-storage-engine=InnoDB`

3. Restart WAMP and verify in phpMyAdmin:

   `SHOW VARIABLES LIKE 'default_storage_engine';`

   It should return `InnoDB`.

4. Configure `.env`:

   - `DB_CONNECTION=mysql`
   - `DB_HOST=127.0.0.1`
   - `DB_PORT=3306`
   - `DB_DATABASE=sms_db`
   - `DB_USERNAME=root`
   - `DB_PASSWORD=` (or your password)

5. From the project folder run:

   `php artisan optimize:clear`

   `php artisan migrate:fresh --seed`

   `php artisan storage:link`

   `php artisan serve`

6. Open `http://127.0.0.1:8000`.

## Demo accounts

- Admin: `admin@sms.com` / `Admin@123`
- Teacher: `teacher@sms.com` / `Teacher@123`
- Student: `student@sms.com` / `Student@123`

## Password reset in local development

Use `MAIL_MAILER=log` in `.env`. Forgot-password reset links will be written to `storage/logs/laravel.log` instead of being sent externally.

## Important note

`php artisan migrate:fresh --seed` deletes all existing tables/data in the configured database. Use it only for first setup or disposable development data.
