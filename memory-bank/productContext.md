# Product Context - KeywordAI

**Version:** 0.1
**Date:** 2025-04-05
**Source:** `projectbrief.md` v0.3

## 1. Purpose & Problem Solved

KeywordAI aims to solve the challenges of managing keywords, negative keywords, and search terms within complex Google Ads campaigns. The core problem is the difficulty in efficiently controlling keyword lists and optimizing advertising budget allocation towards the most effective terms. KeywordAI provides a centralized platform to streamline this management process.

## 2. Core Goals & User Value

*   **Centralized Management:** Offer a single place to view and manage Google Ads keyword data (keywords, negatives, search terms, campaigns, ad groups).
*   **Automation:** Automatically sync data from Google Ads, reducing manual effort.
*   **Efficient Processing:** Handle large volumes of search term report data effectively.
*   **Proactive Optimization:** Quickly identify new search terms, enabling users to add them as keywords or negative keywords promptly.
*   **Budget Control:** Improve ad spend efficiency by correlating search terms with negative lists and focusing investment.

## 3. Target Audience

*   Digital Marketing Professionals
*   PPC Managers
*   Agencies managing multiple or complex Google Ads accounts.

## 4. How It Should Work (Initial Scope Features)

*   **Authentication:** Securely connect to Google Ads via OAuth 2.0.
*   **Data Sync:**
    *   Fetch and store Negative Keyword Lists (and their keywords).
    *   Fetch and store Campaigns.
    *   Fetch and store Ad Groups.
    *   Fetch and store Keywords.
*   **Negative Keyword Input:** Allow users to add new negative keywords to lists *within* the tool (initially for analysis, not direct sync back).
*   **Search Term Processing:**
    *   Fetch search term reports for specified dates.
    *   Store terms, crucially capturing the `first_seen_at` date.
    *   Identify *newly seen* search terms (not previously stored).
    *   Implement a historical import job to back-fill `first_seen_at` accurately.
*   **Analysis:** Correlate fetched search terms against stored negative keyword lists.
*   **Notifications:** Alert an admin user about newly identified search terms.

*(This context is derived from the initial project brief and will evolve as the project progresses.)*
