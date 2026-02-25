<?php

namespace App\Console\Commands;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ApiTokenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:token
                            {action : Ação a executar (create|list|revoke|clean)}
                            {--name= : Nome descritivo do token}
                            {--user= : ID do usuário associado ao token}
                            {--days= : Dias até expiração (opcional)}
                            {--token= : Token para revogar}
                            {--permissions= : Permissões separadas por vírgula (read,write,sync,ai,admin)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gerenciar tokens de API';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'create' => $this->createToken(),
            'list' => $this->listTokens(),
            'revoke' => $this->revokeToken(),
            'clean' => $this->cleanTokens(),
            default => $this->error("Ação inválida: {$action}. Use: create, list, revoke, clean"),
        };
    }

    /**
     * Criar novo token.
     */
    private function createToken(): int
    {
        $name = $this->option('name') ?? $this->ask('Nome do token:');
        
        $userId = $this->option('user');
        if (!$userId) {
            $users = User::all(['id', 'name', 'email']);
            if ($users->isEmpty()) {
                $this->warn('Nenhum usuário encontrado. Criando token sem usuário associado.');
                $userId = null;
            } else {
                $this->info('Usuários disponíveis:');
                $users->each(fn ($u) => $this->line("  [{$u->id}] {$u->name} ({$u->email})"));
                $userId = $this->ask('ID do usuário (opcional):');
                if ($userId === '') {
                    $userId = null;
                }
            }
        }

        $days = $this->option('days');
        if (!$days) {
            $days = $this->ask('Dias até expiração (deixe em branco para nunca expirar):');
        }
        $days = $days ? (int) $days : null;

        $permissionsInput = $this->option('permissions');
        if (!$permissionsInput) {
            $permissionsInput = $this->choice(
                'Permissões',
                ['all (todas)', 'read (apenas leitura)', 'custom (personalizado)'],
                'all (todas)'
            );
        }

        $permissions = match ($permissionsInput) {
            'all (todas)', 'all' => ['*'],
            'read (apenas leitura)', 'read' => ['read'],
            'custom (personalizado)', 'custom' => $this->ask('Digite as permissões separadas por vírgula: read,write,sync,ai,admin'),
            default => explode(',', $permissionsInput),
        };

        if (is_string($permissions)) {
            $permissions = array_map('trim', explode(',', $permissions));
        }

        $token = ApiToken::create([
            'name' => $name,
            'token' => ApiToken::generateToken(),
            'created_by_id' => $userId,
            'expires_at' => $days ? now()->addDays($days) : null,
            'permissions' => $permissions,
            'is_active' => true,
        ]);

        $this->newLine();
        $this->info('✓ Token criado com sucesso!');
        $this->newLine();
        $this->warn('Guarde este token, pois ele não será mostrado novamente:');
        $this->line('');
        $this->line('  ' . $token->token);
        $this->line('');
        $this->info('Detalhes:');
        $this->table(
            ['Campo', 'Valor'],
            [
                ['ID', $token->id],
                ['Nome', $token->name],
                ['Criado por', $token->createdBy?->name ?? 'N/A'],
                ['Expira em', $token->expires_at?->format('Y-m-d H:i:s') ?? 'Nunca'],
                ['Permissões', implode(', ', $permissions)],
            ]
        );

        return self::SUCCESS;
    }

    /**
     * Listar tokens.
     */
    private function listTokens(): int
    {
        $tokens = ApiToken::with('createdBy')
            ->orderByDesc('created_at')
            ->get();

        if ($tokens->isEmpty()) {
            $this->warn('Nenhum token encontrado.');
            return self::SUCCESS;
        }

        $this->info("Total de tokens: {$tokens->count()}");
        $this->newLine();

        $rows = $tokens->map(fn ($t) => [
            $t->id,
            $t->name,
            substr($t->token, 0, 20) . '...',
            $t->createdBy?->name ?? 'N/A',
            $t->is_active ? '✓' : '✗',
            $t->isValid() ? '✓' : '✗',
            $t->expires_at?->format('Y-m-d') ?? 'Nunca',
            $t->last_used_at?->format('Y-m-d H:i') ?? 'Nunca',
            is_array($t->permissions) ? implode(', ', $t->permissions) : '*',
        ]);

        $this->table(
            ['ID', 'Nome', 'Token', 'Criado por', 'Ativo', 'Válido', 'Expira', 'Último uso', 'Permissões'],
            $rows
        );

        $active = $tokens->where('is_active', true)->count();
        $valid = $tokens->filter(fn ($t) => $t->isValid())->count();

        $this->newLine();
        $this->info("Resumo: {$active} ativos, {$valid} válidos");

        return self::SUCCESS;
    }

    /**
     * Revogar token.
     */
    private function revokeToken(): int
    {
        $tokenString = $this->option('token');
        
        if (!$tokenString) {
            // Mostrar tokens e perguntar qual revogar
            $tokens = ApiToken::where('is_active', true)->get();
            if ($tokens->isEmpty()) {
                $this->warn('Nenhum token ativo para revogar.');
                return self::SUCCESS;
            }

            $this->info('Tokens ativos:');
            $tokens->each(fn ($t) => $this->line("  [{$t->id}] {$t->name}"));
            
            $tokenId = $this->ask('ID do token para revogar:');
            $apiToken = ApiToken::find($tokenId);
        } else {
            $apiToken = ApiToken::where('token', $tokenString)->first();
        }

        if (!$apiToken) {
            $this->error('Token não encontrado.');
            return self::FAILURE;
        }

        if (!$apiToken->is_active) {
            $this->warn('Este token já está revogado.');
            return self::SUCCESS;
        }

        if (!$this->confirm("Revogar token '{$apiToken->name}'?")) {
            $this->info('Operação cancelada.');
            return self::SUCCESS;
        }

        $apiToken->update(['is_active' => false]);

        $this->info('✓ Token revogado com sucesso.');

        return self::SUCCESS;
    }

    /**
     * Limpar tokens expirados ou revogados.
     */
    private function cleanTokens(): int
    {
        $expired = ApiToken::where('expires_at', '<', now())
            ->whereNotNull('expires_at')
            ->count();

        $this->info("Tokens expirados encontrados: {$expired}");

        if ($expired > 0 && $this->confirm('Deseja remover os tokens expirados?')) {
            ApiToken::where('expires_at', '<', now())
                ->whereNotNull('expires_at')
                ->delete();
            $this->info('✓ Tokens expirados removidos.');
        }

        return self::SUCCESS;
    }
}
