# Active Context - KeywordAI

**Version:** 2.5
**Date:** 2025-04-21
**Source:** Implementação de Comando para Atualização de Estatísticas de Search Terms

## 1. Current Work Focus

*   **Controle de Criação de Contas:** Implementada funcionalidade para permitir ou bloquear a criação de novas contas de usuário através de uma configuração no arquivo `.env` (`ALLOW_ACCOUNT_CREATION`) e `config/app.php`. Isso permite ao administrador controlar facilmente o acesso ao registro.

*   **Comando para Atualização de Estatísticas de Search Terms:** Implementado um novo comando Artisan `keywordai:queue-sync-stats` que enfileira jobs para sincronizar estatísticas de todos os search terms em um intervalo de datas. O comando utiliza por padrão os últimos 7 dias se não forem fornecidas datas específicas. Todos os jobs são enfileirados para processamento assíncrono, permitindo a atualização em massa de estatísticas sem sobrecarregar a API do Google Ads.

*   **Interface Web para Fila e Comandos:** Implementada uma interface web em `/queue-commands` que permite visualizar estatísticas da fila e executar comandos diretamente pelo navegador. A interface mostra informações detalhadas sobre jobs na fila (prontos, reservados, adiados, enterrados) e permite executar o comando de sincronização de estatísticas com parâmetros personalizados. Adicionado link para esta página no Dashboard para facilitar o acesso.

*   **Análise de Termos de Pesquisa com IA:** Implementado um novo comando Artisan que utiliza modelos de IA (Gemini, OpenAI, Perplexity) para analisar termos de pesquisa com status NONE e identificar candidatos à negativação, com base no contexto de palavras-chave negativas existentes e seus motivos. Criada também uma interface web dedicada para análise de termos com IA, permitindo ao usuário escolher entre análise por data específica ou top termos por custo, e negativar os termos recomendados diretamente pela interface.
*   **Filtros Adicionais para Termos de Pesquisa:** Implementados novos filtros na página `/search-terms` para permitir a filtragem por quantidade de impressões, cliques e custo superiores a valores específicos, facilitando a identificação de termos com real impacto nas campanhas.
*   **Configurações de Inteligência Artificial:** Implementada uma seção dedicada nas configurações globais para gerenciar integrações com serviços de IA, incluindo armazenamento seguro de chaves de API e instruções customizadas para diferentes modelos.
*   **Gráfico de Novos Termos no Dashboard:** Implementado um gráfico de barras na seção "Visão Geral" do Dashboard que mostra a quantidade de novos termos de pesquisa por dia, com base na data em que foram vistos pela primeira vez (`first_seen_at`). O gráfico permite navegação mês a mês e exibe os termos coloridos por status (NONE, EXCLUDED, ADDED, ADDED_EXCLUDED).
*   **Rastreamento de Atividades de Usuários:** Implementado um sistema completo para rastrear quem adicionou palavras-chave (positivas e negativas) ao sistema, com detalhes sobre quando, onde e o que foi adicionado.
*   **Armazenamento de Palavras-chave Negativas:** Criada uma tabela dedicada para armazenar palavras-chave negativas com informações detalhadas, incluindo o motivo da negativação.
*   **Interface para Visualização de Logs:** Desenvolvida uma interface para visualizar e filtrar os logs de atividade, permitindo auditoria completa das ações realizadas no sistema.
*   **Interface para Palavras-chave Negativas:** Implementada uma interface para listar e visualizar palavras-chave negativas, com filtros e detalhes completos.
*   **Melhoria na Experiência do Usuário:** Adicionado campo de "Motivo" ao modal de negativação na tela de termos de pesquisa para capturar a justificativa da adição de palavras-chave negativas.
*   **Menu de Navegação:** Implementado um menu de navegação consistente em todas as páginas da aplicação para facilitar o acesso às principais funcionalidades.
*   **Remoção de Link Flutuante Redundante:** Removido o link/botão flutuante para configurações globais que aparecia no canto inferior esquerdo das telas, uma vez que o acesso às configurações já está disponível no menu de navegação superior.

## 2. Recent Changes

*   **Ajuste na Tela de Análise de IA:**
    *   Modificado o valor padrão do campo "Limite de Termos" de 20 para 50 na view `resources/views/ai_analysis/index.blade.php`.

