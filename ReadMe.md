# PersonSphere

A modern, lightweight CRUD application for managing personal details. Built with Laravel, Vite, and MySQL support, PersonSphere provides a solid foundation for rapid development and easy customisation.

## Prerequisites

* **PHP** 8.2 or higher
* **Composer**
* **Node.js** 18.x or higher and **npm**
* **MySQL** (for local development)

## Installation

1. **Clone the repository**

   ```bash
   git clone <repo-url> && cd PersonSphere
   ```

2. **Install PHP dependencies**

   ```bash
   composer install
   ```

3. **Install JavaScript dependencies**

   ```bash
   npm install
   ```

## Configuration

1. **Environment file**

    ```bash
    cp .env.example .env
    ```
    1.1 **Generate an Application Key**
    
   ```bash
   php artisan key:generate
   ```
2. **Database**
    * **MySQL** (optional local setup):

        * Create a database named `dbo_personsphere` with collation `utf8mb4_unicode_ci`
        * Update your `.env` to match your MySQL credentials:

          ```dotenv
          DB_CONNECTION=mysql
          DB_HOST=127.0.0.1
          DB_PORT=3306
          DB_DATABASE=dbo_personsphere
          DB_USERNAME=your_username
          DB_PASSWORD=your_password
          ```

3. **Run migrations and seeders**

   ```bash
    php artisan migrate --seed
   ```

## Running The Project

PersonSphere includes a convenient script to run all services concurrently:

```bash
 composer run dev
```

This command will:

* Start the PHP development server
* Start the queue listener
* Launch Vite in watch mode

### Alternative manual steps

If you prefer to run each service separately:

```bash
# PHP server
php artisan serve

# Queue listener
php artisan queue:listen --tries=1

# Vite watcher
npm run dev
```

## Usage

Once all services are running, visit:

```
http://127.0.0.1:8000
```

Log in with the default user seeded in your database, then begin creating and managing PersonSphere records.

Email: admin@personsphere.co.za and the 
Password: admin123

