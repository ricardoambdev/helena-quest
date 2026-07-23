# AGENTS.md

> **Diretivas obrigatórias para agentes de IA trabalhando neste projeto.**

## Contexto do Projeto

**Nome:** Helena Quest  
**Tipo:** Gincana gamificada com GPS, QR Code e Telão ao Vivo  
**Stack:** Laravel 13 + Livewire 4 + Flutter (Android + iOS) + Laravel Reverb (WebSocket)  
**Banco:** MySQL 8.0+  
**Hospedagem:** Compartilhada (compatível com MySQL padrão)  
**Documentação:** `HELENA-QUEST.md` (mestre) + `reference/` (RFs) + `CHECKLIST.md` (progresso)

---

## Diretivas Obrigatórias

### 1. Atualização Contínua do Checklist

**SEMPRE** que uma etapa for concluída, marcada como pendente ou alterada de status:

- Abrir `CHECKLIST.md`
- Atualizar o check correspondente: `[ ]` → `[x]` (concluído), `[~]` (em andamento), `[-]` (cancelado)
- Atualizar a tabela **"Histórico de Atualizações"** ao final do arquivo com:
  - Data (YYYY-MM-DD)
  - Fase/Seção alterada
  - Descrição do que mudou

### 2. Logs de Programação em `docs/logs/`

**SEMPRE** antes e depois de qualquer alteração significativa de código:

- Criar/atualizar um arquivo de log em `docs/logs/YYYY-MM-DD.md` (um por dia)
- Formato de entrada:

```
## HH:MM — [TIPO] Descrição curta

**Fase:** X.Y
**Arquivos alterados:** `arquivo1.php`, `arquivo2.dart`
**Ação:** Criado / Modificado / Removido
**Detalhes:** Descrição técnica da mudança
**Validação:** Comando executado + resultado
```

Tipos comuns:
- `[FEAT]` Nova funcionalidade
- `[FIX]` Correção de bug
- `[CHORE]` Tarefa técnica/refatoração
- `[CONFIG]` Mudança de configuração
- `[DOC]` Mudança de documentação

### 3. Histórico de Movimentação de Arquivos

Sempre que mover/renomear/criar/remoção de arquivos/pastas:

- Documentar no log do dia: caminho origem → caminho destino
- Se for parte estrutural, atualizar também `docs/STRUCTURE.md` (criar quando necessário)

### 4. Comandos de Validação

Antes de marcar uma etapa como concluída, executar:
- Backend: `php artisan migrate:status` / `php artisan route:list` / `php artisan test`
- Flutter: `flutter analyze` / `flutter test` (quando o SDK estiver disponível)
- Servidor: testar endpoints com `curl` ou similar

### 5. Git e Commits

- **Não** fazer `git commit` / `git push` sem autorização explícita do usuário
- Mensagens de commit em português, formato: `fase-X.Y: descrição curta`
- **Sempre** atualizar CHECKLIST + log ANTES de sugerir commit

### 6. Não Fazer Sem Pedir

- Não instalar dependências adicionais sem confirmar
- Não rodar migrations destrutivas sem confirmar
- Não publicar em produção sem confirmar
- Não usar comandos `rm` irreversíveis sem confirmar

---

## Convenções de Código

### PHP / Laravel
- PSR-12 + Laravel Pint
- Em PT-BR para nomes de domínio (`Prova`, `Equipe`, `Etapa`), mas em inglês para termos técnicos (`Controller`, `Service`)
- Indentação: 4 espaços
- Tipagem: declare sempre que possível (`declare(strict_types=1);`)

### Flutter / Dart
- `flutter_lints` ativado
- Material Design 3
- Tema centralizado em `lib/config/theme.dart`
- Paleta do projeto (`#FF6600`, `#000000`, `#FFFFFF`) sempre via constantes

---

## Estrutura de Pastas

```
gincana/                       ← raiz
├── app/                       ← Laravel (Models, Controllers, Livewire)
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/                 ← Views + assets
├── routes/
├── storage/
├── tests/
├── vendor/                    ← ignorado pelo git
├── docs/
│   ├── logs/                  ← Logs de programação (um .md por dia)
│   ├── STRUCTURE.md           ← Estrutura do projeto
│   └── ADR/                   ← Architectural Decision Records
├── reference/                 ← Documentação de requisitos
├── HELENA-QUEST.md            ← Documentação mestre
├── CHECKLIST.md               ← Progresso da implementação (atualizar sempre)
├── AGENTS.md                  ← Este arquivo (diretivas)
├── composer.json
└── .env / .env.example
```

---

## Status Atual

- **Fase 0.1:** ✅ Concluída (Laravel 13 + MySQL + Livewire + Reverb + Sanctum)
- **Fase 0.2:** ⏳ Aguardando instalação manual do Flutter SDK

---

**Última atualização:** 2026-07-23
