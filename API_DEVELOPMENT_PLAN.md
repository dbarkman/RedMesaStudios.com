# API Development Plan for RedMesaStudios.com

This document outlines the plan for creating a general-purpose API to serve as the backend for RedMesaStudios.com and associated applications.

## 1. Project Goal & Technology
- **Goal**: Build a secure, scalable, and maintainable API using standard Laravel practices. The initial feature will be to handle contact form submissions, with a robust security layer to prevent abuse.
- **Technology**:
  - **Server**: Rocky Linux 9 with Apache and PHP-FPM
  - **Database**: MariaDB
  - **Framework**: Laravel

## 2. Core Features

### A. Contact Form Endpoint
- An endpoint at `/api/contact` will accept `POST` requests.
- It will handle the following optional fields: `name`, `email`, `phone`, `message`.
- On successful submission, it will save the data to a `contacts` table.
- An email notification will be sent to a designated address using a Laravel Mailable for clean, reusable email logic.

### B. API Security
- **Authentication**: All API endpoints will be protected using bearer tokens managed by **Laravel Sanctum**, the official and recommended package for this purpose.
- **Rate Limiting**: We will use Laravel's built-in **Rate Limiter** to prevent abuse. This avoids building a bespoke solution and is highly configurable.
- **IP Denylisting**: A system will be implemented to automatically block misbehaving IP addresses based on exceeding the rate limit. It will feature escalating ban durations:
  - **1st offense**: 1-hour ban
  - **2nd offense**: 6-hour ban
  - **3rd offense**: 12-hour ban
  - **4th+ offense**: Permanent ban

### C. API Key Management
- A custom Artisan command (`php artisan api:key`) will be created to manage API clients and their tokens. This provides a simple and powerful command-line interface for key administration.
- **Functionality**:
  - `api:key --generate --client="<description>"`: Generate a new token for a client.
  - `api:key --status --token="<token_prefix>" --set="<active|blocked>"`: Update a client's status to enable or disable access. For security, this command will operate on a prefix of the token (e.g., the first 8 characters) rather than the full token.
  - `api:key --list`: List all clients and their token status.

## 3. Database Schema
The following tables will be created to support the API's functionality:
- **`contacts`**: Stores the contact form submissions (`name`, `email`, `phone`, `message`).
- **`api_clients`**: Manages the API clients, including a `description` and an `active`/`blocked` `status`. This table will have a one-to-many relationship with Sanctum's `personal_access_tokens` table.
- **`denied_ips`**: Tracks blocked IP addresses. It will contain the following columns:
  - `ip_address` (string)
  - `banned_until` (datetime)
  - `offense_count` (integer)
- Laravel Sanctum's `personal_access_tokens` table will also be used to store the API tokens themselves.

---

## Implementation Checklist

- [x] Create a new Laravel project in the `api` directory.
- [x] Configure a `.env.example` file with necessary placeholders for database and mail credentials.
- [x] Install Laravel Sanctum for API token management.
- [x] Create database migration files for `contacts`, `api_clients`, and `denied_ips`.
- [x] Define the schema in the `contacts` migration file.
- [x] Define the schema in the `api_clients` migration file.
- [x] Define the schema in the `denied_ips` migration file.
- [x] Run the database migrations to create the tables.
- [x] Create the necessary Eloquent models (`Contact`, `ApiClient`, `DeniedIp`).
- [x] Configure the Rate Limiter with the escalating ban logic in `bootstrap/app.php`.
- [x] Create the `ContactController` to handle the logic for storing submissions and sending emails.
- [x] Create the Mailable class for the contact form notification email.
- [x] Define the `/api/contact` route in the API routes file.
- [x] Create the security middleware to check for a client's `active` status and the IP denylist.
- [x] Apply the rate limiter and security middleware to the API route.
- [x] Build out the logic for the `api:key` Artisan command.
- [x] Update the `index.html` contact form's JavaScript to submit to the new API endpoint with a valid bearer token.

---

## Post-Development Notes & Learnings

- **Laravel Versioning**: The project was built using a modern version of Laravel (11+), which has a streamlined application structure. Key configurations, such as route registration and rate limiting, were handled in `bootstrap/app.php` rather than in dedicated service providers (e.g., `RouteServiceProvider`). This was a key learning point during development.

- **Sanctum Migrations**: The database migration for Laravel Sanctum's `personal_access_tokens` table is not included by default and must be published separately using the `php artisan vendor:publish` command before running the database migrations.

- **Production Deployment Strategy**: For production, the API application code should be moved outside of the main website's public document root for security. A more secure structure is to place the API code in a separate, isolated directory (e.g., `/var/www/html/RedMesaStudiosApi`). An Apache `Alias` directive should then be used in the main site's Virtual Host configuration to point the desired URL (e.g., `/api`) to the Laravel application's `public` directory.

- **Background Processes**: Long-running commands, such as `php artisan queue:work`, must be managed by a process manager like **Supervisor** in a production environment to ensure they run continuously and are restarted automatically if they fail.
