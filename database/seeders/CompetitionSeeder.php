<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Competition;
use App\Models\FinalEnigma;
use App\Models\FinalEnigmaQrCode;
use App\Models\Hint;
use App\Models\Proof;
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
            return; // Já foi semeada
        }

        $prova1 = Proof::create([
            'competition_id' => $comp->id,
            'name' => 'Prova 1 — Caça ao Tesouro Urbano',
            'description' => 'Percorra os pontos históricos da cidade resolvendo enigmas.',
            'order' => 1,
            'status' => 'active',
            'max_score' => 500,
        ]);

        $prova2 = Proof::create([
            'competition_id' => $comp->id,
            'name' => 'Prova 2 — Desafio da Natureza',
            'description' => 'Explore áreas verdes e complete as missões.',
            'order' => 2,
            'status' => 'active',
            'max_score' => 400,
        ]);

        $stagesData = [
            // Prova 1 — 5 etapas
            ['proof_id' => $prova1->id, 'name' => 'Praça Central', 'order' => 1, 'latitude' => -23.5505, 'longitude' => -46.6333, 'radius' => 50, 'narrative_text' => 'Você está na praça central. Encontre o monumento e descubra o número.', 'correct_answer' => '1234', 'secret_number' => '7', 'next_stage_hint' => 'Siga para o norte.', 'score' => 100, 'penalty' => 10],
            ['proof_id' => $prova1->id, 'name' => 'Biblioteca Municipal', 'order' => 2, 'latitude' => -23.5510, 'longitude' => -46.6340, 'radius' => 30, 'narrative_text' => 'Na biblioteca, um livro antigo guarda o próximo número.', 'correct_answer' => '5678', 'secret_number' => '3', 'next_stage_hint' => 'Vá em direção ao rio.', 'score' => 100, 'penalty' => 10],
            ['proof_id' => $prova1->id, 'name' => 'Museu de Arte', 'order' => 3, 'latitude' => -23.5515, 'longitude' => -46.6350, 'radius' => 40, 'narrative_text' => 'No museu, uma pintura esconde um segredo.', 'correct_answer' => '9012', 'secret_number' => '1', 'next_stage_hint' => 'Siga o som da música.', 'score' => 100, 'penalty' => 10],
            ['proof_id' => $prova1->id, 'name' => 'Teatro Municipal', 'order' => 4, 'latitude' => -23.5520, 'longitude' => -46.6360, 'radius' => 35, 'narrative_text' => 'No teatro, o palco revela o próximo número.', 'correct_answer' => '3456', 'secret_number' => '9', 'next_stage_hint' => 'Última etapa da prova 1!', 'score' => 100, 'penalty' => 10],
            ['proof_id' => $prova1->id, 'name' => 'Estação Ferroviária', 'order' => 5, 'latitude' => -23.5525, 'longitude' => -46.6370, 'radius' => 50, 'narrative_text' => 'A estação guarda o último número desta prova.', 'correct_answer' => '7890', 'secret_number' => '5', 'next_stage_hint' => 'Prova 1 concluída!', 'score' => 100, 'penalty' => 10],
            // Prova 2 — 3 etapas
            ['proof_id' => $prova2->id, 'name' => 'Parque Ecológico', 'order' => 1, 'latitude' => -23.5530, 'longitude' => -46.6380, 'radius' => 60, 'narrative_text' => 'No parque, a natureza guarda segredos.', 'correct_answer' => '1111', 'secret_number' => '2', 'next_stage_hint' => 'Siga a trilha.', 'score' => 150, 'penalty' => 15],
            ['proof_id' => $prova2->id, 'name' => 'Mirante da Serra', 'order' => 2, 'latitude' => -23.5535, 'longitude' => -46.6390, 'radius' => 45, 'narrative_text' => 'Do mirante, aviste o próximo destino.', 'correct_answer' => '2222', 'secret_number' => '8', 'next_stage_hint' => 'Desça a montanha.', 'score' => 150, 'penalty' => 15],
            ['proof_id' => $prova2->id, 'name' => 'Cachoeira Escondida', 'order' => 3, 'latitude' => -23.5540, 'longitude' => -46.6400, 'radius' => 40, 'narrative_text' => 'A cachoeira revela o último número.', 'correct_answer' => '3333', 'secret_number' => '4', 'next_stage_hint' => 'Prova 2 concluída!', 'score' => 100, 'penalty' => 10],
        ];

        foreach ($stagesData as $data) {
            $data['qr_code_uuid'] = (string) \Illuminate\Support\Str::uuid();
            Stage::create($data);
        }

        // Dicas para cada etapa
        $stages = Stage::all();
        foreach ($stages as $s) {
            Hint::create(['stage_id' => $s->id, 'hint_text' => 'Dica: A resposta tem ' . strlen($s->correct_answer) . ' dígitos.', 'price' => 10, 'order' => 1]);
            Hint::create(['stage_id' => $s->id, 'hint_text' => 'Dica: O primeiro dígito é ' . $s->correct_answer[0] . '.', 'price' => 20, 'order' => 2]);
            Hint::create(['stage_id' => $s->id, 'hint_text' => 'Dica: A soma dos dígitos é ' . array_sum(str_split($s->correct_answer)) . '.', 'price' => 30, 'order' => 3]);
        }

        // Equipes demo
        $teams = [
            ['name' => 'Equipe Alpha', 'color_hex' => '#FF6600', 'username' => 'alpha', 'password' => 'alpha123', 'crest_url' => null],
            ['name' => 'Equipe Beta', 'color_hex' => '#2563EB', 'username' => 'beta', 'password' => 'beta123', 'crest_url' => null],
            ['name' => 'Equipe Gamma', 'color_hex' => '#16A34A', 'username' => 'gamma', 'password' => 'gamma123', 'crest_url' => null],
        ];

        foreach ($teams as $t) {
            Team::create([
                'competition_id' => $comp->id,
                'name' => $t['name'],
                'color_hex' => $t['color_hex'],
                'username' => $t['username'],
                'password_hash' => Hash::make($t['password']),
                'status' => 'active',
                'crest_path' => $t['crest_url'],
            ]);
        }

        // Enigma final
        $enigma = FinalEnigma::create([
            'competition_id' => $comp->id,
            'word' => 'HELENA',
            'max_attempts' => 3,
            'cooldown_minutes' => 120,
            'final_score' => 500,
            'description' => 'Descubra a palavra final com as letras coletadas! Chave: ' . collect($stages)->pluck('secret_number')->reverse()->implode(''),
        ]);

        $letters = str_split('HELENA');
        foreach ($letters as $i => $letter) {
            FinalEnigmaQrCode::create([
                'final_enigma_id' => $enigma->id,
                'qr_code_uuid' => \Illuminate\Support\Str::uuid()->toString(),
                'letter' => $letter,
                'hint_text' => match ($letter) {
                    'H' => 'Primeira letra da palavra final.',
                    'E' => 'Segunda letra — muito comum.',
                    'L' => 'Terceira letra.',
                    'N' => 'Quinta letra.',
                    'A' => 'Sexta letra — última.',
                    default => 'Letra do enigma final.',
                },
                'order' => $i + 1,
            ]);
        }
    }
}
