<?php

namespace Database\Seeders\Roles;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Role::create(['name' => 'posko-utama']);
        Role::create(['name' => 'posko']);
        Role::create(['name' => 'bansos']);
        Role::create(['name' => 'kecamatan']);

    }
}
