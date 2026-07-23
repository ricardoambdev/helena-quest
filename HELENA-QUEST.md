# Helena Quest

> **Instrução Operacional para IA — Gincana Gamificada com GPS, QR Code e Telão ao Vivo**

**Versão:** 2.0  
**Stack:** Laravel 13 + Flutter (Android + iOS) + Livewire 4  
**Documentos de referência:** `reference/01-INTRODUCAO.md` a `reference/03.05-ETAPAS.md`

---

## 1. Stack Técnica

| Camada | Tecnologia | Versão |
|--------|-----------|--------|
| Backend API | Laravel | 13 |
| Linguagem Backend | PHP | 8.4+ |
| Frontend Admin | Livewire 4 + Alpine.js + Flux UI | latest |
| Estilo Admin | Tailwind CSS v4 | 4.x |
| Mobile | Flutter | latest stable (3.x+) |
| Mobile targets | Android + iOS | ambos |
| Banco | MySQL | 8.0+ |
| Cache/Fila | Redis | 7+ |
| Auth API | Laravel Sanctum | — |
| Tempo real | Laravel Reverb (WebSocket) | — |
| TTS | TTS automático nativo (Android TTS / AVSpeechSynthesizer iOS) | — |
| Mapas | google_maps_flutter (cross-platform) | — |
| QR Scanner | mobile_scanner (Android + iOS) | — |
| GPS | geolocator / location (cross-platform) | — |
| Câmera | image_picker (camera only, no gallery) | — |

> **Laravel 13** é a versão atual no ciclo anual de releases (Março/2026).  
> **Flutter** deve compilar para ambos os targets: `flutter build apk` (Android) e `flutter build ios` (iOS).

---

## 2. Arquitetura — 3 Aplicações

```
┌─────────────────────┐     ┌──────────────────┐     ┌──────────────────────┐
│  Painel Admin       │     │  App Mobile       │     │  Painel Público      │
│  (Web / Organizador)│────▶│  (Flutter)        │────▶│  (Telão / Web)       │
│                     │     │  Android + iOS    │     │  URL pública         │
│  Organiza, cria,    │     │  Executa a prova  │     │  Mapa ao vivo        │
│  acompanha          │     │  GPS, QR, câmera  │     │  Progresso, ranking  │
└─────────────────────┘     └──────────────────┘     └──────────────────────┘
         │                        │                          │
         └────────────────────────┼──────────────────────────┘
                                  ▼
                    ┌─────────────────────────┐
                    │  API REST + WebSocket   │
                    │  (Laravel 13 + Reverb)  │
                    └─────────────────────────┘
```

---

## 3. Mecânica do Jogo (Fluxo Principal)

```
Envelope lacrado (usuário, senha, QR download, 1ª dica)
         │
         ▼
   Login no App
         │
         ▼
   Interpreta 1ª dica → desloca até o local
         │
         ▼
   Lê QR Code do local
         │
         ▼
   Sistema valida: QR Code UUID + GPS (raio 30m)
         │
         ▼ (válido)
   Narrativa exibida + TTS automático
         │
         ▼
   Equipe ENVIA FOTO (câmera, obrigatório, sem galeria)
         │
         ▼
   Pergunta liberada (resposta numérica 4-8 dígitos)
         │
         ▼ (correta)
   → Número secreto (4-8 dígitos) exibido
   → Pontuação registrada
   → Dica da próxima etapa liberada
         │
         ▼ (repetir até última etapa)
   ┌────────────────────────────────────────────┐
   │  SEGUNDA FASE — Chave do Enigma Final      │
   │  Números secretos concatenados em ordem    │
   │  inversa da obtenção → chave de 4-8 dígitos│
   │  por etapa, total podendo ser maior.        │
   └────────────────────────────────────────────┘
         │
         ▼ (chave correta)
   Enigma Final na escola:
   QR Codes espalhados → cada um → letra
   Anagrama das letras → palavra final
   3 tentativas máximas → cooldown 2h
   Palavra correta → vitória + pontos finais
```

---

## 4. Regras de Negócio (Consolidadas)

