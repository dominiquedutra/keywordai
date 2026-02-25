# System Patterns - KeywordAI

**Version:** 0.1
**Date:** 2025-04-05
**Source:** `projectbrief.md` v0.3

## 1. Core Architecture

*   **Framework:** Laravel 12 (or latest stable PHP framework).
*   **Processing Model:** Heavy reliance on background processing via Laravel's Job Queue system. This is crucial for handling potentially long-running Google Ads API interactions and data processing without blocking user interactions (if a UI existed) or timing out web requests.
*   **Automation:** Use of Laravel's Scheduler for recurring tasks (e.g., daily data synchronization).
*   **Interaction Layer:** Extensive use of custom Laravel Artisan Commands for:
    *   Manual triggering of actions (syncs, reports).
    *   Console-based execution.
    *   Decoupled invocation of functionality by other components (Jobs, Services).
    *   Testing (especially integration and live API tests).
*   **External API Integration:** All Google Ads interactions are strictly channeled through the official `googleads/google-ads-php` library. Encapsulation of API logic is implied.

## 2. Key Technical Decisions & Patterns

*   **Background Jobs:** Essential for API calls, report fetching/processing, and historical data imports. Prevents timeouts and keeps the (potential future) frontend responsive.
*   **Scheduled Tasks:** Automates routine data fetching and synchronization.
*   **Artisan Commands:** Serve as primary entry points for many core processes, promoting modularity and testability. Commands can be triggered manually, scheduled, or called internally.
*   **Google Ads Client Injection:** The `GoogleAdsClient` (provided by the official library and configured via `AppServiceProvider` from `.ini`) is directly injected into components that need API access (e.g., Artisan Commands, Jobs).
*   **Service Layer (for Business Logic):** While API client instantiation is handled by the provider, business logic related to processing fetched data or preparing API requests should still be encapsulated in dedicated Service classes (`app/Services`) if complexity warrants it (following SRP).
*   **Single Responsibility Principle (SRP):** Applied to Services, Commands, and Jobs. Classes should be small and focused. Refactoring is expected if classes grow too large.
*   **Golden Rule (Code Modification):** Changes must be strictly limited to the scope of the specific task/request. No unrelated refactoring.
*   **AI-Assisted Workflow:** A defined process involving developer confirmation before Memory Bank updates and commits ensures clarity and control.
*   **Execução de Comandos CLI:** Padronizada através do servidor MCP `iterm-mcp`. Todas as interações com a linha de comando devem utilizar a ferramenta `write_to_terminal` deste servidor, em vez da ferramenta `execute_command` nativa.

## 3. Component Relationships (Initial High-Level)

```mermaid
graph TD
    subgraph User/Scheduler
        ManualTrigger[Manual `php artisan <command>`]
        SchedulerTrigger[Laravel Scheduler]
    end

    subgraph Core Application
        ArtisanCommands[Artisan Commands]
        JobsQueues[Jobs & Queues]
        Services[Service Classes (Business Logic)]
        Models[Eloquent Models (e.g., Campaign, AdGroup, Keyword, NegativeKeywordList, SearchTerm)]
        Database[(Database: SQLite/MySQL)]
        GoogleAdsClientDI{GoogleAdsClient (DI)}
    end

    subgraph External Systems
        GoogleAdsAPI[Google Ads API]
    end

    subgraph Libraries
        GoogleAdsLib[`googleads/google-ads-php`]
    end

    ManualTrigger --> ArtisanCommands
    SchedulerTrigger --> ArtisanCommands
    ArtisanCommands --> JobsQueues
    ArtisanCommands --> Services
    ArtisanCommands --> GoogleAdsClientDI %% Inject Client
    JobsQueues --> Services
    JobsQueues --> GoogleAdsClientDI %% Inject Client
    Services --> Models
    Services --> GoogleAdsClientDI %% Inject Client (if needed)
    Models -- Interacts with --> Database
    GoogleAdsClientDI -- Uses --> GoogleAdsLib
    GoogleAdsLib -- Communicates with --> GoogleAdsAPI

```

## 4. Key Considerations

*   **API Rate Limits & Errors:** Robust handling within the Google Ads integration layer (likely a Service) is critical.
*   **Database Compatibility:** Careful use of Eloquent ORM features to ensure compatibility between SQLite (dev) and MySQL (prod).
*   **Code Size/Context:** Maintain small, focused classes due to SRP and AI context limitations.
*   **Manual Validation:** Feature validation relies on manual execution of Artisan commands and verification of output/state.

*(This context is derived from the initial project brief and will evolve as the project progresses.)*
