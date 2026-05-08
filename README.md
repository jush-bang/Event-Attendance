# AttendanceEvent

A Laravel attendance tracking application.

## Project overview

This repository provides an attendance event management app built with Laravel. It includes:

- event and session tracking
- student attendance records
- database migrations and backend APIs
- a web interface served by `php artisan serve`

## Install and run using Windows Command Prompt only

## Prerequisites

Install and verify these tools first:

- PHP 8.2 or later
- Composer
- Node.js and npm
- MySQL server
- Optional: Git if you need to clone the repository

### Install tools on Windows

1. PHP: download from https://windows.php.net/download and install the Thread Safe version. Add the PHP folder to your `PATH`.
2. Composer: download and run the Composer-Setup installer from https://getcomposer.org/download/. Choose the installed PHP executable when prompted.
3. Node.js and npm: download and install from https://nodejs.org/. The installer includes npm.
4. MySQL server: download and install from https://dev.mysql.com/downloads/mysql/. During install, create or note a root password.
5. Git (optional): download and install from https://git-scm.com/download/win/ if you need to clone the repo.

Verify they are available in CMD:

```cmd
php -v
composer -V
node -v
npm -v
mysql --version
```

## Step-by-step setup

### 1. Open Command Prompt

Press `Win + R`, type `cmd`, and press Enter.

### 2. Go to the project folder

Use `cd` to change directory into the repository. Example:

```cmd
cd C:\AttendanceEvent
```

### 3. Install PHP dependencies

Run:

```cmd
composer install
```

### 4. Copy the environment file

Create `.env` from the sample file:

```cmd
copy .env.example .env
```

### 5. Edit `.env` with MySQL settings

Open `.env` in Notepad or another plain editor, then update the database configuration.

Example:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=attendance_event
DB_USERNAME=root
DB_PASSWORD=your_password_here
```

> Use a plain editor, not VS Code.

### 6. Create the MySQL database

Run MySQL and create the database:

```cmd
mysql -u root -p
```

Enter your password, then run:

```sql
CREATE DATABASE attendance_event;
EXIT;
```

If your MySQL credentials differ, use the values from `.env`.

### 7. Generate the application key

Run:

```cmd
php artisan key:generate
```

This writes a new `APP_KEY` into your `.env` file.

### 8. Run migrations

Run:

```cmd
php artisan migrate --force
```

If migration fails, verify your database credentials and that the database exists.

### 9. Install frontend dependencies

Run:

```cmd
npm install
```

### 10. Build frontend assets

Run:

```cmd
npm run build
```

### 11. Start the application

Run:

```cmd
php artisan serve --host=127.0.0.1 --port=8000
```

Then open:

```text
http://127.0.0.1:8000
```

## Quick command summary

```cmd
cd C:\AttendanceEvent
composer install
copy .env.example .env
REM Edit .env to set DB_DATABASE, DB_USERNAME, DB_PASSWORD
php artisan key:generate
php artisan migrate --force
npm install
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

## Troubleshooting

- `composer install` not found: ensure Composer is installed and in PATH.
- `php artisan migrate` fails: verify `.env` values and that the database exists.
- `npm install` fails: verify Node.js and npm are installed and in PATH.
- If port `8000` is busy, run:

```cmd
php artisan serve --host=127.0.0.1 --port=8080
```

## Notes

- This guide is written for Windows CMD only.
- You may use Notepad or another text editor to modify `.env`.
- Do not use VS Code for install or `php artisan serve` steps.