*   **Implementação de Comando para Atualização de Estatísticas de Search Terms:**
    *   Criado comando Artisan `keywordai:queue-sync-stats` para enfileirar jobs `SyncSearchTermStatsJob` para todos os termos de pesquisa em um intervalo de datas.
    *   Implementada lógica para filtrar termos de pesquisa pelo campo `first_seen_at` dentro do intervalo de datas especificado.
    *   Adicionadas opções para personalizar a execução: `--start-date`, `--end-date`, `--chunk-size`, `--queue` e `--dry-run`.
    *   Implementado processamento em chunks para evitar problemas de memória com grandes conjuntos de dados.
    *   Adicionado feedback visual durante a execução, mostrando o progresso a cada 100 jobs enfileirados.
    *   Implementada validação robusta das datas de entrada, com mensagens de erro claras.
    *   Configurado para usar os últimos 7 dias como intervalo padrão quando nenhuma data é fornecida.

*   **Implementação de Interface para Fila e Comandos:**
    *   Criado controlador `QueueCommandsController` para gerenciar a interface de filas e comandos.
    *   Implementada página `/queue-commands` que exibe estatísticas da fila e permite executar comandos.
    *   Adicionadas estatísticas detalhadas da fila: total de jobs, jobs prontos, jobs reservados, jobs falhos e contagem por fila.
    *   Implementado formulário para executar o comando `keywordai:queue-sync-stats` com todas as suas opções.
    *   Adicionado link "Fila e Comandos" ao menu de navegação principal.
    *   Adicionado card para "Fila e Comandos" no Dashboard, com ícone de Terminal e descrição.
    *   Implementado feedback visual após a execução de comandos, exibindo o resultado ou erros.
    *   Corrigido problema com objetos Pheanstalk no controlador para garantir compatibilidade com diferentes versões da biblioteca.
    *   Restaurada a proteção de autenticação para a rota `/queue-commands`, exigindo login e senha para acesso.
    *   Esta implementação permite aos usuários monitorar o estado da fila e executar comandos diretamente pela interface web, sem necessidade de acesso ao terminal.

*   **Implementação de Comandos para Análise de Termos de Pesquisa com IA:**
    *   Criado comando Artisan `keywordai:analyze-search-terms` para analisar termos de pesquisa com status NONE de uma data específica usando IA.
    *   Criado comando Artisan `keywordai:analyze-top-search-terms` para analisar os termos de pesquisa com status NONE de maior custo usando IA, independentemente da data.
    *   Criado serviço `AiAnalysisService` para centralizar a lógica de análise de termos de pesquisa com IA, evitando duplicação de código entre os comandos.
    *   Implementada lógica para coletar termos de pesquisa com base em diferentes critérios (data específica ou maior custo), aplicar filtros de impressões, cliques e custo, e limitar a quantidade de termos analisados.
    *   Implementada lógica para coletar palavras-chave negativas existentes e seus motivos, bem como palavras-chave positivas, para fornecer contexto à IA.
    *   Implementada integração com três APIs de IA: Gemini, OpenAI e Perplexity, permitindo ao usuário escolher qual modelo usar.
    *   Implementada lógica para construir um prompt detalhado para a IA, incluindo instruções customizadas das configurações globais.
    *   Implementada opção `--show-prompt` para exibir o prompt gerado antes de enviar à IA, permitindo ao usuário revisar e confirmar.
    *   Implementada lógica para processar a resposta da IA e exibir os resultados em uma tabela ordenada por recomendação de negativação.
    *   Implementado tratamento de erros robusto para lidar com falhas nas chamadas às APIs de IA.
    *   Adicionado campo `search_term` ao formato de resposta JSON para incluir o texto do termo de pesquisa na resposta da IA.
    *   **Melhoria:** O comando agora exibe o nome exato do modelo de IA sendo utilizado (ex: `gemini-pro`) e o tempo de resposta da API em segundos.
    *   **Melhoria:** Adicionado delay de 10 segundos ao despachar o job `SyncSearchTermStatsJob` após negativar um termo, garantindo que o status seja atualizado e o termo não apareça em análises futuras.
    *   Testado o comando com dados reais, confirmando seu funcionamento correto.
    *   Esta implementação permite uma análise em massa de termos de pesquisa, facilitando a identificação de candidatos à negativação com base em padrões e contexto existente, e fornece informações adicionais sobre a execução da API.

