<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuthenticationLog;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string',
            'device_id' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        /** @var Team|null $team */
        $team = Team::where('username', $data['username'])->first();

        if (!$team || !Hash::check($data['password'], $team->password_hash)) {
            AuthenticationLog::create([
                'username_attempted' => $data['username'],
                'ip' => $request->ip(),
                'device_id' => $data['device_id'] ?? null,
                'user_agent' => substr((string) $request->userAgent(), 0, 200),
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'action' => 'failed',
                'success' => false,
                'created_at' => now(),
            ]);

            throw ValidationException::withMessages([
                'username' => ['Credenciais inválidas.'],
            ]);
        }

        if (!$team->canLogin()) {
            $reason = match ($team->status) {
                'blocked' => 'Equipe bloqueada. Contate a organização.',
                'inactive' => 'Equipe inativa.',
                'eliminated' => 'Equipe eliminada.',
                default => 'Equipe não autorizada a autenticar.',
            };

            AuthenticationLog::create([
                'team_id' => $team->id,
                'username_attempted' => $data['username'],
                'ip' => $request->ip(),
                'device_id' => $data['device_id'] ?? null,
                'user_agent' => substr((string) $request->userAgent(), 0, 200),
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'action' => 'failed',
                'success' => false,
                'created_at' => now(),
            ]);

            return response()->json(['message' => $reason, 'status' => $team->status], 403);
        }

        // NOVO LOGIN → INVALIDAR SESSÕES ANTERIORES
        $previousTokenCount = $team->tokens()->count();
        if ($previousTokenCount > 0) {
            $team->tokens()->delete();

            AuthenticationLog::create([
                'team_id' => $team->id,
                'username_attempted' => $data['username'],
                'ip' => $request->ip(),
                'device_id' => $data['device_id'] ?? null,
                'action' => 'session_killed',
                'success' => true,
                'created_at' => now(),
            ]);
        }

        $token = $team->createToken($data['device_id'] ?? 'mobile', ['team'])->plainTextToken;

        AuthenticationLog::create([
            'team_id' => $team->id,
            'username_attempted' => $data['username'],
            'ip' => $request->ip(),
            'device_id' => $data['device_id'] ?? null,
            'user_agent' => substr((string) $request->userAgent(), 0, 200),
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'action' => 'login',
            'success' => true,
            'created_at' => now(),
        ]);

        return response()->json([
            'token' => $token,
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'color' => $team->color_hex,
                'competition_id' => $team->competition_id,
                'crest_url' => $team->crest_path ? \Storage::url($team->crest_path) : null,
                'status' => $team->status,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var Team $team */
        $team = $request->user();

        $token = $team->currentAccessToken();
        if ($token) {
            $token->delete();
        }

        AuthenticationLog::create([
            'team_id' => $team->id,
            'ip' => $request->ip(),
            'device_id' => $request->input('device_id'),
            'action' => 'logout',
            'success' => true,
            'created_at' => now(),
        ]);

        return response()->json(['message' => 'Logout realizado.']);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var Team $team */
        $team = $request->user()->load(['competition', 'proofProgress']);

        return response()->json([
            'id' => $team->id,
            'name' => $team->name,
            'color' => $team->color_hex,
            'crest_url' => $team->crest_path ? \Storage::url($team->crest_path) : null,
            'status' => $team->status,
            'competition' => [
                'id' => $team->competition->id,
                'name' => $team->competition->name,
                'status' => $team->competition->status,
            ],
            'progress' => $team->proofProgress->map(fn ($p) => [
                'proof_id' => $p->proof_id,
                'total_score' => $p->total_score,
                'stages_completed' => $p->stages_completed,
                'hint_used' => (bool) $p->hints_bought,
            ]),
        ]);
    }

    public function check(Request $request): JsonResponse
    {
        return response()->json([
            'valid' => true,
            'team_id' => $request->user()->id,
            'expires_at' => $request->user()->currentAccessToken()?->expires_at,
        ]);
    }
}
