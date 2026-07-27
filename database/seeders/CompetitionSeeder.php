<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Competition;
use App\Models\Hint;
use App\Models\Stage;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CompetitionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@helenaquest.com.br'],
            [
                'name' => 'Admin Helena Quest',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $comp = Competition::firstOrCreate(
            ['name' => 'Gincana Helena Quest 2026', 'year' => 2026],
            [
                'description' => 'Gincana gamificada com GPS, QR Code e desafios.',
                'date' => '2026-08-15',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'status' => 'planning',
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]
        );

        if ($comp->wasRecentlyCreated === false) {
            return;
        }

        $stagesData = [
            [
                'name' => 'Praça Central', 'stage_type' => 'charada', 'order' => 1,
                'latitude' => -23.5505, 'longitude' => -46.6333, 'radius' => 50,
                'narrative_text' => 'Você está na praça central. Encontre o monumento e descubra o número.',
                'correct_answer' => '1234', 'secret_number' => '7',
                'next_stage_hint' => 'Siga para o norte.', 'score' => 50, 'penalty' => 0,
            ],
            [
                'name' => 'Caça ao Tesouro Urbano', 'stage_type' => 'caca_tesouro', 'order' => 2,
                'latitude' => -23.5510, 'longitude' => -46.6340, 'radius' => 30,
                'narrative_text' => 'Na biblioteca, um livro antigo guarda o próximo número.',
                'correct_answer' => '5678', 'secret_number' => '3',
                'next_stage_hint' => 'Vá em direção ao rio.', 'score' => 50, 'penalty' => 0,
            ],
            [
                'name' => 'Desafio da Bússola', 'stage_type' => 'mapas_bussola', 'order' => 3,
                'latitude' => -23.5515, 'longitude' => -46.6350, 'radius' => 40,
                'narrative_text' => 'Use a bússola para encontrar o ponto correto.',
                'sub_questions' => json_encode([
                    ['question' => 'Quantas janelas tem o prédio à direita?', 'answer' => '12'],
                    ['question' => 'Qual a cor da porta principal?', 'answer' => 'azul'],
                ]),
                'correct_answer' => '9012', 'secret_number' => '1',
                'next_stage_hint' => 'Siga o som da música.', 'score' => 50, 'penalty' => 0,
            ],
            [
                'name' => 'Teatro Municipal', 'stage_type' => 'charada', 'order' => 4,
                'latitude' => -23.5520, 'longitude' => -46.6360, 'radius' => 35,
                'narrative_text' => 'No teatro, o palco revela o próximo número.',
                'correct_answer' => '3456', 'secret_number' => '9',
                'next_stage_hint' => 'Última etapa antes do enigma final!', 'score' => 50, 'penalty' => 0,
            ],
        ];

        foreach ($stagesData as $data) {
            $data['competition_id'] = $comp->id;
            $data['qr_code_uuid'] = (string) \Illuminate\Support\Str::uuid();
            Stage::create($data);
        }

        // Enigma final
        $stages = Stage::where('competition_id', $comp->id)->get();
        Stage::create([
            'competition_id' => $comp->id,
            'name' => 'Enigma Final',
            'stage_type' => 'enigma_final',
            'order' => 5,
            'narrative_text' => 'Descubra a palavra final com os números coletados!',
            'correct_answer' => str_repeat('0', 5),
            'score' => 50,
            'penalty' => 0,
        ]);

        // Dicas para cada etapa (exceto enigma final)
        foreach ($stages as $s) {
            Hint::create(['stage_id' => $s->id, 'hint_text' => 'Dica: A resposta tem ' . strlen($s->correct_answer) . ' dígitos.', 'price' => 10, 'order' => 1]);
            Hint::create(['stage_id' => $s->id, 'hint_text' => 'Dica: O primeiro dígito é ' . $s->correct_answer[0] . '.', 'price' => 20, 'order' => 2]);
            Hint::create(['stage_id' => $s->id, 'hint_text' => 'Dica: A soma dos dígitos é ' . array_sum(str_split($s->correct_answer)) . '.', 'price' => 30, 'order' => 3]);
        }

        // Equipes demo
        $teams = [
            ['name' => 'Equipe Alpha', 'color_hex' => '#FF6600', 'username' => 'alpha', 'password' => 'alpha123'],
            ['name' => 'Equipe Beta', 'color_hex' => '#2563EB', 'username' => 'beta', 'password' => 'beta123'],
        ];

        foreach ($teams as $t) {
            Team::create([
                'competition_id' => $comp->id,
                'name' => $t['name'],
                'color_hex' => $t['color_hex'],
                'username' => $t['username'],
                'password_hash' => Hash::make($t['password']),
                'status' => 'active',
            ]);
        }
    }
}
