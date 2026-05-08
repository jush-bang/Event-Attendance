# AttendanceEvent Installation (Windows CMD Only)

This guide explains how to install and set up the project using only Windows Command Prompt (`cmd.exe`).

## Prerequisites

Make sure the laptop has these installed:

- PHP 8.2 or later
- Composer
- Node.js and npm
- Git (optional if the project is already copied)

## 1. Open Command Prompt

Press `Win + R`, type `cmd`, and press Enter.

## 2. Change to the project folder

Use `cd` to go to the project directory. Example:

```cmd
cd C:\AttendanceEvent
```

## 3. Install PHP dependencies

Run:

```cmd
composer install
```

## 4. Create the environment file

Copy the example environment file:

```cmd
copy .env.example .env
```

## 5. Configure MySQL and create the database

This laptop will use MySQL Workbench. Before running migrations, update `.env` to use your MySQL connection:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=attendance_event
DB_USERNAME=root
DB_PASSWORD=your_password
```

Then create the database in MySQL Workbench or with the MySQL CLI. Example CLI commands:

```cmd
mysql -u root -p
CREATE DATABASE attendance_event;
EXIT;
```

If you want to keep SQLite as a fallback, you can still create the SQLite file instead:

```cmd
copy nul database\database.sqlite
```

## 6. Generate the application key

Run:

```cmd
php artisan key:generate
```

## 7. Run database migrations

Run:

```cmd
php artisan migrate --force
```

## 8. Install frontend dependencies

Run:

```cmd
npm install
```

## 9. Build frontend assets

Run:

```cmd
npm run build
```

## 10. Start the application

Run:

```cmd
php artisan serve --host=127.0.0.1 --port=8000
```

Open a browser and go to:

```text
http://127.0.0.1:8000
```

## Notes

- If `composer install` fails, ensure Composer is in your Windows PATH.
- If `npm install` fails, ensure Node.js and npm are installed and in PATH.
- If `php artisan migrate` fails while using SQLite, confirm `database\database.sqlite` exists.
- For a MySQL setup, create the database first and verify the `.env` credentials.

## Quick command sequence

```cmd
cd C:\AttendanceEvent
composer install
copy .env.example .env
REM Update .env with MySQL settings and create the MySQL database first
php artisan key:generate
php artisan migrate --force
npm install
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

That is all. The project should now be ready on a Windows machine using only CMD with MySQL Workbench.
