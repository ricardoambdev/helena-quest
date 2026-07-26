# 🏆 Helena Quest

**Gincana gamificada com GPS, QR Code e Telão ao Vivo**

Helena Quest é uma plataforma completa para organizar gincanas ao ar livre. Equipes percorrem pontos geográficos, escaneiam QR Codes, tiram fotos, respondem enigmas numéricos e coletam números secretos para formar a chave do enigma final — tudo em tempo real com transmissão ao vivo para um telão.

```
┌─────────────────────┐     ┌──────────────────┐     ┌──────────────────────┐
│  Painel Admin       │     │  App Mobile       │     │  Painel Público      │
│  (Web / Organizador)│────▶│  (Flutter)        │────▶│  (Telão / Web)       │
│                     │     │  Android + iOS    │     │  URL pública         │
│  Cria, gerencia,    │     │  Executa a prova  │     │  Mapa ao vivo        │
│  acompanha em tempo │     │  GPS, QR, câmera  │     │  Progresso, ranking  │
│  real               │     │  TTS automático   │     │  Fotos, áudios       │
└─────────────────────┘     └──────────────────┘     └──────────────────────┘
         │                        │                          │
         └────────────────────────┼──────────────────────────┘
                                  ▼
                    ┌─────────────────────────┐
                    │  API REST + WebSocket   │
                    │  (Laravel 13 + Reverb)  │
                    └─────────────────────────┘
                                  │
                                  ▼
                    ┌─────────────────────────┐
                    │       MySQL 8.0+        │
                    └─────────────────────────┘
```

---

## Stack Técnica

| Camada | Tecnologia | Versão |
|--------|-----------|--------|
| Backend API | Laravel | 13.21+ |
| Linguagem Backend | PHP | 8.3+ |
| Frontend Admin | Livewire 4 + Alpine.js | 4.3+ |
| Estilo Admin | Tailwind CSS v4 | CDN |
| Mobile | Flutter | 3.44+ |
| Mobile targets | Android + iOS | ambos |
| Banco | MySQL | 8.0+ |
| Cache/Fila | Database driver | shared hosting |
| Auth API (mobile) | Laravel Sanctum | tokens |
| Auth Admin | Session + users table | guard web |
| Tempo real | Laravel Reverb | WebSocket |
| TTS | flutter_tts | nativo |
| Mapas | google_maps_flutter | — |
| QR Scanner | mobile_scanner | — |
| GPS | geolocator | — |
| Câmera | image_picker | camera |

---

## Design System

### Paleta de Cores

| Token | Hex | Uso |
|-------|-----|-----|
| `ignite` | `#FF6600` | Principal (botões, acentos) |
| `ember` | `#CC5200` | Hover/pressed |
| `flame` | `#FF8533` | Variação clara |
| `ink` | `#0D0D0F` | Fundo escuro (telão), texto |
| `paper` | `#FAF8F5` | Fundo claro (admin, app) |
| `chalk` | `#7A7468` | Texto secundário |
| `rule` | `#E0DCD3` | Bordas, divisores |

### Tipografia

- **Inter** (800/700) — Display, títulos grandes
- **Nunito** (400/600/700) — Body, textos corridos
- **JetBrains Mono** (400/500) — Botões, números, scores, labels técnicos

### Modos

- **Light** — Admin (painel web) e App mobile
- **Dark** — Telão (projeção 1920×1080)

---

## Estrutura do Projeto

```
gincana/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # AuthController, TelaoController
│   │   │   └── Api/            # AuthController, StageController, AudioController, FinalEnigmaController, PublicApiController
│   ├── Livewire/Admin/         # 13 componentes Livewire
│   ├── Models/                 # 14 Models Eloquent
│   ├── Services/
│   │   ├── GameEngine.php      # Motor principal do jogo
│   │   └── DistanceCalculator.php  # Fórmula de Haversine
│   ├── Events/                 # 6 eventos broadcasting
│   └── Jobs/                   # ProcessPhotoThumbs, SendNotification
├── bootstrap/
├── config/
├── database/
│   ├── migrations/             # 18 migrations (5 Laravel + 13 domínio)
│   └── seeders/                # DatabaseSeeder, CompetitionSeeder
├── public/
│   └── storage/ → storage/app/public
├── resources/views/
│   ├── admin/                  # Login, telão
│   ├── layouts/                # admin.blade.php
│   └── livewire/admin/         # 13 views de componentes
├── routes/
│   ├── api.php                 # 22 rotas REST
│   ├── web.php                 # Rotas admin + telão
│   └── channels.php            # Canais Reverb
├── lib/                        # App Flutter
│   ├── config/                 # theme, constants, routes
│   ├── services/               # 7 services (api, auth, location, qr, tts, audio, websocket)
│   ├── providers/              # 4 providers (auth, stage, team, audio)
│   ├── screens/                # 11 telas
│   └── widgets/                # 4 widgets compartilhados
├── android/                    # Configuração Android (Flutter)
├── ios/                        # Configuração iOS (Flutter)
├── docs/
│   ├── logs/                   # Logs de desenvolvimento
│   └── ADR/                    # Architectural Decision Records
├── reference/                  # Documentos de requisitos
├── HELENA-QUEST.md             # Documento mestre
├── CHECKLIST.md                # Progresso de implementação
├── AGENTS.md                   # Diretivas para agentes IA
├── composer.json
├── pubspec.yaml
└── .env                        # Configuração local
```

