# Laravel Inventory Example

This repository contains a minimal Laravel 12 application demonstrating an inventory system with hierarchical categories, warehouse management and sales tracking. The application now uses Jetstream's Livewire stack for authentication.

## Usage

1. Install PHP dependencies using Composer:
   ```bash
   composer install
   ```
2. Install JavaScript dependencies and build assets:
   ```bash
   npm install && npm run build
   ```
3. Run database migrations:
   ```bash
   php artisan migrate
   ```
4. Start the built-in server:
   ```bash
   php artisan serve
   ```
5. Visit `/register` to create an account or `/login` if one already exists. You can still visit `/example` to seed demo data and see a daily sales total in CUP.
