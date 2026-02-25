# KeywordAI: LLM-First Architecture Improvements

> Transforming KeywordAI into an AI-native tool for LLM agents and copilots

## 🎯 Vision: AI-Native PPC Management

Instead of being a web app that LLMs can call, KeywordAI becomes a **skill** that LLMs can deeply integrate with—understanding context, making decisions, and taking actions autonomously.

---

## 1. MCP (Model Context Protocol) Integration

### Why
MCP is the new standard for AI tool integration ( Anthropic, OpenAI, etc. supporting it).

### Implementation
```python
# New: app/Services/McpServer.php
class McpServer {
    // Expose tools as MCP-compatible endpoints
    // LLMs discover capabilities automatically
}
```

**New Files:**
- `routes/mcp.php` - MCP protocol endpoints
- `app/Services/Mcp/Tools/` - Tool definitions
- `app/Services/Mcp/Prompts/` - System prompts for LLMs

**Capabilities Exposed:**
```json
{
  "tools": [
    {
      "name": "analyze_search_terms",
      "description": "Analyze search terms using AI and suggest negative keywords",
      "inputSchema": {...}
    },
    {
      "name": "add_negative_keyword",
      "description": "Add a term as a negative keyword in Google Ads",
      "inputSchema": {...}
    },
    {
      "name": "get_campaign_performance",
      "description": "Get performance metrics for campaigns",
      "inputSchema": {...}
    }
  ]
}
```

---

## 2. Structured Output API (JSON Schema)

### Current Problem
API returns generic JSON. LLMs have to parse and interpret.

### Solution: Strict Schema Validation
```php
// New: Always return structured responses
{
  "success": true,
  "data": {...},
  "metadata": {
    "request_id": "uuid",
    "processing_time_ms": 123,
    "tokens_used": 456,
    "confidence_score": 0.92
  },
  "actions_taken": [
    {"action": "analyzed", "count": 50},
    {"action": "suggested_negative", "count": 12}
  ],
  "next_recommended_actions": [
    "Review high-cost terms without conversions",
    "Check brand term performance"
  ]
}
```

**New Endpoint:** `POST /api/ai/analyze-structured`
- Returns Pydantic-style JSON schemas
- Includes reasoning chains for LLM understanding
- Confidence scores for each recommendation

---

## 3. Function Calling Interface

### New: Direct Function Call API
```php
// routes/api.php
Route::post('/ai/function-call', [AiFunctionController::class, 'handle']);
```

**Request:**
```json
{
  "functions": [
    {
      "name": "get_search_terms",
      "arguments": {"min_cost": 100, "date_from": "2025-01-01"}
    },
    {
      "name": "analyze_for_negatives", 
      "arguments": {"aggressiveness": "high"}
    }
  ],
  "context": {
    "campaign_focus": ["Brand", "Competitor"],
    "exclude_categories": ["adult", "gambling"]
  }
}
```

**Response:**
```json
{
  "results": [
    {"function": "get_search_terms", "result": [...]},
    {"function": "analyze_for_negatives", "result": [...]}
  ],
  "orchestration_metadata": {
    "execution_order": [0, 1],
    "dependencies_resolved": true
  }
}
```

---

## 4. Natural Language Query Interface

### New Endpoint: `/api/nlq/query`

Let LLMs (and users) ask questions in natural language:

**Request:**
```json
{
  "query": "Show me expensive search terms from last week with low CTR that I should probably negate",
  "context": {
    "campaign_id": "1234567890",
    "max_suggestions": 10
  }
}
```

**Response:**
```json
{
  "interpreted_query": {
    "filters": {
      "date_from": "2025-02-17",
      "date_to": "2025-02-24",
      "min_cost": 50,
      "max_ctr": 1.0
    },
    "sort": "cost_desc"
  },
  "results": [...],
  "llm_analysis": {
    "summary": "Found 15 terms spending over $50 with CTR below 1%",
    "recommendations": [...],
    "risk_assessment": "low"
  }
}
```

**Implementation:**
- Use Gemini/OpenAI to parse NL to query parameters
- Fallback to structured query if confidence low
- Cache common query patterns

---

## 5. Streaming/Real-Time API