---

## Rodando Localmente

### Pré-requisitos

- PHP 8.3+ com extensões: `pdo_mysql`, `mbstring`, `xml`, `bcmath`, `curl`, `gd`
- Composer 2.x
- MySQL 8.0+
- Node.js 20+ (opcional — Tailwind via CDN em dev)
- Flutter SDK 3.44+ (para o app mobile)
- Java 17 (para build Android)

### 1. Backend (Laravel)

```bash
# Clonar e instalar dependências
git clone https://github.com/ricardoambdev/helena-quest.git
cd helena-quest
composer install

# Configurar ambiente
cp .env.example .env
php artisan key:generate

# Configurar .env com suas credenciais MySQL:
# DB_DATABASE=helena_quest
# DB_USERNAME=root
# DB_PASSWORD=seu_password

# Rodar migrations e seeders
php artisan migrate
php artisan db:seed

# Criar link de storage
php artisan storage:link

# Iniciar servidor de desenvolvimento
php artisan serve
```

Acessar: `http://localhost:8000/admin/login`
- **Email:** `admin@helenaquest.com.br`
- **Senha:** `admin123`

### 2. Filas (processamento em background)

```bash
php artisan queue:work --queue=default
```

### 3. WebSocket (Laravel Reverb)

```bash
php artisan reverb:start
```

### 4. App Flutter

```bash
# Configurar URL da API em lib/config/constants.dart
# Altere baseUrl para seu servidor local

# Verificar ambiente
flutter doctor

# Rodar em modo debug
flutter run

# Build Android APK
flutter build apk --debug

# Build Android release (requer keystore configurado)
flutter build apk --release

# Build iOS (requer macOS + Xcode)
flutter build ios
```

### 5. Dados de Teste

O seeder cria automaticamente:

- **Admin:** admin@helenaquest.com.br / admin123
- **Competição:** "Gincana Helena Quest 2026" (status: planning)
- **2 Provas:** Caça ao Tesouro Urbano (5 etapas) + Desafio da Natureza (3 etapas)
- **8 Etapas:** com coordenadas GPS, QR UUIDs, respostas e números secretos
- **3 Equipes:** Alpha (alpha/alpha123), Beta (beta/beta123), Gamma (gamma/gamma123)
- **24 Dicas:** 3 dicas por etapa
- **Enigma Final:** Palavra "HELENA" com 6 QR Codes de letras

---

## API REST (22 rotas)

### Autenticação (Sanctum)

Todas as rotas `POST` são protegidas por token Bearer, exceto login.

```
POST   /api/auth/login               Login → {token, team}
POST   /api/auth/logout              Invalida token
GET    /api/auth/me                  Dados da equipe + progresso
POST   /api/auth/check               Valida token

GET    /api/stages/current           Etapa atual da equipe
POST   /api/stages/{id}/validate-qr  Valida QR + GPS (opcional)
POST   /api/stages/{id}/send-photo   Envia foto (multipart)
POST   /api/stages/{id}/answer       Responde pergunta
GET    /api/stages/{id}/hints        Dicas disponíveis
POST   /api/stages/{id}/buy-hint/{hint}  Compra dica

POST   /api/audios                   Envia áudio (multipart)
GET    /api/audios                   Lista áudios da equipe

GET    /api/final-enigma/status      Status do enigma final
POST   /api/final-enigma/validate-letter/{qr}  Escaneia QR de letra
POST   /api/final-enigma/guess       Tenta palavra final
GET    /api/final-enigma/attempts    Histórico de tentativas

GET    /api/public/competition/{id}           Dados públicos
GET    /api/public/teams-location/{compId}    Localizações
GET    /api/public/ranking/{compId}           Ranking
GET    /api/public/progress/{compId}          Progresso
GET    /api/public/photos/{compId}            Fotos recentes (40)
GET    /api/public/audios/{compId}            Áudios recentes (20)
```