### 4.1 Equipes
- 2 equipes, cada uma com 1 usuário + senha
- 1 dispositivo por equipe (login substitui sessão anterior)
- Status: Ativa | Bloqueada | Inativa | Eliminada

### 4.2 Etapas
- Cada etapa exige: QR Code válido + GPS dentro do raio (30m)
- Foto obrigatória ANTES da pergunta (câmera, sem galeria)
- Resposta numérica obrigatória: 4 a 8 dígitos
- Resposta correta → número secreto + dica da próxima etapa
- Dicas extras podem ser compradas

### 4.3 Pontuação
- Por etapa concluída corretamente
- Menos tentativas = mais pontos (a definir no admin)
- Penalidades configuráveis por etapa
- Tempo influencia desempate

### 4.4 Enigma Final
- Chave = números secretos concatenados (ordem inversa)
- QR Codes na escola → cada um libera uma letra
- Anagrama → palavra final
- 3 tentativas para acertar a palavra
- Após 3 erros → cooldown de 2h (novas 3 tentativas após)

### 4.5 Telão
- Mapa 4×3 com localização em tempo real das equipes
- Barra de progresso por equipe
- Pontuação + ranking
- Fotos enviadas (carrossel)
- Áudios enviados (tocam automaticamente)
- URL pública — qualquer um pode assistir

### 4.6 Áudios
- Equipes enviam áudios durante toda a prova
- Áudios tocam em tempo real no Telão
- Todos os áudios são registrados

### 4.7 Dicas
- Dica principal liberada após resposta correta
- Dicas extras compráveis (configuradas no admin)
- Preço/tipo das dicas cadastrável no sistema

---

## 5. Paleta Visual

| Cor | Hex | Uso |
|-----|-----|-----|
| Laranja principal | `#FF6600` | Botões, destaques, header, primary |
| Preto | `#000000` | Fundo (modo escuro), textos principais |
| Branco | `#FFFFFF` | Fundo (modo claro), texto sobre laranja |
| Laranja escuro | `#CC5200` | Hover states do laranja |
| Cinza claro | `#F5F5F5` | Fundos alternativos, cards |
| Cinza médio | `#CCCCCC` | Bordas, separadores |

**Ícones:** Heroicons (admin) / Material Icons (Flutter) — sem emojis  
**Transições:** `transition-colors duration-200` no admin, AnimatedContainer no Flutter  
**Layout:** Mobile-first, responsivo no admin, adaptável no telão (1920×1080)

---

## 6. Banco de Dados — Entidades Principais

```
competitions
  id, name, description, year, date, start_time, end_time,
  status (planning|published|ongoing|paused|finished|archived),
  created_at, updated_at

teams
  id, competition_id, name, color_hex, username, password_hash,
  status (active|blocked|inactive|eliminated),
  crest_url, created_at, updated_at

proofs
  id, competition_id, name, description, order,
  status (configuring|active|inactive|finished),
  max_score, created_at, updated_at

stages
  id, proof_id, name, description, order,
  latitude, longitude, radius (default 30),
  qr_code_uuid (unique),
  narrative_text, image_url,
  correct_answer (4-8 digits),
  secret_number (4-8 digits),
  next_stage_hint,
  score, penalty, time_limit,
  created_at, updated_at

team_stage_progress
  id, team_id, stage_id,
  status (locked|active|photo_sent|answered_correct|answered_wrong|completed),
  qr_scanned_at, gps_lat, gps_lng,
  photo_url, photo_sent_at,
  attempts_count,
  started_at, completed_at, score_earned,
  created_at

team_progress
  id, team_id, proof_id,
  current_stage_id, total_score, total_time,
  stages_completed, correct_answers, wrong_answers,
  photos_count, audios_count, hints_bought,
  started_at, completed_at

audios
  id, team_id, stage_id, audio_url,
  duration, sent_at

hints
  id, stage_id, hint_text, price, order

final_enigma
  id, competition_id, word, max_attempts (default 3),
  cooldown_minutes (default 120)

final_enigma_qr_codes
  id, final_enigma_id, qr_code_uuid (unique),
  letter, hint_text, order

team_final_enigma_attempts
  id, team_id, final_enigma_id,
  attempt_number, guessed_word, correct (bool),
  created_at, next_available_at

authentication_logs
  id, team_id, ip, device_id, action (login|logout|failed),
  created_at
```

