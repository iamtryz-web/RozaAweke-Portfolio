# Roza Aweke | PHP Portfolio

A responsive portfolio website built with PHP, CSS, and JavaScript. The contact form stores messages in SQLite. It does **not** prepare or send email.

## Profile details
- Name: Roza Aweke
- Contact email: rozaaweke123@gmail.com

## Requirements
- PHP 8.0+
- PDO SQLite extension enabled (`pdo_sqlite`)
- A web server with permission to write to the `data/` directory

## Run locally
From this project directory, run:

```bash
php -S localhost:8000
```

Then open http://localhost:8000.

On Windows, if PHP is installed through XAMPP, you can run:

```powershell
C:\xampp\php\php.exe -S localhost:8000
```

## Contact form database
On the first valid submission, the application creates `data/messages.sqlite` and a `messages` table. It stores the sender's name, email, message, and timestamp using a prepared SQL statement.

The SQLite database is excluded from Git because it contains private visitor messages. Keep the `data/` directory writable by the PHP process. Do not commit the live database.

## Before publishing
1. Replace sample skills and project descriptions in `config.php` with Roza's accurate information.
2. Add an optional profile image and update the page if you choose to use one.
3. Test the contact form on a server with `pdo_sqlite` enabled.
4. For a public production site, use HTTPS and add an authenticated, access-controlled admin interface before exposing stored messages.
