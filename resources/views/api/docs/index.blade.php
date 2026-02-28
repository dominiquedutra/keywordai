<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KeywordAI API Documentation</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    <style>
        .method-get { @apply bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200; }
        .method-post { @apply bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200; }
        .method-put { @apply bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200; }
        .method-patch { @apply bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200; }
        .method-delete { @apply bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200; }
        
        .endpoint-card { 
            @apply border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden mb-4;
        }
        
        .code-block {
            @apply bg-gray-900 text-gray-100 rounded-md p-4 overflow-x-auto text-sm font-mono;
        }
        
        .nav-link {
            @apply block px-3 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800 rounded-md;
        }
        
        .nav-link.active {
            @apply text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 font-medium;
        }
        
        html { scroll-behavior: smooth; }
        
        .copy-btn {
            @apply absolute top-2 right-2 px-2 py-1 text-xs bg-gray-700 text-gray-300 rounded hover:bg-gray-600 transition-colors;
        }
    </style>
</head>
<body class="font-sans antialiased bg-white dark:bg-gray-900">
    <!-- Header -->
    <header class="sticky top-0 z-50 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="/" class="flex items-center">
                        <svg class="h-8 w-8 text-indigo-600" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                        <span class="ml-2 text-xl font-bold text-gray-900 dark:text-white">KeywordAI API</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ url('/llms.txt') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">llms.txt</a>
                    <a href="{{ url('/llms-full.txt') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">llms-full.txt</a>
                    <a href="{{ url('/api/health') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">Health Check</a>
                    <a href="{{ url('/api/info') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">API Info</a>
                    @auth
                        <a href="{{ route('api.tokens.ui') }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            Manage Tokens
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">Login</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="lg:grid lg:grid-cols-12 lg:gap-8">
            <!-- Sidebar Navigation -->
            <div class="hidden lg:block lg:col-span-3">
                <nav class="sticky top-24 overflow-y-auto h-[calc(100vh-8rem)] pr-4">
                    <h5 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider">Getting Started</h5>
                    <ul class="space-y-1 mb-6">
                        <li><a href="#introduction" class="nav-link active">Introduction</a></li>
                        <li><a href="#authentication" class="nav-link">Authentication</a></li>
                        <li><a href="#quickstart" class="nav-link">Quick Start</a></li>
                        <li><a href="#permissions" class="nav-link">Permissions</a></li>
                    </ul>

                    <h5 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider">Endpoints</h5>
                    <ul class="space-y-1 mb-6">
                        <li><a href="#health" class="nav-link">Health & Info</a></li>
                        <li><a href="#dashboard" class="nav-link">Dashboard</a></li>
                        <li><a href="#search-terms" class="nav-link">Search Terms</a></li>
                        <li><a href="#campaigns" class="nav-link">Campaigns</a></li>
                        <li><a href="#ad-groups" class="nav-link">Ad Groups</a></li>
                        <li><a href="#negative-keywords" class="nav-link">Negative Keywords</a></li>
                        <li><a href="#sync" class="nav-link">Sync Operations</a></li>
                        <li><a href="#ai" class="nav-link">AI Analysis</a></li>
                    </ul>

                    <h5 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider">Integrations</h5>
                    <ul class="space-y-1 mb-6">
                        <li><a href="#llm-integration" class="nav-link">LLM Integration</a></li>
                    </ul>

                    <h5 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider">Reference</h5>
                    <ul class="space-y-1">
                        <li><a href="#errors" class="nav-link">Error Codes</a></li>
                        <li><a href="#rate-limits" class="nav-link">Rate Limits</a></li>
                        <li><a href="#sdks" class="nav-link">SDKs & Examples</a></li>
                    </ul>
                </nav>
            </div>

            <!-- Main Content -->
            <main class="lg:col-span-9">
                <!-- Introduction -->
                <section id="introduction" class="mb-12">
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">KeywordAI API</h1>
                    <p class="text-lg text-gray-600 dark:text-gray-300 mb-6">
                        Welcome to the KeywordAI API documentation. This API provides programmatic access to your Google Ads search terms data, 
                        allowing you to integrate keyword management into your own applications and workflows.
                    </p>
                    
                    <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg p-4 mb-6">
                        <h3 class="text-indigo-900 dark:text-indigo-200 font-semibold mb-2">Base URL</h3>
                        <code class="text-indigo-800 dark:text-indigo-300 font-mono text-sm">{{ url('/api') }}</code>
                    </div>

                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">Features</h2>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="flex items-start">
                            <svg class="h-6 w-6 text-green-500 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">Search Terms Management</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Access and filter search terms with detailed metrics</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="h-6 w-6 text-green-500 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">Negative Keywords</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Add and manage negative keywords programmatically</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="h-6 w-6 text-green-500 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">AI Analysis</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Leverage AI to analyze and suggest negative keywords</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="h-6 w-6 text-green-500 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">Sync Operations</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Trigger data synchronization with Google Ads</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Authentication -->
                <section id="authentication" class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Authentication</h2>
                    <p class="text-gray-600 dark:text-gray-300 mb-4">
                        The API uses token-based authentication. You must include your API token in every request. 
                        Tokens can be obtained from the <a href="{{ route('api.tokens.ui') }}" class="text-indigo-600 hover:text-indigo-500">Token Management</a> page.
                    </p>

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Methods</h3>
                    
                    <div class="space-y-4 mb-6">
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 dark:text-white mb-2">1. X-API-Token Header (Recommended)</h4>
                            <div class="relative">
                                <pre class="code-block"><code>GET /api/search-terms