---

## 7. API (Laravel Sanctum)

### 7.1 Autenticação
| Método | Rota | Descrição |
|--------|------|-----------|
| POST | `/api/auth/login` | Login equipe (user+pass) → token |
| POST | `/api/auth/logout` | Invalida token |
| GET | `/api/auth/me` | Dados da equipe logada |
| POST | `/api/auth/check` | Verifica validade do token |

### 7.2 Etapas
| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/api/stages/current` | Etapa atual da equipe |
| POST | `/api/stages/{stage}/validate-qr` | Valida QR Code (UUID + GPS) |
| POST | `/api/stages/{stage}/send-photo` | Envia foto (multipart) |
| POST | `/api/stages/{stage}/answer` | Responde pergunta |
| GET | `/api/stages/{stage}/hints` | Dicas disponíveis |
| POST | `/api/stages/{stage}/buy-hint/{hint}` | Compra dica extra |

### 7.3 Áudios
| Método | Rota | Descrição |
|--------|------|-----------|
| POST | `/api/audios` | Envia áudio (multipart) |
| GET | `/api/audios` | Lista áudios enviados |

### 7.4 Enigma Final
| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/api/final-enigma/status` | Status do enigma final |
| POST | `/api/final-enigma/validate-letter/{qr}` | Valida QR Code de letra |
| POST | `/api/final-enigma/guess` | Tenta descobrir palavra |
| GET | `/api/final-enigma/attempts` | Histórico de tentativas |

