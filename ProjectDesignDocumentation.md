# KeywordAI - Project Design Documentation for Google Ads API Access

**Company Name**: Fibersals

**Business Model:** Fibersals offers professional waterproofing and corrosion protection services primarily for industrial and large-scale clients (government entities, chemical/metal plants, food processing facilities). We use Google Ads to acquire new clients. KeywordAI is an internal tool to manage and optimize these campaigns efficiently.

**Tool Access/Use:** KeywordAI will be used **internally** by Fibersals' marketing team. Its core function is to improve reaction time to new search terms appearing in our Google Ads campaigns.
*   It synchronizes Google Ads data (campaigns, ad groups, keywords, negative keywords, search terms).
*   It identifies **newly appeared search terms** daily.
*   **Primary Interaction:** When a new search term is detected, the tool sends a **Google Chat notification (Card message V2)** to a designated internal space/user within our Google Workspace domain.
*   **Chat Actions:** This notification card will include buttons allowing the user to immediately:
    *   Add the term as a positive keyword (to the relevant ad group).
    *   Add the term to a negative keyword list.
    *   Trigger an AI analysis of the term against our internal knowledge base (good/bad terms, guidelines) to aid decision-making.
*   **Goal:** This allows rapid, manual moderation of new search terms as they appear and incur costs, directly from Google Chat, improving budget control and relevance in our broad "waterproofing" segment which often attracts irrelevant searches (e.g., clothing/furniture waterproofing).

The tool is **not** for external access.

**Tool Design**: KeywordAI is a backend application built using Laravel (PHP).
*   Connects to Google Ads API via `googleads/google-ads-php`.
*   Stores synchronized data (campaigns, keywords, negatives, search terms) in a local database (MySQL/SQLite).
*   Background jobs handle data sync and new search term detection.
*   **Google Chat Integration:** Uses Google Chat API (webhooks or Pub/Sub) to send interactive Card V2 messages for new search term notifications and potentially receive actions back from button clicks.
*   **AI Component:** Integrates with an AI model (e.g., via API) for the "analyze term" action requested from Google Chat.
*   **Web Interface (Secondary/Future):** A web UI might be developed later for comprehensive reporting and bulk management, but the primary workflow is via Google Chat.

**API Services Called** (Planned):

*   **Reading/Reporting:**
    *   `GoogleAdsService`: Fetching core entity data and performance reports (especially Search Terms).
    *   `SharedSetService` / `CampaignSharedSetService`: Fetching shared negative keyword lists.
*   **Writing (Triggered by Chat Actions):**
    *   `AdGroupCriterionService`: Adding positive keywords.
    *   `SharedSetService` / `SharedCriterionService`: Adding keywords to shared negative lists.
    *   `CampaignCriterionService` / `AdGroupCriterionService`: Adding negative keywords directly to campaigns/ad groups.

*(Note: List subject to refinement during development).*

**Tool Mockups**: The primary interface is Google Chat Cards V2.
*   *(Imagine a Google Chat message)*
    *   **Title:** New Search Term Found: "industrial tank waterproofing"
    *   **Details:** Campaign: [Campaign Name], Ad Group: [Ad Group Name], Cost: [$X.XX], Clicks: [Y], Impressions: [Z]
    *   **Buttons:** [Add as Keyword] [Add to Negatives] [Analyze Term (AI)]
