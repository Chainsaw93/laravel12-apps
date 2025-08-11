<?php

namespace Database\Seeders;

use App\Models\{User, Unit};
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = ['admin', 'cajero', 'supervisor'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        Unit::firstOrCreate(
            ['name' => 'Piece'],
            ['abbreviation' => 'pc']
        );

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $user->assignRole('admin');
    }
}
