# FundRaiser API Backend

This is the backend RESTful API server for the FundRaiser crowdfunding and charity donation platform. The project is built using Laravel 13 and FrankenPHP (powered by Octane), providing high-performance, stateless API endpoints, transaction processing, and queue management.

## Core Features

- **OAuth 2.0 Authentication**: Seamless social authentication flow using Laravel Socialite for Google and GitHub.
- **Campaign Architecture**: Full CRUD for campaigns, category mapping, tag management, cover images, and admin verification workflow.
- **Secure Transaction & Donation Processing**: Integrated with Midtrans payment gateway, mapping secure transaction channels, gross-to-net calculations, and invoice logging.
- **Administrative Control Panel**: Secure admin logins authenticated via 6-digit OTP codes, banner promotions, FAQ configurations, and site settings.
- **Global Maintenance Mode Guard**: Dedicated site status toggling. Routes under /admin are bypassed so administration panels remain fully accessible during system maintenance.
- **Queue & Event Architecture**: Offloads long-running processes (such as email dispatches via Resend and system notifications) to a RabbitMQ queue worker.
- **Real-time WebSockets**: Supports instant message broadcasting and real-time alerts via Laravel Reverb integration.
- **Audit Logging**: Automated tracing of critical administrator actions for audit trails and security compliance.
- **Pure Headless API Mode**: Web root and API entry points return structured JSON metadata status checks. All route exceptions, validation errors, and authentication failures are captured and returned in unified JSON response payloads.

## Technology Stack

- **Framework**: Laravel 13
- **Runtime Server**: FrankenPHP / PHP 8.x
- **Message Broker**: RabbitMQ 3
- **Cache & Key-Value Database**: Redis (TLS-enabled Valkey compatibility)
- **Payment Gateway**: Midtrans Sandbox SDK
- **Security Check**: Cloudflare Turnstile API
- **Email Gateway**: Resend API
- **Containerization**: Docker & Docker Compose

## Getting Started

### Prerequisites

Ensure you have the following installed:
- PHP 8.3 or higher
- Composer
- Docker & Docker Compose

### Local Development Installation

1. Clone the repository and navigate to the project directory:
   ```bash
   cd fundraiser-backend
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Create your local environment configuration:
   ```bash
   cp .env.example .env
   ```
   Modify the `.env` variables with your local database, Redis, RabbitMQ, and Midtrans credentials.

4. Generate the application key:
   ```bash
   php artisan key:generate
   ```

5. Run database migrations and mock data seeders:
   ```bash
   php artisan migrate --seed
   ```

6. Start the local server:
   ```bash
   php artisan serve
   ```

## Environment Configuration

Configure these key parameters in your `.env` file:

```env
# Application Settings
APP_NAME=Fundraiser
APP_ENV=local
APP_KEY=your-app-key
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3000

# Database Configuration (MySQL)
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your-db-name
DB_USERNAME=your-db-username
DB_PASSWORD=your-db-password
MYSQL_ATTR_SSL_CA=/etc/ssl/certs/aiven-ca.pem # Optional path for SSL databases

# Queue Configuration (RabbitMQ)
QUEUE_CONNECTION=rabbitmq
RABBITMQ_HOST=rabbitmq
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest

# Cache & Redis Settings
CACHE_STORE=file
REDIS_HOST=your-redis-host
REDIS_PASSWORD=your-redis-password
REDIS_PORT=6379

# Midtrans Credentials
MIDTRANS_MERCHANT_ID=your-merchant-id
MIDTRANS_CLIENT_KEY=your-client-key
MIDTRANS_SERVER_KEY=your-server-key
MIDTRANS_IS_PRODUCTION=false

# Security & Cloudflare
TURNSTILE_SECRET_KEY=your-turnstile-secret-key
```

## Running with Docker

The application includes a `docker-compose.yml` to orchestrate the app service, queue worker, and RabbitMQ dependencies.

### Running Containers Locally

1. Rebuild and start the containers:
   ```bash
   docker compose up -d --build
   ```

2. Run migrations inside the running container:
   ```bash
   docker compose exec app php artisan migrate
   ```

### Production Docker Guidelines

To ensure smooth runtime environment variable loading, the image does not cache configurations during the build stage (no `config:cache` is executed). Ensure that you supply the `.env` file at runtime:

- **Via Docker run**:
  ```bash
  docker run -d --env-file .env -p 80:80 -p 443:443 your-image:latest
  ```
- **Via Docker Compose**:
  Ensure the `env_file` section is pointing to your environment configuration in your `docker-compose.yml`.

## CI/CD Pipeline

The project is configured with GitHub Actions. Any merge or push to the `main` branch triggers the build workflow:
1. Installs dependencies and checks PHP syntax.
2. Builds the FrankenPHP Docker image.
3. Automatically pushes the image to Docker Hub under the tag defined in your registry configurations.
