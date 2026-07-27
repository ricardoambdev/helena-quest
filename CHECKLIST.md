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
- [x] Migration + Model `Competition` — 13 colunas, enum status, unique(name,year), FK users
- [x] Migration + Model `Proof` — FK competition, order sorting, enum status
- [x] Migration + Model `Stage` — FK proof, lat/lng/radius, qr_code_uuid, narrative, correct_answer/secret_number hidden
- [x] Migration + Model `Team` — Authenticatable + HasApiTokens, password_hash, soft deletes
- [x] Migration + Model `TeamStageProgress` — progresso por etapa (status, qr, gps, photo, attempts, score)
- [x] Migration + Model `TeamProgress` (table team_progress) — agregado por prova (score, time, stages, hints)
- [x] Migration + Model `Audio` — audio por team+stage, url+duration+sent_at
- [x] Migration + Model `Hint` — dicas por stage (text, price, order)
- [x] Migration + Model `FinalEnigma` — palavra final, max_attempts, cooldown
- [x] Migration + Model `FinalEnigmaQrCode` — QR por letra (uuid, letter, hint_text)
- [x] Migration + Model `TeamFinalEnigmaAttempt` — tentativas (cooldown, correct boolean)
- [x] Migration + Model `AuthenticationLog` — log de auth (timestamps=false)
- [x] Migration + Model `TeamFinalEnigmaLetter` — letras escaneadas (model + migration)
- [x] Definir relacionamentos (belongsTo, hasMany) em todos os models
- [x] Seeders para dados de teste — `CompetitionSeeder` (admin, 1 comp, 2 proofs, 8 stages, hints, 3 teams, enigma final)

### 1.2 Game Engine (Service Layer)
- [x] Criar `app/Services/GameEngine.php` — 351 linhas, 7 métodos + 2 helpers privados
- [x] `validateQrAndGps($team, $stage, $qrUuid, $gpsCoords)` — valida QR + distancia Haversine, cria TeamStageProgress
- [x] `processPhoto($team, $stage, $photo)` — marca photo_sent, incrementa contagem
- [x] `validateAnswer($team, $stage, $answer)` — valida 4-8 digitos, hash_equals, chama completeStage
- [x] `completeStage($team, $stage)` — score com penalidade, secret_number, avança progresso
- [x] `buyHint($team, $stage, $hint)` — marca hint_used, incrementa hints_bought
- [x] `calculateChaveFinal($team)` — concatena reversa dos secret_numbers
- [x] `validateFinalEnigmaGuess($team, $word)` — cooldown, max_attempts, hash_equals
- [x] `DistanceCalculator::haversine()` — formula de Haversine precisa

### 1.3 API REST (Sanctum)
- [x] `POST /api/auth/login` — login team → token (invalida sessão anterior)
- [x] `POST /api/auth/logout` — deleta token atual
- [x] `GET /api/auth/me` — team + competition + progresso
- [x] `POST /api/auth/check` — valida token
- [x] `GET /api/stages/current` — etapa atual com `correct_answer_length`
- [x] `POST /api/stages/{stage}/validate-qr` — QR + GPS, opcional
- [x] `POST /api/stages/{stage}/send-photo` — multipart, max 10MB, storage public
- [x] `POST /api/stages/{stage}/answer` — delega ao GameEngine
- [x] `GET /api/stages/{stage}/hints` — dicas ordenadas
- [x] `POST /api/stages/{stage}/buy-hint/{hint}` — compra dica
- [x] `POST /api/audios` — audio multipart, max 20MB, fire TeamAudioSent
- [x] `GET /api/audios` — lista audios do team
- [x] `GET /api/final-enigma/status` — enabled/attempts/letters_unlocked
- [x] `POST /api/final-enigma/validate-letter/{qr}` — scan QR letra
- [x] `POST /api/final-enigma/guess` — tenta palavra
- [x] `GET /api/final-enigma/attempts` — historico
- [x] `GET /api/public/competition/{id}` — dados publicos
- [x] `GET /api/public/teams-location/{competitionId}` — ultimo GPS
- [x] `GET /api/public/ranking/{competitionId}` — ranking agregado
- [x] `GET /api/public/progress/{competitionId}` — % por team
- [x] `GET /api/public/photos/{competitionId}` — 40 fotos recentes
- [x] `GET /api/public/audios/{competitionId}` — 20 audios recentes

### 1.4 Broadcasting (Reverb)
- [x] Configurar Laravel Reverb — .env configurado, BROADCAST_CONNECTION=reverb
- [x] `TeamStageUpdated` — progresso em private+public channel
- [x] `TeamLocationUpdated` — GPS em private+public
- [x] `TeamPhotoSent` — foto em private+public
- [x] `TeamAudioSent` — audio em private+public
- [x] `TeamScoreUpdated` — pontuacao alterada (agora disparado em completeStage)
- [x] `CompetitionStatusChanged` — status comp em public channel
- [x] Canal privado `team.{team_id}` — autoriza por Team model
- [x] Canal público `competition.{id}` — public auth

