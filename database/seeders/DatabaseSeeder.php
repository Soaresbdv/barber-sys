<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Bruno Admin',
            'email' => 'admin@barber.com',
            'password' => Hash::make('password'), 
            'role' => 'admin',
            'phone' => '41999999999',
            'avatar' => null,
        ]);

        User::create([
            'name' => 'João Navalha',
            'email' => 'joao@barber.com',
            'password' => Hash::make('password'),
            'role' => 'barber',
            'phone' => '41988888888',
            'avatar' => null,
        ]);

        Service::create([
            'name' => 'Corte Degradê',
            'description' => 'Corte detalhado com tesoura e máquina',
            'price' => 35.00,
            'duration_minutes' => 45,
            'active' => true
        ]);

        Service::create([
            'name' => 'Barba Lenhador',
            'description' => 'Modelagem completa e terapia com toalha quente',
            'price' => 25.00,
            'duration_minutes' => 30,
            'active' => true
        ]);
    }
}