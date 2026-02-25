# Configuração de Filas para KeywordAI

Este documento descreve a configuração de filas implementada para o KeywordAI, explicando a estrutura, prioridades e como configurar os workers no servidor de produção.

## Estrutura de Filas

Implementamos uma estrutura de filas com diferentes prioridades para garantir que as operações críticas sejam processadas primeiro:

### 1. Fila `critical` (Prioridade Máxima)
- **Propósito:** Processar operações críticas iniciadas pelo usuário
- **Jobs:**
  - `AddKeywordToAdGroupJob` - Adiciona palavras-chave positivas
  - `AddNegativeKeywordJob` - Adiciona palavras-chave negativas
- **Características:** Processamento imediato, alta prioridade

### 2. Fila `notifications` (Alta Prioridade)
- **Propósito:** Enviar notificações sobre novos termos de pesquisa
- **Jobs:**
  - `SendNewSearchTermNotificationJob` - Envia notificações via Google Chat
- **Características:** Processamento rápido, prioridade alta

### 3. Fila `default` (Média Prioridade)
- **Propósito:** Sincronizações regulares e atualizações de estatísticas
- **Jobs:**
  - `SyncAdsEntitiesJob` - Sincroniza campanhas e grupos de anúncios
  - `SyncSearchTermStatsJob` - Atualiza estatísticas de termos de pesquisa
- **Características:** Processamento regular, prioridade média

### 4. Fila `bulk` (Baixa Prioridade)
- **Propósito:** Tarefas de fundo longas e intensivas
- **Jobs:**
  - `SyncSearchTermsForDateJob` - Sincroniza termos de pesquisa para uma data específica
- **Características:** Processamento em segundo plano, prioridade baixa

### Implementação das Filas

As filas são especificadas no momento do despacho dos jobs usando o método `onQueue()`. Por exemplo:

```php
// Despachar um job para a fila 'critical'
AddKeywordToAdGroupJob::dispatch($searchTerm, $adGroupId, $matchType)->onQueue('critical');

// Despachar um job para a fila 'notifications'
SendNewSearchTermNotificationJob::dispatch($searchTerm, $campaignName)->onQueue('notifications');

// Despachar um job para a fila 'default'
SyncSearchTermStatsJob::dispatch($searchTerm)->onQueue('default');

// Despachar um job para a fila 'bulk'
dispatch(new SyncSearchTermsForDateJob($date))->onQueue('bulk');
```

> **Nota Importante:** Não defina a propriedade `$queue` diretamente nas classes de Job, pois isso pode causar conflitos com o trait `Queueable`. Sempre use o método `onQueue()` no momento do despacho.

## Configuração no Servidor de Produção

### Pré-requisitos
- Laravel configurado com um driver de fila (recomendamos Redis para produção)
- Supervisor instalado no servidor

### Passos para Configuração

1. **Configurar o Driver de Fila no Laravel**

   Edite o arquivo `.env` no servidor de produção:
   ```
   QUEUE_CONNECTION=redis
   REDIS_HOST=127.0.0.1
   REDIS_PASSWORD=null
   REDIS_PORT=6379
   ```

   Se você preferir usar o banco de dados como driver de fila:
   ```
   QUEUE_CONNECTION=database
   ```
   E certifique-se de que a tabela `jobs` foi criada:
   ```bash
   php artisan queue:table
   php artisan migrate
   ```

2. **Instalar o Supervisor (se ainda não estiver instalado)**
   ```bash
   sudo apt-get update
   sudo apt-get install supervisor
   ```

3. **Configurar o Supervisor**
   
   Copie o arquivo `supervisor-keywordai.conf` para o diretório de configuração do Supervisor:
   ```bash
   sudo cp supervisor-keywordai.conf /etc/supervisor/conf.d/
   ```

   Ajuste os caminhos no arquivo de configuração conforme necessário para o seu ambiente.

4. **Recarregar e Iniciar o Supervisor**
   ```bash
   sudo supervisorctl reread
   sudo supervisorctl update
   ```

5. **Verificar o Status dos Workers**
   ```bash
   sudo supervisorctl status all
   ```

## Monitoramento e Manutenção

### Verificar Logs
Os logs dos workers estão configurados para serem salvos em:
- `/var/log/supervisor/keywordai-critical.log`
- `/var/log/supervisor/keywordai-notifications.log`
- `/var/log/supervisor/keywordai-default.log`
- `/var/log/supervisor/keywordai-bulk.log`

### Reiniciar Workers
Se precisar reiniciar todos os workers:
```bash
sudo supervisorctl restart keywordai:*
```

Para reiniciar apenas um grupo específico:
```bash
sudo supervisorctl restart keywordai-critical:*
```

### Verificar Filas
Para verificar o status das filas (número de jobs pendentes):
```bash
php artisan queue:monitor critical,notifications,default,bulk
```

## Ajustes de Performance

Os parâmetros de configuração dos workers podem ser ajustados conforme necessário:

- **numprocs**: Número de processos worker para cada fila
- **sleep**: Tempo de espera (em segundos) entre verificações da fila quando vazia
- **tries**: Número de tentativas antes de marcar um job como falho
- **max-time**: Tempo máximo (em segundos) que um worker deve rodar antes de ser reiniciado

Ajuste esses valores com base na carga de trabalho e recursos do servidor.

## Considerações Adicionais

- **Balanceamento de Carga**: Se você estiver executando o KeywordAI em múltiplos servidores, certifique-se de que apenas um deles execute o scheduler (`php artisan schedule:run`).
- **Monitoramento**: Considere implementar um sistema de monitoramento para alertar sobre falhas nos workers ou acúmulo de jobs nas filas.
- **Falhas**: Os jobs que falharem após todas as tentativas serão movidos para a tabela `failed_jobs`. Use `php artisan queue:failed` para listar e `php artisan queue:retry all` para retentar todos.
