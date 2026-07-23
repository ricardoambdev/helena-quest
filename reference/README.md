# Helena Quest

> Gamified Treasure Hunt Platform

Versão: 1.0.0 (Planejamento)
Status: Em desenvolvimento

---

# Visão Geral

**Helena Quest** é uma plataforma completa para criação e gerenciamento de gincanas, caças ao tesouro, eventos gamificados e desafios presenciais baseados em localização geográfica.

O sistema foi concebido para transformar atividades educacionais em experiências imersivas, utilizando tecnologias como GPS, QR Codes, narrativas interativas, gamificação, acompanhamento em tempo real e desafios colaborativos.

Embora seu primeiro caso de uso seja uma gincana escolar do Colégio Helena, sua arquitetura foi projetada para permitir a criação de qualquer tipo de evento baseado em missões.

---

# Objetivos

O projeto possui cinco objetivos principais.

- Automatizar completamente uma gincana presencial.
- Garantir a integridade das provas utilizando GPS e QR Codes.
- Criar uma experiência divertida e imersiva para os participantes.
- Permitir acompanhamento em tempo real pelos organizadores e pelo público.
- Servir como plataforma para futuras competições e eventos.

---

# Tecnologias

## Backend

- Laravel 13
- PHP 8.4+
- Livewire 4
- MySQL
- Laravel Sanctum
- Laravel Reverb (WebSocket)
- Queue (Redis)
- Broadcasting

## Frontend Administrativo

- Tailwind CSS
- Flux UI
- Alpine.js

## Aplicativo

- Flutter (Android + iOS)
- Material Design 3
- Google Maps (google_maps_flutter)
- QR Scanner (mobile_scanner)
- GPS (geolocator/location)
- Camera (image_picker, camera only)
- Text To Speech (nativo Android TTS / iOS AVSpeechSynthesizer)

---

# Arquitetura Geral

Helena Quest é dividido em três aplicações independentes.

## 1. Painel Administrativo

Sistema web destinado aos organizadores.

Permite criar gincanas, equipes, provas, desafios, QR Codes, acompanhar participantes e controlar toda a competição.

---

## 2. Aplicativo Mobile

Aplicação utilizada pelas equipes durante a competição.

Todas as interações da prova acontecem através do aplicativo.

---

## 3. Painel Público

Sistema em tempo real destinado ao público.

Exibe:

- mapa ao vivo;
- progresso das equipes;
- ranking;
- fotos;
- áudios;
- eventos da competição.

---

# Princípios do Projeto

O Helena Quest foi desenvolvido seguindo os seguintes princípios:

- Arquitetura modular.
- Código limpo (Clean Code).
- SOLID.
- Repository Pattern.
- Service Layer.
- Event Driven.
- API First.
- Mobile First.
- Segurança por padrão.
- Escalabilidade.
- Alta configurabilidade.

---

# Estrutura da Documentação

| Documento | Descrição |
|-----------|-----------|
| 01-SRS | Especificação de Requisitos |
| 02-REQUISITOS | Requisitos Funcionais e Não Funcionais |
| 03-REGRAS-DE-NEGOCIO | Regras completas da plataforma |
| 04-ARQUITETURA | Arquitetura técnica |
| 05-BANCO-DE-DADOS | Modelagem do banco |
| 06-API | Documentação da API |
| 07-PAINEL | Sistema Administrativo |
| 08-APP | Aplicativo Flutter |
| 09-TELAO | Painel Público |
| 10-GAME-ENGINE | Motor da Gincana |
| 11-GPS | Geolocalização |
| 12-QRCODE | Sistema de QR Codes |
| 13-SEGURANCA | Segurança |
| 14-WIREFRAMES | Protótipos |
| 15-UML | Diagramas |
| 16-ROADMAP | Planejamento |
| 17-PROMPTS | Prompts do OpenCode |

---

# Objetivo da Documentação

Esta documentação é considerada a única fonte oficial das regras de negócio do projeto.

Toda implementação deverá seguir rigorosamente as especificações aqui descritas.

Qualquer alteração deverá ser refletida primeiro na documentação e somente depois no código-fonte.

---

**"Documentação primeiro. Código depois."**