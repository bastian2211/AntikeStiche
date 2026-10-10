<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        AdminUser::updateOrCreate(
            ['email' => 'admin@antike-stiche.de'],
            [
                'name'     => 'Administrator',
                'password' => Hash::make('ChangeMe2024!'), // ← SOFORT ändern!
            ]
        );

        $this->command->warn('⚠️  Admin-Passwort ist "ChangeMe2024!" – bitte sofort in der Admin-Oberfläche ändern!');
        $this->command->info('✅ Admin-Benutzer: admin@antike-stiche.de');
    }
}