*   **Implementação de Filtros Adicionais na Página de Termos de Pesquisa:**
    *   Adicionados três novos campos de filtro na página `/search-terms`: "Impressões > que", "Cliques > que" e "Custo > que (R$)".
    *   Modificado o arquivo `resources/views/search_terms/index.blade.php` para incluir os novos campos de entrada numérica no formulário de filtro existente.
    *   Atualizado o método `index` no `SearchTermController` para processar os novos parâmetros de filtro.
    *   Implementada lógica para converter o valor de custo de reais para micros (multiplicando por 1.000.000) antes de aplicar o filtro.
    *   Adicionados os novos parâmetros à lista de filtros que são passados de volta para a view, garantindo que os valores sejam mantidos após a submissão do formulário.
    *   Esta implementação permite aos usuários identificar facilmente termos de pesquisa com alto volume de impressões, cliques ou custo, facilitando a otimização de campanhas com foco nos termos de maior impacto.


*   **Agendamento de Sincronização de Termos de Pesquisa a Cada 10 Minutos:**
    *   Modificado o arquivo `app/Console/Kernel.php` para agendar o job `SyncSearchTermsForDateJob` a cada 10 minutos para o dia atual.
    *   Utilizado `Carbon::today()` para garantir que o job sempre execute para a data corrente.
    *   Adicionadas restrições `withoutOverlapping()` e `onOneServer()` para evitar execuções simultâneas e garantir consistência.
    *   Esta implementação permite ter dados mais frescos sobre novos termos de pesquisa, facilitando a rápida identificação e ação sobre termos recém-aparecidos.
    *   A sincronização frequente é especialmente útil para campanhas de alto volume, onde novos termos podem aparecer várias vezes ao longo do dia.

*   **Implementação de Configurações de Inteligência Artificial:**
    *   Adicionada uma nova seção "Configurações de Inteligência Artificial" na página de configurações globais (`/settings/global`).
    *   Implementados campos para armazenar chaves de API para Gemini, OpenAI e Perplexity.
    *   Implementado campo de texto grande para "Instruções Globais Customizadas" que serão aplicadas a todos os modelos de IA.
    *   Implementados campos de texto menores para instruções específicas para cada modelo (Gemini, OpenAI, Perplexity).
    *   Atualizado o controlador `GlobalSettingsController` para validar e salvar as novas configurações.
    *   Atualizado o seeder `SettingsSeeder` para incluir valores padrão vazios para as novas configurações.
    *   Estas configurações serão utilizadas em futuras integrações de IA para análise de termos de pesquisa.

*   **Atualização da Interface do Dashboard:**
    *   Substituído o texto "Laravel Starter Kit" por "KeywordAI" no componente `AppLogo.vue` para refletir corretamente o nome da aplicação.
    *   Removidos os links "Github Repo" e "Documentation" do rodapé da barra lateral no componente `AppSidebar.vue`, eliminando elementos desnecessários da interface.
    *   Estas alterações melhoram a identidade visual da aplicação e removem links que não são relevantes para o projeto KeywordAI.

*   **Implementação de Gráfico de Novos Termos no Dashboard:**
    *   Criado controller `DashboardController` com método `getNewTermsChartData` para fornecer dados agregados de novos termos por dia e status.
    *   Adicionada rota `/api/dashboard/new-terms-chart` para o endpoint da API que fornece os dados do gráfico.
    *   Instalado pacote `chart.js` para renderização do gráfico no frontend.
    *   Modificado `resources/js/pages/Dashboard.vue` para incluir o gráfico na seção "Visão Geral".
    *   Implementada navegação mês a mês com botões "Anterior" e "Próximo".
    *   Configurado gráfico de barras com cores distintas para cada status (NONE, EXCLUDED, ADDED, ADDED_EXCLUDED).
    *   Adicionado indicador de carregamento durante a busca de dados.
    *   Implementada atualização automática do gráfico quando o mês/ano é alterado.
    *   Esta implementação permite visualizar facilmente a quantidade de novos termos de pesquisa por dia, ajudando a identificar tendências e monitorar o crescimento de termos ao longo do tempo.