### Why
LLM agents need real-time feedback for long operations.

### Implementation: Server-Sent Events (SSE)
```php
// New: routes/api-stream.php
Route::get('/stream/sync-progress', [StreamController::class, 'syncProgress']);
```

**Usage:**
```javascript
const eventSource = new EventSource('/api/stream/sync-progress?job_id=123');
eventSource.onmessage = (event) => {
  const data = JSON.parse(event.data);
  // Update LLM on progress: "Processed 450/1000 terms..."
};
```

**Use Cases:**
- Long sync operations with Google Ads
- AI analysis progress ("Analyzed 50 terms, found 12 candidates...")
- Batch operations status

---

## 6. Memory & Context Management

### New: Conversation Context API

LLM agents need to maintain context across interactions:

```php
// POST /api/context/session
{
  "session_id": "uuid",
  "context": {
    "active_campaigns": ["Brand", "Competitor"],
    "focus_areas": ["high_spend", "low_ctr"],
    "excluded_terms": ["free", "cheap"],
    "risk_tolerance": "conservative"
  }
}
```

**Benefits:**
- LLM doesn't need to repeat context
- Personalized recommendations
- Learning from past actions

---

## 7. Embeddings & Vector Search

### Why
Semantic search for similar terms, not just exact match.

### Implementation
```php
// New: app/Services/EmbeddingService.php
class EmbeddingService {
    // Generate embeddings for search terms
    // Store in vector DB (Pinecone, Weaviate, or pgvector)
    // Semantic similarity search
}
```

**New Endpoints:**
```
POST /api/terms/similar    # Find semantically similar terms
POST /api/terms/cluster    # Group terms by meaning
```

**Use Case:**
```json
{
  "query": "Find terms similar to 'best ppc software'",
  "threshold": 0.8,
  "limit": 20
}
```

---

## 8. Decision Audit Trail

### Why
LLMs making decisions need accountability and rollback.

### Implementation
```php
// New table: ai_decisions
// - decision_id
// - context (full prompt/context)
// - reasoning_chain (how LLM decided)
// - action_taken
// - outcome (success/failure)
// - rollback_possible (boolean)
```

**New Endpoints:**
```
GET  /api/decisions/history
POST /api/decisions/{id}/rollback
GET  /api/decisions/{id}/explain  # Why was this decision made?
```

---

## 9. Multi-Modal Capabilities

### Future: Image/Chart Analysis
```php
// POST /api/analytics/chart-interpret
{
  "chart_image": "base64_encoded_png",
  "question": "What trends do you see in this CTR chart?"
}
```

**Use Cases:**
- Analyze performance charts
- Screenshot analysis of Google Ads UI
- Report generation with visualizations

---

## 10. Autonomous Agent Workflows

### New: Workflow Engine
```php
// POST /api/workflows/define
{
  "name": "Weekly Optimization",
  "trigger": "cron:0 9 * * 1",  // Mondays at 9am
  "steps": [
    {
      "action": "sync_search_terms",
      "params": {"days": 7}
    },
    {
      "action": "ai_analyze",
      "params": {"focus": "high_cost_low_conversion"}
    },
    {
      "action": "suggest_negatives",
      "params": {"auto_approve_if_confidence_above": 0.9}
    },
    {
      "action": "notify",
      "params": {"channel": "email", "summary": true}
    }
  ],
  "approval_required": false  // Fully autonomous
}
```

---

## 11. LLM-Optimized Error Messages

### Current
```json
{
  "success": false,
  "message": "Error syncing with Google Ads"
}
```

### Improved
```json
{
  "success": false,
  "error": {
    "code": "GOOGLE_ADS_RATE_LIMIT",
    "message": "Rate limit exceeded for Google Ads API",
    "llm_context": "The Google Ads API has a daily limit of 15,000 operations. We have exceeded this limit. Wait until tomorrow (Pacific Time midnight) or request increased quota.",
    "suggested_actions": [
      "Wait until tomorrow and retry",
      "Request quota increase from Google",
      "Enable batching to reduce API calls"
    ],
    "retry_after": "2025-02-25T08:00:00Z",
    "documentation_url": "https://developers.google.com/google-ads/api/docs/rate-limits"
  }
}
```