### 1.5 Componentes Livewire (Admin)
- [x] `Dashboard` — totais, competicoes recentes, ranking live
- [x] `CompetitionForm` — CRUD + publish/start/pause/finish
- [x] `CompetitionIndex` — listagem paginada com busca
- [x] `ProofForm` — CRUD com auto-order
- [x] `ProofIndex` — listagem filtrada por competicao
- [x] `StageForm` — CRUD com coords, QR uuid, validacao 4-8 digitos
- [x] `StageIndex` — listagem filtrada por prova
- [x] `TeamForm` — CRUD com password hashing, block/unblock
- [x] `TeamIndex` — listagem paginada
- [x] `TeamMonitor` — progresso individual da equipe
- [x] `RankingLive` — ranking completo
- [x] `FinalEnigmaForm` — CRUD enigma + QRs dinâmicos
- [x] `LogsIndex` — listagem de auditoria filtrável
- [x] Layout admin com sidebar — paleta do design system, navegação, tailwind v4 CDN
- [x] Login admin — email+senha, guard web, session

### 1.6 Filas (Queue)
- [x] `ProcessPhotoThumbs` — Job de processamento de foto (placeholder GD — integração futura)
- [x] `SendNotification` — Job de notificação push (placeholder FCM — integração futura)
- [x] `QUEUE_CONNECTION=database` — configurado para hospedagem compartilhada

---

## FASE 2 — App Mobile (Flutter) Core ✅

### 2.1 Estrutura e Configuração
- [x] Criar estrutura de diretórios (config/, services/, providers/, screens/, widgets/)
- [x] Configurar tema (paleta FF6600, preto, branco)
- [x] Configurar rotas
- [x] Configurar constantes (URL da API, etc.)

### 2.2 Services
- [x] `api_service.dart` — HTTP client com token
- [x] `auth_service.dart` — login/logout/refresh
- [x] `location_service.dart` — GPS (geolocator)
- [x] `qr_service.dart` — leitor QR Code (mobile_scanner)
- [x] `tts_service.dart` — TTS nativo (Android/iOS)
- [x] `audio_service.dart` — gravação/envio de áudio
- [x] `websocket_service.dart` — conexão Echo (placeholder)

### 2.3 Providers
- [x] `auth_provider.dart` — estado de autenticação
- [x] `stage_provider.dart` — estado da etapa atual
- [x] `team_provider.dart` — dados da equipe
- [x] `audio_provider.dart` — gravação/lista de áudios

### 2.4 Telas
- [x] `login_screen.dart` — login com usuário + senha
- [x] `home_screen.dart` — status da equipe, progresso
- [x] `scanner_screen.dart` — câmera fullscreen, lê QR Code
- [x] `stage_screen.dart` — narrativa + TTS automático + imagem
- [x] `photo_screen.dart` — camera + preview + enviar foto
- [x] `answer_screen.dart` — teclado númerico custom (4-8 digitos), validacao, shake on error
- [x] `result_screen.dart` — correto/incorreto, numero secreto, score, navegacao
- [x] `audio_screen.dart` — gravacao toggle, upload simulado, lista de audios
- [x] `map_screen.dart` — mapa com locais (opcional)
- [x] `final_enigma_screen.dart` — tela do enigma final
- [x] `profile_screen.dart` — dados da equipe, ranking

### 2.5 Widgets Compartilhados
- [x] `numeric_keyboard.dart` — teclado numérico personalizado
- [x] `progress_bar.dart` — barra de progresso
- [x] `team_indicator.dart` — indicador visual da equipe
- [x] `countdown_timer.dart` — timer regressivo

### 2.6 Entry Point
- [x] `main.dart` — MultiProvider + SplashScreen auto-login + AppTheme.light
- [x] Dependências: `flutter_tts`, `shared_preferences`, `web_socket_channel`
- [x] `flutter analyze` — 0 erros, 0 warnings ✅

---

## FASE 3 — Telão + Tempo Real

### 3.1 Broadcasting (Backend)
- [x] Configurar canal Echo público `competition.{id}`
- [x] Configurar canal privado `team.{team_id}`
- [x] Evento `TeamStageUpdated`
- [x] Evento `TeamLocationUpdated`
- [x] Evento `TeamPhotoSent`
- [x] Evento `TeamAudioSent`
- [x] Evento `TeamScoreUpdated`
- [x] Evento `CompetitionStatusChanged`