### 7.5 Telão (público)
| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/api/public/competition/{id}` | Dados da competição |
| GET | `/api/public/teams-location` | Localizações das equipes |
| GET | `/api/public/ranking` | Ranking atualizado |
| GET | `/api/public/progress` | Progresso de cada equipe |
| GET | `/api/public/photos` | Fotos recentes |
| GET | `/api/public/audios` | Áudios recentes |

### 7.6 Admin (Livewire, não REST)
Toda administração via Livewire 4 com componentes Laravel, sem API REST.

### 7.7 Canal WebSocket (Reverb)
```
echo.private: team.{team_id}    → notificações para a equipe
echo.public: competition.{id}   → telão em tempo real
```

Eventos broadcast:
- `TeamStageUpdated` — progresso
- `TeamLocationUpdated` — GPS
- `TeamPhotoSent` — foto chegou
- `TeamAudioSent` — áudio chegou
- `TeamScoreUpdated` — pontuação alterada
- `CompetitionStatusChanged` — pausa/início/fim

---

## 8. Implementação Backend (Laravel 13)

### 8.1 Setup do Projeto
```bash
composer create-project laravel/laravel helena-quest
cd helena-quest
composer require livewire/livewire laravel/reverb laravel/sanctum
```

### 8.2 Modelos (eloquentes)
Criar migrations + models para todas as entidades da seção 6.

Relacionamentos principais:
- `Competition` hasMany `Proof`, hasMany `Team`, hasMany `FinalEnigma`
- `Proof` belongsTo `Competition`, hasMany `Stage`
- `Stage` belongsTo `Proof`, hasOne `FinalEnigmaQrCode` (opcional para enigma final)
- `Team` belongsTo `Competition`, hasMany `TeamStageProgress`
- `TeamStageProgress` pertence a `Team` + `Stage`

### 8.3 Game Engine (Service Layer)
`app/Services/GameEngine.php` — classe central que orquestra:

- `validateQrAndGps($team, $stage, $qrUuid, $gpsCoords)` → bool
- `processPhoto($team, $stage, $photo)` → libera resposta
- `validateAnswer($team, $stage, $answer)` → resultado
- `completeStage($team, $stage)` → registra progresso, número secreto, próxima dica
- `buyHint($team, $stage, $hint)` → libera dica
- `calculateChaveFinal($team)` → concatena números secretos
- `validateFinalEnigmaGuess($team, $word)` → checa + cooldown

### 8.4 Broadcasting
Configurar Laravel Reverb com eventos Echo.

Broadcast de localização: app mobile envia GPS via POST, backend dispara evento WebSocket para o canal público.

### 8.5 Componentes Livewire 4
- `CompetitionForm` — CRUD competição
- `ProofForm` — CRUD prova com ordenação drag-and-drop
- `StageForm` — CRUD etapa com mapa (lat/lng picker)
- `TeamForm` — CRUD equipe
- `Dashboard` — visão geral com estatísticas
- `RankingLivewire` — ranking atualizado em tempo real
- `TeamMonitor` — acompanhamento individual da equipe

### 8.6 Filas (Queue)
- Processamento de fotos (redimensionamento, thumb)
- Processamento de áudios (transcodificação)
- Notificações push

---

## 9. Implementação Flutter (Android + iOS)

### 9.1 Setup
```bash
flutter create --org com.helenaquest --project-name helena_quest_app .
flutter pub add google_maps_flutter mobile_scanner geolocator image_picker audioplayers http provider dart:convert
```

### 9.2 Permissões

**Android** (`AndroidManifest.xml`):
```xml
<uses-permission android:name="android.permission.CAMERA" />
<uses-permission android:name="android.permission.ACCESS_FINE_LOCATION" />
<uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION" />
<uses-permission android:name="android.permission.RECORD_AUDIO" />
<uses-permission android:name="android.permission.INTERNET" />
```

**iOS** (`Info.plist`):
```xml
<key>NSCameraUsageDescription</key>
<string>Para fotografar sua equipe no local da prova</string>
<key>NSLocationWhenInUseUsageDescription</key>
<string>Para validar sua presença nos locais da gincana</string>
<key>NSMicrophoneUsageDescription</key>
<string>Para enviar áudios durante a competição</string>
<key>NSPhotoLibraryUsageDescription</key>
<string>usar apenas camera, não a galeria</string>
```

### 9.3 Telas do App

| Tela | Rota | Descrição |
|------|------|-----------|
| Login | `/login` | Usuário + senha, mantém sessão |
| Home | `/home` | Status da equipe, progresso |
| Scanner | `/scanner` | Câmera fullscreen, lê QR Code |
| Stage | `/stage` | Narrativa + imagem + botão responder |
| Photo | `/photo` | Câmera para selfie/foto do local |
| Answer | `/answer` | Input numérico (4-8 dígitos) |
| Result | `/result` | Acertou/errou, número secreto, dica |
| Audio | `/audio` | Gravação e envio de áudio |
| Map | `/map` | Mapa mostrando locais (opcional) |
| FinalEnigma | `/final-enigma` | Tela do enigma final |
| Profile | `/profile` | Dados da equipe, ranking |

### 9.4 Estrutura de Widgets
```
lib/
├── main.dart
├── app.dart
├── config/
│   ├── theme.dart          # Paleta FF6600, preto, branco
│   ├── routes.dart
│   └── constants.dart
├── services/
│   ├── api_service.dart     # HTTP client com token
│   ├── auth_service.dart
│   ├── location_service.dart
│   ├── qr_service.dart
│   ├── tts_service.dart     # TTS nativo (Android/iOS)
│   ├── audio_service.dart
│   └── websocket_service.dart
├── providers/
│   ├── auth_provider.dart
│   ├── stage_provider.dart
│   ├── team_provider.dart
│   └── audio_provider.dart
├── screens/
│   ├── login_screen.dart
│   ├── home_screen.dart
│   ├── scanner_screen.dart
│   ├── stage_screen.dart
│   ├── photo_screen.dart
│   ├── answer_screen.dart
│   ├── result_screen.dart
│   ├── audio_screen.dart
│   ├── map_screen.dart
│   ├── final_enigma_screen.dart
│   └── profile_screen.dart
└── widgets/
    ├── numeric_keyboard.dart
    ├── progress_bar.dart
    ├── team_indicator.dart
    └── countdown_timer.dart