---

## 12. OpenAPI Specification with LLM Extensions

### New: `openapi-llm.yaml`
Enhanced OpenAPI spec with:
- `x-llm-description`: Detailed description for LLMs
- `x-llm-examples`: Few-shot examples
- `x-llm-cautions`: Warnings about usage
- `x-llm-cost`: Token/cost estimates

```yaml
paths:
  /api/search-terms/negate:
    post:
      summary: Add negative keyword
      x-llm-description: |
        This permanently adds a keyword to the negative list in Google Ads.
        Use with caution as it will prevent ads from showing for this term.
        Always review the search term context before negating.
      x-llm-examples:
        - input: {"keyword": "free", "reason": "Low quality traffic"}
          context: "Campaign targeting premium users"
      x-llm-cautions:
        - "Cannot be undone through API - must use Google Ads UI"
        - "May take 24 hours to take effect"
```

---

## 13. Prompt Templates Library

### New: `resources/prompts/`

Pre-built prompts for common LLM use cases:

```yaml
# prompts/analyze_terms.yaml
name: analyze_search_terms
system: |
  You are a PPC optimization expert. Analyze these search terms and identify:
  1. Terms that should be negated (irrelevant, low quality)
  2. Terms that should be added as positive keywords
  3. Emerging trends or patterns
  
  Consider: campaign goals, brand safety, conversion data.
  
  Return ONLY valid JSON in the specified schema.
  
few_shot_examples:
  - input: "cheap software"
    output: {"should_negate": true, "reason": "Low purchase intent", "match_type": "broad"}
  - input: "brandname pricing"
    output: {"should_negate": false, "should_add_positive": true, "match_type": "phrase"}
```

**Endpoint:** `GET /api/prompts/{name}` - Retrieve prompt for LLM use

---

## 14. Cost & Token Tracking

### New: LLM Usage Analytics

Track how LLMs use the API:
```php
// Table: llm_usage_logs
// - model (gpt-4, claude, etc.)
// - tokens_input/output
// - cost_estimate
// - actions_taken
// - success_rate
```

**Dashboard:** `/api/admin/llm-usage`

---

## 15. Skill Marketplace Integration

### Future: Publish as Official Skill

**OpenAI GPTs:**
- Create GPT with KeywordAI actions
- Users configure with their API token

**Claude Computer Use:**
- Native tool integration
- Claude can directly call API

**LangChain Integration:**
```python
from langchain_community.tools import KeywordAITool

tool = KeywordAITool(
    api_token="...",
    actions=["analyze", "negate", "report"]
)
```

---

## Implementation Priority

### Phase 1: Foundation (1-2 weeks)
1. ✅ Structured JSON responses
2. ✅ Better error messages with LLM context
3. ✅ Natural language query endpoint
4. ✅ Conversation context API

### Phase 2: Intelligence (2-3 weeks)
5. Function calling interface
6. Embeddings/semantic search
7. Decision audit trail
8. Prompt templates library

### Phase 3: Integration (2-3 weeks)
9. MCP protocol support
10. Streaming API
11. Autonomous workflows
12. LangChain package

### Phase 4: Polish (1 week)
13. OpenAPI-LLM spec
14. Cost tracking
15. Documentation & examples

---

## Benefits

| Feature | User Benefit | LLM Benefit |
|---------|--------------|-------------|
| MCP Protocol | Works with any MCP client | Automatic tool discovery |
| Structured Output | Predictable responses | Easy parsing, no hallucination |
| NLQ Interface | Ask in plain English | No need to learn query syntax |
| Function Calling | Complex multi-step operations | Can chain operations |
| Embeddings | Find similar terms | Semantic understanding |
| Audit Trail | Accountability | Can explain decisions |
| Streaming | Real-time feedback | Better UX for long ops |

---

## Next Steps

1. **Priority 1:** Implement structured output API
2. **Priority 2:** Create MCP server wrapper
3. **Priority 3:** Build NLQ endpoint with Gemini
4. **Priority 4:** Design embedding pipeline

Want me to implement any of these? I can start with Phase 1 features immediately.