*   **Melhoria na Navegação e Acesso às Configurações:**
    *   Adicionado link para Configurações Globais (`/settings/global`) no menu lateral (`resources/js/components/AppSidebar.vue`).
    *   Adicionado link para Configurações Globais no menu superior (`resources/views/components/main-navigation.blade.php`).
    *   Adicionado card para Configurações Globais no Dashboard (`resources/js/pages/Dashboard.vue`).
    *   Modificado o layout do Dashboard para usar grid de 4 colunas em telas grandes (`lg:grid-cols-4`).
    *   Modificados os componentes `NavMain.vue` e `Dashboard.vue` para usar links HTML regulares (`<a>`) em vez do componente `Link` do Inertia.js, garantindo que os links abram as páginas em tela cheia.
    *   Modificada a página de configurações globais (`resources/views/settings/global.blade.php`) para usar o layout principal `app.blade.php`, garantindo que ela também tenha o menu de navegação.
    *   Estas alterações melhoram a navegação e facilitam o acesso às configurações globais do sistema.

*   **Implementação de Menu de Navegação:**
    *   Criado componente Blade `resources/views/components/main-navigation.blade.php` para um menu de navegação consistente.
    *   O menu inclui links para Dashboard, Termos de Pesquisa, Palavras-chave Negativas, Log de Atividades e Configurações.
    *   Adicionado o componente ao layout principal `resources/views/layouts/app.blade.php` para que todas as páginas que usam este layout tenham o menu.
    *   Modificada a página `resources/views/search_terms/index.blade.php` para incluir o componente de navegação.
    *   Implementado design responsivo com suporte a dispositivos móveis (menu colapsável).
    *   Adicionados links diretos no Dashboard para as principais seções da aplicação.
    *   Esta implementação melhora significativamente a navegação e a experiência do usuário, permitindo acesso rápido às principais funcionalidades do sistema.

*   **Adição do Campo "Motivo" ao Modal de Negativação:**
    *   Adicionado campo "Motivo da Negativação" ao modal HTML de adição de palavra-chave negativa na tela de termos de pesquisa (`/search-terms`).
    *   Modificado o arquivo `resources/views/search_terms/index.blade.php` para incluir o campo de texto (textarea) para o motivo.
    *   Atualizada a lógica JavaScript para mostrar/esconder o campo de motivo com base no tipo de ação (mostrar apenas para negativação).
    *   Modificado o método de submissão do formulário para incluir o campo `reason` nos dados enviados quando o tipo de ação é "negate".
    *   Testado e validado o funcionamento correto do campo no modal.
    *   Esta modificação melhora a captura de informações sobre o motivo da negativação, facilitando a auditoria e o entendimento das decisões tomadas pelos usuários.

*   **Correção do Problema de Layout nas Views:**
    *   Identificado problema nas views de logs de atividade e palavras-chave negativas que estavam usando `@extends('layouts.app')`, mas o layout não existia.
    *   Criada a pasta `resources/views/layouts` e o arquivo `app.blade.php` com um layout base para as views.
    *   O layout inclui a estrutura HTML básica, links para CSS e JavaScript, e seções para conteúdo e scripts.
    *   Testadas as páginas `/negative-keywords` e `/activity-logs` para confirmar que estão funcionando corretamente.

*   **Implementação de Logs de Atividade e Palavras-chave Negativas:**
    *   **Novas Tabelas:**
        *   Criada tabela `negative_keywords` para armazenar palavras-chave negativas com campos para keyword, match_type, reason, list_id, resource_name, created_by_id, updated_by_id e timestamps.
        *   Criada tabela `activity_logs` para registrar todas as atividades relacionadas a palavras-chave, com campos para user_id, action_type, entity_type, entity_id, ad_group_id, ad_group_name, campaign_id, campaign_name, details (JSON) e timestamps.
    *   **Modificações nos Jobs:**
        *   Atualizado `AddNegativeKeywordJob` para incluir parâmetros `reason` e `userId`, criar um registro na tabela `negative_keywords` e registrar a atividade na tabela `activity_logs`.
        *   Atualizado `AddKeywordToAdGroupJob` para incluir parâmetro `userId` e registrar a atividade na tabela `activity_logs`.
    *   **Modificações nos Controladores:**
        *   Atualizado `NegativeKeywordController` para passar o ID do usuário autenticado para o job e incluir o campo `reason` no formulário.
        *   Atualizado `KeywordController` para passar o ID do usuário autenticado para o job.
        *   Criado `ActivityLogController` para gerenciar a exibição dos logs de atividade, com métodos para listar e visualizar logs.
    *   **Novas Views:**
        *   Criadas views `activity_logs/index.blade.php` e `activity_logs/show.blade.php` para listar e visualizar logs de atividade.
        *   Criadas views `negative_keyword/index.blade.php` e `negative_keyword/show.blade.php` para listar e visualizar palavras-chave negativas.
        *   Atualizada view `negative_keyword/create.blade.php` para incluir campo `reason` para capturar o motivo da negativação.
    *   **Novas Rotas:**
        *   Adicionadas rotas para listar e visualizar logs de atividade (`/activity-logs` e `/activity-logs/{activity_log}`).
        *   Adicionadas rotas para listar e visualizar palavras-chave negativas (`/negative-keywords` e `/negative-keywords/{negativeKeyword}`).