```

### 9.5 TTS (Text-to-Speech) — Nativo
Não usar plugin de TTS de terceiros. Usar APIs nativas:

**Android:** `TextToSpeech` (Android SDK nativo via MethodChannel ou `flutter_tts` com motor padrão)  
**iOS:** `AVSpeechSynthesizer` (nativo)

A narrativa deve ser reproduzida automaticamente assim que a etapa for carregada, SEM botão de play inicial (mas com opção de replay/stop).

### 9.6 Build Cross-platform
```bash
flutter build apk --release                      # Android
flutter build ios --release --no-codesign        # iOS (precisa Xcode + Apple Developer)
flutter build ipa --release                      # iOS (distribution)
```

---

## 10. Implementação do Telão (Painel Público)

O Telão pode ser implementado de duas formas:

### Opção A: Livewire + Alpine.js (recomendada)
Mesmo Laravel, sem app separado:
- Página pública `/telao/{competition}`
- Alpine.js escuta canal Echo público (`competition.{id}`)
- Atualiza mapa (Leaflet ou Google Maps JS), barra de progresso, ranking, fotos e áudios

### Opção B: App separado (Vue/React com Inertia)
- Usar Inertia.js + Vue 3 ou React
- Consome API pública + Echo WebSocket

### Layout do Telão (1920×1080)
```
┌──────────────────────────────────────────────────────────┐
│  ┌─────────────────────────┐  ┌────────────────────────┐ │
│  │      MAPA 4×3           │  │   EQUIPE 1             │ │
│  │  (posições em tempo     │  │   ████████░░ 80%       │ │
│  │   real das equipes)     │  │   Pontos: 450          │ │
│  │                         │  │   ──────────────────   │ │
│  │                         │  │   EQUIPE 2             │ │
│  │                         │  │   ██████░░░░ 60%       │ │
│  │                         │  │   Pontos: 320          │ │
│  └─────────────────────────┘  └────────────────────────┘ │
│  ┌──────────────────────────────────────────────────────┐ │
│  │  📸 FOTOS (carrossel)     🎤 ÁUDIOS (player auto)    │ │
│  └──────────────────────────────────────────────────────┘ │
└──────────────────────────────────────────────────────────┘
```

Requisitos do Telão:
- Fonte grande (legível a distância)
- Fundo escuro (preto) com laranja nos destaques
- Atualização via WebSocket (sem refresh manual)
- Áudios tocam automaticamente em fila
- Fotos em carrossel com transição suave
- Mapa atualiza posição a cada ~5s

---

## 11. Fases de Implementação

### Fase 1 — Fundação (Backend + Admin)
- [ ] Setup Laravel 13 + Sanctum + Reverb
- [ ] Migrations + Models (todas as entidades)
- [ ] GameEngine service (validação QR/GPS, resposta, progresso)
- [ ] Componentes Livewire: Competição, Provas, Etapas, Equipes
- [ ] Dashboard Admin (visão geral)

### Fase 2 — App Mobile (Flutter) Core
- [ ] Login + token persistente
- [ ] Leitor QR Code (mobile_scanner)
- [ ] Validação GPS (geolocator)
- [ ] Tela de Stage (narrativa + TTS automático)
- [ ] Câmera selfie (image_picker, camera only)
- [ ] Teclado numérico + resposta 4-8 dígitos
- [ ] Resultado + número secreto + dica

### Fase 3 — Telão + Tempo Real
- [ ] Canal Echo público (Reverb)
- [ ] Eventos: localização, progresso, foto, áudio
- [ ] Página pública do telão
- [ ] Mapa + barra de progresso + ranking + fotos + áudios

### Fase 4 — Enigma Final
- [ ] Geração de chave (conCATenação reversa dos números)
- [ ] QR Codes de letras (escola)
- [ ] Anagrama + tentativas + cooldown
- [ ] Validação palavra final + pontos

### Fase 5 — Compras de Dicas + Áudios
- [ ] Dicas extras compráveis no app
- [ ] Gravação e envio de áudio
- [ ] Player de áudio no telão
- [ ] Histórico de compras

### Fase 6 — Polimento
- [ ] Logs de auditoria
- [ ] Exportação/relatórios
- [ ] Testes E2E
- [ ] Deploy (Laravel Forge / Vapor + Google Play / App Store)

---

## 12. Design System — Diretrizes Visuais

### Princípios
- **Energia esportiva:** laranja vibrante + preto dramático
- **Clareza máxima:** legível a distância (telão) e em movimento (app mobile)
- **Consistência:** mesma paleta e tipografia nos 3 aplicativos

### Tipografia
- **Títulos:** Inter (bold/extra-bold) — sans-serif moderna e legível
- **Corpo:** Inter (regular) ou Nunito (caso queira algo mais arredondado e juvenil)
- **Código/números:** JetBrains Mono (teclado numérico do app)
- Monospace para os dígitos da resposta (facilita leitura)

### Estados de Hover
- Botões laranja: hover escurece para `#CC5200` com `transition-colors duration-200`
- Cards clicáveis: `cursor-pointer` + leve elevação no hover
- Botão desabilitado: opacidade 0.5, sem hover

