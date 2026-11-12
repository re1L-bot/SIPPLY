# SipPly

SipPly is a Laravel drink-ordering website for the municipalities of Bantayan, Madridejos, and Sta. Fe. Customers can browse and filter a product menu, add items to a session or account cart, and place guest or registered-customer orders. Administrators manage products, inventory, order progress, delivery fees, promotions, and manual payment records.

## Requirements

- PHP 8.3 or newer with the extensions required by Laravel (including `fileinfo`)
- Composer
- MySQL 8+ or another Laravel-supported database
- Node.js and npm

## Local setup

1. Install PHP and JavaScript dependencies:

   ```sh
   composer install
   npm install
   ```

2. Create the local environment file and application key:

   ```sh
   cp .env.example .env
   php artisan key:generate
   ```

3. Configure `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env`.

4. Create the database, run migrations, and load local demo data:

   ```sh
   php artisan migrate --seed
   ```

5. Build the frontend assets and start the application:

   ```sh
   npm run build
   php artisan serve
   ```

For active frontend development, use `npm run dev` in a separate terminal. Configure SMTP values in `.env` if order-confirmation emails should be delivered; orders are still saved if email delivery fails.

The migrations add the commerce tables when they are not already present, preserve existing tables, and add customer email snapshots for order history. Existing orders receive their email from the associated customer account when possible.

## Demo accounts and data

`php artisan db:seed` creates demo accounts and ten fictional drinks in three categories only in the `local` and `testing` environments. The seed includes an unavailable product and an available product with zero stock.

| Account | Email | Password |
|---|---|---|
| Customer | `test@example.com` | `password` |
| Admin owner | `owner@example.test` | `password` |

These are local demonstration credentials only. Do not use them in a deployed environment. The seeder intentionally does not create accounts in production.

To reset a disposable local database and reload its demo data:

```sh
php artisan migrate:fresh --seed
```

`migrate:fresh` drops every table in the configured database. Never run it against data you need to keep.

## Main routes

| Path | Purpose |
|---|---|
| `/` | Search, category filter, sort, and browse the menu |
| `/menu/{productId}` | Product details and related drinks |
| `/cart` | Guest or signed-in cart |
| `/checkout` | Guest or signed-in checkout |
| `/login`, `/register` | Customer authentication |
| `/account` | Profile, saved addresses, and registered-customer order history |
| `/admin/login` | Administrator sign-in |
| `/admin` | Admin dashboard |
| `/admin/menu`, `/admin/orders`, `/admin/inventory` | Catalog and order operations |
| `/admin/customers`, `/admin/payments`, `/admin/reports` | Order-based customer summaries and reporting |
| `/admin/delivery-fees`, `/admin/coupons`, `/admin/promotions` | Store settings and marketing |

## Order and payment behavior

- Product availability, stock, and quantities are checked again when an order is committed. Stock changes and order records are written in a database transaction.
- A guest order stores the supplied email, recipient, contact number, and delivery address. Registered customers with saved addresses select one without re-entering address details.
- Delivery fees are configured per municipality by an owner and are added to the order total. This preserves the municipality-fee behavior requested for this site.
- Order states move forward from pending through confirmation, preparation, readiness, delivery, and completion. Customers and admins can cancel only eligible pending or confirmed orders; cancellation restores inventory once.
- Payment statuses are recorded manually. No payment gateway or online charge processing is connected.
- Guest orders are not linked to an account. Customers who want order history in their account should sign in before ordering.

## Verification

Run the PHPUnit feature and unit suites with:

```sh
php artisan test --compact
```

Build the production frontend bundle with:

```sh
npm run build
```

The image-upload feature tests require PHP's `fileinfo` extension. Enable it in the test PHP runtime if those tests report that `finfo` is unavailable.

Latest verification in this workspace:

- Fresh SQLite migrations and `db:seed`: passed.
- Configured MySQL migrations and order-email backfill: passed; no registered orders were left without a customer email.
- Customer/admin focused feature tests: passed.
- `npm run build`: passed. Vite reported that the optional `fontaine` package is not installed; it did not prevent the build.
- Full PHPUnit suite: 73 passed; 3 image-upload tests could not run because this PHP runtime does not have `fileinfo`/`finfo`.

## AI disclosure

AI coding assistance was used to implement and test application changes. Generated demo catalog entries are fictional.

## License

This project is provided for educational and assessment use. Confirm the applicable license and third-party asset rights before redistribution.