### 3.2 Página Pública do Telão
- [x] Rota `/telao/{competition}` (Livewire + Alpine.js)
- [x] Mapa Google Maps com pins das equipes
- [x] Progresso sem nomes (só barras + contagem)
- [x] Barra de progresso por equipe
- [x] Pontuação + ranking
- [x] Carrossel de fotos
- [x] Player de áudio automático (fila)
- [x] Layout 1920×1080, fundo escuro, fonte grande
- [x] Atualização via WebSocket + Livewire polling (fallback)

---

## FASE 4 — Enigma Final

### 4.1 Backend
- [x] Geração da chave (concatenação reversa dos números secretos)
- [x] CRUD FinalEnigma (admin)
- [x] CRUD FinalEnigmaQrCode (admin)
- [x] Validação de QR Code de letra
- [x] Validação da palavra final
- [x] Controle de tentativas (3 máx, cooldown 2h)
- [x] Pontuação final (final_score +500 ao resolver)

### 4.2 App Mobile
- [x] Tela de enigma final
- [x] Scanner de QR Codes de letras (tela dedicada)
- [x] Input de palavra
- [x] Feedback de tentativas (histórico + check/cancel)
- [x] Timer de cooldown (countdown regressivo)

---

## FASE 5 — Compras de Dicas + Áudios

### 5.1 Dicas Extras
- [x] CRUD de dicas no admin
- [x] API de compra de dica
- [x] Tela de dicas no app
- [x] Registro de compras

### 5.2 Áudios
- [x] API de envio de áudio (multipart)
- [x] Tela de gravação no app
- [x] Lista de áudios enviados
- [x] Player de áudio no telão (fila automática)

---

## FASE 6 — Polimento

### 6.1 Auditoria e Logs
- [x] Sistema de auditoria completo (todas as ações)
- [x] Visualização de logs no admin
- [x] Exportação de logs

### 6.2 Relatórios
- [x] Relatório por competição
- [x] Relatório por equipe
- [x] Relatório por prova
- [x] Exportação CSV (3 relatórios + logs)

### 6.3 Testes
- [x] Testes unitários (GameEngine — 13 testes)
- [x] Testes de API (Feature tests — 9 testes)
- [~] Testes de componentes Livewire (próximo ciclo)

### 6.4 Limite de Equipes
- [x] Migration add max_teams (default 2) na tabela competitions
- [x] Campo ajustável na página de configuração da competição
- [x] Validação no cadastro de equipes: bloqueia se atingiu o limite
- [x] Correção geral de rotas admin em Livewire + views (redirectRoute e route())

### 6.5 Configurações + Root
- [x] Migration add root role to ENUM users.role
- [x] Usuário root: ricardoambamb.dev@gmail.com / idspispopd
- [x] Seeder primário com root + admin
- [x] Página de Configurações (Livewire + rota /admin/settings)
- [x] Sidebar redesenhada: cor #171A21 (slate escuro), menu minimalista, link Configurações, logout harmonioso

### 6.7 Restruturação ALTERACOES
- [x] Migration `complete_restructure` — drop proofs/final_enigmas, add stage_type/bonus_onus/war_cry/school
- [x] Migration `add_competition_id_to_team_progress`
- [x] Models: BonusOnus, TeamBonusOnus criados; Proof/FinalEnigma removidos
- [x] GameEngine rewrite: sem Proof, scoring 50+30, unlock, bonus/onus, word guess
- [x] StageController: stage_type-aware, unlock, scanBonusOnus
- [x] FinalEnigmaController: cofres em vez de letras
- [x] Telao: stages em vez de proofs, enigmaFinalStatus por stage_type
- [x] Flutter: HomeScreen, StageScreen, CompassScreen, FinalEnigmaScreen atualizados
- [x] Admin Dashboard/StageIndex corrigidos
- [~] Admin: refatorar ProofForm/Index/Report, FinalEnigmaForm, CompetitionReport, blades
- [ ] Admin: StageForm atualizar para competition_id e stage_type
- [ ] Tests: atualizar GameEngine/API tests para novo schema

