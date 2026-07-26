# Guia de Deploy — HostGator (Hospedagem Compartilhada)

> **Stack:** Laravel 13 + Livewire 4 + MySQL 8.0 + Filas via DB/CRON  
> **Telão:** funciona com `wire:poll.5s` (sem Reverb/WebSocket na maioria dos planos)  
> **Build:** apenas backend — o app Flutter é publicado separadamente (Google Play/App Store)

---

## Pré-requisitos

### No HostGator (via cPanel)

| Recurso | Necessário | Onde verificar |
|---------|-----------|----------------|
| PHP 8.3+ | ✅ | cPanel → "MultiPHP Manager" ou "PHP Selector" |
| MySQL 8.0+ | ✅ | cPanel → "MySQL Databases" |
| Extensões PHP | ✅ | `BCMath`, `Ctype`, `Fileinfo`, `JSON`, `Mbstring`, `OpenSSL`, `PDO`, `pdo_mysql`, `Tokenizer`, `XML`, `cURL`, `GD`, `ZIP` |
| CRON | ✅ | cPanel → "Cron Jobs" |
| FTP/SFTP | ✅ | Para upload inicial |
| Composer | ⚠️ | Via SSH (se disponível) ou instalação manual |

### Verificar PHP

No cPanel, vá em **MultiPHP Manager** e selecione PHP 8.3 (ou 8.4) para o domínio.

Extensões necessárias (ativar no **PHP Selector** ou via suporte):
```
bcmath, ctype, fileinfo, json, mbstring, openssl, pdo, pdo_mysql, tokenizer, xml, curl, gd, zip
```

---

## Passo a passo

### 1. Preparar o projeto localmente

```bash
# 1. Instalar dependências de produção (sem dev)
composer install --no-dev --optimize-autoloader

# 2. Criar .env de produção
cp .env.example .env.production
```

Edite `.env.production` com os dados do HostGator (veja passo 3 para obter esses dados):

```env
APP_NAME="Helena Quest"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seudominio.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=helenaquest_seubanco
DB_USERNAME=helenaquest_seuusuario
DB_PASSWORD=sua_senha_forte

QUEUE_CONNECTION=database

BROADCAST_CONNECTION=null

FILESYSTEM_DISK=public

SESSION_DRIVER=file
CACHE_STORE=file
```

> **Importante:** `BROADCAST_CONNECTION=null` desliga o Reverb. O telão usará `wire:poll.5s` (já implementado).

### 2. Compactar para upload

```bash
# Remover diretórios desnecessários
rm -rf node_modules/ vendor/ .git/ tests/ docs/
# Recriar vendor com só produção
composer install --no-dev --optimize-autoloader

# Compactar (via FTP ou File Manager)
zip -r helena.zip . -x "*.git*" "node_modules/*" ".env"
```

### 3. Criar banco de dados no HostGator

1. cPanel → **MySQL Databases**
2. Criar banco: `helenaseu` (ex: `helena_seubanco`)
3. Criar usuário: `helenaseu` (ex: `helena_seuusuario`)
4. Adicionar usuário ao banco com **TODOS OS PRIVILÉGIOS**
5. Anote: **Database Name**, **Username**, **Password**, **Server** (geralmente `localhost`)

### 4. Fazer upload dos arquivos

**Via FTP** (recomendado para arquivos grandes):

```
Servidor: ftp.seudominio.com
Usuário: seu usuário do cPanel
Senha: sua senha do cPanel
Destino: /public_html/helena-quest/  (ou raiz do domínio)
```

**Via cPanel File Manager**:
1. Enviar `helena.zip` para a pasta desejada
2. Extrair: botão direito → "Extract"

### 5. Configurar o servidor

#### 5.1 Ajustar a pasta public

A pasta `public/` do Laravel deve ser o **document root** do domínio.

**No cPanel → "Domains" → document root do seu domínio:**
- Aponte para: `public_html/helena-quest/public`  
  (ou o caminho onde você extraiu)

#### 5.2 Configurar .env

```bash
# No servidor (File Manager ou SSH):
cp .env.example .env
```

Edite `.env` com os dados do passo 3.

#### 5.3 Gerar chave + migrar

**Via SSH** (se disponível — peça à HostGator ativar):

```bash
cd ~/public_html/helena-quest
php artisan key:generate --force
php artisan storage:link --force
php artisan migrate --force
```

