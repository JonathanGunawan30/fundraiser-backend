# AI Assistant Handoff Document: Fundraiser Backend (LATEST)

Hello, fellow AI! 👋 
This is the **Final Brain Dump** for the **Fundraiser Backend** project. The application has evolved from a basic CRUD to a production-ready system with complex integrations and robust infrastructure.

---

## 1. Project Context & Tech Stack
*   **Purpose**: Crowdfunding platform API.
*   **Framework**: Laravel 11.x (PHP 8.2+).
*   **Database**: MySQL & SQLite (Testing).
*   **Infrastructure (Dockerized)**:
    *   **Redis**: Atomic locking and caching.
    *   **RabbitMQ**: Message broker for asynchronous background jobs.
    *   **Laravel Reverb**: High-performance WebSocket server for real-time notifications.
    *   **Cloudflare R2**: Object storage for all images and PDF invoices.
*   **External Integrations**:
    *   **Midtrans**: Official Payment Gateway (Snap API & Webhooks).

---

## 2. Global Architecture & Routing (IMPORTANT)
The project uses a strict **Layered Architecture** (Controller -> Service -> Repository).
**Routing has been refactored** to ensure a clear separation of concerns:

1.  **Admin Area (`/api/admin/`)**: Guards: `admin-api`. For all management and modification tasks.
2.  **Auth/User Area (`/api/auth/`)**: Guards: `api`. For user-specific actions (donating, requesting withdrawals, profile).
3.  **Public Area (`/api/`)**: Read-only access for campaigns, categories, etc.

*Refer to `GEMINI.md` for JSON standards and `CAMPAIGN_WORKFLOW_ID/EN.md` for specific business logic.*

---

## 3. Completed Feature Summary
We have reached **121 tests (100% PASS)** covering these modules:

1.  **Campaign Management**:
    *   Full CRUD with cover and gallery images stored on R2.
    *   Many-to-Many Tag synchronization.
    *   Admin verification workflow (`pending` -> `approved`/`rejected`).
2.  **Donations & Payments**:
    *   Midtrans Snap integration for payments.
    *   **Async Processing**: Success triggers a RabbitMQ job to update campaign totals.
    *   **Otomatisasi Invoice**: Generates professional PDF invoices (with "PAID" watermark) stored on R2.
    *   **Email**: Sends the invoice to donors automatically.
3.  **Real-Time Notifications**:
    *   WebSocket broadcasting via **Laravel Reverb**.
    *   Database-backed history.
    *   Triggers: New donation, campaign verification, withdrawal updates.
4.  **Analytics Dashboard**:
    *   Tailored statistics for Admins (platform-wide) and Users (personal campaign ROI).
    *   Daily donation trends and category distribution charts.
5.  **Audit Logs**:
    *   Powered by `spatie/laravel-activitylog`.
    *   Strictly Read-Only logs of critical actions (withdrawals, verifications, settings).
6.  **Profile Management**: 
    *   User can update name, phone, and avatar (R2 with auto-cleanup).
7.  **Master Data**: CRUD for Tags, Categories, Banners, FAQs, Site Settings.

---

## 4. Current Testing & Infrastructure Status
*   **Run All Tests**: `make test` (inside Docker).
*   **Websocket Server**: `php artisan reverb:start`.
*   **Queue Worker**: `php artisan rabbitmq:consume`.
*   **Storage**: All public URLs point to Cloudflare R2.
*   **Env Config**: See `.env.example` for Redis, RabbitMQ, Reverb, and Midtrans keys.

---

## 5. Security Posture
The platform has built-in protections and automated tests for:
*   **SQL Injection**: Verified via `tests/Feature/Security/SecurityTest.php`.
*   **Mass Assignment**: Prevented using strict FormRequests and model `$fillable` property.
*   **XSS Mitigation**: User content is stored as strings; frontend is responsible for safe rendering (Blade handles this by default).

**Run Security Tests Only**:
`docker compose exec app php artisan test tests/Feature/Security`

---

## 6. Roadmap: What's Next?
If you are starting a new session, here are the recommended next steps:

1.  **KYC (Know Your Customer)**: 
    *   Implement identity verification (ID card upload to R2) to grant "Verified" badges to campaign creators.
2.  **Enhanced Search (PostgreSQL/Meilisearch)**: 
    *   If the database grows, transition from simple `LIKE` queries to a real search engine.
3.  **API Documentation**: 
    *   Integrate Swagger/OpenAPI (L5-Swagger) for frontend developers.
4.  **Automatic Completion**: 
    *   Create a Laravel Scheduler task to automatically mark campaigns as `completed` when they hit their `deadline`.

---

## 6. Development Workflow Rules
1.  **Interfaces**: Always define an Interface for Services and Repositories.
2.  **Validation**: Use FormRequests extending `BaseRequest`.
3.  **Resources**: Always wrap models in `JsonResource`.
4.  **R2 Files**: Use UUID filenames. Implement `deleteFromR2` in Services to prevent orphan files.
5.  **Audit**: Add `LogsActivity` trait to any new critical model.

Good luck! You are working on a high-quality, structured codebase. Keep it clean! 🚀
