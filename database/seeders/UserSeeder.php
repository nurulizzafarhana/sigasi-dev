<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $posko_utama = User::create([
            'name' => 'posko utama',
            'username' => 'posko_utama',
            'phone' => '081122334455',
            'email' => 'posko-utama@sisfosigasi.com',
            'password'=> bcrypt('admin123')
        ]);

        $posko_utama->assignRole('posko-utama');

        $posko = User::create([
            'name' => 'posko',
            'username' => 'posko',
            'phone' => '081122334455',
            'email' => 'posko@sisfosigasi.com',
            'password'=> bcrypt('admin123')
        ]);

        $posko->assignRole('posko');

        $bansos = User::create([
            'name' => 'bansos',
            'username' => 'bansos',
            'phone' => '081122334455',
            'email' => 'bansos@sisfosigasi.com',
            'password'=> bcrypt('admin123')
        ]);

        $bansos->assignRole('bansos');

        $kecamatan = User::create([
            'name' => 'kecamatan',
            'username' => 'kecamatan',
            'phone' => '081122334455',
            'email' => 'kecamatan@sisfosigasi.com',
            'password'=> bcrypt('admin123')
        ]);

        $kecamatan->assignRole('kecamatan');
    }
}
