<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@yarom.co'],
            [
                'name'     => 'Administrador YarOM',
                'password' => Hash::make('YarOM2026!'),
            ]
        );
        $admin->assignRole('super_admin');

        $this->command->info("✅ Usuario admin creado:");
        $this->command->line("   Email:    admin@yarom.co");
        $this->command->line("   Password: YarOM2026!");
        $this->command->line("   Rol:      super_admin");
    }
}
