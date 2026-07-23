# Helena Quest

# Documento 03
# Requisitos Funcionais

**Versão:** 1.0

**Status:** Em elaboração

---

# 1. Introdução

Este documento descreve todos os requisitos funcionais do Helena Quest.

Cada requisito funcional representa uma funcionalidade obrigatória do sistema.

Todos os requisitos estão identificados por um código único no formato RF-XXX.

---

# 2. Convenções

RF = Requisito Funcional

Todo requisito deverá ser implementado integralmente.

Caso um requisito seja alterado, sua versão deverá ser atualizada.

---

# MÓDULO 01
# Autenticação

## RF-001

O sistema deverá permitir autenticação utilizando usuário e senha.

---

## RF-002

Cada equipe deverá possuir um único usuário de acesso.

---

## RF-003

Cada equipe poderá permanecer conectada em apenas um dispositivo simultaneamente.

---

## RF-004

Caso um segundo dispositivo tente autenticar utilizando o mesmo usuário, o sistema deverá encerrar automaticamente a sessão anterior.

---

## RF-005

O aplicativo deverá manter o usuário autenticado até que seja realizado logout.

---

## RF-006

O sistema deverá registrar data, horário e dispositivo utilizado em cada login.

---

## RF-007

O sistema deverá registrar a localização aproximada do primeiro login.

---

## RF-008

O painel administrativo deverá permitir redefinir a senha de qualquer equipe.

---

## RF-009

O administrador poderá bloquear uma equipe.

---

## RF-010

Uma equipe bloqueada não poderá autenticar-se.

---

## RF-011

O sistema deverá registrar todas as tentativas de autenticação.

---

## RF-012

Após logout todas as credenciais deverão ser invalidadas.

---

# MÓDULO 02
# Equipes

## RF-013

O sistema deverá permitir cadastrar equipes.

---

## RF-014

Cada equipe possuirá nome.

---

## RF-015

Cada equipe possuirá cor oficial.

---

## RF-016

Cada equipe possuirá usuário.

---

## RF-017

Cada equipe possuirá senha.

---

## RF-018

Cada equipe poderá possuir fotografia.

---

## RF-019

Cada equipe possuirá status.

Ativa

Inativa

Bloqueada

---

## RF-020

O sistema deverá permitir editar equipes.

---

## RF-021

O sistema deverá permitir excluir equipes.

---

## RF-022

O sistema deverá impedir nomes duplicados.

---

## RF-023

O sistema deverá impedir usuários duplicados.

---

## RF-024

O administrador poderá redefinir a cor da equipe.

---

## RF-025

Todas as alterações deverão ser registradas em log.

---

# MÓDULO 03
# Competições

## RF-026

O sistema deverá permitir criar múltiplas competições.

---

## RF-027

Cada competição possuirá nome.

---

## RF-028

Cada competição possuirá descrição.

---

## RF-029

Cada competição possuirá data.

---

## RF-030

Cada competição possuirá horário.

---

## RF-031

Cada competição possuirá status.

Planejamento

Publicada

Em andamento

Encerrada

Arquivada

---

## RF-032

Uma competição poderá possuir diversas provas.

---

## RF-033

Uma competição poderá possuir diversas equipes.

---

## RF-034

Uma competição poderá ser duplicada.

---

## RF-035

O administrador poderá arquivar uma competição.

---

# MÓDULO 04
# Provas

## RF-036

Cada competição poderá possuir diversas provas.

---

## RF-037

Cada prova possuirá nome.

---

## RF-038

Cada prova possuirá descrição.

---

## RF-039

Cada prova poderá possuir pontuação máxima.

---

## RF-040

Cada prova possuirá ordem de execução.

---

## RF-041

Cada prova poderá ser ativada ou desativada.

---

## RF-042

Cada prova conterá uma ou mais etapas.

---

## RF-043

O administrador poderá duplicar uma prova.

---

## RF-044

O administrador poderá reordenar provas através de drag-and-drop.

---

# MÓDULO 05
# Etapas

## RF-045

Cada prova será composta por uma ou mais etapas.

---

## RF-046

Cada etapa possuirá ordem sequencial.

---

## RF-047

Cada etapa possuirá nome.

---

## RF-048

Cada etapa possuirá descrição administrativa.

---

## RF-049

Cada etapa possuirá latitude.

---

## RF-050

Cada etapa possuirá longitude.

---

## RF-051

Cada etapa utilizará raio fixo de 30 metros.

---

## RF-052

Cada etapa possuirá um QR Code exclusivo.

---

## RF-053

Cada etapa possuirá uma narrativa.

---

## RF-054

Cada narrativa será reproduzida automaticamente utilizando TTS.

---

## RF-055

Cada etapa poderá possuir uma imagem ilustrativa.

---

## RF-056

Cada etapa possuirá exatamente uma pergunta.

---

## RF-057

Toda pergunta deverá possuir resposta exclusivamente numérica.

---

## RF-058

Toda resposta deverá possuir entre quatro e oito dígitos.

---

## RF-059

O sistema deverá validar o formato da resposta antes do envio.

---

## RF-060

Cada etapa possuirá uma resposta correta.

---

## RF-061

Cada etapa possuirá um número secreto.

---

## RF-062

Cada etapa possuirá uma dica para a próxima etapa.

---

## RF-063

Cada etapa poderá possuir dicas adicionais compráveis.

---

## RF-064

Cada etapa poderá definir penalidades.

---

## RF-065

Cada etapa poderá definir pontuação própria.

---

## RF-066

Cada etapa somente poderá ser concluída uma única vez por equipe.

---

## RF-067

A etapa deverá registrar horário de início.

---

## RF-068

A etapa deverá registrar horário de conclusão.

---

## RF-069

A etapa deverá registrar tempo total.

---

## RF-070

Todas as ações deverão ser registradas.

---

# MÓDULO 06
# QR Code

## RF-071

Cada etapa possuirá um QR Code exclusivo.

---

## RF-072

Os QR Codes deverão ser gerados automaticamente.

---

## RF-073

Cada QR Code utilizará identificador único (UUID).

---

## RF-074

O sistema deverá impedir reutilização de QR Codes.

---

## RF-075

A leitura somente será aceita na etapa correta.

---

## RF-076

O sistema deverá validar GPS antes de aceitar o QR Code.

---

## RF-077

Caso o GPS seja inválido o QR Code não será processado.

---

## RF-078

O sistema deverá registrar todas as leituras realizadas.

---

## RF-079

Leituras inválidas também deverão ser registradas.

---

## RF-080

Cada leitura registrará data, hora e coordenadas.

---