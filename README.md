# Coworking Digital

O **Coworking Digital** é uma plataforma de **gestão e comunicação interna** desenvolvida para otimizar o fluxo de trabalho em empresas.  
A solução foca na **integração eficiente da comunicação com o controle de tarefas**, visando solucionar problemas comuns como:

- Desorganização  
- Perda de informações  
- Dificuldade no acompanhamento de atividades por gestores  

---

## Propósito e Funcionalidade Central

O objetivo principal do projeto é criar uma plataforma capaz de **otimizar a informação e a comunicação**, facilitando a **gestão de equipes e funcionários**.

A funcionalidade central permite que **avisos e comunicados sejam automaticamente transformados em tarefas**, contendo **checklists integrados**, o que proporciona:

- Mais clareza para os colaboradores  
- Maior controle para gestores  
- Um fluxo de trabalho mais eficiente  

---

## Estrutura Hierárquica e Fluxo de Tarefas

A arquitetura do sistema foi definida com base em uma **hierarquia de três níveis**, estabelecida na reunião de **23 de Novembro**:

1. **Dono da Empresa (Administrador)**  
   Responsável pela criação do **Projeto principal**.

2. **Gerente / Gestor**  
   Cria as **atividades e tarefas** a partir do projeto e as atribui aos colaboradores.

3. **Colaborador**  
   Executa as atividades e **envia a tarefa para revisão**, ao invés de concluí-la diretamente, permitindo a **aprovação final pelo gestor**.

---

## Funcionalidades Chave Implementadas

O desenvolvimento priorizou telas essenciais para garantir a funcionalidade básica do sistema:

- **Equipe**
- **Minhas Tarefas**
- **Calendário**
- **Configurações**

### Gestão de Tarefas
- Definição de **prioridade**, **prazo** e **status**
- Campo **tempo total (minutos)** no banco de dados para controle de produtividade  
  *(decisão tomada na reunião de 12 de Dezembro)*

### Interface (UI/UX)
- Layout **responsivo** e **intuitivo**
- Segue boas práticas de design conforme o **Relato de Condução**

### Escopo Revisado
- Remoção da seção **"Emergencial"**
- Simplificação do projeto para garantir a entrega dos **requisitos essenciais**  
  *(decisão tomada na reunião de 7 de Dezembro)*

---

## Ajustes Finais e Desafios Críticos

A reunião de **13 de Dezembro** marcou a etapa final de ajustes antes da submissão do projeto, com foco em garantir que o **fluxo principal do sistema estivesse totalmente funcional**.

### Ajustes de Última Hora

- **Funcionalidade para Gestores**  
  Ajustes na lógica da tela *"Minhas Tarefas"*, pois os checklists criados pelos gestores não se adequavam ao mesmo fluxo de execução dos colaboradores.

- **Correção de Fluxo**  
  Implementação de soluções como a função **`closeModal()`** (sugerida por *Jhon Abner*) para corrigir falhas na criação de projetos e tarefas e garantir a atualização correta da interface.

- **Documentação**  
  Finalização da estrutura do **README.md** e organização dos documentos para envio.

---

## Principais Dificuldades Enfrentadas

1. **Conflitos de Código e Sincronização**  
   Problemas graves de merge e sincronização causaram a quebra do projeto próximo à entrega final, exigindo ações emergenciais para estabilização.

2. **Comunicação e Disponibilidade**  
   A falta de comunicação contínua e a disponibilidade limitada de integrantes foram desafios recorrentes ao longo do semestre.

3. **Requisito Não Atendido**  
   O envio de **notificações em tempo real (RF011)** não foi implementado.

---

## Mentoria e Apoio ao Desenvolvimento

O projeto contou com o apoio e orientação de diversos profissionais e alunos:

- **Cássio (Orientador)** – Direcionamento geral do projeto  
- **Júlio** – Apoio na implementação inicial do banco de dados  
- **Fabinho** – Orientações para melhorias e refinamentos no banco de dados  
- **Vinícius** – Contribuições sobre aplicação prática e nomenclaturas de mercado  
- **Alunos Veteranos** – Apoio técnico, sugestões de implementação e apresentação do projeto  

---
