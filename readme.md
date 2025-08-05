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

## Production Deployment

1. Copy the example environment file and update the values for your server.
   ```bash
   cp .env.example .env
   php artisan key:generate --force
   ```
2. Install production dependencies and compile assets.
   ```bash
   composer install --no-dev --optimize-autoloader
   npm install && npm run build
   ```
3. Run database migrations with the `--force` flag and cache configuration.
   ```bash
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
4. Ensure the web server serves the `public/` directory and that the `storage/` and `bootstrap/cache/` directories are writable by the web user.
5. Start the queue worker and scheduler if your application uses them.
   ```bash
   php artisan queue:work
   * * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
   ```

## Exchange Rates

Access the *Exchange Rates* section of the application to register new rates manually. Provide the currency, the value of one unit in CUP and the date from which the rate is effective. Each sale or invoice keeps a reference to the rate used so historical operations preserve their original conversion.

## Product cost in CUP

When a product is priced in USD or MLC the system multiplies the entered price by the selected exchange rate to determine its cost in CUP. Invoices store both the original price and the computed amount in CUP.

## Reports

Sales reports display totals converted to CUP using the exchange rate linked to each sale. Inventory reports summarize inputs and outputs per product and warehouse so you can verify stock levels after transfers and sales.
