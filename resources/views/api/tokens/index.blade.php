@extends('layouts.app')

@section('title', 'API Token Management')

@section('styles')
<style>
    .token-mask {
        font-family: monospace;
        letter-spacing: 0.05em;
    }
    .copy-btn {
        transition: all 0.2s;
    }
    .copy-btn:hover {
        transform: scale(1.05);
    }
    .permission-badge {
        @apply inline-flex items-center px-2 py-0.5 rounded text-xs font-medium;
    }
    .perm-read { @apply bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200; }
    .perm-write { @apply bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200; }
    .perm-sync { @apply bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200; }
    .perm-ai { @apply bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200; }
    .perm-admin { @apply bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200; }

    .modal-enter {
        opacity: 0;
        transform: scale(0.95);
    }
    .modal-enter-active {
        opacity: 1;
        transform: scale(1);
        transition: opacity 0.2s ease-out, transform 0.2s ease-out;
    }
    .modal-exit {
        opacity: 1;
        transform: scale(1);
    }
    .modal-exit-active {
        opacity: 0;
        transform: scale(0.95);
        transition: opacity 0.2s ease-in, transform 0.2s ease-in;
    }
</style>
@endsection

@section('content')
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div class="min-w-0 flex-1">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">
                    API Token Management
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Create and manage API tokens for client access to the KeywordAI API.
                </p>
            </div>
            <div class="mt-4 flex md:ml-4 md:mt-0">
                <a href="/api/docs" target="_blank" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:hover:bg-gray-700 mr-3">
                    <svg class="mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                    API Documentation
                </a>
                <button onclick="openCreateModal()" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                    </svg>
                    Create Token
                </button>
            </div>
        </div>

        <!-- New Token Alert -->
        @if(session('new_token'))
        <div class="rounded-md bg-green-50 dark:bg-green-900/30 p-4 mb-6 border border-green-200 dark:border-green-800">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium text-green-800 dark:text-green-200">Token Created Successfully!</h3>
                    <div class="mt-2 text-sm text-green-700 dark:text-green-300">
                        <p>Your new API token is shown below. Copy it now as it won't be displayed again.</p>
                    </div>
                    <div class="mt-3 bg-white dark:bg-gray-800 rounded-md p-3 border border-green-300 dark:border-green-700">
                        <code id="new-token-display" class="text-sm font-mono text-gray-800 dark:text-gray-200 break-all">{{ session('new_token') }}</code>
                        <button onclick="copyToken('{{ session('new_token') }}')" class="ml-2 inline-flex items-center px-2 py-1 text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200 dark:bg-green-800 dark:text-green-200 dark:hover:bg-green-700 copy-btn">
                            Copy
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Messages -->
        @if(session('success'))
        <div class="rounded-md bg-green-50 dark:bg-green-900/30 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('success') }}</p>
                </div>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="rounded-md bg-red-50 dark:bg-red-900/30 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ session('error') }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-4 mb-8">
            <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Total Tokens</dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white">{{ $tokens->total() }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Active</dt>
                                <dd class="text-lg font-medium text-green-600 dark:text-green-400">{{ \App\Models\ApiToken::where('is_active', true)->count() }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Revoked</dt>
                                <dd class="text-lg font-medium text-red-600 dark:text-red-400">{{ \App\Models\ApiToken::where('is_active', false)->count() }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Expiring Soon</dt>
                                <dd class="text-lg font-medium text-yellow-600 dark:text-yellow-400">{{ \App\Models\ApiToken::where('is_active', true)->whereNotNull('expires_at')->where('expires_at', '<', now()->addDays(7))->count() }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tokens Table -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">API Tokens</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">Manage access tokens for API clients.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Token</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Permissions</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Created By</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Last Used</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Expires</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($tokens as $token)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ $token->name }}
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $token->created_at->format('M d, Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <code class="token-mask bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-xs">...{{ substr($token->token, -8) }}</code>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($token->permissions ?? ['*'] as $perm)
                                        <span class="permission-badge perm-{{ $perm === '*' ? 'admin' : $perm }}">{{ $perm }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $token->createdBy?->name ?? 'System' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($token->is_active && $token->isValid())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        Active
                                    </span>
                                @elseif(!$token->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        Revoked
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                        Expired
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $token->last_used_at?->diffForHumans() ?? 'Never' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                @if($token->expires_at)
                                    @if($token->expires_at->isPast())
                                        <span class="text-red-600 dark:text-red-400">Expired {{ $token->expires_at->diffForHumans() }}</span>
                                    @elseif($token->expires_at->diffInDays() < 7)
                                        <span class="text-yellow-600 dark:text-yellow-400">{{ $token->expires_at->diffForHumans() }}</span>
                                    @else
                                        {{ $token->expires_at->format('M d, Y') }}
                                    @endif
                                @else
                                    <span class="text-gray-400">Never</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    @if($token->is_active)
                                    <form action="{{ route('api.tokens.revoke', $token) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" onclick="return confirm('Revoke this token? This action cannot be undone.')" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" title="Revoke">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                    <form action="{{ route('api.tokens.destroy', $token) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Delete this token permanently?')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" title="Delete">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No tokens</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new API token.</p>
                                <div class="mt-6">
                                    <button onclick="openCreateModal()" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                                        </svg>
                                        Create Token
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($tokens->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $tokens->links() }}
            </div>
            @endif
        </div>

        <!-- Quick Start Guide -->
        <div class="mt-8 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg p-6 border border-indigo-200 dark:border-indigo-800">
            <h3 class="text-lg font-medium text-indigo-900 dark:text-indigo-200 mb-3">Quick Start Guide</h3>
            <div class="prose prose-indigo dark:prose-invert max-w-none">
                <ol class="list-decimal list-inside space-y-2 text-sm text-indigo-800 dark:text-indigo-300">
                    <li>Create a new API token using the "Create Token" button above.</li>
                    <li>Copy the token immediately - it won't be shown again!</li>
                    <li>Include the token in your API requests via the <code class="bg-indigo-100 dark:bg-indigo-800 px-1 rounded">X-API-Token</code> header or <code class="bg-indigo-100 dark:bg-indigo-800 px-1 rounded">Authorization: Bearer</code> header.</li>
                    <li>Visit the <a href="/api/docs" class="font-medium underline">API Documentation</a> for endpoint details and examples.</li>
                </ol>
            </div>
            <div class="mt-4 bg-white dark:bg-gray-800 rounded-md p-3 border border-indigo-300 dark:border-indigo-700">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Example Request:</p>
                <code class="text-sm text-gray-800 dark:text-gray-200">curl -H "X-API-Token: your_token_here" {{ url('/api/search-terms') }}</code>
                <button onclick="copyExample()" class="ml-2 text-xs text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">Copy</button>
            </div>
        </div>

    <!-- Create Token Modal -->
    <div id="create-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeCreateModal()"></div>
            <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <form action="{{ route('api.tokens.store') }}" method="POST">
                    @csrf
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white" id="modal-title">Create New API Token</h3>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Token Name</label>
                                        <input type="text" name="name" id="name" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g., Client XYZ Production">
                                    </div>

                                    <div>
                                        <label for="created_by_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assign to User (optional)</label>
                                        <select name="created_by_id" id="created_by_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="">-- None --</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label for="expires_in_days" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Expires In (days, optional)</label>
                                        <input type="number" name="expires_in_days" id="expires_in_days" min="1" max="365" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Leave empty for no expiration">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Permissions</label>
                                        <div class="space-y-2">
                                            <label class="flex items-center">
                                                <input type="checkbox" name="permissions[]" value="read" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Read <span class="text-gray-500">- View data</span></span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="checkbox" name="permissions[]" value="write" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Write <span class="text-gray-500">- Create/update data</span></span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="checkbox" name="permissions[]" value="sync" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Sync <span class="text-gray-500">- Run sync operations</span></span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="checkbox" name="permissions[]" value="ai" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">AI <span class="text-gray-500">- Use AI analysis</span></span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="checkbox" name="permissions[]" value="admin" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Admin <span class="text-gray-500">- Manage tokens</span></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="submit" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto">Create Token</button>
                        <button type="button" onclick="closeCreateModal()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto dark:bg-gray-800 dark:text-white dark:ring-gray-600 dark:hover:bg-gray-700">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function openCreateModal() {
            document.getElementById('create-modal').classList.remove('hidden');
            document.getElementById('name').focus();
        }

        function closeCreateModal() {
            document.getElementById('create-modal').classList.add('hidden');
        }

        function copyToken(token) {
            navigator.clipboard.writeText(token).then(() => {
                const btn = event.target;
                const original = btn.textContent;
                btn.textContent = 'Copied!';
                btn.classList.add('bg-green-200');
                setTimeout(() => {
                    btn.textContent = original;
                    btn.classList.remove('bg-green-200');
                }, 2000);
            });
        }

        function copyExample() {
            const example = 'curl -H "X-API-Token: your_token_here" {{ url('/api/search-terms') }}';
            navigator.clipboard.writeText(example);
            const btn = event.target;
            btn.textContent = 'Copied!';
            setTimeout(() => btn.textContent = 'Copy', 2000);
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeCreateModal();
            }
        });
    </script>
@endsection