*   **Melhoria na Seleção de Grupo de Anúncios para Palavras-Chave:** Implementada a funcionalidade que permite ao usuário escolher em qual grupo de anúncios a palavra-chave será adicionada, tanto no formulário independente (rota /keyword/add) quanto no modal que abre ao clicar no botão (+) na rota /search-terms.
*   **Filtragem de Grupos de Anúncios por Status e Tipo da Campanha:** Modificados os controllers para filtrar não apenas grupos de anúncios ativos, mas também garantir que esses grupos pertençam a campanhas ativas e do tipo SEARCH, evitando a exibição de grupos de anúncios de campanhas pausadas ou de outros tipos (como Performance Max ou Display).
*   **Sincronização do Tipo de Canal de Publicidade:** Atualizado o comando `SyncAdsEntitiesCommand` para salvar corretamente o valor de `advertising_channel_type` no banco de dados, permitindo a filtragem de grupos de anúncios por tipo de campanha.
*   **Otimização do SyncSearchTermStatsJob:** Modificada a consulta GAQL para remover `segments.date` das cláusulas SELECT e ORDER BY, mantendo-o apenas na cláusula WHERE, permitindo que a API do Google Ads realize a agregação e reduza o volume de dados processados pelo PHP.

*   **Implementação de Autenticação com Login e Senha:**
    *   Protegidas todas as rotas relevantes da aplicação com o middleware de autenticação `auth` no arquivo `routes/web.php`.
    *   Agrupadas todas as rotas da aplicação (exceto a página inicial e as rotas de autenticação) dentro de um grupo de middleware `auth`.
    *   Criado seeder `AdminUserSeeder` para adicionar um usuário administrador inicial ao sistema.
    *   Atualizado o `DatabaseSeeder` para incluir o seeder do usuário administrador.
    *   Verificado que o sistema já possuía todos os componentes necessários para autenticação (controladores, componentes Vue, layout, modelo User, migração).
    *   Configuradas credenciais de acesso: email `admin@keywordai.com` e senha `password`.
    *   Mantido o middleware de whitelist de IP (`IpWhitelistMiddleware`) para uso em conjunto com a autenticação para maior segurança.

*   **Implementação de Configurações Default para Match Type:**
    *   Criada tabela `settings` no banco de dados para armazenar configurações globais do sistema.
    *   Implementado modelo `Setting` com métodos para obter e definir valores de configuração.
    *   Criado seeder `SettingsSeeder` para configurações padrão de match type.
    *   Criado arquivo de helpers `app/Support/helpers.php` com a função global `setting()`.
    *   Modificado `bootstrap/app.php` para carregar o arquivo de helpers automaticamente.
    *   Modificados os controllers `KeywordController` e `NegativeKeywordController` para usar as configurações do banco.
    *   Criado controller `GlobalSettingsController` para gerenciar configurações globais.
    *   Criada view `settings/global.blade.php` para editar configurações.
    *   Adicionado componente `settings-shortcut.blade.php` para acesso rápido às configurações em todas as telas.
    *   Integrado o atalho nas views de search terms, keyword e negative keyword.

