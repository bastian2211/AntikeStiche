<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PagesSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
