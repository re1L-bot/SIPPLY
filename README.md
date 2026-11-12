Sipply – Milk Tea & Coffee E-Commerce Website

**Sipply** is a modern e-commerce website designed for a milk tea and coffee shop. It provides customers with a convenient online shopping experience where they can explore products, view product details, and place orders through an intuitive and user-friendly interface.

The platform also provides an administrative system for managing products, customers, and store operations.

---

Table of Contents

- [Project Overview](#-project-overview)
- [Features](#-features)
- [Technology Stack](#-technology-stack)
- [User Roles](#-user-roles)
- [Database Structure](#-database-structure)
- [Installation and Setup](#-installation-and-setup)
- [Environment Configuration](#-environment-configuration)
- [Running the Application](#-running-the-application)
- [Security Features](#-security-features)
- [Project Structure](#-project-structure)
- [Screenshots](#-screenshots)
- [License](#-license)

---

Project Overview

Sipply aims to make ordering milk tea and coffee products easier and more accessible through an online platform.

The website focuses on providing:

- A clean, modern, and responsive user interface.
- Organized product categories.
- Detailed product information.
- Convenient online ordering.
- Efficient product and order management.
- Secure authentication for customers and administrators.

 Features

Customer Features
- Customer registration and login.
- Browse available products.
- View product details, descriptions, and prices.
- Explore products by category.
- Add products to shopping cart.
- Update product quantities in the cart.
- Place and manage orders.
- View order history.
- Manage customer account information.

Administrator Features
- Secure administrator login.
- Dashboard overview.
- Product management (Create, Read, Update, Delete).
- Product category management.
- Inventory and stock management.
- Order management.
- Customer management.
- Monitor product availability.
- Manage product information, prices, descriptions, and images.

Shopping Features
- Dynamic shopping cart.
- Automatic order total calculation.
- Product quantity validation.
- Stock availability checking.
- Order processing.
- Prevention of overselling through database-level stock management.

Technology Stack

| Technology | Purpose |
|---|---|
| PHP | Backend programming language |
| Laravel | Backend framework |
| MySQL | Relational database management |
| HTML5 | Website structure |
| CSS3 | Website styling |
| JavaScript | Frontend interactivity |
| Bootstrap 5 | Responsive interface |
| Laragon | Local development environment |

 User Roles

Sipply supports two primary account types:

Administrator
- Accesses the administration dashboard.
- Manages products and categories.
- Monitors inventory.
- Processes and manages customer orders.
- Oversees store operations.

Customer
- Creates and manages an account.
- Browses available products.
- Adds products to the shopping cart.
- Places orders.
- Tracks previous orders.

Database Structure

The application uses MySQL as its relational database management system.

The database is designed to organize customer information, administrator accounts, product records, inventory, and transactions.

Core database tables include:

- `users` – Stores customer account information.
- `admins` – Stores administrator account information.
- `categories` – Stores product categories.
- `products` – Stores product information, pricing, and stock quantities.
- `carts` – Stores shopping cart information.
- `cart_items` – Stores individual products added to carts.
- `orders` – Stores customer order information.
- `order_items` – Stores individual products associated with orders.

Installation and Setup

Follow these instructions to run Sipply on your local machine.

Prerequisites

Make sure you have installed the following:

- PHP 8.2 or higher
- Composer
- MySQL
- Laragon
- Git
- Node.js and npm (if frontend assets require compilation)
1. Clone the Repository

```bash
git clone YOUR_REPOSITORY_URL
```

Navigate to the project directory:

```bash
cd sipply
```

2. Install Dependencies

Install Laravel dependencies:

```bash
composer install
```

Install frontend dependencies if applicable:

```bash
npm install
```

 3. Configure Environment Variables

Create your environment configuration file:

```bash
copy .env.example .env
```

Generate the Laravel application key:

```bash
php artisan key:generate
```

4. Create the Database

Open your MySQL database manager and create a database named:

```sql
CREATE DATABASE sipply;
```

5. Configure Database Connection

Update the following values in your `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sipply
DB_USERNAME=root
DB_PASSWORD=
```

Update the username and password according to your MySQL configuration.

6. Run Database Migrations

```bash
php artisan migrate
```

If your project includes seeders:

```bash
php artisan db:seed
```

7. Create Storage Link

```bash
php artisan storage:link
```

8. Build Frontend Assets

```bash
npm run build
```

For development:

```bash
npm run dev
```

 Running the Application

Start the Laravel development server:

```bash
php artisan serve
```

Open your browser and visit:

```text
http://127.0.0.1:8000
```

You can now access the Sipply e-commerce website.

---

Security Features

Sipply incorporates security practices designed to protect customer information and maintain reliable transactions.

These include:

- Password hashing.
- Authentication and authorization.
- Role-based access control.
- CSRF protection.
- Server-side input validation.
- SQL injection protection through Laravel's query builder and Eloquent ORM.
- Session management.
- Product stock validation.
- Database transactions for order processing.
- Protection against simultaneous stock deductions.

Project Structure

```text
sipply/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   ├── Models/
│   └── Providers/
│
├── bootstrap/
├── config/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
│
├── public/
│   ├── images/
│   └── storage/
│
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│
├── routes/
│   ├── web.php
│   └── api.php
│
├── storage/
├── tests/
├── .env.example
├── artisan
├── composer.json
└── package.json
```

Screenshots

Add screenshots of your actual application below to showcase its interface and functionality.

Suggested screenshots:

1. Homepage
2. Product Catalog
3. Product Details
4. Shopping Cart
5. Checkout Page
6. Customer Dashboard
7. Administrator Dashboard
8. Product Management
9. Order Management
License

This project is developed for the Sipply milk tea and coffee shop e-commerce platform.

All rights reserved unless otherwise specified.

---
Sipply – Sip Something Special. 🧋**
