<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Executar o seeder de usuário administrador
        $this->call(AdminUserSeeder::class);
        
        // Executar o seeder de configurações
        $this->call(SettingsSeeder::class);
    }
}
