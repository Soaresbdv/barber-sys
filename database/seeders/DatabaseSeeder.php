<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario administrador (pode ser usado para testes ou gerenciamento)
        User::firstOrCreate(['email' => 'admin@barber.com'], [
            'name' => 'Admin BarberSys',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Barbeiro
        User::firstOrCreate(['email' => 'joao@barber.com'], [
            'name' => 'João Navalha',
            'password' => Hash::make('password'),
            'role' => 'barber',
            'phone' => '11999999999'
        ]);
        // Barbeiro
        User::firstOrCreate(['email' => 'carlos@barber.com'], [
            'name' => 'Carlos Tesoura',
            'password' => Hash::make('password'),
            'role' => 'barber',
            'phone' => '11988888888'
        ]);

        // Serviços disnponíveis
        Service::create([
            'name' => 'Corte Clássico',
            'price' => 45.00,
            'duration_minutes' => 45
        ]);
        // Serviços disnponíveis
        Service::create([
            'name' => 'Barba Terapia',
            'price' => 35.00,
            'duration_minutes' => 30
        ]);
        // Serviços disnponíveis
        Service::create([
            'name' => 'Combo Completo',
            'price' => 70.00,
            'duration_minutes' => 60
        ]);
    }
}