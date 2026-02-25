# KeywordAI - Project Documentation

**Version:** 0.3
**Date:** 2025-04-05

## 1. Introduction

KeywordAI is a software tool designed to assist in the management of keywords, negative keywords, and search terms within complex Google Ads campaigns. The primary goal is to help users optimize their advertising budget by efficiently controlling keyword lists and directing investment towards the most effective terms.

## 2. Goals

* Provide a centralized platform for managing Google Ads keyword-related data.
* Automate the synchronization of keywords, negative keywords, campaigns, and ad groups from Google Ads.
* Efficiently process and store Google Ads search term reports.
* Identify newly appearing search terms to allow for quick reaction (e.g., adding as keywords or negative keywords).
* Help correlate search terms with existing negative keyword lists.
* Improve budget control and investment allocation in Google Ads campaigns.

## 3. Target Audience

* Digital Marketing Professionals
* PPC Managers
* Agencies managing multiple or complex Google Ads accounts.

## 4. Technology Stack

* **Backend Framework:** Laravel 12 (*Note: As of April 2025, latest stable is Laravel 11. Assuming intent for future version or latest available at build time.*)
* **Programming Language:** PHP
* **Database (Development):** SQLite
* **Database (Production):** MySQL
* **Testing Framework:** Pest
* **Key Laravel Features:**
    * Jobs & Queues (for background, parallel, and queued processing)
    * Scheduler (for automated, timed tasks)
    * Artisan Commands (for CLI operations, testing, and internal triggers)
* **External Libraries:**
    * `googleads/google-ads-php` (Google Ads API Client Library for PHP)

## 5. Core Features (Initial Scope)

The initial implementation will focus on the following core functionalities:

* **Authentication:**
    * Connect and authenticate securely with a user's Google Ads account using OAuth 2.0.
* **Data Synchronization:**
    * **Negative Keyword Lists:** Sync lists and all keywords within them from Google Ads to the local database.
    * **Campaigns:** Sync campaign details.
    * **Ad Groups:** Sync ad group details.
    * **Keywords:** Sync keyword details associated with campaigns/ad groups.
* **Negative Keyword Management:**
    * Add new negative keywords to specific negative keyword lists within the tool, to be potentially synced back or used for analysis.
* **Search Term Processing:**
    * Fetch search term reports from Google Ads for specified dates.
    * Store search terms in the local database, accurately capturing the `first_seen_at` date (the date a term first appeared in reports).
    * **New Term Identification:** Detect search terms present in reports that haven't been recorded previously in the local database.
    * **Full Sync / Historical Import:** Implement a job to fetch daily search term reports chronologically over a specified date range to accurately back-fill the `first_seen_at` date for all terms.
* **Reporting & Analysis:**
    * **Correlation:** Correlate fetched search terms against the synchronized negative keyword lists.
* **Notifications:**
    * Notify a designated admin user (initially, potentially configurable later) about newly identified search terms.

## 6. Architecture Notes

* **Background Processing:** Heavy reliance on Laravel's Job Queue system for tasks like API synchronization, report processing, and historical data import. This ensures the UI remains responsive and handles potentially long-running Google Ads API interactions gracefully.
* **Scheduled Tasks:** Use Laravel's Scheduler for recurring tasks like daily synchronization of campaigns, keywords, and fetching recent search term reports.
* **Command-Line Interface (CLI):** Extensive use of custom Laravel Artisan Commands. These commands will serve multiple purposes:
    * Triggering specific actions manually during development and testing.
    * Providing a way to run processes (like syncs or reports) directly from the console.
    * Allowing internal components (like Jobs or Services) to invoke specific functionalities in a decoupled manner.
    * Facilitating testing, including integration tests run via the console.
* **Google Ads API Integration:** All interactions with Google Ads will be channeled through the official `googleads/google-ads-php` library. Proper handling of API credentials, rate limits, and error responses is crucial.
* **Database:** SQLite for development simplicity, MySQL for production robustness and scalability. Careful use of Eloquent ORM to maintain compatibility.

## 7. Coding Standards & Principles

* **Single Responsibility:** Keep Service Classes (`app/Services`), Artisan Commands, and Jobs focused and small. Each class should ideally have a single, well-defined purpose.
* **Code Size & Context:** Be mindful of file size, particularly considering AI context window limitations during development. If a class becomes too large or handles too many responsibilities, refactor it by splitting it into smaller, more focused classes. Remember that splitting implementation classes may require adjustments to corresponding test classes.
* **Golden Rule (Code Modification):** When implementing a new feature or fixing a bug based on a specific request, **only change the code necessary and directly related to that request.** Avoid refactoring, changing, or attempting to fix any part of the code that is not strictly related to the task at hand. Unrelated improvements or refactoring should be handled as separate, dedicated tasks.

## 8. Development Methodology & Validation Strategy

*   **Methodology:** Development follows a feature-driven approach. Implementation focuses on delivering the required functionality as per the defined goals.
*   **Validation Strategy:** Feature validation and testing are performed **manually** through direct interaction with the application, primarily via its Artisan commands.
*   **Process:**
    1.  **Implementation:** Develop the required feature or fix.
    2.  **Manual Validation:** Execute relevant `php artisan` commands with various inputs and parameters.
    3.  **Observe Output:** Carefully check the console output for correctness, expected results, and error messages.
    4.  **Verify State:** Check the database, external systems (like Google Ads, if applicable via its UI), or file system to confirm the expected changes occurred.
    5.  **Iterate:** If validation fails, debug the implementation, and repeat the manual validation steps.
*   **Rationale:** This approach prioritizes rapid feature development. Validation relies on direct, hands-on verification of command execution and system state changes.

## 9. AI-Assisted Development Workflow

To ensure clarity and consistency when working with AI assistance, the following workflow will be adhered to:

1.  **Request:** A specific feature request or bug fix is provided.
2.  **Plan and Act:** The AI assistant (and developer) plans the necessary steps and implements the required code changes, following the Coding Standards (Section 7) and Development Methodology (Section 8).
3.  **Developer Confirmation:** The human developer reviews the changes and signals completion/satisfaction (e.g., "looks good," "close shop," "finish up").
4.  **Task Finalization Signal:** This confirmation signals that the specific task is considered complete from an implementation perspective.
5.  **Memory Bank Update & Commit:** The AI assistant updates its internal knowledge base ("Memory Bank") regarding the changes made and the context, and then prepares/suggests a clear, descriptive commit message summarizing the work done for version control.
6.  **Completion:** A task is **only** considered fully completed once the Memory Bank update and the code commit have occurred.
7.  **Clarification:** If the AI assistant is unsure whether a task is considered finished or if further action is needed, it will ask the human developer for clarification before proceeding to step 5.

## 10. Future Considerations (Roadmap Placeholder)

* UI/UX for managing keywords and terms.
* Adding keywords directly to campaigns/ad groups.
* More sophisticated analysis and reporting features.
* User roles and permissions.
* Syncing changes made *in* the tool back *to* Google Ads (bi-directional sync).

## 11. Setup (To be detailed)

* Clone repository.
* Install PHP dependencies (`composer install`).
* Configure `.env` file (Database, Google Ads API Credentials, Queue Driver, etc.).
* Run migrations (`php artisan migrate`).
* Set up queue worker and scheduler.