**SEM SSH** — usar um script PHP temporário:

Crie `setup.php` na raiz do projeto:

```php
<?php
$_SERVER['HTTP_HOST'] = 'seudominio.com';
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Gerar key
$app->make('config')->set('app.key', 'base64:' . base64_encode(random_bytes(32)));
file_put_contents('.env', str_replace(
    'APP_KEY=',
    'APP_KEY=base64:' . base64_encode(random_bytes(32)) . "\n#",
    file_get_contents('.env')
));

echo "Key generated\n";
```

Execute no navegador: `https://seudominio.com/setup.php`  
**Delete o arquivo após usar.**

### 6. Configurar CRON para filas

No cPanel → **Cron Jobs**:

```
Intervalo: * * * * *
Comando: /usr/local/bin/php /home/usuario/public_html/helena-quest/artisan schedule:run >> /dev/null 2>&1
```

Isso executa o agendador a cada minuto, que por sua vez processa uma job da fila via `queue:work --once`.

### 7. Permissões

No File Manager do cPanel:

1. Navegue até `helena-quest/`
2. Selecione as pastas: `storage/`, `bootstrap/cache/`
3. Botão direito → **Change Permissions** → `755` (ou `775` se precisar)
4. Marcar "Change permissions for subdirectories"

Se houver erro de permissão, mude para `777` temporariamente, depois volte para `755`.

---

## Verificação pós-deploy

### Checklist

| Item | Como testar |
|------|------------|
| ✅ Home redireciona | `https://seudominio.com` → `/admin` |
| ✅ Login admin | `https://seudominio.com/admin/login` |
| ✅ CRUD competições | Criar competição, prova, etapa, equipe |
| ✅ Telão | `https://seudominio.com/telao/{id}` |
| ✅ API | `curl https://seudominio.com/api/auth/login` |
| ✅ Logs | `https://seudominio.com/admin/game-logs` |
| ✅ Relatórios | `https://seudominio.com/admin/reports/competition` |
| ✅ Filas | Criar equipe, testar login — CRON roda a cada 1min |

### Logs de erro

Em caso de tela branca ou erro 500:

```bash
# Verificar logs do Laravel
storage/logs/laravel.log

# Verificar logs do PHP (no cPanel)
# cPanel → "Errors" → "Error Log"
```

Erros comuns:

| Erro | Causa | Solução |
|------|-------|---------|
| `No application encryption key` | `.env` sem `APP_KEY` | Rodar `php artisan key:generate` |
| `BASE_PATH` não definido | `.env` mal formatado | Verificar aspas e espaços |
| `Connection refused` | DB errado | Verificar `DB_HOST`, `DB_DATABASE`, usuário/senha |
| `Class not found` | Faltou `composer install` | Rodar `composer install --no-dev` |
| `403 Forbidden` | Permissão das pastas | `chmod -R 755 storage/ bootstrap/cache/` |

---

## Atualizações futuras

### Deploy de nova versão

```bash
# Local
composer install --no-dev --optimize-autoloader
zip -r update.zip app/ config/ database/ resources/ routes/ vendor/ \
  composer.json composer.lock -x "*.git*"
```

1. Fazer backup do `.env` e `storage/`
2. Upload + extrair `update.zip`
3. Rodar migrations:

```bash
php artisan migrate --force
```

### Cache (opcional, se o servidor tiver performance)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> **Atenção:** Se usar `config:cache`, o `.env` não será mais lido em runtime. Qualquer alteração precisa rodar `php artisan config:clear` e `config:cache` novamente.

---

## Observações importantes

### HostGator específico

- **SSH**: solicitar ativação pelo suporte (geralmente liberam em até 24h)
- **PHP**: a versão padrão pode ser 7.4 ou 8.1 — mudar no MultiPHP Manager
- **Tempo de execução**: scripts longos podem ser mortos — as filas via `queue:work --once` evitam isso
- **Limite de arquivos**: `inode` pode ser um problema se houver muitos arquivos pequenos — o Laravel gera muitos. Monitore em cPanel → "File Usage"

### Reverb/WebSocket

A maioria dos planos compartilhados **não permite** processos persistentes (Reverb). O telão já funciona com `wire:poll.5s` como fallback.

### Flutter

O app mobile não vai para HostGator — publicar separadamente:
- Android: Google Play Console
- iOS: App Store Connect

O app aponta para a URL `${APP_URL}/api` definida no build.