*   **Correção de Constraint Violation no SyncSearchTermsForDateJob:**
    *   Implementada uma solução definitiva usando `whereRaw` com a função SQL `DATE()` para resolver o problema de constraint violation.
    *   Identificado que o SQLite armazena datas com formato `YYYY-MM-DD 00:00:00` (incluindo a hora), enquanto o código estava buscando apenas por `YYYY-MM-DD`.
    *   Utilizado `whereRaw("DATE(sync_date) = ?", [$syncDateFormatted])` para buscar o registro existente, comparando apenas a parte da data.
    *   Substituída a abordagem com `updateOrCreate` por uma abordagem mais explícita de buscar-e-criar para evitar problemas com expressões SQL em arrays associativos.
    *   Adicionados logs detalhados em cada etapa para facilitar o diagnóstico de problemas, incluindo logs específicos para registros novos vs. existentes.
    *   Esta alteração resolve o erro `UNIQUE constraint failed: search_term_sync_dates.sync_date` que ocorria quando o job era executado múltiplas vezes para a mesma data.
    *   A nova implementação permite re-sincronizar a mesma data várias vezes (especialmente útil para o dia atual) sem causar erros de constraint.
    *   Mantida a lógica de atualização de status (`markAsProcessing`, `markAsCompleted`, `markAsFailed`) após obter ou criar o registro.
    *   Melhorado o tratamento de exceções para falhar o job imediatamente se não for possível obter ou criar o registro de controle.
    *   Verificado que o `GoogleAdsQuotaService` já estava configurado com limite de 1 requisição por minuto para facilitar testes de throttle.
    *   **Testado e validado** o funcionamento correto da solução em ambiente de produção.

*   **Implementação de Tratamento Adequado para Cota Excedida no SyncSearchTermStatsJob:**
    *   Criada classe de exceção específica `App\Exceptions\GoogleAdsQuotaExceededException` para identificar claramente erros de cota excedida.
    *   Modificado o método `handleSynchronous` do `SyncSearchTermStatsJob` para lançar a nova exceção específica quando a cota é excedida.
    *   Adicionado um bloco `catch` específico no método `handle` para capturar a exceção `GoogleAdsQuotaExceededException`.
    *   Implementado log informativo detalhado quando o job é liberado de volta para a fila devido à cota excedida.
    *   Configurado o job para usar `$this->release(60)` para devolver o job à fila com um delay de 60 segundos, permitindo que a cota seja restabelecida.
    *   Esta implementação garante que os jobs que encontram limites de cota sejam tratados de forma consistente, com logs claros e um delay apropriado antes de tentar novamente.
    *   A solução mantém a consistência com o tratamento já implementado nos jobs `AddKeywordToAdGroupJob` e `AddNegativeKeywordJob`.

*   **Correção do Modal de Adição de Palavra-Chave:**
    *   Removida a linha de código JavaScript `broadOption.classList.add('hidden')` no arquivo `resources/views/search_terms/index.blade.php` que escondia a opção "Ampla (Broad)" quando o tipo de ação era "add" (adicionar palavra-chave positiva).
    *   A correção garante que a opção "Ampla" esteja sempre visível no seletor de tipo de correspondência, tanto para adicionar palavras-chave positivas quanto negativas.

*   **Correção da Função updateTableRow na Página de Termos de Pesquisa:**
    *   Corrigido um problema na função JavaScript `updateTableRow` que estava atualizando as células erradas na tabela.
    *   Ajustados os índices das células para corresponder corretamente às colunas da tabela (Impressões, Cliques, Custo, CTR e Status).
    *   Adicionada lógica de depuração robusta para facilitar a identificação de problemas futuros.
    *   Implementada verificação de existência do botão e da linha antes de tentar atualizar as células.
    *   Adicionados logs detalhados para cada etapa da atualização.
    *   A correção garante que os dados atualizados sejam exibidos nas colunas corretas após o refresh.

*   **Modal para Adição/Negativação de Palavras-Chave:**
    *   Implementado componente Vue `KeywordActionModal.vue` para substituir a abertura de novas abas por modais na página de termos de pesquisa (`/search-terms`).
    *   Criado diretório `resources/js/components/modals` para organizar componentes de modal.
    *   O componente utiliza os componentes Shadcn/ui (`Dialog`, `DialogContent`, etc.) para manter a consistência visual.
    *   Modificados os botões "Add Neg." e "Add Kw." na página de listagem para abrir o modal em vez de novas abas.
    *   Implementada lógica para carregar dinamicamente o componente Vue quando necessário.
    *   Adicionado suporte para submissão AJAX dos formulários, mantendo o usuário na mesma página.
    *   Reutilizados os endpoints existentes (`/negative-keyword/add` e `/keyword/add`) para processamento dos dados.
    *   Implementada atualização da página após conclusão da ação para refletir as mudanças de status.
    *   Integrado com o sistema de notificações existente para feedback ao usuário.
    *   A implementação melhora significativamente a experiência do usuário ao eliminar a necessidade de alternar entre abas.

