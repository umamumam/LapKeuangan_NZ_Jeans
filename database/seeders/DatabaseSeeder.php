<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Toko;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Umam',
            'email' => 'umam@gmail.com',
            'password' => bcrypt('umamumam'),
        ]);

        // Seed data toko
        $tokos = [
            ['nama' => 'NZ JEANS'],
        ];

        foreach ($tokos as $toko) {
            Toko::create($toko);
        }

        $this->command->info('Data toko berhasil ditambahkan:');
        $this->command->info('- NZ JEANS');
    }
}