X-API-Token: your_token_here</code></pre>
                                <button onclick="copyCode(this)" class="copy-btn">Copy</button>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 dark:text-white mb-2">2. Authorization Bearer Header</h4>
                            <div class="relative">
                                <pre class="code-block"><code>GET /api/search-terms
Authorization: Bearer your_token_here</code></pre>
                                <button onclick="copyCode(this)" class="copy-btn">Copy</button>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 dark:text-white mb-2">3. Query String (Less Secure)</h4>
                            <div class="relative">
                                <pre class="code-block"><code>GET /api/search-terms?api_token=your_token_here</code></pre>
                                <button onclick="copyCode(this)" class="copy-btn">Copy</button>
                            </div>
                        </div>
                    </div>

                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                        <div class="flex">
                            <svg class="h-5 w-5 text-yellow-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div>
                                <h4 class="font-medium text-yellow-900 dark:text-yellow-200">Security Note</h4>
                                <p class="text-sm text-yellow-800 dark:text-yellow-300">Keep your tokens secure. Never expose them in client-side code or public repositories.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Quick Start -->
                <section id="quickstart" class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Quick Start</h2>
                    
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">1. Create a Token</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-2">
                                First, <a href="{{ route('api.tokens.ui') }}" class="text-indigo-600 hover:text-indigo-500">create an API token</a> with the appropriate permissions for your use case.
                            </p>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">2. Make Your First Request</h3>
                            <div class="relative">
                                <pre class="code-block"><code class="language-bash">curl -H "X-API-Token: your_token_here" \
  {{ url('/api/search-terms') }}</code></pre>
                                <button onclick="copyCode(this)" class="copy-btn">Copy</button>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">3. Check Health</h3>
                            <div class="relative">
                                <pre class="code-block"><code class="language-bash">curl {{ url('/api/health') }}</code></pre>
                                <button onclick="copyCode(this)" class="copy-btn">Copy</button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Permissions -->
                <section id="permissions" class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Permissions</h2>
                    <p class="text-gray-600 dark:text-gray-300 mb-4">
                        Tokens can have specific permissions that control what actions they can perform:
                    </p>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Permission</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Endpoints</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600 dark:text-blue-400">read</td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">View data</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">All GET endpoints</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600 dark:text-green-400">write</td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">Create and modify data</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">POST, PUT, PATCH endpoints</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-purple-600 dark:text-purple-400">sync</td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">Run sync operations</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">/api/sync/*</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-orange-600 dark:text-orange-400">ai</td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">Use AI analysis features</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">/api/ai/*</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600 dark:text-red-400">admin</td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">Manage tokens</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">/api/admin/*</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">*</td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">All permissions</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">All endpoints</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Health & Info -->
                <section id="health" class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Health & Info</h2>
                    
                    <div class="endpoint-card">
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <span class="method-get px-2 py-1 rounded text-xs font-bold mr-3">GET</span>
                                <code class="text-sm font-mono text-gray-800 dark:text-gray-200">/api/health</code>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="text-gray-600 dark:text-gray-300 mb-3">Check API health status. No authentication required.</p>
                            <div class="relative">
                                <pre class="code-block"><code class="language-json">{
  "success": true,
  "status": "healthy",
  "timestamp": "2025-02-24T14:30:00Z",
  "checks": {
    "database": { "status": "ok" },
    "queue": { "status": "ok", "pending_jobs": 0 }
  }
}</code></pre>
                                <button onclick="copyCode(this)" class="copy-btn">Copy</button>
                            </div>
                        </div>
                    </div>

                    <div class="endpoint-card">
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <span class="method-get px-2 py-1 rounded text-xs font-bold mr-3">GET</span>
                                <code class="text-sm font-mono text-gray-800 dark:text-gray-200">/api/info</code>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="text-gray-600 dark:text-gray-300">Get API information and available features.</p>
                        </div>
                    </div>
                </section>

                <!-- Dashboard -->
                <section id="dashboard" class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Dashboard</h2>
                    
                    <div class="endpoint-card">
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <span class="method-get px-2 py-1 rounded text-xs font-bold mr-3">GET</span>
                                <code class="text-sm font-mono text-gray-800 dark:text-gray-200">/api/dashboard/metrics</code>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="text-gray-600 dark:text-gray-300 mb-3">Get dashboard metrics and statistics.</p>
                        </div>
                    </div>

                    <div class="endpoint-card">
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <span class="method-get px-2 py-1 rounded text-xs font-bold mr-3">GET</span>
                                <code class="text-sm font-mono text-gray-800 dark:text-gray-200">/api/dashboard/chart/new-terms</code>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="text-gray-600 dark:text-gray-300 mb-2">Get new terms chart data.</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Query params: <code>days</code> (7-90, default: 30)</p>
                        </div>
                    </div>
                </section>

                <!-- Search Terms -->
                <section id="search-terms" class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Search Terms</h2>
                    
                    <div class="endpoint-card">
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <span class="method-get px-2 py-1 rounded text-xs font-bold mr-3">GET</span>
                                <code class="text-sm font-mono text-gray-800 dark:text-gray-200">/api/search-terms</code>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="text-gray-600 dark:text-gray-300 mb-3">List all search terms with filtering and pagination.</p>
                            <div class="bg-gray-50 dark:bg-gray-800 rounded p-3 mb-3">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Query Parameters:</p>
                                <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1 ml-4">
                                    <li><code>search_term</code> - Filter by term (LIKE)</li>
                                    <li><code>campaign_id</code> - Filter by campaign ID</li>
                                    <li><code>ad_group_id</code> - Filter by ad group ID</li>
                                    <li><code>status</code> - Filter by status (NONE, ADDED, EXCLUDED)</li>
                                    <li><code>min_impressions</code>, <code>min_clicks</code>, <code>min_cost</code> - Minimum metrics</li>
                                    <li><code>date_from</code>, <code>date_to</code> - Date range (Y-m-d)</li>
                                    <li><code>sort_by</code>, <code>sort_direction</code> - Sorting</li>
                                    <li><code>per_page</code> - Items per page (1-1000)</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="endpoint-card">
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <span class="method-get px-2 py-1 rounded text-xs font-bold mr-3">GET</span>
                                <code class="text-sm font-mono text-gray-800 dark:text-gray-200">/api/search-terms/{id}</code>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="text-gray-600 dark:text-gray-300">Get a specific search term by ID.</p>
                        </div>
                    </div>

                    <div class="endpoint-card">
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <span class="method-post px-2 py-1 rounded text-xs font-bold mr-3">POST</span>
                                <code class="text-sm font-mono text-gray-800 dark:text-gray-200">/api/search-terms/{id}/negate</code>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="text-gray-600 dark:text-gray-300 mb-3">Add a search term as a negative keyword.</p>
                            <div class="relative">
                                <pre class="code-block"><code class="language-json">{
  "match_type": "phrase",
  "reason": "Irrelevant term"
}</code></pre>
                                <button onclick="copyCode(this)" class="copy-btn">Copy</button>
                            </div>
                        </div>
                    </div>

                    <div class="endpoint-card">
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <span class="method-post px-2 py-1 rounded text-xs font-bold mr-3">POST</span>
                                <code class="text-sm font-mono text-gray-800 dark:text-gray-200">/api/search-terms/batch-negate</code>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="text-gray-600 dark:text-gray-300 mb-3">Negate multiple terms at once.</p>
                            <div class="relative">
                                <pre class="code-block"><code class="language-json">{
  "terms": [
    { "id": 1, "reason": "Irrelevant" },
    { "id": 2, "reason": "Competitor" }
  ],
  "match_type": "phrase"
}</code></pre>
                                <button onclick="copyCode(this)" class="copy-btn">Copy</button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Campaigns -->
                <section id="campaigns" class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Campaigns</h2>
                    
                    <div class="endpoint-card">
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <span class="method-get px-2 py-1 rounded text-xs font-bold mr-3">GET</span>
                                <code class="text-sm font-mono text-gray-800 dark:text-gray-200">/api/campaigns</code>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="text-gray-600 dark:text-gray-300">List all campaigns with optional filtering.</p>
                        </div>
                    </div>

                    <div class="endpoint-card">
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <span class="method-get px-2 py-1 rounded text-xs font-bold mr-3">GET</span>
                                <code class="text-sm font-mono text-gray-800 dark:text-gray-200">/api/campaigns/{id}/stats</code>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="text-gray-600 dark:text-gray-300">Get campaign statistics including search term counts and performance metrics.</p>
                        </div>
                    </div>
                </section>

                <!-- Ad Groups -->
                <section id="ad-groups" class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Ad Groups</h2>
                    
                    <div class="endpoint-card">
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <span class="method-get px-2 py-1 rounded text-xs font-bold mr-3">GET</span>
                                <code class="text-sm font-mono text-gray-800 dark:text-gray-200">/api/ad-groups</code>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="text-gray-600 dark:text-gray-300">List all ad groups.</p>
                        </div>
                    </div>
                </section>

                <!-- Negative Keywords -->
                <section id="negative-keywords" class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Negative Keywords</h2>
                    
                    <div class="endpoint-card">
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <span class="method-get px-2 py-1 rounded text-xs font-bold mr-3">GET</span>
                                <code class="text-sm font-mono text-gray-800 dark:text-gray-200">/api/negative-keywords</code>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="text-gray-600 dark:text-gray-300">List all negative keywords.</p>
                        </div>
                    </div>

                    <div class="endpoint-card">
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <span class="method-post px-2 py-1 rounded text-xs font-bold mr-3">POST</span>
                                <code class="text-sm font-mono text-gray-800 dark:text-gray-200">/api/negative-keywords</code>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="text-gray-600 dark:text-gray-300 mb-3">Create a new negative keyword.</p>
                            <div class="relative">
                                <pre class="code-block"><code class="language-json">{
  "keyword": "termo negativo",
  "match_type": "phrase",
  "reason": "Motivo da negação",
  "list_id": "1234567890"
}</code></pre>
                                <button onclick="copyCode(this)" class="copy-btn">Copy</button>
                            </div>
                        </div>
                    </div>

                    <div class="endpoint-card">
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <span class="method-post px-2 py-1 rounded text-xs font-bold mr-3">POST</span>
                                <code class="text-sm font-mono text-gray-800 dark:text-gray-200">/api/negative-keywords/batch</code>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="text-gray-600 dark:text-gray-300">Create multiple negative keywords at once.</p>
                        </div>
                    </div>
                </section>

                <!-- Sync -->
                <section id="sync" class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Sync Operations</h2>
                    
                    <div class="endpoint-card">
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <span class="method-post px-2 py-1 rounded text-xs font-bold mr-3">POST</span>
                                <code class="text-sm font-mono text-gray-800 dark:text-gray-200">/api/sync/search-terms</code>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="text-gray-600 dark:text-gray-300 mb-3">Sync search terms for a specific date.</p>
                            <div class="relative">
                                <pre class="code-block"><code class="language-json">{
  "date": "2025-02-24"
}</code></pre>
                                <button onclick="copyCode(this)" class="copy-btn">Copy</button>
                            </div>
                        </div>
                    </div>

                    <div class="endpoint-card">
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <span class="method-post px-2 py-1 rounded text-xs font-bold mr-3">POST</span>
                                <code class="text-sm font-mono text-gray-800 dark:text-gray-200">/api/sync/search-terms-range</code>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="text-gray-600 dark:text-gray-300 mb-3">Sync search terms for a date range.</p>
                            <div class="relative">
                                <pre class="code-block"><code class="language-json">{
  "date_from": "2025-02-20",
  "date_to": "2025-02-24"
}</code></pre>
                                <button onclick="copyCode(this)" class="copy-btn">Copy</button>
                            </div>
                        </div>
                    </div>

                    <div class="endpoint-card">
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <span class="method-get px-2 py-1 rounded text-xs font-bold mr-3">GET</span>
                                <code class="text-sm font-mono text-gray-800 dark:text-gray-200">/api/sync/status</code>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="text-gray-600 dark:text-gray-300">Check sync status and history.</p>
                        </div>
                    </div>
                </section>

                <!-- AI Analysis -->
                <section id="ai" class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">AI Analysis</h2>
                    
                    <div class="endpoint-card">
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <span class="method-get px-2 py-1 rounded text-xs font-bold mr-3">GET</span>
                                <code class="text-sm font-mono text-gray-800 dark:text-gray-200">/api/ai/models</code>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="text-gray-600 dark:text-gray-300">List available AI models and their status.</p>
                        </div>
                    </div>

                    <div class="endpoint-card">
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <span class="method-post px-2 py-1 rounded text-xs font-bold mr-3">POST</span>
                                <code class="text-sm font-mono text-gray-800 dark:text-gray-200">/api/ai/analyze</code>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="text-gray-600 dark:text-gray-300 mb-3">Analyze search terms using AI.</p>
                            <div class="relative">
                                <pre class="code-block"><code class="language-json">{
  "analysis_type": "date",
  "date": "2025-02-24",
  "model": "gemini",
  "limit": 50,
  "min_impressions": 10
}</code></pre>
                                <button onclick="copyCode(this)" class="copy-btn">Copy</button>
                            </div>
                        </div>
                    </div>

                    <div class="endpoint-card">
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <span class="method-post px-2 py-1 rounded text-xs font-bold mr-3">POST</span>
                                <code class="text-sm font-mono text-gray-800 dark:text-gray-200">/api/ai/suggest-negatives</code>
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="text-gray-600 dark:text-gray-300 mb-3">Get AI suggestions for negative keywords. Optionally auto-negate.</p>
                            <div class="relative">
                                <pre class="code-block"><code class="language-json">{
  "model": "gemini",
  "date_from": "2025-02-20",
  "date_to": "2025-02-24",
  "min_impressions": 50,
  "auto_negate": true,
  "match_type": "phrase"
}</code></pre>
                                <button onclick="copyCode(this)" class="copy-btn">Copy</button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- LLM Integration -->
                <section id="llm-integration" class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">LLM Integration</h2>
                    <p class="text-gray-600 dark:text-gray-300 mb-6">
                        KeywordAI provides machine-readable documentation following the <a href="https://llmstxt.org/" class="text-indigo-600 hover:text-indigo-500" target="_blank">llms.txt standard</a> for LLM agents that need to programmatically manage search terms.
                    </p>

                    <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg p-4 mb-6">
                        <h3 class="text-indigo-900 dark:text-indigo-200 font-semibold mb-2">Machine-Readable Docs</h3>
                        <ul class="space-y-1 text-sm text-indigo-800 dark:text-indigo-300">
                            <li><a href="{{ url('/llms.txt') }}" class="underline font-mono">{{ url('/llms.txt') }}</a> — Concise API overview for LLM context</li>
                            <li><a href="{{ url('/llms-full.txt') }}" class="underline font-mono">{{ url('/llms-full.txt') }}</a> — Full endpoint reference with schemas</li>
                        </ul>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Recommended Token Permissions</h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-4">
                        For LLM agents, create a token with <code class="text-sm bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded">read</code>, <code class="text-sm bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded">write</code>, and <code class="text-sm bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded">ai</code> permissions. This allows analyzing terms and negating them without granting sync or admin access.
                    </p>

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Analyze → Negate Workflow</h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-4">
                        The typical LLM agent workflow is a two-step process: analyze search terms with AI, then batch-negate the ones flagged as irrelevant.
                    </p>

                    <div class="space-y-4">
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 dark:text-white mb-2">Step 1: Analyze search terms</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Run AI analysis on terms for a specific date. Each term receives a <code>should_negate</code> flag and a <code>rationale</code>.</p>
                            <div class="relative">
                                <pre class="code-block"><code class="language-bash">curl -X POST {{ url('/api/ai/analyze') }} \
  -H "X-API-Token: your_token" \
  -H "Content-Type: application/json" \
  -d '{
    "analysis_type": "date",
    "date": "2026-02-27",
    "model": "gemini",
    "limit": 50
  }'</code></pre>
                                <button onclick="copyCode(this)" class="copy-btn">Copy</button>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Response includes per-term: <code>id</code>, <code>search_term</code>, <code>should_negate</code> (bool), <code>rationale</code> (string)</p>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 dark:text-white mb-2">Step 2: Batch negate flagged terms</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Filter terms where <code>should_negate === true</code> and send them to batch-negate. The <code>rationale</code> field from the analysis response can be passed directly.</p>
                            <div class="relative">
                                <pre class="code-block"><code class="language-bash">curl -X POST {{ url('/api/search-terms/batch-negate') }} \
  -H "X-API-Token: your_token" \
  -H "Content-Type: application/json" \
  -d '{
    "terms": [
      {"id": 123, "rationale": "Irrelevant to business"},
      {"id": 456, "rationale": "Competitor brand name"}
    ],
    "match_type": "phrase"
  }'</code></pre>
                                <button onclick="copyCode(this)" class="copy-btn">Copy</button>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Both <code>reason</code> and <code>rationale</code> are accepted as field names.</p>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 dark:text-white mb-2">Alternative: Single-step auto-negate</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Use <code>suggest-negatives</code> with <code>auto_negate: true</code> for a fully automated single-step workflow.</p>
                            <div class="relative">
                                <pre class="code-block"><code class="language-bash">curl -X POST {{ url('/api/ai/suggest-negatives') }} \
  -H "X-API-Token: your_token" \
  -H "Content-Type: application/json" \
  -d '{
    "model": "gemini",
    "date_from": "2026-02-20",
    "date_to": "2026-02-27",
    "auto_negate": true,
    "match_type": "phrase"
  }'</code></pre>
                                <button onclick="copyCode(this)" class="copy-btn">Copy</button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Error Codes -->
                <section id="errors" class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Error Codes</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Meaning</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr><td class="px-6 py-4 text-sm font-medium text-green-600">200</td><td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">Success</td></tr>
                                <tr><td class="px-6 py-4 text-sm font-medium text-green-600">201</td><td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">Created</td></tr>
                                <tr><td class="px-6 py-4 text-sm font-medium text-yellow-600">400</td><td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">Bad Request - Invalid parameters</td></tr>
                                <tr><td class="px-6 py-4 text-sm font-medium text-yellow-600">401</td><td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">Unauthorized - Invalid or missing token</td></tr>
                                <tr><td class="px-6 py-4 text-sm font-medium text-yellow-600">403</td><td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">Forbidden - Insufficient permissions</td></tr>
                                <tr><td class="px-6 py-4 text-sm font-medium text-yellow-600">404</td><td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">Not Found</td></tr>
                                <tr><td class="px-6 py-4 text-sm font-medium text-yellow-600">422</td><td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">Validation Error</td></tr>
                                <tr><td class="px-6 py-4 text-sm font-medium text-red-600">500</td><td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">Internal Server Error</td></tr>
                                <tr><td class="px-6 py-4 text-sm font-medium text-red-600">503</td><td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">Service Unavailable</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Rate Limits -->
                <section id="rate-limits" class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Rate Limits</h2>
                    <p class="text-gray-600 dark:text-gray-300 mb-4">
                        The API enforces rate limits based on Google Ads API quotas:
                    </p>
                    <ul class="list-disc list-inside text-gray-600 dark:text-gray-300 space-y-2">
                        <li><strong>Google Ads API:</strong> 14,000 requests/day, 60 requests/minute</li>
                        <li><strong>KeywordAI API:</strong> No specific limits (constrained by Google Ads quotas)</li>
                    </ul>
                </section>

                <!-- SDKs -->
                <section id="sdks" class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">SDKs & Examples</h2>
                    
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">cURL</h3>
                            <div class="relative">
                                <pre class="code-block"><code class="language-bash"># List search terms
curl -H "X-API-Token: your_token" {{ url('/api/search-terms') }}

# Create negative keyword
curl -X POST -H "X-API-Token: your_token" \
  -H "Content-Type: application/json" \
  -d '{"keyword":"term","match_type":"phrase"}' \
  {{ url('/api/negative-keywords') }}

# Sync data for a date
curl -X POST -H "X-API-Token: your_token" \
  -H "Content-Type: application/json" \
  -d '{"date":"2025-02-24"}' \
  {{ url('/api/sync/search-terms') }}</code></pre>
                                <button onclick="copyCode(this)" class="copy-btn">Copy</button>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Python</h3>
                            <div class="relative">
                                <pre class="code-block"><code class="language-python">import requests

API_URL = "{{ url('/api') }}"
TOKEN = "your_token_here"
headers = {"X-API-Token": TOKEN}

# List search terms
response = requests.get(f"{API_URL}/search-terms", headers=headers)
terms = response.json()

# Create negative keyword
data = {
    "keyword": "termo negativo",
    "match_type": "phrase",
    "reason": "Irrelevante"
}
response = requests.post(
    f"{API_URL}/negative-keywords",
    headers=headers,
    json=data
)</code></pre>
                                <button onclick="copyCode(this)" class="copy-btn">Copy</button>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">JavaScript</h3>
                            <div class="relative">
                                <pre class="code-block"><code class="language-javascript">const API_URL = '{{ url('/api') }}';
const TOKEN = 'your_token_here';

// List search terms
fetch(`${API_URL}/search-terms`, {
  headers: { 'X-API-Token': TOKEN }
})
.then(res => res.json())
.then(data => console.log(data));

// AI Analysis
fetch(`${API_URL}/ai/analyze`, {
  method: 'POST',
  headers: {
    'X-API-Token': TOKEN,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    analysis_type: 'date',
    date: '2025-02-24',
    model: 'gemini'
  })
})</code></pre>
                                <button onclick="copyCode(this)" class="copy-btn">Copy</button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Footer -->
                <footer class="border-t border-gray-200 dark:border-gray-800 pt-8 mt-12">
                    <p class="text-center text-gray-500 dark:text-gray-400 text-sm">
                        KeywordAI API Documentation &copy; {{ date('Y') }}
                    </p>
                </footer>
            </main>
        </div>
    </div>

    <script>
        // Copy code functionality
        function copyCode(btn) {
            const code = btn.previousElementSibling.querySelector('code').textContent;
            navigator.clipboard.writeText(code).then(() => {
                const original = btn.textContent;
                btn.textContent = 'Copied!';
                setTimeout(() => btn.textContent = original, 2000);
            });
        }

        // Active nav link on scroll
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav-link');

        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                if (pageYOffset >= sectionTop - 100) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#' + current) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
