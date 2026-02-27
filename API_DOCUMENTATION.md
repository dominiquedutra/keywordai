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
- [Exemplos de Uso](#exemplos-de-uso)
- [Rate Limits](#rate-limits)
- [Códigos de Erro](#códigos-de-erro)

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

## Formato de Resposta

Todas as respostas seguem o padrão:

**Sucesso (recurso único):**
```json
{
  "success": true,
  "message": "Descrição",
  "data": { ... }
}
```

**Sucesso (paginado):**
```json
{
  "success": true,
  "message": "Descrição",
  "data": [ ... ],
  "pagination": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 50,
    "total": 500
  }
}
```

**Erro:**
```json
{
  "success": false,
  "message": "Descrição do erro",
  "errors": {
    "campo": ["Mensagem de erro"]
  }
}
```

> **Nota sobre custos:** Valores de custo da Google Ads são armazenados em **micros** (1 real = 1.000.000 micros). O parâmetro `min_cost` aceita valores em reais e converte internamente.

---

## Endpoints

### Health & Info

> Endpoints públicos — não requerem autenticação.

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
    "negative_keywords": { "total": 500 },
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
| Parâmetro | Tipo | Default | Descrição |
|-----------|------|---------|-----------|
| `days` | int | 30 | Período em dias (7–90) |

**Resposta:**
```json
{
  "success": true,
  "data": {
    "labels": ["2025-01-01", "2025-01-02"],
    "data": [12, 15],
    "period": "Últimos 30 dias"
  }
}
```

#### GET `/api/dashboard/top-terms`
Top termos de pesquisa por métrica.

**Parâmetros:**
| Parâmetro | Tipo | Default | Descrição |
|-----------|------|---------|-----------|
| `limit` | int | 10 | Quantidade de resultados (1–100) |
| `sort_by` | string | impressions | Campo de ordenação: `impressions`, `clicks`, `cost_micros`, `ctr` |

#### GET `/api/dashboard/activity`
Atividade recente (novos termos e palavras-chave negativas).

**Parâmetros:**
| Parâmetro | Tipo | Default | Descrição |
|-----------|------|---------|-----------|
| `limit` | int | 20 | Quantidade de resultados (1–100) |

---

### Search Terms

#### GET `/api/search-terms`
Listar todos os termos de pesquisa com filtros avançados.

**Parâmetros:**
| Parâmetro | Tipo | Default | Descrição |
|-----------|------|---------|-----------|
| `search_term` | string | — | Filtrar por termo (LIKE) |
| `campaign_id` | int | — | Filtrar por ID da campanha |
| `campaign_name` | string | — | Filtrar por nome da campanha (LIKE) |
| `ad_group_id` | int | — | Filtrar por ID do grupo de anúncios |
| `status` | string | — | Filtrar por status |
| `match_type` | string | — | Filtrar por tipo de correspondência |
| `min_impressions` | int | — | Mínimo de impressões |
| `min_clicks` | int | — | Mínimo de cliques |
| `min_cost` | float | — | Mínimo de custo (em reais, convertido internamente para micros) |
| `date_from` | date | — | Data inicial (Y-m-d) |
| `date_to` | date | — | Data final (Y-m-d) |
| `sort_by` | string | first_seen_at | Campo de ordenação: `impressions`, `clicks`, `cost_micros`, `first_seen_at`, `created_at`, `search_term` |
| `sort_direction` | string | desc | Direção: `asc` ou `desc` |
| `per_page` | int | 50 | Itens por página (1–1000) |

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

#### GET `/api/search-terms/stats`
Estatísticas agregadas dos termos.

**Parâmetros:**
| Parâmetro | Tipo | Default | Descrição |
|-----------|------|---------|-----------|
| `date_from` | date | — | Data inicial (Y-m-d) |
| `date_to` | date | — | Data final (Y-m-d) |

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
    "by_match_type": { "BROAD": 8000, "PHRASE": 4000, "EXACT": 3000 },
    "top_campaigns": [ ... ]
  }
}
```

#### GET `/api/search-terms/{id}`
Detalhes de um termo específico.

#### POST `/api/search-terms/{id}/refresh`
Atualizar estatísticas de um termo específico (síncrono, chama Google Ads API diretamente).

#### POST `/api/search-terms/{id}/negate`
Adicionar termo como palavra-chave negativa (via fila).

**Body:**
```json
{
  "match_type": "phrase",
  "reason": "Termo irrelevante para o negócio"
}
```

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `match_type` | string | Sim | `exact`, `phrase` ou `broad` |
| `reason` | string | Não | Motivo da negação (máx. 1000 caracteres) |

#### POST `/api/search-terms/{id}/add-positive`
Adicionar termo como palavra-chave positiva em um grupo de anúncios (via fila).

**Body:**
```json
{
  "ad_group_id": 123,
  "match_type": "exact"
}
```

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `ad_group_id` | int | Sim | ID do grupo de anúncios (deve existir no banco) |
| `match_type` | string | Sim | `exact`, `phrase` ou `broad` |

#### POST `/api/search-terms/batch-negate`
Negar múltiplos termos em lote (via fila).

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

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `terms` | array | Sim | Lista de termos com `id` (obrigatório) e `reason` (opcional) |
| `match_type` | string | Sim | `exact`, `phrase` ou `broad` |

---

### Campaigns

#### GET `/api/campaigns`
Listar todas as campanhas.

**Parâmetros:**
| Parâmetro | Tipo | Default | Descrição |
|-----------|------|---------|-----------|
| `status` | string | — | Filtrar por status |
| `name` | string | — | Buscar por nome (LIKE) |
| `channel_type` | string | — | Tipo de canal |
| `active_only` | bool | — | Apenas campanhas ativas |
| `sort_by` | string | name | Campo de ordenação |
| `sort_direction` | string | asc | Direção: `asc` ou `desc` |
| `per_page` | int | 50 | Itens por página (1–500) |

#### GET `/api/campaigns/{id}`
Detalhes de uma campanha (inclui grupos de anúncios relacionados).

#### GET `/api/campaigns/{id}/ad-groups`
Listar grupos de anúncios da campanha.

**Parâmetros:**
| Parâmetro | Tipo | Default | Descrição |
|-----------|------|---------|-----------|
| `active_only` | bool | — | Apenas ativos |
| `per_page` | int | 50 | Itens por página (1–500) |

#### GET `/api/campaigns/{id}/search-terms`
Listar termos de pesquisa da campanha.

**Parâmetros:**
| Parâmetro | Tipo | Default | Descrição |
|-----------|------|---------|-----------|
| `per_page` | int | 50 | Itens por página (1–1000) |

#### GET `/api/campaigns/{id}/stats`
Estatísticas da campanha: contagem de ad groups, termos, impressões, cliques, custo e CTR.

---

### Ad Groups

#### GET `/api/ad-groups`
Listar todos os grupos de anúncios.

**Parâmetros:**
| Parâmetro | Tipo | Default | Descrição |
|-----------|------|---------|-----------|
| `campaign_id` | int | — | Filtrar por ID de campanha Google Ads |
| `status` | string | — | Filtrar por status |
| `name` | string | — | Buscar por nome (LIKE) |
| `active_only` | bool | — | Apenas ativos |
| `sort_by` | string | name | Campo de ordenação |
| `sort_direction` | string | asc | Direção: `asc` ou `desc` |
| `per_page` | int | 50 | Itens por página (1–500) |

#### GET `/api/ad-groups/{id}`
Detalhes de um grupo (inclui campanha relacionada).

#### GET `/api/ad-groups/{id}/search-terms`
Termos de pesquisa do grupo.

**Parâmetros:**
| Parâmetro | Tipo | Default | Descrição |
|-----------|------|---------|-----------|
| `per_page` | int | 50 | Itens por página (1–1000) |

---

### Negative Keywords

#### GET `/api/negative-keywords`
Listar palavras-chave negativas.

**Parâmetros:**
| Parâmetro | Tipo | Default | Descrição |
|-----------|------|---------|-----------|
| `keyword` | string | — | Buscar por termo (LIKE) |
| `match_type` | string | — | Filtrar por tipo: `exact`, `phrase`, `broad` |
| `list_id` | string | — | Filtrar por lista |
| `created_by` | int | — | Filtrar por ID do criador |
| `sort_by` | string | created_at | Campo de ordenação |
| `sort_direction` | string | desc | Direção: `asc` ou `desc` |
| `per_page` | int | 50 | Itens por página (1–1000) |

#### GET `/api/negative-keywords/stats`
Estatísticas de palavras-chave negativas: total, por match type, por lista, últimos 7 dias.

#### GET `/api/negative-keywords/{id}`
Detalhes de uma palavra-chave negativa.

#### POST `/api/negative-keywords`
Criar nova palavra-chave negativa (enfileira job para Google Ads).

**Body:**
```json
{
  "keyword": "termo negativo",
  "match_type": "phrase",
  "reason": "Motivo da negação",
  "list_id": "1234567890"
}
```

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `keyword` | string | Sim | Termo (máx. 255 caracteres) |
| `match_type` | string | Sim | `exact`, `phrase` ou `broad` |
| `reason` | string | Não | Motivo (máx. 1000 caracteres) |
| `list_id` | string | Não | ID da lista negativa (default: `config/googleads.default_negative_list_id`) |

#### POST `/api/negative-keywords/batch`
Criar múltiplas palavras-chave negativas em lote.

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

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `keywords` | array | Sim | Lista de objetos com `keyword` (obrigatório), `match_type` (obrigatório), `reason` (opcional) |
| `list_id` | string | Não | ID da lista negativa (default: configuração global) |

Retorna status individual para cada item (queued ou erro).

---

### Sync Operations

#### POST `/api/sync/search-terms`
Sincronizar termos de pesquisa para uma data (via fila).

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
Sincronizar termos para um range de datas (cria um job por dia).

**Body:**
```json
{
  "date_from": "2025-02-20",
  "date_to": "2025-02-24"
}
```

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `date_from` | date | Sim | Data inicial (Y-m-d) |
| `date_to` | date | Sim | Data final (Y-m-d, deve ser >= date_from) |

#### POST `/api/sync/entities`
Sincronizar campanhas e grupos de anúncios (via fila).

#### GET `/api/sync/status`
Status das sincronizações de termos.

**Parâmetros:**
| Parâmetro | Tipo | Default | Descrição |
|-----------|------|---------|-----------|
| `date_from` | date | — | Data inicial |
| `date_to` | date | — | Data final |
| `status` | string | — | Filtrar: `pending`, `processing`, `completed`, `failed` |
| `per_page` | int | 30 | Itens por página (1–100) |

**Resposta inclui:**
- Lista paginada de status por data
- Resumo: pending, processing, completed, failed

#### GET `/api/sync/queue-status`
Status das filas de processamento: jobs pendentes, jobs com falha, últimos 10 jobs.

---

### AI Analysis

#### GET `/api/ai/models`
Modelos de IA disponíveis e seus status.

**Resposta:**
```json
{
  "success": true,
  "data": {
    "gemini": {
      "name": "Gemini (Google)",
      "model": "gemini-2.5-flash",
      "available": true
    },
    "openai": {
      "name": "OpenAI (GPT)",
      "model": "gpt-4o",
      "available": false
    },
    "openrouter": {
      "name": "OpenRouter",
      "model": "google/gemini-2.0-flash-001",
      "available": true
    }
  }
}
```

> **Nota sobre modelos:** Use sempre nomes estáveis de modelos (ex: `gemini-2.5-flash`), nunca nomes com sufixo `-preview-*`.

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

| Campo | Tipo | Obrigatório | Default | Descrição |
|-------|------|-------------|---------|-----------|
| `analysis_type` | string | Sim | — | `date` (por data), `top` (top termos por custo), `custom` (filtros personalizados) |
| `date` | date | Se type=date | — | Data no formato Y-m-d |
| `model` | string | Sim | — | `gemini`, `openai` ou `openrouter` |
| `limit` | int | Não | 50 | Quantidade de termos a analisar (1–200) |
| `min_impressions` | int | Não | 0 | Mínimo de impressões |
| `min_clicks` | int | Não | 0 | Mínimo de cliques |
| `min_cost` | float | Não | 0 | Mínimo de custo (em reais) |
| `filters` | array | Não | — | Filtros adicionais (para type=custom) |

**Tipos de análise:**
- `date` — Analisa termos vistos numa data específica
- `top` — Analisa os termos com maior custo
- `custom` — Filtros personalizados via parâmetro `filters`

#### POST `/api/ai/suggest-negatives`
Sugestões de negativação baseadas em IA. Opcionalmente executa a negação automaticamente.

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

| Campo | Tipo | Obrigatório | Default | Descrição |
|-------|------|-------------|---------|-----------|
| `model` | string | Sim | — | `gemini`, `openai` ou `openrouter` |
| `date_from` | date | Não | — | Data inicial (Y-m-d) |
| `date_to` | date | Não | — | Data final (Y-m-d) |
| `min_impressions` | int | Não | 0 | Mínimo de impressões |
| `limit` | int | Não | 50 | Quantidade de termos (1–100) |
| `auto_negate` | bool | Não | false | Se true, nega automaticamente os termos sugeridos |
| `match_type` | string | Se auto_negate | — | `exact`, `phrase` ou `broad` (obrigatório se auto_negate=true) |

---

### Token Management

> **Nota:** Endpoints de administração (`/api/admin/*`) requerem permissão `admin`.

#### GET `/api/token/me`
Informações do token atual (qualquer token autenticado).

#### GET `/api/admin/tokens`
Listar todos os tokens.

**Parâmetros:**
| Parâmetro | Tipo | Default | Descrição |
|-----------|------|---------|-----------|
| `is_active` | bool | — | Filtrar por status ativo/inativo |
| `sort_by` | string | created_at | Campo de ordenação |
| `sort_direction` | string | desc | Direção: `asc` ou `desc` |
| `per_page` | int | 20 | Itens por página (1–100) |

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

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `name` | string | Não | Nome descritivo do token |
| `expires_in_days` | int | Não | Dias até expirar (1–365) |
| `permissions` | array | Não | Lista: `read`, `write`, `sync`, `ai`, `admin` |

> **Importante:** O token completo é retornado **apenas na criação**. Armazene-o com segurança.

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

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `name` | string | Não | Novo nome |
| `is_active` | bool | Não | Ativar/desativar token |
| `permissions` | array | Não | Novas permissões |

#### DELETE `/api/admin/tokens/{id}`
Revogar token (define `is_active=false`).

---

## Resumo de Endpoints

| # | Método | Endpoint | Auth | Descrição |
|---|--------|----------|------|-----------|
| 1 | GET | `/api/health` | Público | Health check |
| 2 | GET | `/api/info` | Público | Info da API |
| 3 | GET | `/api/dashboard/metrics` | Token | Métricas do dashboard |
| 4 | GET | `/api/dashboard/chart/new-terms` | Token | Gráfico de novos termos |
| 5 | GET | `/api/dashboard/top-terms` | Token | Top termos |
| 6 | GET | `/api/dashboard/activity` | Token | Atividade recente |
| 7 | GET | `/api/search-terms` | Token | Listar termos |
| 8 | GET | `/api/search-terms/stats` | Token | Estatísticas dos termos |
| 9 | GET | `/api/search-terms/{id}` | Token | Detalhes do termo |
| 10 | POST | `/api/search-terms/{id}/refresh` | Token | Atualizar stats |
| 11 | POST | `/api/search-terms/{id}/negate` | Token | Negar termo |
| 12 | POST | `/api/search-terms/{id}/add-positive` | Token | Adicionar como positiva |
| 13 | POST | `/api/search-terms/batch-negate` | Token | Negar em lote |
| 14 | GET | `/api/campaigns` | Token | Listar campanhas |
| 15 | GET | `/api/campaigns/{id}` | Token | Detalhes da campanha |
| 16 | GET | `/api/campaigns/{id}/ad-groups` | Token | Ad groups da campanha |
| 17 | GET | `/api/campaigns/{id}/search-terms` | Token | Termos da campanha |
| 18 | GET | `/api/campaigns/{id}/stats` | Token | Stats da campanha |
| 19 | GET | `/api/ad-groups` | Token | Listar ad groups |
| 20 | GET | `/api/ad-groups/{id}` | Token | Detalhes do ad group |
| 21 | GET | `/api/ad-groups/{id}/search-terms` | Token | Termos do ad group |
| 22 | GET | `/api/negative-keywords` | Token | Listar negativas |
| 23 | GET | `/api/negative-keywords/stats` | Token | Stats negativas |
| 24 | GET | `/api/negative-keywords/{id}` | Token | Detalhes negativa |
| 25 | POST | `/api/negative-keywords` | Token | Criar negativa |
| 26 | POST | `/api/negative-keywords/batch` | Token | Criar negativas em lote |
| 27 | POST | `/api/sync/search-terms` | Token | Sync por data |
| 28 | POST | `/api/sync/search-terms-range` | Token | Sync por range |
| 29 | POST | `/api/sync/entities` | Token | Sync campanhas/grupos |
| 30 | GET | `/api/sync/status` | Token | Status de sync |
| 31 | GET | `/api/sync/queue-status` | Token | Status da fila |
| 32 | GET | `/api/ai/models` | Token | Modelos disponíveis |
| 33 | POST | `/api/ai/analyze` | Token | Análise com IA |
| 34 | POST | `/api/ai/suggest-negatives` | Token | Sugestões de negação |
| 35 | GET | `/api/token/me` | Token | Info do token atual |
| 36 | GET | `/api/admin/tokens` | Admin | Listar tokens |
| 37 | POST | `/api/admin/tokens` | Admin | Criar token |
| 38 | GET | `/api/admin/tokens/{id}` | Admin | Detalhes do token |
| 39 | PUT | `/api/admin/tokens/{id}` | Admin | Atualizar token |
| 40 | DELETE | `/api/admin/tokens/{id}` | Admin | Revogar token |

**Total: 40 endpoints** (2 públicos, 33 autenticados, 5 admin)

---

## Exemplos de Uso

### cURL

```bash
# Listar termos de pesquisa
curl -X GET "https://keywordai.fibersals.com.br/api/search-terms" \
  -H "X-API-Token: seu_token_aqui"

# Criar palavra-chave negativa
curl -X POST "https://keywordai.fibersals.com.br/api/negative-keywords" \
  -H "X-API-Token: seu_token_aqui" \
  -H "Content-Type: application/json" \
  -d '{
    "keyword": "termo negativo",
    "match_type": "phrase",
    "reason": "Irrelevante"
  }'

# Sincronizar termos para uma data
curl -X POST "https://keywordai.fibersals.com.br/api/sync/search-terms" \
  -H "X-API-Token: seu_token_aqui" \
  -H "Content-Type: application/json" \
  -d '{"date": "2025-02-24"}'

# Analisar com IA
curl -X POST "https://keywordai.fibersals.com.br/api/ai/analyze" \
  -H "X-API-Token: seu_token_aqui" \
  -H "Content-Type: application/json" \
  -d '{
    "analysis_type": "date",
    "date": "2025-02-24",
    "model": "gemini",
    "limit": 50
  }'

# Sugestões de negação com auto-negate
curl -X POST "https://keywordai.fibersals.com.br/api/ai/suggest-negatives" \
  -H "X-API-Token: seu_token_aqui" \
  -H "Content-Type: application/json" \
  -d '{
    "model": "gemini",
    "date_from": "2025-02-20",
    "date_to": "2025-02-24",
    "min_impressions": 50,
    "limit": 100,
    "auto_negate": true,
    "match_type": "phrase"
  }'
```

### Python

```python
import requests

API_URL = "https://keywordai.fibersals.com.br/api"
TOKEN = "seu_token_aqui"
headers = {"X-API-Token": TOKEN}

# Listar termos com filtros
response = requests.get(
    f"{API_URL}/search-terms",
    headers=headers,
    params={"min_impressions": 100, "sort_by": "cost_micros", "sort_direction": "desc"}
)
terms = response.json()

# Criar negativa
response = requests.post(
    f"{API_URL}/negative-keywords",
    headers=headers,
    json={"keyword": "termo", "match_type": "phrase", "reason": "Irrelevante"}
)

# Análise de IA
response = requests.post(
    f"{API_URL}/ai/analyze",
    headers=headers,
    json={
        "analysis_type": "top",
        "model": "gemini",
        "limit": 100,
        "min_impressions": 10
    }
)
analysis = response.json()
```

### JavaScript/Node.js

```javascript
const API_URL = 'https://keywordai.fibersals.com.br/api';
const TOKEN = 'seu_token_aqui';
const headers = {
  'X-API-Token': TOKEN,
  'Content-Type': 'application/json'
};

// Listar termos
const terms = await fetch(`${API_URL}/search-terms?min_cost=5&sort_by=cost_micros`, {
  headers
}).then(r => r.json());

// Análise de IA
const analysis = await fetch(`${API_URL}/ai/analyze`, {
  method: 'POST',
  headers,
  body: JSON.stringify({
    analysis_type: 'date',
    date: '2025-02-24',
    model: 'gemini',
    limit: 50
  })
}).then(r => r.json());

// Negar termos em lote
const result = await fetch(`${API_URL}/search-terms/batch-negate`, {
  method: 'POST',
  headers,
  body: JSON.stringify({
    terms: [
      { id: 1, reason: 'Irrelevante' },
      { id: 2, reason: 'Concorrência' }
    ],
    match_type: 'phrase'
  })
}).then(r => r.json());
```

---

## Rate Limits

- **Google Ads API**: 14.000 requisições/dia, 60/minuto
- **API KeywordAI**: Sem limites específicos (limitado pelo Google Ads e pela capacidade do servidor)

---

## Códigos de Erro

| Código | Descrição |
|--------|-----------|
| 200 | Sucesso |
| 201 | Criado com sucesso |
| 400 | Requisição inválida |
| 401 | Token não fornecido ou inválido |
| 403 | Sem permissão para o recurso |
| 404 | Recurso não encontrado |
| 422 | Validação falhou (detalhes no campo `errors`) |
| 500 | Erro interno do servidor |
| 503 | Serviço indisponível |
