# Technical Context - KeywordAI

**Version:** 0.2
**Date:** 2025-04-16
**Source:** `projectbrief.md` v0.3 + Implementação de Configurações de IA

## 1. Core Technologies

*   **Backend Framework:** Laravel 12 (or latest stable version at build time)
*   **Programming Language:** PHP (Version compatible with the chosen Laravel version)
*   **Database (Development):** SQLite (File-based, for ease of setup)
*   **Database (Production):** MySQL (Standard relational database for production)

## 2. Key Laravel Features Utilized

*   **Jobs & Queues:** For background processing (API calls, report processing). Requires configuration of a queue driver (e.g., database, Redis) and running a queue worker process.
*   **Scheduler:** For automating recurring tasks (e.g., daily syncs). Requires server-level cron job configuration to run `php artisan schedule:run` every minute.
*   **Artisan Commands:** For CLI operations, manual task triggering, and internal process invocation.
*   **Eloquent ORM:** For database interactions, ensuring compatibility between SQLite and MySQL where possible.

## 3. External Libraries & Dependencies

*   **`googleads/google-ads-php`:** The official Google Ads API Client Library for PHP. This is the sole interface for interacting with the Google Ads API. Requires proper configuration with API credentials (Developer Token, Client ID, Client Secret, Refresh Token, Login Customer ID).
*   **Composer:** Manages PHP dependencies.
*   **Integrações de IA (Planejadas):**
    *   **Gemini API:** API do Google para acesso aos modelos de IA Gemini. Requer chave de API armazenada nas configurações.
    *   **OpenAI API:** API para acesso aos modelos GPT da OpenAI. Requer chave de API armazenada nas configurações.
    *   **Perplexity API:** API para acesso aos modelos de IA da Perplexity. Requer chave de API armazenada nas configurações.

## 4. Development Setup (Based on `projectbrief.md` Section 11)

*   **Prerequisites:** PHP, Composer, Node.js (for frontend assets if applicable, though not detailed in core features yet), potentially Docker for environment consistency.
*   **Steps:**
    1.  Clone the repository.
    2.  Install PHP dependencies: `composer install`.
    3.  Configure API Credentials:
        *   Create `config/google_ads_php.ini` with Developer Token, OAuth2 credentials (Client ID, Secret, Refresh Token), `loginCustomerId` (MCC account), and `clientCustomerId` (target account). Use `scripts/get_refresh_token.php` to generate the Refresh Token.
        *   Update `config/app.php` to define `google_ads_php_path` and `client_customer_id`.
    4.  Configure the `.env` file:
        *   Database connection details (SQLite path for dev, MySQL credentials for prod).
        *   Queue driver configuration (e.g., `QUEUE_CONNECTION=database`).
        *   App key (`php artisan key:generate`).
        *   Google Chat notification control (`SEND_GOOGLE_CHAT_NOTIFICATIONS=true/false`).
        *   *(Note: Google Ads credentials are now primarily in `.ini`, not `.env`)*.
    5.  Run database migrations: `php artisan migrate`.
    5.  Set up queue worker: `php artisan queue:work` (needs to run continuously in the background).
    6.  Set up scheduler: Configure server cron job for `php artisan schedule:run`.

## 5. Technical Constraints & Considerations

*   **Google Ads API:**
    *   Configuration managed via `config/google_ads_php.ini` as preferred by the official library.
    *   Requires valid OAuth 2.0 credentials and an approved Developer Token (Basic Access obtained).
    *   Uses `loginCustomerId` (MCC) for authentication but targets `clientCustomerId` for data queries.
    *   Subject to rate limits; implementation must handle potential throttling.
    *   Requires careful error handling for API responses (`ApiException`).
*   **Database:** Differences between SQLite and MySQL might require careful query construction or testing if advanced features are used.
*   **Background Processing:** Requires a running queue worker process and proper configuration of the queue driver. Failures in jobs need monitoring and potentially retry mechanisms.
*   **PHP/Laravel Version:** Stick to versions compatible with `googleads/google-ads-php`.
*   **Integrações de IA:**
    *   As chaves de API para serviços de IA (Gemini, OpenAI, Perplexity) são armazenadas na tabela `settings` do banco de dados.
    *   Instruções customizadas para modelos de IA também são armazenadas na tabela `settings`.
    *   A interface para gerenciar essas configurações está disponível em `/settings/global`.
    *   Futuras integrações de IA para análise de termos de pesquisa utilizarão essas configurações.

*(This context is derived from the initial project brief and will evolve as the project progresses.)*