### Dark Mode (Telão)
- Fundo: `#000000`
- Cards: `#1A1A1A` com borda `#333333`
- Texto primário: `#FFFFFF`
- Texto secundário: `#AAAAAA`
- Destaque: `#FF6600` (laranja)

### Light Mode (App Admin)
- Fundo: `#FFFFFF`
- Cards: `#F5F5F5` com borda `#E0E0E0`
- Texto primário: `#000000` ou `#1A1A1A`
- Texto secundário: `#666666`
- Destaque: `#FF6600`

### Acessibilidade
- Contraste mínimo 4.5:1 em todos os textos
- `prefers-reduced-motion` respeitado (desabilitar animações)
- Foco visível em todos os elementos interativos
- Não usar cor como único indicador de estado

### Anti-patterns (evitar)
- Emojis como ícones (usar SVGs/Material Icons)
- Hover com scale (causa layout shift)
- Texto claro sobre laranja (#FF6600 + branco é OK, mas testar)
- Botões sem feedback visual

---

## 13. Documentos de Referência

Os seguintes arquivos em `reference/` contêm a especificação detalhada de requisitos:

| Arquivo | Conteúdo |
|---------|----------|
| `01-INTRODUCAO.md` | Visão geral, objetivos, escopo, definições |
| `02-VISAO-GERAL.md` | Estrutura hierárquica, fluxo geral, fases |
| `03-REQUISITOS-FUNCIONAIS.md` | Todos os RFs (RF-001 a RF-080) consolidados |
| `03.01-AUTENTICACAO.md` | RFs detalhados de autenticação (RF-AUT-001 a 034) |
| `03.02-EQUIPES.md` | RFs detalhados de equipes (RF-EQP-001 a 050) |
| `03.03-COMPETICOES.md` | RFs detalhados de competições (RF-CMP-001 a 050) |
| `03.04-PROVAS.md` | RFs detalhados de provas (RF-PRV-001 a 050) |
| `03.05-ETAPAS.md` | RFs detalhados de etapas (RF-ETP-001 a 066) |
| `Gincana.txt` | Especificação original em texto livre |

> **Nota:** Os documentos de referência foram atualizados para Laravel 13 + Livewire 4. Usar sempre a stack definida neste documento.

---

## 14. Commands Úteis

```bash
# Backend
composer create-project laravel/laravel helena-quest "^13.0"
php artisan make:model Competition -m
php artisan make:livewire Dashboard
php artisan reverb:install

# Flutter
flutter create --org com.helenaquest --platforms android,ios helena_quest_app
flutter pub add google_maps_flutter mobile_scanner geolocator image_picker
flutter build apk --release
flutter build ios --release

# Flutter (iOS build requirements)
# Precisa de macOS + Xcode + CocoaPods
cd ios && pod install && cd ..
flutter build ios --release --no-codesign  # development
flutter build ipa --release                # distribution
```
