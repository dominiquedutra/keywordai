<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiToken extends Model
{
    use HasFactory;

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'token',
        'created_by_id',
        'last_used_at',
        'expires_at',
        'permissions',
        'is_active',
    ];

    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Obter o usuário que criou este token.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * Verificar se o token está válido (ativo e não expirado).
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Verificar se o token tem uma permissão específica.
     */
    public function hasPermission(string $permission): bool
    {
        if (empty($this->permissions)) {
            return true; // Sem restrições = todas as permissões
        }

        return in_array($permission, $this->permissions) || in_array('*', $this->permissions);
    }

    /**
     * Registrar uso do token.
     */
    public function recordUsage(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Gerar um novo token aleatório.
     */
    public static function generateToken(): string
    {
        return hash('sha256', uniqid(rand(), true) . time() . env('APP_KEY'));
    }

    /**
     * Buscar token válido.
     */
    public static function findValid(string $token): ?self
    {
        $apiToken = self::where('token', $token)->first();

        if (!$apiToken || !$apiToken->isValid()) {
            return null;
        }

        return $apiToken;
    }
}
