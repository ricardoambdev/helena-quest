# Helena Quest

# Documento 02
# Visão Geral do Sistema

**Versão:** 1.0

**Status:** Em elaboração

---

# 1. Introdução

O Helena Quest é uma plataforma de gerenciamento de competições presenciais baseada em localização geográfica, QR Codes e desafios interativos.

Seu objetivo é permitir a criação, administração e execução de gincanas educacionais de maneira totalmente digital, reduzindo a necessidade de intervenção manual dos organizadores e proporcionando uma experiência imersiva para os participantes.

O sistema foi concebido para ser altamente configurável, permitindo que diferentes competições sejam criadas utilizando a mesma plataforma.

---

# 2. Conceito

Uma competição é composta por um conjunto de provas.

Cada prova possui diversas etapas.

Cada etapa acontece em um local físico previamente definido.

Cada local possui um QR Code exclusivo.

O aplicativo somente permitirá o avanço da equipe caso ela esteja fisicamente no local correto e realize todas as ações exigidas naquela etapa.

---

# 3. Estrutura Hierárquica

Sistema

↓

Competições

↓

Provas

↓

Etapas

↓

Desafios

↓

Resultados

---

# 4. Estrutura Geral

A plataforma é composta por três aplicações independentes.

## 4.1 Painel Administrativo

Responsável pela criação e gerenciamento da competição.

Utilizado exclusivamente pelos organizadores.

Principais responsabilidades:

- criar competições;
- cadastrar provas;
- cadastrar etapas;
- cadastrar equipes;
- cadastrar usuários;
- gerar QR Codes;
- acompanhar equipes;
- visualizar ranking;
- configurar pontuação;
- visualizar estatísticas;
- controlar permissões.

---

## 4.2 Aplicativo Mobile

Aplicação utilizada pelas equipes durante toda a competição.

É através dele que todos os desafios serão realizados.

O aplicativo será responsável por:

- autenticação;
- leitura de QR Codes;
- validação do GPS;
- reprodução da narrativa;
- captura de fotografias;
- envio de áudios;
- resposta das perguntas;
- recebimento das próximas pistas;
- comunicação com a API.

---

## 4.3 Painel Público

Aplicação destinada ao acompanhamento da competição.

Seu objetivo é tornar o evento visualmente interessante para o público presente.

Será acessível através de uma URL pública.

---

# 5. Fluxo Geral da Competição

Antes do início da prova cada equipe receberá um envelope lacrado contendo:

- nome da equipe;
- usuário;
- senha;
- QR Code para download do aplicativo;
- primeira pista.

Após realizar o login, a equipe deverá interpretar a primeira pista e deslocar-se até o primeiro local.

Ao chegar ao destino deverá realizar a leitura do QR Code existente naquele ponto.

O aplicativo verificará automaticamente:

- autenticidade do QR Code;
- localização GPS;
- etapa atual da equipe.

Caso todas as validações sejam aprovadas, a narrativa será exibida.

Após a narrativa será apresentada uma pergunta.

Antes de responder, a equipe deverá enviar uma fotografia comprovando sua presença no local.

Somente após o envio da fotografia será permitido responder ao desafio.

Respondendo corretamente, a equipe receberá:

- pontuação;
- número secreto;
- próxima pista.

Esse processo será repetido até o término da última etapa.

---

# 6. Segunda Fase

Ao concluir todas as etapas, a equipe possuirá diversos números secretos.

Esses números deverão ser organizados em ordem inversa da obtenção.

A concatenação desses números formará uma chave de desbloqueio.

Somente após informar corretamente essa chave será liberado o Enigma Final.

---

# 7. Enigma Final

O Enigma Final ocorrerá nas dependências da escola.

Diversos QR Codes serão distribuídos pelo ambiente.

Cada QR Code representará um desafio independente.

Ao concluir um desafio, a equipe receberá uma letra.

As letras obtidas formarão um conjunto embaralhado.

A equipe deverá descobrir qual palavra é formada por essas letras.

A palavra correta desbloqueará o desafio final da competição.

---

# 8. Arquitetura Lógica

A plataforma é dividida em módulos independentes.

- Autenticação
- Usuários
- Equipes
- Competições
- Provas
- Etapas
- GPS
- QR Codes
- Narrativas
- Perguntas
- Fotografias
- Áudios
- Dicas
- Pontuação
- Ranking
- Telão Público
- Relatórios
- Configurações
- Logs

Cada módulo possui responsabilidades específicas e comunicação através da camada de serviços da aplicação.

---

# 9. Comunicação

Todo o fluxo de comunicação será realizado através de uma API REST autenticada.

O painel administrativo e o aplicativo utilizarão a mesma API.

Atualizações em tempo real utilizarão WebSockets.

---

# 10. Princípios da Plataforma

O Helena Quest foi projetado seguindo os seguintes princípios:

- simplicidade operacional;
- alta confiabilidade;
- rastreabilidade das ações;
- modularização;
- reutilização;
- segurança;
- escalabilidade;
- facilidade de manutenção.

---

# 11. Características Gerais

O sistema deverá:

- permitir múltiplas competições;
- permitir múltiplas provas por competição;
- permitir múltiplas etapas por prova;
- permitir múltiplas equipes;
- registrar todas as ações;
- manter histórico permanente;
- funcionar integralmente através do aplicativo;
- permitir acompanhamento em tempo real.

---

# 12. Restrições

O avanço entre etapas dependerá obrigatoriamente de:

- localização GPS válida;
- leitura do QR Code correto;
- envio da fotografia obrigatória;
- resposta correta da pergunta.

Caso qualquer uma dessas condições não seja satisfeita, a equipe permanecerá na etapa atual.

---

# 13. Escalabilidade

Embora desenvolvido inicialmente para uma gincana escolar, o Helena Quest deverá suportar futuras expansões sem necessidade de alterações estruturais significativas.

Sua arquitetura permitirá a criação de diferentes tipos de eventos utilizando a mesma base tecnológica.

---

Fim do Documento.