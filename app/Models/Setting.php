<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array<string>
     */
    protected $fillable = ['key', 'value', 'type', 'description'];
    
    /**
     * Método para obter valor com conversão de tipo
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getValue($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }
        
        // Converter valor conforme o tipo
        switch ($setting->type) {
            case 'boolean':
                return filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $setting->value;
            case 'float':
                return (float) $setting->value;
            case 'json':
                return json_decode($setting->value, true);
            case 'encrypted':
                if (empty($setting->value)) {
                    return '';
                }
                try {
                    return Crypt::decryptString($setting->value);
                } catch (\Exception $e) {
                    return '';
                }
            default:
                return $setting->value;
        }
    }
    
    /**
     * Método para definir valor
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param string|null $description
     * @return \App\Models\Setting
     */
    public static function setValue($key, $value, $type = 'string', $description = null)
    {
        // Garantir que valores NULL sejam convertidos para strings vazias
        if ($value === null) {
            $value = '';
        }
        
        // Converter valor conforme o tipo antes de salvar
        if ($type === 'json' && is_array($value)) {
            $value = json_encode($value);
        } elseif ($type === 'encrypted' && !empty($value)) {
            $value = Crypt::encryptString($value);
        }
        
        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description
            ]
        );
    }
}
