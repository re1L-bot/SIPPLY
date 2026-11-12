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
DB_DATABASE=--------
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


http://127.0.0.1:8000


You can now access the Sipply e-commerce website.


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

1. Homepage<img width="1535" height="880" alt="image" src="https://github.com/user-attachments/assets/7f169a8e-2fc6-450f-9388-f2fbee5bcdc7" />

2. Product Catalog<img width="1535" height="877" alt="image" src="https://github.com/user-attachments/assets/d47dfa07-f810-4eee-8459-ea8c78c3dd3b" />

3. Product Details<img width="310" height="457" alt="image" src="https://github.com/user-attachments/assets/942f9240-74de-4a75-ab16-174426c06b0d" />

4. Shopping Cart<img width="1535" height="880" alt="image" src="https://github.com/user-attachments/assets/dac1c646-d718-46b9-a71a-bb9b92d077cf" />

5. Checkout Page<img width="1535" height="877" alt="image" src="https://github.com/user-attachments/assets/88291641-d465-4825-bf27-b6aa008bcc88" />

6. Customer Dashboard<img width="1535" height="877" alt="image" src="https://github.com/user-attachments/assets/b10950ec-9e29-4876-b9d0-4d2bf3458379" />

7. Administrator Dashboard<img width="1532" height="880" alt="image" src="https://github.com/user-attachments/assets/41862f0b-62f7-42ad-bdbc-5b1ac27e3e3a" />

8. Product Management<img width="1535" height="873" alt="image" src="https://github.com/user-attachments/assets/92d566d3-647e-4476-acba-1424114f39f3" />

9. Order Management<img width="1535" height="882" alt="image" src="https://github.com/user-attachments/assets/5fc96f8f-1bf8-49a0-a947-4f9f2611bcc1" />

License

This project is developed for the Sipply milk tea and coffee shop e-commerce platform.

All rights reserved unless otherwise specified.

---
Sipply – Sip Something Special. 🧋**