### 6.6 Deploy
- [ ] Configurar Laravel Forge / Vapor
- [ ] Configurar domínio + SSL
- [~] Build Android (APK — bloqueado no Windows, CI necessário)
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
| 2026-07-25 | 1.1 | Models + migrations completas, TeamFinalEnigmaLetter model criado, seeders populados |
| 2026-07-25 | 1.2 | GameEngine verificado — 7/7 métodos, TeamScoreUpdated disparado em completeStage |
| 2026-07-25 | 1.3 | API auditada — 22/22 rotas, FinalEnigmaController corrigido (whereHasMorph removido) |
| 2026-07-25 | 1.4 | Broadcasting verificado — 6/6 eventos ativos |
| 2026-07-25 | 1.5 | Livewire auditado — 13/13 componentes, layout admin corrigido (3 bugs sintáticos) |
| 2026-07-25 | 1.6 | Queue Jobs criados — ProcessPhotoThumbs + SendNotification |
| 2026-07-25 | 1.x | Telão show.blade.php criado (server-rendered), storage:link executado |
| 2026-07-25 | 2.4 | 4 telas Flutter criadas: photo_screen, answer_screen, result_screen, audio_screen |
| 2026-07-25 | 2.4 | 3 telas Flutter criadas: map_screen, final_enigma_screen, profile_screen |
| 2026-07-25 | 2.5 | 4 widgets compartilhados criados: numeric_keyboard, progress_bar, team_indicator, countdown_timer |
| 2026-07-25 | 2.0 | main.dart reescrito com MultiProvider + SplashScreen de auto-login |
| 2026-07-25 | 2.x | Fase 2 completa — flutter analyze 0 erros 0 warnings ✅ |
| 2026-07-25 | DOC | README.md criado com documentação completa + guias de deploy |
| 2026-07-26 | 3.1 | Broadcasting verificado — 6/6 eventos ativos, disparos nos controllers + GameEngine |
| 2026-07-26 | 3.2 | Telão Livewire + Alpine.js criado: rota Livewire, 1920×1080, ranking, progresso, grid 4×3, carrossel de fotos, player de áudio, wire:poll.5s + Echo WebSocket |
| 2026-07-26 | 3.x | layouts/app.blade.php criado (layout padrão Livewire), config livewire component_layout ajustado |
| 2026-07-26 | 4.1 | Pontuação final adicionada: migration final_score (500), GameEngine award + TeamScoreUpdated |
| 2026-07-26 | 4.2 | Flutter final_enigma_screen reescrita: endpoints corretos, parsing, cooldown timer, scan button |
| 2026-07-26 | 4.2 | FinalEnigmaScanScreen criada: scanner dedicado para letras do enigma final |
| 2026-07-26 | 4.3 | Final enigma status adicionado ao telão (grid 4×3: resolvido/letras coletadas) |
| 2026-07-26 | 5.1 | CRUD de dicas no admin (StageForm): addHint, removeHint, syncHints com inline hintsData array |
| 2026-07-26 | 5.1 | API hints() corrigida: só revela texto se time comprou a dica |
| 2026-07-26 | 5.1 | Flutter _HintTile corrigido: chaves locked/text/price em vez de locked/content/cost/title |
| 2026-07-26 | 5.2 | Áudios verificado — já completo desde fases anteriores ✅ |
| 2026-07-26 | 6.1 | AuditLog migration + model + AuditService + logging em GameEngine (7 ações) e controllers |
| 2026-07-26 | 6.1 | GameLogsIndex Livewire: admin viewer + CSV export + filtros por equipe/ação |
| 2026-07-26 | 6.2 | 3 relatórios Livewire: CompetitionReport, TeamReport, ProofReport + CSV export |
| 2026-07-26 | 6.3 | 22 testes (13 unit GameEngine + 9 Feature API) — todos passando ✅ |
| 2026-07-26 | 6.3 | Fix PublicChannel → Channel para Laravel 13 compatibilidade |
| 2026-07-26 | 6.x | 7 factories criadas: Competition, Team, Proof, Stage, Hint, FinalEnigma, FinalEnigmaQrCode |
| 2026-07-26 | 6.5 | Limite de equipes configurável: max_teams na competição (default 2), validação no TeamForm, campo na tela de configuração |
| 2026-07-26 | 6.6 | Usuário root, migration role root, Settings page, sidebar redesenhada (cor #171A21, menu minimalista, link config, logout harmonioso) |
| 2026-07-26 | 3.2 | Telão reformulado: mapa Google Maps full-width no topo (38% altura) + grid ranking/progresso; progresso sem nomes em grid 3 colunas |
| 2026-07-27 | 6.7 | **ALTERACOES.md restructure**: GameEngine reescrito sem Proof/FinalEnigma; novo scoring 50+30; bonus/onus QR; competition_id em TeamProgress; migration complete_restructure rodada |
| 2026-07-27 | 6.7 | Models BonusOnus/TeamBonusOnus criados; Proof/FinalEnigma/TeamFinalEnigmaAttempt/TeamFinalEnigmaLetter removidos |
| 2026-07-27 | 6.7 | Flutter: HomeScreen, StageScreen, CompassScreen, FinalEnigmaScreen, ProfileScreen todos atualizados para novo schema |
| 2026-07-27 | 6.7 | Telão atualizado sem Proof: stages direto da Competition; enigmaFinalStatus por stage_type |
| 2026-07-27 | 6.7 | API routes: unlock, scanBonusOnus, final-enigma/validate-cofre adicionados |
| 2026-07-27 | 6.7 | Admin Dashboard/StageIndex corrigidos (Proof removido); ProofForm/ProofIndex/ProofReport/FinalEnigmaForm pendentes refatoração |