*   **Otimização do SyncSearchTermStatsJob:**
    *   Modificada a consulta GAQL para remover `segments.date` das cláusulas SELECT e ORDER BY, mantendo-o apenas na cláusula WHERE.
    *   Ajustada a lógica de processamento para usar atribuição direta (`=`) em vez de soma (`+=`) para as métricas, já que a API agora retorna dados agregados.
    *   Simplificada a lógica de captura de status, pois não há mais ordenação por data.
    *   Removida a referência a `segments` no código que processa os resultados.
    *   Estas alterações permitem que a API do Google Ads realize a agregação dos dados no período solicitado, reduzindo significativamente o volume de dados processados pelo PHP.
    *   A otimização resolve o problema de parada do worker que ocorria devido ao grande volume de dados sendo processados.

*   **Search Term Stats Refresh Button:**
    *   Adicionado botão de refresh com ícone para cada termo de pesquisa na página de listagem (`/search-terms`).
    *   Implementado método `handleSynchronous` no `SyncSearchTermStatsJob` para executar a lógica de atualização de forma síncrona e retornar o modelo atualizado.
    *   Criado método `refreshStats` no `SearchTermController` para processar requisições AJAX de atualização.
    *   Adicionada rota POST `/search-terms/{search_term}/refresh` para receber as requisições.
    *   Implementado JavaScript para gerenciar o clique no botão, enviar a requisição AJAX e atualizar a UI dinamicamente.
    *   Adicionado sistema de notificações visuais para informar o usuário sobre o status da atualização.
    *   A implementação permite que o usuário atualize as estatísticas de um termo específico em tempo real, sem precisar recarregar a página inteira.
    *   **Correção de Bug na Fila:** Modificado o método `handle` para envolver a chamada a `handleSynchronous` em um bloco `try...catch` e usar `$this->fail($exception)` para notificar a fila sobre falhas, evitando que o worker encerre abruptamente quando ocorrem exceções durante a execução do job pela fila.

*   **MCP Server iterm-mcp Configuration:**
    *   Configurado o servidor MCP iterm-mcp para permitir a execução de comandos CLI através do terminal iTerm.
    *   Clonado o repositório, instaladas as dependências e construído o projeto.
    *   Atualizado o arquivo de configuração `cline_mcp_settings.json` para incluir o servidor com o nome `github.com/pashpashpash/iterm-mcp`.
    *   Estabelecida a regra de que todos os comandos CLI devem ser executados exclusivamente através deste servidor MCP.
    *   Atualizado `.clinerules` e `systemPatterns.md` para documentar esta regra.
    *   Testadas as ferramentas `write_to_terminal`, `read_terminal_output` e `send_control_character` para confirmar o funcionamento correto.

*   **Search Term Stats Tracking Enhancement:**
    *   Adicionada coluna `statistics_synced_at` (timestamp, nullable) à tabela `search_terms` para registrar quando as estatísticas de cada termo foram sincronizadas pela última vez.
    *   Atualizado o modelo `SearchTerm` para incluir o novo campo nos atributos `$fillable` e `$casts`.
    *   Modificado o job `SyncSearchTermStatsJob` para atualizar o campo `statistics_synced_at` com o timestamp atual quando as estatísticas são sincronizadas com sucesso.
    *   Adicionado o campo `statistics_synced_at` aos logs para facilitar o rastreamento das sincronizações.

*   **Melhoria no Comando de Busca de Termos de Pesquisa:**
    *   Modificado o comando `googleads:fetch-search-terms` para usar a data atual (hoje) como valor padrão para as opções `--start-date` e `--end-date` quando não forem fornecidas.
    *   Atualizada a descrição do comando e a documentação das opções para refletir que as datas são opcionais.
    *   Adicionadas mensagens informativas quando as datas padrão são utilizadas.
    *   Esta melhoria simplifica o uso do comando, permitindo executá-lo como `php artisan googleads:fetch-search-terms --save` para buscar os termos de pesquisa do dia atual.

