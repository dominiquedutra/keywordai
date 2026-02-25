<?php

use App\Models\Setting;

if (!function_exists('setting')) {
    /**
     * Obtém o valor de uma configuração do banco de dados.
     *
     * @param string $key A chave da configuração
     * @param mixed $default Valor padrão caso a configuração não exista
     * @return mixed O valor da configuração
     */
    function setting($key, $default = null) {
        return Setting::getValue($key, $default);
    }
}
