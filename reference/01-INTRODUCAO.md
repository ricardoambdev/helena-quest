# Helena Quest

## Documento 01
# Introdução

**Versão:** 1.0

**Status:** Em elaboração

---

# 1. Objetivo

Este documento apresenta a visão geral do projeto Helena Quest e define seu propósito, escopo e objetivos.

Toda a documentação deste projeto deverá ser considerada a referência oficial para desenvolvimento do sistema.

Qualquer implementação deverá respeitar integralmente as especificações descritas nesta documentação.

---

# 2. Sobre o Projeto

O Helena Quest é uma plataforma para gerenciamento e execução de gincanas presenciais baseadas em localização geográfica.

O sistema permite que equipes realizem desafios distribuídos em diversos locais utilizando dispositivos móveis.

Cada desafio é validado através da combinação entre geolocalização (GPS) e QR Codes exclusivos, garantindo que as equipes realmente estejam presentes no local correto.

Durante a competição as equipes interagem com narrativas, perguntas, desafios e enigmas até concluírem todas as etapas da prova.

Toda a competição poderá ser acompanhada em tempo real por organizadores e espectadores através de um painel público.

---

# 3. Objetivos do Sistema

O Helena Quest possui como objetivos principais:

- Automatizar integralmente a execução da gincana.
- Eliminar controles manuais durante a competição.
- Garantir justiça através da validação por GPS e QR Code.
- Registrar todas as ações realizadas pelas equipes.
- Permitir acompanhamento em tempo real.
- Fornecer uma plataforma reutilizável para futuras competições.

---

# 4. Público-alvo

O sistema foi desenvolvido inicialmente para utilização em gincanas escolares.

Entretanto sua arquitetura permitirá utilização em:

- escolas;
- universidades;
- museus;
- eventos culturais;
- treinamentos corporativos;
- atividades turísticas;
- eventos esportivos.

---

# 5. Escopo

O Helena Quest será composto por três aplicações independentes.

## 5.1 Painel Administrativo

Aplicação Web utilizada pelos organizadores.

Responsável por:

- criação das competições;
- cadastro de equipes;
- gerenciamento das etapas;
- acompanhamento da competição;
- configuração geral do sistema.

---

## 5.2 Aplicativo Mobile

Aplicação Flutter utilizada exclusivamente pelas equipes.

Responsável por:

- autenticação;
- leitura dos QR Codes;
- validação do GPS;
- reprodução das narrativas;
- envio de fotografias;
- envio de áudios;
- resposta dos desafios;
- acompanhamento da competição.

---

## 5.3 Painel Público

Aplicação destinada ao público.

Responsável por exibir:

- mapa em tempo real;
- localização das equipes;
- ranking;
- progresso;
- fotografias;
- áudios enviados;
- informações da competição.

---

# 6. Tecnologias previstas

## Backend

- Laravel 13
- PHP 8.4+
- MySQL
- Laravel Sanctum
- Livewire 4

## Frontend Administrativo

- Tailwind CSS
- Alpine.js
- Flux UI

## Aplicativo

- Flutter (Android + iOS)
- Material Design 3
- GPS (geolocator/location packages)
- QR Scanner (mobile_scanner)
- Camera (image_picker, camera only, sem galeria)
- Text To Speech (nativo Android TTS / AVSpeechSynthesizer iOS)

---

# 7. Premissas

Para o correto funcionamento do sistema considera-se:

- acesso à internet durante a competição;
- dispositivo Android compatível;
- permissão de GPS concedida;
- permissão de câmera concedida;
- permissão para utilização do microfone.

---

# 8. Definições

## Equipe

Grupo de participantes que realiza a competição.

## Competição

Evento completo composto por uma ou mais provas.

## Prova

Conjunto de etapas relacionadas.

## Etapa

Desafio executado em um determinado local.

## Narrativa

Texto apresentado e reproduzido por voz após a validação do QR Code.

## Pergunta

Desafio numérico respondido pela equipe.

## Dica

Informação liberada para orientar a equipe até a próxima etapa.

---

# 9. Convenções

Todos os requisitos deste projeto utilizarão a seguinte nomenclatura:

- RF → Requisito Funcional
- RNF → Requisito Não Funcional
- RN → Regra de Negócio
- UC → Caso de Uso
- API → Endpoint da aplicação

---

# 10. Controle da Documentação

Toda alteração deverá:

1. ser registrada neste repositório;
2. atualizar o número da versão;
3. manter histórico das alterações.

---

Fim do Documento.