### Exemplo de Login

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"alpha","password":"alpha123"}'

# Resposta:
# {"token":"1|abc123...","team":{"id":1,"name":"Equipe Alpha",...}}
```

---

## Broadcast (WebSocket)

### Canais

| Canal | Tipo | Descrição |
|-------|------|-----------|
| `team.{teamId}` | Private | Eventos da equipe (progresso, score, localização) |
| `competition.{competitionId}` | Public | Eventos públicos para o telão |

### Eventos

| Evento | Canal | Dados |
|--------|-------|-------|
| `stage.updated` | team + competition | Progresso da etapa |
| `location.updated` | team + competition | Coordenadas GPS |
| `photo.sent` | team + competition | Foto enviada |
| `audio.sent` | team + competition | Áudio enviado |
| `score.updated` | team + competition | Pontuação alterada |
| `competition.status` | competition | Status alterado |

---

## Deploy

### 1. Servidor Compartilhado (Hospedagem Standard)

Requisitos mínimos:
- PHP 8.3+
- MySQL 8.0+
- Apache/Nginx com mod_rewrite
- FTP ou cPanel

**Passo a passo:**

```bash
# 1. Build do backend
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force

# 2. Ajustes para shared hosting
# - Queue: QUEUE_CONNECTION=database (já configurado)
# - Cache: CACHE_STORE=database (já configurado)
# - Session: SESSION_DRIVER=database
# - Broadcast: configurar .env com credenciais Reverb
# - Cron: Adicionar tarefa para php artisan schedule:run

# 3. Upload via FTP
# Envie todo o conteúdo EXCETO:
# - .env (configurar manualmente no servidor)
# - vendor/ (rodar composer install no servidor)
# - node_modules/ (não usado — Tailwind via CDN)
# - storage/ (criar link manualmente no servidor)

# 4. Pós-upload
php artisan storage:link
php artisan queue:table  # Se não existir
```

**Configuração do .env para produção:**

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seudominio.com

DB_HOST=localhost
DB_DATABASE=helena_quest
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha

QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database

BROADCAST_CONNECTION=reverb
REVERB_APP_ID=seu-app-id
REVERB_APP_KEY=sua-key
REVERB_APP_SECRET=seu-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

### 2. VPS (Digital Ocean, Linode, Hetzner)

```bash
# Usando Laravel Forge (recomendado)
# 1. Conecte seu servidor ao Forge
# 2. Crie um site apontando para /public
# 3. Configure o banco MySQL
# 4. Forge gerencia: queue workers, cron, SSL, deploy

# Manualmente:
sudo apt update && sudo apt install php8.3 php8.3-mysql php8.3-xml php8.3-bcmath \
  php8.3-curl php8.3-gd composer mysql-server nginx
composer install --no-dev --optimize-autoloader
# Configure nginx apontando para /public
# Configure MySQL + .env
php artisan migrate --force
php artisan storage:link

# Supervisor para queue worker
# /etc/supervisor/conf.d/helena-queue.conf:
# [program:helena-queue]
# process_name=%(program_name)s_%(process_num)02d
# command=php /path/to/artisan queue:work --sleep=3 --tries=3 --max-time=3600
# autostart=true
# autorestart=true
# numprocs=2

# Supervisor para Reverb
# /etc/supervisor/conf.d/helena-reverb.conf:
# [program:helena-reverb]
# command=php /path/to/artisan reverb:start
# autostart=true
# autorestart=true
```

### 3. Docker

```dockerfile
# Dockerfile
FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    nginx supervisor \
    && docker-php-ext-install pdo_mysql bcmath

COPY . /var/www
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install --no-dev --optimize-autoloader && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

COPY docker/nginx.conf /etc/nginx/sites-enabled/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/

CMD ["supervisord", "-n"]
```

### 4. Cloudflare Tunnel (alternativa sem abrir portas)

Requer: Cloudflare Tunnel (cloudflared) instalado no servidor.

```bash
cloudflared tunnel create helena-quest
cloudflared tunnel route dns helena-quest helena.seudominio.com

# Configurar .env com APP_URL=https://helena.seudominio.com
```

### 5. Build e Distribuição do App Mobile

**Android (APK):**

```bash
# Gerar keystore (uma vez)
keytool -genkey -v -keystore android/app/upload-keystore.jks \
  -alias upload -keyalg RSA -keysize 2048 -validity 10000

# Criar android/key.properties:
# storePassword=sua_senha
# keyPassword=sua_senha
# keyAlias=upload
# storeFile=upload-keystore.jks

