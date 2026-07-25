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

### 7. Skill OBRIGATÓRIA de Design de UI — `frontend-design`

**SEMPRE** que for criar ou refatorar qualquer **tela**, **componente visual**, **página** ou **layout** das três camadas do sistema (Painel Admin Livewire, App Mobile Flutter, Painel Público/Telão), **invocar a skill `frontend-design`** **antes** de escrever qualquer código.

Cobertura:
- **`app/Livewire/...`** → componentes Livewire/Blade (admin, telão Alpine.js)
- **`resources/views/...`** → views Blade (admin, telão)
- **`lib/screens/...`** → telas do Flutter (mobile)
- **`lib/widgets/...`** → widgets visuais Flutter
- Quaisquer composables/views para protótipos ou ajustes visuais

#### Procedimento obrigatório

1. **Carregar a skill** via ferramenta `skill` com `name: "frontend-design"`.
2. **Aplicar o processo em duas passadas** definido na skill:
   - *Passada 1*: brainstorm curto (paleta nomeada com 4–6 hex, 2+ papéis tipográficos, layout conceitual, elemento assinatura).
   - *Passada 2*: revisar o plano contra o brief do projeto e revisar os AI-defaults do meio (creme/terracota; preto/acid-green; broadsheet) para garantir que **nenhuma escolha vire templated**.
3. **Respeitar o Design System já definido em `HELENA-QUEST.md` § 5 e § 12**:
   - Paleta base: `#FF6600` (laranja principal), `#000000` (preto/fundo escuro), `#FFFFFF` (branco/claro), `#CC5200` (laranja escuro hover), `#F5F5F5` (cinza claro), `#CCCCCC` (cinza médio).
   - Tipografia: Inter (bold/extra-bold display, regular body) + Nunito (variação juvenil, opcional) + JetBrains Mono (números).
   - Modos: Light (admin/app) e Dark (telão).
   - Acessibilidade: contraste mínimo 4.5:1, `prefers-reduced-motion` respeitado, foco visível, cor nunca como único indicador de estado.
   - Anti-patterns: **nunca** usar emojis como ícones (Material Icons/Heroicons), **nunca** hover com scale, sempre feedback visual em botões.
4. **Derivar cada decisão visual** (cor, fonte, espaçamento, raio, sombra) das escolhas do plano da skill — **nunca** cair em defaults genéricos sem justificativa explícita.
5. **Implementar Mobile-First** para admin e app; **1920×1080** como referência fixa para o telão.
6. **Documentar a escolha final** no log do dia (`docs/logs/YYYY-MM-DD.md`), na seção "Decisões de UI", resumindo os tokens eleitos (cor, tipo, layout, assinatura) e a justificativa.

#### Exceções (NÃO exigem a skill)

- Código puramente backend (Controllers, Services, Migrations, Jobs, Models, sem render visual).
- Configuração de rotas, broadcasting, filas, .env.
- APIs REST (retornam JSON; UI é consumida por cliente).
- Testes automatizados.

Resumo: **qualquer pixel que vire código no projeto passa pela `frontend-design`** — sem exceção.

---

## Convenções de Código

### PHP / Laravel
- PSR-12 + Laravel Pint
- Em PT-BR para nomes de domínio (`Prova`, `Equipe`, `Etapa`), mas em inglês para termos técnicos (`Controller`, `Service`)
- Indentação: 4 espaços
- Tipagem: declare sempre que possível (`declare(strict_types=1);`)

### Flutter / Dart
- `flutter_lints` ativado
- Material Design 3 (com custom theme)
- Tema centralizado em `lib/config/theme.dart`
- Paleta do projeto (`#FF6600`, `#000000`, `#FFFFFF`) sempre via constantes
- Toda tela → criar primeiro o plano via skill `frontend-design` (ver § 7 acima)

---

## Estrutura de Pastas

```
gincana/                            ← raiz
├── app/                            ← Laravel (Models, Controllers, Livewire)
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/                      ← Views Blade + assets
├── routes/
├── storage/
├── tests/
├── vendor/                         ← ignorado pelo git
├── android/                        ← Flutter Android (gerado por `flutter create`)
├── ios/                            ← Flutter iOS (gerado por `flutter create`)
├── lib/                            ← Flutter app (Dart)
│   ├── main.dart
│   ├── config/                     ← theme.dart, routes.dart, constants.dart
│   ├── services/                   ← api_service, auth_service, ...
│   ├── providers/                  ← auth_provider, stage_provider, ...
│   ├── screens/                    ← telas
│   └── widgets/                    ← componentes visuais
├── docs/
│   ├── logs/                       ← Logs de programação (um .md por dia)
│   ├── STRUCTURE.md                ← Estrutura do projeto
│   └── ADR/                        ← Architectural Decision Records
├── reference/                      ← Documentação de requisitos
├── HELENA-QUEST.md                 ← Documentação mestre
├── CHECKLIST.md                    ← Progresso (atualizar sempre)
├── AGENTS.md                       ← Este arquivo (diretivas)
├── composer.json
├── pubspec.yaml
└── .env / .env.example
```

---

## Status Atual

- **Fase 0.1:** ✅ Concluída (Laravel 13 + MySQL + Livewire + Reverb + Sanctum)
- **Fase 0.2:** ✅ Concluída (Flutter 3.44.8 instalado, projeto criado na raiz, dependências adicionadas, permissões Android/iOS configuradas)
- **Fase 1+:** ⏳ Próxima etapa — Migrations de domínio, Models, GameEngine, API, Livewire, Broadcasting

---

**Última atualização:** 2026-07-23