*   **Search Term Stats Synchronization System:**
    *   Added `GOOGLE_ADS_ABSOLUTE_START_DATE` (default: 2000-01-01) to `.env.example` e `config/googleads.php` para buscar estatísticas históricas completas.
    *   Criado `SyncSearchTermStatsJob` para buscar e atualizar estatísticas de um termo de pesquisa específico (impressions, clicks, cost_micros, ctr, status).
    *   Implementado `SearchTermObserver` para disparar o job automaticamente quando um novo SearchTerm é criado.
    *   Registrado o observer no `AppServiceProvider`.
    *   Modificado `AddNegativeKeywordJob` e `AddKeywordToAdGroupJob` para disparar o job de sincronização após adicionar palavras-chave.
    *   O job usa o período desde `config('googleads.absolute_start_date')` até a data atual para garantir estatísticas históricas completas.
    *   Implementado tratamento de erros e controle de cotas da API.
    *   Criado comando Artisan `googleads:sync-term-stats {term}` para sincronizar manualmente as estatísticas de um termo específico.
    *   **Corrigido problema de contexto (Tentativa 1):** Modificado o `SyncSearchTermStatsJob` para filtrar por `campaign.id` e `ad_group.id` na cláusula `WHERE`. (Falhou em isolar métricas)
    *   **Corrigido problema de contexto (Tentativa 2):** Modificado o `SyncSearchTermStatsJob` para usar `segments.campaign` e `segments.ad_group` (com resource names) na cláusula `WHERE` da consulta GAQL. (Falhou em isolar métricas)
    *   **Corrigido problema de contexto (Tentativa 3 - Atual):** Implementada uma abordagem híbrida onde a consulta GAQL busca todos os dados para o termo e a filtragem por campanha e grupo de anúncios é feita no código PHP. Os dados são agrupados por combinação única de campanha e grupo de anúncios, e apenas as métricas da combinação específica são usadas para atualizar o registro.

*   **Implementação de Comando para Sincronização de Estatísticas em Lote:**
    *   Criado comando Artisan `keywordai:queue-sync-stats` para enfileirar jobs `SyncSearchTermStatsJob` para todos os termos de pesquisa em um intervalo de datas.
    *   Implementada lógica para filtrar termos de pesquisa pelo campo `first_seen_at` dentro do intervalo de datas especificado.
    *   Adicionadas opções para personalizar a execução: `--start-date`, `--end-date`, `--chunk-size`, `--queue` e `--dry-run`.
    *   Implementado processamento em chunks para evitar problemas de memória com grandes conjuntos de dados.
    *   Adicionado feedback visual durante a execução, mostrando o progresso a cada 100 jobs enfileirados.
    *   Implementada validação robusta das datas de entrada, com mensagens de erro claras.
    *   Configurado para usar os últimos 7 dias como intervalo padrão quando nenhuma data é fornecida.

*   **Implementação de Interface para Fila e Comandos:**
    *   Criado controlador `QueueCommandsController` para gerenciar a interface de filas e comandos.
    *   Implementada página `/queue-commands` que exibe estatísticas da fila e permite executar comandos.
    *   Adicionadas estatísticas detalhadas da fila: total de jobs, jobs prontos, jobs reservados, jobs falhos e contagem por fila.
    *   Implementado formulário para executar o comando `keywordai:queue-sync-stats` com todas as suas opções.
    *   Adicionado link "Fila e Comandos" ao menu de navegação principal.
    *   Implementado feedback visual após a execução de comandos, exibindo o resultado ou erros.
    *   Esta implementação permite aos usuários monitorar o estado da fila e executar comandos diretamente pela interface web, sem necessidade de acesso ao terminal.

*   **Implementação de Controle de Criação de Contas:**
    *   Adicionada variável de ambiente `ALLOW_ACCOUNT_CREATION` ao arquivo `.env.example` (padrão `true`).
    *   Adicionada configuração `allow_account_creation` ao arquivo `config/app.php`, lendo a variável de ambiente.
    *   Modificado o método `store` do `RegisteredUserController` para verificar a configuração `app.allow_account_creation`. Se for `false`, retorna um erro `registration` para a view.
    *   Modificado o método `create` do `RegisteredUserController` para passar o valor da configuração `allow_account_creation` para a view Inertia `auth/Register`.
    *   Modificada a view `resources/js/pages/auth/Register.vue` para:
        *   Receber a propriedade `allowAccountCreation`.
        *   Exibir o formulário de registro apenas se `allowAccountCreation` for `true`.
        *   Exibir uma mensagem informativa e um botão para ir ao login se `allowAccountCreation` for `false`.
        *   Adicionado `InputError` para exibir a mensagem de erro `registration`.
        *   Corrigido erro TypeScript adicionando `registration?: string` à definição de tipo dos erros do formulário.
    *   Esta implementação permite ao administrador habilitar ou desabilitar facilmente a criação de novas contas.