# Build release
flutter build apk --release
# APK em: build/app/outputs/flutter-apk/app-release.apk

# Build app bundle (Google Play)
flutter build appbundle --release
# AAB em: build/app/outputs/bundle/release/app-release.aab
```

**iOS (IPA) — requer macOS + Xcode:**

```bash
flutter build ios --release
# Abrir ios/Runner.xcworkspace no Xcode
# Product → Archive → Distribute App
```

### 6. Publicação nas Lojas

**Google Play Store:**
1. Crie conta de desenvolvedor Google Play ($25 única vez)
2. Acesse Google Play Console → Criar novo app
3. Faça upload do AAB em `build/app/outputs/bundle/release/app-release.aab`
4. Preencha ficha da loja (descrição, screenshots, categorias)
5. Envie para revisão

**Apple App Store:**
1. Crie conta de desenvolvedor Apple ($99/ano)
2. Via Xcode ou App Store Connect:
   - Product → Archive
   - Distribute App → App Store Connect
3. Preencha ficha no App Store Connect
4. Envie para revisão

---

## Administração

### Painel Admin

Acessar: `http://localhost:8000/admin`

| Rota | Descrição |
|------|-----------|
| `/admin` | Dashboard com estatísticas |
| `/admin/competitions` | CRUD competições |
| `/admin/proofs` | CRUD provas |
| `/admin/stages` | CRUD etapas |
| `/admin/teams` | CRUD equipes |
| `/admin/final-enigma` | Gerenciar enigma final |
| `/admin/ranking` | Ranking em tempo real |
| `/admin/monitor` | Monitoramento de equipes |
| `/admin/logs` | Auditoria de ações |

### Telão Público

Acessar: `http://localhost:8000/telao`

Layout otimizado para projeção 1920×1080 com fundo escuro, ranking ao vivo, progresso por etapa, barra de progresso e atualização em tempo real via WebSocket.

---

## Motor do Jogo (GameEngine)

O `GameEngine` em `app/Services/GameEngine.php` implementa as regras de negócio:

| Método | Função |
|--------|--------|
| `validateQrAndGps()` | Valida QR Code + distância Haversine |
| `processPhoto()` | Registra foto enviada |
| `validateAnswer()` | Valida resposta numérica (4-8 dígitos) |
| `completeStage()` | Calcula score, número secreto, avança etapa |
| `buyHint()` | Libera dica paga |
| `calculateChaveFinal()` | Concatena números secretos (ordem reversa) |
| `validateFinalEnigmaGuess()` | Valida palavra final com cooldown |

### Regras de Pontuação

```
Score = max(0, score_base - (attempts - 1) * penalty)
```

- Acerto de primeira: score máximo
- Penalidade por tentativa extra: configurável por etapa
- Tempo não influencia pontuação (apenas registro)

### Enigma Final

1. Equipes coletam números secretos ao completar etapas
2. A chave final é a concatenação reversa de todos os números
3. QR Codes escondidos liberam letras da palavra final
4. Palpite com cooldown de 2h após esgotar tentativas
5. Acerto = gincana finalizada para a equipe

---

## Comandos Úteis

```bash
# Backend
php artisan migrate               # Rodar migrations
php artisan migrate:fresh         # Resetar banco (cuidado!)
php artisan db:seed               # Popular dados de teste
php artisan route:list            # Listar rotas
php artisan tinker                # REPL interativo
php artisan cache:clear           # Limpar cache
php artisan config:clear          # Limpar config
php artisan queue:work            # Processar filas
php artisan reverb:start          # Iniciar WebSocket
php artisan storage:link          # Link public/storage
php artisan test                  # Rodar testes
php artisan make:livewire Nome    # Criar componente Livewire

# Flutter
flutter analyze                   # Análise estática
flutter run                       # Rodar em dispositivo
flutter build apk                 # Build Android
flutter build ios                 # Build iOS
flutter clean                     # Limpar build
flutter pub get                   # Atualizar deps
flutter pub outdated              # Verificar versões

# Manutenção
composer update                   # Atualizar PHP deps
composer outdated                 # Verificar versões PHP
```

---

## Licença

MIT License — ver `LICENSE` para detalhes.

---

## Links

- **Repositório:** [github.com/ricardoambdev/helena-quest](https://github.com/ricardoambdev/helena-quest)
- **Documentação mestra:** `HELENA-QUEST.md`
- **Progresso:** `CHECKLIST.md`
- **Diretivas IA:** `AGENTS.md`
- **Requisitos:** `reference/`
