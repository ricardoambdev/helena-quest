<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'ricardoambamb.dev@gmail.com'],
            [
                'name' => 'Ricardo',
                'password' => bcrypt('idspispopd'),
                'role' => 'root',
                'is_active' => true,
            ]
        );

        $this->call([
            CompetitionSeeder::class,
        ]);
    }
}
