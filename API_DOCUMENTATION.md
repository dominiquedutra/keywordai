# KeywordAI API Documentation

API completa e protegida por token para acesso a todo o toolset da ferramenta KeywordAI.

## Índice

- [Autenticação](#autenticação)
- [Endpoints](#endpoints)
  - [Health & Info](#health--info)
  - [Dashboard](#dashboard)
  - [Search Terms](#search-terms)
  - [Campaigns](#campaigns)
  - [Ad Groups](#ad-groups)
  - [Negative Keywords](#negative-keywords)
  - [Sync Operations](#sync-operations)
  - [AI Analysis](#ai-analysis)
  - [Token Management](#token-management)

---

## Autenticação

A API usa tokens de autenticação. O token deve ser enviado em todas as requisições (exceto endpoints de health) através de um dos métodos:

### Header X-API-Token
```http
X-API-Token: seu_token_aqui
```

### Header Authorization Bearer
```http
Authorization: Bearer seu_token_aqui
```

### Query String (menos seguro, útil para testes)
```
?api_token=seu_token_aqui
```

### Gerenciamento de Tokens

Criar um novo token:
```bash
php artisan api:token create
```

Listar tokens:
```bash
php artisan api:token list
```

Revogar token:
```bash
php artisan api:token revoke --token=seu_token
```

### Permissões

Tokens podem ter as seguintes permissões:
- `read` - Leitura de dados
- `write` - Modificação de dados
- `sync` - Operações de sincronização
- `ai` - Análise de IA
- `admin` - Gerenciamento de tokens
- `*` - Todas as permissões

---

## Endpoints

### Health & Info

#### GET `/api/health`
Verifica a saúde da API e seus componentes.

**Resposta:**
```json
{
  "success": true,
  "status": "healthy",
  "timestamp": "2025-02-24 14:30:00",
  "checks": {
    "database": { "status": "ok", "message": "Database connection successful" },
    "queue": { "status": "ok", "message": "Queue operational", "pending_jobs": 0 },
    "google_ads_config": { "status": "ok", "message": "Google Ads configuration found" }
  }
}
```

#### GET `/api/info`
Informações gerais sobre a API.

**Resposta:**
```json
{
  "success": true,
  "data": {
    "name": "KeywordAI API",
    "version": "1.0.0",
    "environment": "production",
    "features": {
      "search_terms": true,
      "campaigns": true,
      "negative_keywords": true,
      "sync": true,
      "ai_analysis": true
    }
  }
}
```

---

### Dashboard

#### GET `/api/dashboard/metrics`
Métricas gerais do dashboard.

**Resposta:**
```json
{
  "success": true,
  "data": {
    "search_terms": {
      "total": 15000,
      "today": 45,
      "this_week": 312,
      "this_month": 1234
    },
    "campaigns": { "total": 12, "active": 8 },
    "performance": {
      "total_impressions": 5000000,
      "total_clicks": 150000,
      "total_cost_micros": 2500000000,
      "avg_ctr": 3.5
    }
  }
}
```

#### GET `/api/dashboard/chart/new-terms`
Dados para gráfico de novos termos.

**Parâmetros:**
- `days` (int): Período em dias (7-90, default: 30)

**Resposta:**
```json
{
  "success": true,
  "data": {
    "labels": ["2025-01-01", "2025-01-02", ...],
    "data": [12, 15, 8, ...],
    "period": "Últimos 30 dias"
  }
}
```

#### GET `/api/dashboard/top-terms`
Top termos de pesquisa.

**Parâmetros:**
- `limit` (int): Quantidade de resultados (1-100, default: 10)
- `sort_by` (string): Campo de ordenação (impressions, clicks, cost_micros, ctr)

---

### Search Terms

#### GET `/api/search-terms`
Listar todos os termos de pesquisa.

**Parâmetros:**
- `search_term` (string): Filtrar por termo (LIKE)
- `campaign_id` (int): Filtrar por ID da campanha
- `campaign_name` (string): Filtrar por nome da campanha
- `ad_group_id` (int): Filtrar por ID do grupo
- `status` (string): Filtrar por status
- `match_type` (string): Filtrar por tipo de correspondência
- `min_impressions` (int): Mínimo de impressões
- `min_clicks` (int): Mínimo de cliques
- `min_cost` (float): Mínimo de custo (em reais)
- `date_from` (date): Data inicial (Y-m-d)
- `date_to` (date): Data final (Y-m-d)
- `sort_by` (string): Campo de ordenação
- `sort_direction` (string): Direção (asc/desc)
- `per_page` (int): Itens por página (1-1000, default: 50)

**Resposta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "search_term": "exemplo de busca",
      "keyword_text": "exemplo",
      "match_type": "BROAD",
      "status": "NONE",
      "impressions": 1500,
      "clicks": 45,
      "cost_micros": 25000000,
      "cost_formatted": "R$ 25,00",
      "ctr": 3.0,
      "ctr_formatted": "3,00%",
      "campaign": { "id": 1234567890, "name": "Campanha Exemplo" },
      "ad_group": { "id": 9876543210, "name": "Grupo Exemplo" },
      "first_seen_at": "2025-02-24",
      "statistics_synced_at": "2025-02-24 10:30:00"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 50,
    "total": 500
  }
}
```

#### GET `/api/search-terms/{id}`
Detalhes de um termo específico.

#### GET `/api/search-terms/stats`
Estatísticas agregadas dos termos.

**Resposta:**
```json
{
  "success": true,
  "data": {
    "total_terms": 15000,
    "total_impressions": 5000000,
    "total_clicks": 150000,
    "total_cost_micros": 2500000000,
    "avg_ctr": 3.5,
    "by_status": { "NONE": 12000, "ADDED": 2000, "EXCLUDED": 1000 },
    "by_match_type": { "BROAD": 8000, "PHRASE": 4000, "EXACT": 3000 }
  }
}
```

#### POST `/api/search-terms/{id}/refresh`
Atualizar estatísticas de um termo específico (síncrono).

#### POST `/api/search-terms/{id}/negate`
Adicionar termo como palavra-chave negativa.

**Body:**
```json
{
  "match_type": "phrase",
  "reason": "Termo irrelevante para o negócio"
}
```

#### POST `/api/search-terms/{id}/add-positive`
Adicionar termo como palavra-chave positiva.

**Body:**
```json
{
  "ad_group_id": 123,
  "match_type": "exact"
}
```

#### POST `/api/search-terms/batch-negate`
Negar múltiplos termos em lote.

**Body:**
```json
{
  "terms": [
    { "id": 1, "reason": "Irrelevante" },
    { "id": 2, "reason": "Concorrência" }
  ],
  "match_type": "phrase"
}
```

---

### Campaigns

#### GET `/api/campaigns`
Listar todas as campanhas.

**Parâmetros:**
- `status` (string): Filtrar por status
- `name` (string): Buscar por nome (LIKE)
- `channel_type` (string): Tipo de canal
- `active_only` (bool): Apenas campanhas ativas
- `sort_by`, `sort_direction`, `per_page`

#### GET `/api/campaigns/{id}`
Detalhes de uma campanha.

#### GET `/api/campaigns/{id}/ad-groups`
Listar grupos de anúncios da campanha.

#### GET `/api/campaigns/{id}/search-terms`
Listar termos de pesquisa da campanha.

#### GET `/api/campaigns/{id}/stats`
Estatísticas da campanha.

---

### Ad Groups

#### GET `/api/ad-groups`
Listar todos os grupos de anúncios.

**Parâmetros:**
- `campaign_id` (int): Filtrar por campanha
- `status` (string): Filtrar por status
- `name` (string): Buscar por nome
- `active_only` (bool): Apenas ativos

#### GET `/api/ad-groups/{id}`
Detalhes de um grupo.

#### GET `/api/ad-groups/{id}/search-terms`
Termos de pesquisa do grupo.

---

### Negative Keywords

#### GET `/api/negative-keywords`
Listar palavras-chave negativas.

**Parâmetros:**
- `keyword` (string): Buscar por termo
- `match_type` (string): Filtrar por tipo
- `list_id` (string): Filtrar por lista
- `created_by` (int): Filtrar por criador

#### GET `/api/negative-keywords/{id}`
Detalhes de uma palavra-chave negativa.

#### POST `/api/negative-keywords`
Criar nova palavra-chave negativa.

**Body:**
```json
{
  "keyword": "termo negativo",
  "match_type": "phrase",
  "reason": "Motivo da negação",
  "list_id": "1234567890"
}
```

#### POST `/api/negative-keywords/batch`
Criar múltiplas palavras-chave negativas.

**Body:**
```json
{
  "keywords": [
    { "keyword": "termo1", "match_type": "broad", "reason": "Motivo 1" },
    { "keyword": "termo2", "match_type": "exact", "reason": "Motivo 2" }
  ],
  "list_id": "1234567890"
}
```

#### GET `/api/negative-keywords/stats`
Estatísticas de palavras-chave negativas.

---

### Sync Operations

#### POST `/api/sync/search-terms`
Sincronizar termos para uma data.

**Body:**
```json
{
  "date": "2025-02-24"
}
```

**Resposta:**
```json
{
  "success": true,
  "data": {
    "job_id": "uuid-do-job",
    "date": "2025-02-24",
    "message": "Sincronização de termos de pesquisa iniciada."
  }
}
```

#### POST `/api/sync/search-terms-range`
Sincronizar termos para um range de datas.

**Body:**
```json
{
  "date_from": "2025-02-20",
  "date_to": "2025-02-24"
}
```

#### POST `/api/sync/entities`
Sincronizar campanhas e grupos de anúncios.

#### GET `/api/sync/status`
Status das sincronizações.

**Parâmetros:**
- `date_from`, `date_to`, `status`, `per_page`

**Resposta inclui:**
- Lista de status por data
- Resumo: pending, processing, completed, failed

#### GET `/api/sync/queue-status`
Status das filas de processamento.

---

### AI Analysis

#### GET `/api/ai/models`
Modelos de IA disponíveis.

**Resposta:**
```json
{
  "success": true,
  "data": {
    "gemini": {
      "name": "Gemini (Google)",
      "model": "gemini-2.5-flash-preview-04-17",
      "available": true
    },
    "openai": {
      "name": "OpenAI (GPT)",
      "model": "gpt-4o",
      "available": false
    }
  }
}
```

#### POST `/api/ai/analyze`
Analisar termos de pesquisa com IA.

**Body:**
```json
{
  "analysis_type": "date",
  "date": "2025-02-24",
  "model": "gemini",
  "limit": 50,
  "min_impressions": 10,
  "min_clicks": 0,
  "min_cost": 0
}
```

**Tipos de análise:**
- `date` - Análise por data específica
- `top` - Top termos
- `custom` - Filtros personalizados

#### POST `/api/ai/suggest-negatives`
Sugestões de negativação baseadas em IA.

**Body:**
```json
{
  "model": "gemini",
  "date_from": "2025-02-20",
  "date_to": "2025-02-24",
  "min_impressions": 50,
  "limit": 100,
  "auto_negate": true,
  "match_type": "phrase"
}
```

---

### Token Management

> **Nota:** Endpoints de administração requerem permissão `admin`.

#### GET `/api/admin/tokens`
Listar todos os tokens.

#### POST `/api/admin/tokens`
Criar novo token via API.

**Body:**
```json
{
  "name": "Token para integração",
  "expires_in_days": 90,
  "permissions": ["read", "write", "sync"]
}
```

#### GET `/api/admin/tokens/{id}`
Detalhes de um token.

#### PUT `/api/admin/tokens/{id}`
Atualizar token.

**Body:**
```json
{
  "name": "Novo nome",
  "is_active": false,
  "permissions": ["read"]
}
```

#### DELETE `/api/admin/tokens/{id}`
Revogar token.

#### GET `/api/token/me`
Informações do token atual.

---

## Exemplos de Uso

### cURL

```bash
# Listar termos de pesquisa
curl -X GET "https://api.exemplo.com/api/search-terms" \
  -H "X-API-Token: seu_token_aqui"

# Criar palavra-chave negativa
curl -X POST "https://api.exemplo.com/api/negative-keywords" \
  -H "X-API-Token: seu_token_aqui" \
  -H "Content-Type: application/json" \
  -d '{
    "keyword": "termo negativo",
    "match_type": "phrase",
    "reason": "Irrelevante"
  }'

# Sincronizar termos para uma data
curl -X POST "https://api.exemplo.com/api/sync/search-terms" \
  -H "X-API-Token: seu_token_aqui" \
  -H "Content-Type: application/json" \
  -d '{"date": "2025-02-24"}'

# Analisar com IA
curl -X POST "https://api.exemplo.com/api/ai/analyze" \
  -H "X-API-Token: seu_token_aqui" \
  -H "Content-Type: application/json" \
  -d '{
    "analysis_type": "date",
    "date": "2025-02-24",
    "model": "gemini",
    "limit": 50
  }'
```

### Python

```python
import requests

API_URL = "https://api.exemplo.com/api"
TOKEN = "seu_token_aqui"
headers = {"X-API-Token": TOKEN}

# Listar termos
response = requests.get(f"{API_URL}/search-terms", headers=headers)
terms = response.json()

# Criar negativa
response = requests.post(
    f"{API_URL}/negative-keywords",
    headers={**headers, "Content-Type": "application/json"},
    json={"keyword": "termo", "match_type": "phrase"}
)
```

### JavaScript/Node.js

```javascript
const API_URL = 'https://api.exemplo.com/api';
const TOKEN = 'seu_token_aqui';

// Listar termos
fetch(`${API_URL}/search-terms`, {
  headers: { 'X-API-Token': TOKEN }
})
.then(res => res.json())
.then(data => console.log(data));

// Análise de IA
fetch(`${API_URL}/ai/analyze`, {
  method: 'POST',
  headers: {
    'X-API-Token': TOKEN,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    analysis_type: 'date',
    date: '2025-02-24',
    model: 'gemini',
    limit: 50
  })
})
.then(res => res.json())
.then(data => console.log(data));
```

---

## Rate Limits

- **Google Ads API**: 14.000 requisições/dia, 60/minuto
- **API KeywordAI**: Sem limites específicos (limitado pelo Google Ads)

---

## Códigos de Erro

| Código | Descrição |
|--------|-----------|
| 200 | Sucesso |
| 201 | Criado com sucesso |
| 400 | Requisição inválida |
| 401 | Token não fornecido ou inválido |
| 403 | Sem permissão |
| 404 | Recurso não encontrado |
| 422 | Validação falhou |
| 500 | Erro interno do servidor |
| 503 | Serviço indisponível |

---

## Formato de Erro

```json
{
  "success": false,
  "message": "Descrição do erro",
  "errors": {
    "campo": ["Mensagem de erro"]
  }
}
```
