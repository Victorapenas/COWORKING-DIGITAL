# Coworking Digital

O **Coworking Digital** é uma plataforma de gestão e comunicação interna desenvolvida para otimizar o fluxo de trabalho em empresas.  
A solução foca na integração eficiente da comunicação com o controle de tarefas, visando solucionar problemas comuns de desorganização, perda de informações e dificuldades no acompanhamento de atividades por parte dos gestores.

---

## Propósito e Funcionalidade Central

O objetivo principal é criar uma plataforma capaz de otimizar a informação e a comunicação e facilitar a gestão de equipes e funcionários.

A funcionalidade central da plataforma permite que avisos e comunicados se transformem automaticamente em tarefas com checklists integrados. Isso promove clareza para colaboradores e maior controle para gestores, tornando o fluxo de trabalho mais eficiente.

---

## Estrutura Hierárquica e Fluxo de Tarefas

A arquitetura do sistema foi definida em torno de uma hierarquia de três níveis, estabelecida na Reunião de 23 de Novembro:  
https://docs.google.com/document/d/1z9QvTbl29PJ0UUdrQGdB06mJBSVwvR6SMswpcysKd_0/edit?tab=t.qdhlrwk0513g

1. **Dono da Empresa (Administrador):**  
   Responsável pela criação do Projeto principal.

2. **Gerente/Gestor:**  
   Cria as Atividades/Tarefas a partir do projeto e as atribui aos colaboradores.

3. **Colaborador:**  
   Executa as atividades e envia a tarefa para revisão ao invés de concluí-la diretamente, permitindo a aprovação final pelo Gestor.

---

## Funcionalidades Chave Implementadas

O desenvolvimento priorizou telas essenciais (Equipe, Minhas Tarefas, Calendário e Configurações) para garantir a funcionalidade básica:

### Gestão de Tarefas

Permite definir prioridade, prazo e status da tarefa.  
Inclui o campo **tempo total (minutos)** no banco de dados para controle de produtividade (decisão na Reunião de 12 de Dezembro):  
https://docs.google.com/document/d/1wFhM7LjGoRqU8IiMPAtgJL00fHPFNKNgyV-NAWMxObM/edit?tab=t.rujeokiu43bs

### Interface (UI/UX)

O layout é responsivo e intuitivo, atendendo ao requisito de seguir boas práticas de design (Relato de Condução).

### Escopo Revisado

O projeto foi simplificado na reta final, eliminando a seção **"Emergencial"** para garantir a entrega dos requisitos essenciais (decisão na Reunião de 7 de Dezembro):  
https://docs.google.com/document/d/1LJvgQMH9G5PUWWsIuHzyxdBZRLOZCGg9sNr_F0AvPTg/edit?tab=t.xia051othp8z#heading=h.j1zu40gfkmwh

---

## Ajustes Finais e Desafios Críticos

A Reunião de 13 de Dezembro:  
https://docs.google.com/document/d/14hHkOzSuoclhriFoi6C_U3wIfDt362krHkZuJf1QKGE/edit?usp=sharing  

foi a etapa final para definir os ajustes necessários para submeter o projeto aos professores, com foco em garantir que o fluxo principal do sistema estivesse funcional.

Além das reuniões remotas, o grupo também realizou **reuniões presenciais**, que foram fundamentais para alinhar decisões, ajustar o fluxo do projeto e resolver problemas de forma mais eficiente, contribuindo diretamente para a evolução e estabilidade do sistema.

### Ajustes de Última Hora

Os principais aprimoramentos e correções focaram na estabilidade e usabilidade:

- **Funcionalidade para Gestores:**  
  Necessidade de aprimorar a lógica da tela *"Minhas Tarefas"* para gestores, pois o modelo de checklist criado por eles (que deveriam ser tarefas para colaboradores) não se encaixava em um fluxo onde o próprio gestor pudesse executá-las da mesma forma.

- **Correção de Fluxo:**  
  Implementação de soluções, como a função **close modal** (sugerida por *Jhon Abner*), para corrigir falhas na criação de novos projetos e tarefas e garantir a correta atualização da interface (front-end) após essas ações.

- **Documentação:**  
  Finalização da estruturação do **README.md** e organização dos documentos para envio.

---

## Principais Dificuldades Enfrentadas

O processo de desenvolvimento foi marcado por desafios técnicos e de gestão de equipe:

1. **Conflitos de Código e Sincronização:**  
   Um problema significativo de sincronização e merge de código causou a quebra do projeto pouco antes da entrega final (Reunião de 12 de Dezembro), exigindo um esforço de emergência para a estabilização.

2. **Comunicação e Disponibilidade:**  
   O Relato de Condução destaca a comunicação e a disponibilidade dos integrantes como um dos principais desafios ao longo do semestre.

3. **Requisito Não Atendido:**  
   O requisito essencial de enviar notificações em tempo real (**RF011**) não foi implementado.

---

## Mentoria e Apoio ao Desenvolvimento

O projeto Coworking Digital contou com o apoio e orientação de diversos profissionais e alunos:

- **Cássio (Orientador):**  
  Orientação principal sobre a ideia do projeto e o direcionamento do desenvolvimento.

- **Júlio:**  
  Orientações na implementação inicial do Banco de Dados.

- **Fabinho:**  
  Contribuições com orientações sobre melhorias e refinamentos no Banco de Dados.

- **Vinícius:**  
  Orientações sobre a funcionalidade do sistema no mercado, incluindo nomenclaturas e aspectos de aplicação prática.

- **Alunos Veteranos:**  
  Orientações sobre a apresentação do projeto, implementações e questões técnicas, conforme discutido na Reunião de 12 e 13 de Dezembro.
