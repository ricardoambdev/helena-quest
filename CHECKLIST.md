# Helena Quest — Checklist Detalhado de Implementação

> **Stack:** Laravel 13 + Livewire 4 + Flutter (Android + iOS)  
> **Documento mestre:** `HELENA-QUEST.md`  
> **RFs:** `reference/03-REQUISITOS-FUNCIONAIS.md` e subdocumentos

---

## FASE 0 — Setup do Projeto

### 0.1 Criar projeto Laravel
- [x] `composer create-project laravel/laravel helena-quest "^13.0"` (Laravel 13.21.1 instalado — 2026-07-23)
- [x] Configurar `.env` (MySQL, Redis, Reverb, APP_URL=http://gincana.test)
- [x] `php artisan install:api` (Sanctum)
- [x] `composer require livewire/livewire laravel/reverb`
- [ ] `php artisan reverb:install` (instalado manualmente — config editada direto no .env)
- [x] Configurar broadcasting no `.env` (BROADCAST_CONNECTION=reverb)
- [x] Configurar filas (QUEUE_CONNECTION=database — ajustado para hospedagem compartilhada sem Redis garantido)
- [x] Verificar PHP 8.4+ e extensões (PHP 8.3.12 detectado — validar em produção)
- [x] MySQL conectado (database `helena_quest` criado)
- [x] Migrations iniciais rodadas (users, cache, jobs, personal_access_tokens)
- [x] App responde HTTP 200 em `/`

### 0.2 Setup Flutter
- [x] Instalar Flutter SDK 3.44.8 (instalado manualmente em `C:\flutter\flutter`)
- [x] `flutter create --org com.helenaquest --platforms android,ios helena_quest_app` (projeto gerado na raiz `E:\Projetos\gincana`)
- [x] Adicionar dependências: `google_maps_flutter`, `mobile_scanner`, `geolocator`, `image_picker`, `audioplayers`, `http`, `provider`, `permission_handler`
- [x] Configurar permissões Android (`AndroidManifest.xml`): CAMERA, ACCESS_FINE_LOCATION, ACCESS_COARSE_LOCATION, RECORD_AUDIO, INTERNET + features de hardware related
- [x] Configurar permissões iOS (`Info.plist`): NSCameraUsageDescription, NSLocationWhenInUseUsageDescription, NSMicrophoneUsageDescription, NSPhotoLibraryUsageDescription, NSPhotoLibraryAddUsageDescription
- [x] `flutter analyze` → "No issues found!" ✅

---

## FASE 1 — Fundação (Backend + Admin)

### 1.1 Migrations e Models
- [ ] Migration + Model `Competition` (name, description, year, date, start_time, end_time, status)
- [ ] Migration + Model `Proof` (competition_id, name, description, order, status, max_score)
- [ ] Migration + Model `Stage` (proof_id, name, description, order, latitude, longitude, radius, qr_code_uuid, narrative_text, image_url, correct_answer, secret_number, next_stage_hint, score, penalty, time_limit)
- [ ] Migration + Model `Team` (competition_id, name, color_hex, username, password_hash, status, crest_url)
- [ ] Migration + Model `TeamStageProgress` (team_id, stage_id, status, qr_scanned_at, gps_lat, gps_lng, photo_url, photo_sent_at, attempts_count, started_at, completed_at, score_earned)
- [ ] Migration + Model `TeamProofProgress` (team_id, proof_id, current_stage_id, total_score, total_time, stages_completed, correct_answers, wrong_answers, photos_count, audios_count, hints_bought, started_at, completed_at)
- [ ] Migration + Model `Audio` (team_id, stage_id, audio_url, duration, sent_at)
- [ ] Migration + Model `Hint` (stage_id, hint_text, price, order)
- [ ] Migration + Model `FinalEnigma` (competition_id, word, max_attempts, cooldown_minutes)
- [ ] Migration + Model `FinalEnigmaQrCode` (final_enigma_id, qr_code_uuid, letter, hint_text, order)
- [ ] Migration + Model `TeamFinalEnigmaAttempt` (team_id, final_enigma_id, attempt_number, guessed_word, correct, created_at, next_available_at)
- [ ] Migration + Model `AuthenticationLog` (team_id, ip, device_id, action, created_at)
- [ ] Definir relacionamentos (belongsTo, hasMany, etc.)
- [ ] Seeders para dados de teste

### 1.2 Game Engine (Service Layer)
- [ ] Criar `app/Services/GameEngine.php`
- [ ] Método `validateQrAndGps($team, $stage, $qrUuid, $gpsCoords)` → bool
- [ ] Método `processPhoto($team, $stage, $photo)` → libera pergunta
- [ ] Método `validateAnswer($team, $stage, $answer)` → resultado
- [ ] Método `completeStage($team, $stage)` → registra progresso, número secreto, próxima dica
- [ ] Método `buyHint($team, $stage, $hint)` → libera dica
- [ ] Método `calculateChaveFinal($team)` → concatena números secretos
- [ ] Método `validateFinalEnigmaGuess($team, $word)` → checa + cooldown

### 1.3 API REST (Sanctum)
- [ ] `POST /api/auth/login` — login equipe → token
- [ ] `POST /api/auth/logout` — invalida token
- [ ] `GET /api/auth/me` — dados da equipe logada
- [ ] `POST /api/auth/check` — verifica validade do token
- [ ] `GET /api/stages/current` — etapa atual da equipe
- [ ] `POST /api/stages/{stage}/validate-qr` — valida QR + GPS
- [ ] `POST /api/stages/{stage}/send-photo` — envia foto (multipart)
- [ ] `POST /api/stages/{stage}/answer` — responde pergunta
- [ ] `GET /api/stages/{stage}/hints` — dicas disponíveis
- [ ] `POST /api/stages/{stage}/buy-hint/{hint}` — compra dica extra
- [ ] `POST /api/audios` — envia áudio (multipart)
- [ ] `GET /api/audios` — lista áudios enviados
- [ ] `GET /api/final-enigma/status` — status do enigma final
- [ ] `POST /api/final-enigma/validate-letter/{qr}` — valida QR de letra
- [ ] `POST /api/final-enigma/guess` — tenta palavra
- [ ] `GET /api/final-enigma/attempts` — histórico de tentativas
- [ ] `GET /api/public/competition/{id}` — dados da competição
- [ ] `GET /api/public/teams-location` — localizações
- [ ] `GET /api/public/ranking` — ranking
- [ ] `GET /api/public/progress` — progresso
- [ ] `GET /api/public/photos` — fotos recentes
- [ ] `GET /api/public/audios` — áudios recentes

### 1.4 Broadcasting (Reverb)
- [ ] Configurar Laravel Reverb
- [ ] Evento `TeamStageUpdated` — progresso
- [ ] Evento `TeamLocationUpdated` — GPS
- [ ] Evento `TeamPhotoSent` — foto chegou
- [ ] Evento `TeamAudioSent` — áudio chegou
- [ ] Evento `TeamScoreUpdated` — pontuação alterada
- [ ] Evento `CompetitionStatusChanged` — pausa/início/fim
- [ ] Canal privado `team.{team_id}`
- [ ] Canal público `competition.{id}`

### 1.5 Componentes Livewire (Admin)
- [ ] `CompetitionForm` — CRUD competição
- [ ] `ProofForm` — CRUD prova com drag-and-drop
- [ ] `StageForm` — CRUD etapa com mapa (lat/lng picker)
- [ ] `TeamForm` — CRUD equipe
- [ ] `Dashboard` — visão geral com estatísticas
- [ ] `RankingLivewire` — ranking em tempo real
- [ ] `TeamMonitor` — acompanhamento individual

### 1.6 Filas (Queue)
- [ ] Job: processamento de fotos (redimensionamento, thumb)
- [ ] Job: processamento de áudios (transcodificação)
- [ ] Job: notificações push

---

## FASE 2 — App Mobile (Flutter) Core

### 2.1 Estrutura e Configuração
- [ ] Criar estrutura de diretórios (config/, services/, providers/, screens/, widgets/)
- [ ] Configurar tema (paleta FF6600, preto, branco)
- [ ] Configurar rotas
- [ ] Configurar constantes (URL da API, etc.)

### 2.2 Services
- [ ] `api_service.dart` — HTTP client com token
- [ ] `auth_service.dart` — login/logout/refresh
- [ ] `location_service.dart` — GPS (geolocator)
- [ ] `qr_service.dart` — leitor QR Code (mobile_scanner)
- [ ] `tts_service.dart` — TTS nativo (Android/iOS)
- [ ] `audio_service.dart` — gravação/envio de áudio
- [ ] `websocket_service.dart` — conexão Echo

### 2.3 Providers
- [ ] `auth_provider.dart` — estado de autenticação
- [ ] `stage_provider.dart` — estado da etapa atual
- [ ] `team_provider.dart` — dados da equipe
- [ ] `audio_provider.dart` — gravação/lista de áudios

### 2.4 Telas
- [ ] `login_screen.dart` — login com usuário + senha
- [ ] `home_screen.dart` — status da equipe, progresso
- [ ] `scanner_screen.dart` — câmera fullscreen, lê QR Code
- [ ] `stage_screen.dart` — narrativa + TTS automático + imagem
- [ ] `photo_screen.dart` — câmera para selfie/foto do local
- [ ] `answer_screen.dart` — teclado numérico (4-8 dígitos)
- [ ] `result_screen.dart` — acertou/errou, número secreto, dica
- [ ] `audio_screen.dart` — gravação e envio de áudio
- [ ] `map_screen.dart` — mapa com locais (opcional)
- [ ] `final_enigma_screen.dart` — tela do enigma final
- [ ] `profile_screen.dart` — dados da equipe, ranking

### 2.5 Widgets Compartilhados
- [ ] `numeric_keyboard.dart` — teclado numérico personalizado
- [ ] `progress_bar.dart` — barra de progresso
- [ ] `team_indicator.dart` — indicador visual da equipe
- [ ] `countdown_timer.dart` — timer regressivo

---

## FASE 3 — Telão + Tempo Real

### 3.1 Broadcasting (Backend)
- [ ] Configurar canal Echo público `competition.{id}`
- [ ] Configurar canal privado `team.{team_id}`
- [ ] Evento `TeamStageUpdated`
- [ ] Evento `TeamLocationUpdated`
- [ ] Evento `TeamPhotoSent`
- [ ] Evento `TeamAudioSent`
- [ ] Evento `TeamScoreUpdated`
- [ ] Evento `CompetitionStatusChanged`

### 3.2 Página Pública do Telão
- [ ] Rota `/telao/{competition}` (Livewire + Alpine.js)
- [ ] Mapa 4×3 com posições em tempo real
- [ ] Barra de progresso por equipe
- [ ] Pontuação + ranking
- [ ] Carrossel de fotos
- [ ] Player de áudio automático (fila)
- [ ] Layout 1920×1080, fundo escuro, fonte grande
- [ ] Atualização via WebSocket (sem refresh manual)

---

## FASE 4 — Enigma Final

### 4.1 Backend
- [ ] Geração da chave (concatenação reversa dos números secretos)
- [ ] CRUD FinalEnigma (admin)
- [ ] CRUD FinalEnigmaQrCode (admin)
- [ ] Validação de QR Code de letra
- [ ] Validação da palavra final
- [ ] Controle de tentativas (3 máx, cooldown 2h)
- [ ] Pontuação final

### 4.2 App Mobile
- [ ] Tela de enigma final
- [ ] Scanner de QR Codes de letras
- [ ] Input de palavra (anagrama)
- [ ] Feedback de tentativas
- [ ] Timer de cooldown

---

## FASE 5 — Compras de Dicas + Áudios

### 5.1 Dicas Extras
- [ ] CRUD de dicas no admin
- [ ] API de compra de dica
- [ ] Tela de dicas no app
- [ ] Registro de compras

### 5.2 Áudios
- [ ] API de envio de áudio (multipart)
- [ ] Tela de gravação no app
- [ ] Lista de áudios enviados
- [ ] Player de áudio no telão (fila automática)

---

## FASE 6 — Polimento

### 6.1 Auditoria e Logs
- [ ] Sistema de auditoria completo (todas as ações)
- [ ] Visualização de logs no admin
- [ ] Exportação de logs

### 6.2 Relatórios
- [ ] Relatório por competição
- [ ] Relatório por equipe
- [ ] Relatório por prova
- [ ] Exportação CSV/PDF

### 6.3 Testes
- [ ] Testes unitários (GameEngine)
- [ ] Testes de API (Feature tests)
- [ ] Testes de componentes Livewire
- [ ] Testes E2E (opcional)

### 6.4 Deploy
- [ ] Configurar Laravel Forge / Vapor
- [ ] Configurar domínio + SSL
- [ ] Build Android (APK)
- [ ] Build iOS (IPA)
- [ ] Publicar na Google Play
- [ ] Publicar na App Store

---

## Legenda

| Símbolo | Significado |
|---------|-------------|
| `[ ]` | Pendente |
| `[~]` | Em andamento |
| `[x]` | Concluído |
| `[-]` | Cancelado / não aplicável |

---

## Histórico de Atualizações

| Data | Fase | Mudança |
|------|------|---------|
| 2026-07-23 | 0.1 | Setup Laravel 13 + MySQL + Livewire + Reverb + Sanctum — **CONCLUÍDA** |
| 2026-07-23 | 0.2 | Flutter SDK 3.44.8 instalado, projeto criado, dependências adicionadas, permissões Android/iOS configuradas, `flutter analyze` passa — **CONCLUÍDA** |
| 2026-07-23 | AGENTS | Adicionada diretiva § 7 obrigando uso da skill `frontend-design` antes de criar/refatorar qualquer tela ou componente visual |
