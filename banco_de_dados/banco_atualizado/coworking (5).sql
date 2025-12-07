-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 07/12/2025 às 19:06
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `coworking`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `comentario_tarefa`
--

CREATE TABLE `comentario_tarefa` (
  `id` int(11) NOT NULL,
  `tarefa_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `mensagem` text NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `emergencia`
--

CREATE TABLE `emergencia` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `solicitante_id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descricao` text NOT NULL,
  `prioridade` enum('ALTA','CRITICA') NOT NULL DEFAULT 'ALTA',
  `status` enum('ABERTO','EM_ANDAMENTO','RESOLVIDO') DEFAULT 'ABERTO',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolvido_em` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `empresa`
--

CREATE TABLE `empresa` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `logo_url` varchar(255) DEFAULT NULL,
  `padrao_senha` varchar(50) DEFAULT '@NomeEmpresa123' COMMENT 'Padrão para novos usuários'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `empresa`
--

INSERT INTO `empresa` (`id`, `nome`, `criado_em`, `logo_url`, `padrao_senha`) VALUES
(13, 'mbvcompany', '2025-12-01 04:51:40', NULL, '@NomeEmpresa123'),
(14, 'Coworking', '2025-12-03 05:40:45', 'uploads/logos/logo_14_1765130704.svg', '@NomeEmpresa123');

-- --------------------------------------------------------

--
-- Estrutura para tabela `equipe`
--

CREATE TABLE `equipe` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `equipe`
--

INSERT INTO `equipe` (`id`, `empresa_id`, `nome`, `descricao`, `criado_em`) VALUES
(17, 13, 'Geral', NULL, '2025-12-01 04:51:40'),
(18, 14, 'Geral', NULL, '2025-12-03 05:40:45'),
(19, 14, 'Gestor', NULL, '2025-12-03 05:54:08');

-- --------------------------------------------------------

--
-- Estrutura para tabela `papel`
--

CREATE TABLE `papel` (
  `id` int(11) NOT NULL,
  `nome` enum('LIDER','GESTOR','COLABORADOR') NOT NULL,
  `nivel_hierarquia` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `papel`
--

INSERT INTO `papel` (`id`, `nome`, `nivel_hierarquia`) VALUES
(1, 'LIDER', 100),
(2, 'GESTOR', 50),
(3, 'COLABORADOR', 10);

-- --------------------------------------------------------

--
-- Estrutura para tabela `projeto`
--

CREATE TABLE `projeto` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL COMMENT 'Vinculo de seguranca',
  `nome` varchar(160) NOT NULL,
  `cliente_nome` varchar(100) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `gestor_id` int(11) DEFAULT NULL COMMENT 'Quem criou/gerencia',
  `lider_id` int(11) DEFAULT NULL COMMENT 'Lider Tecnico',
  `status` enum('PLANEJADO','EM_ANDAMENTO','CONCLUIDO','CANCELADO') NOT NULL DEFAULT 'PLANEJADO',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `links_externos` text DEFAULT NULL,
  `arquivos_privados` text DEFAULT NULL,
  `data_inicio` date DEFAULT NULL,
  `data_fim_prevista` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `projeto`
--

INSERT INTO `projeto` (`id`, `empresa_id`, `nome`, `cliente_nome`, `descricao`, `gestor_id`, `lider_id`, `status`, `criado_em`, `atualizado_em`, `links_externos`, `arquivos_privados`, `data_inicio`, `data_fim_prevista`, `data_fim`, `ativo`) VALUES
(6, 13, 'AULÂO', 'IFBA', 'aula no ifba', 74, 74, 'PLANEJADO', '2025-12-01 05:22:30', '2025-12-01 05:22:30', '[{\"titulo\":\"Logo Cliente\",\"url\":\"uploads\\/projetos\\/logo_692d2616b75f4.png\",\"tipo\":\"logo\"},{\"titulo\":\"Detalhamento_do_Agendamento.pdf\",\"url\":\"uploads\\/projetos\\/doc_692d2616b79b8.pdf\",\"tipo\":\"arquivo\"}]', '[{\"titulo\":\"medico.png\",\"url\":\"uploads\\/projetos\\/priv_692d2616b7c0f.png\",\"tipo\":\"arquivo\"}]', NULL, NULL, NULL, 1),
(7, 14, 'Coworking Digital', 'IFBA', 'Desenvolver Sistema Coworking Digital', 79, 79, 'EM_ANDAMENTO', '2025-12-03 06:19:43', '2025-12-07 18:02:49', '[{\"titulo\":\"Gemini_Generated_Image_mwfp26mwfp26mwfp.png\",\"url\":\"uploads\\/projetos\\/doc_69320f2be1140.png\",\"tipo\":\"arquivo\"},{\"titulo\":\"iniciativa.png\",\"url\":\"uploads\\/projetos\\/doc_69320f3685f93.png\",\"tipo\":\"arquivo\"},{\"titulo\":\"carla .png\",\"url\":\"uploads\\/projetos\\/doc_6935c13c1e51d.png\",\"tipo\":\"arquivo\"}]', '[{\"titulo\":\"Gemini_Generated_Image_mwfp26mwfp26mwfp.png\",\"url\":\"uploads\\/projetos\\/priv_6935c1492ecda.png\",\"tipo\":\"arquivo\"}]', '2025-11-16', NULL, '2025-12-13', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `projeto_equipe`
--

CREATE TABLE `projeto_equipe` (
  `projeto_id` int(11) NOT NULL,
  `equipe_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `projeto_equipe`
--

INSERT INTO `projeto_equipe` (`projeto_id`, `equipe_id`) VALUES
(6, 17),
(7, 18),
(7, 19);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tarefa`
--

CREATE TABLE `tarefa` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `projeto_id` int(11) DEFAULT NULL,
  `titulo` varchar(180) NOT NULL,
  `descricao` text DEFAULT NULL,
  `prioridade` enum('NORMAL','IMPORTANTE','URGENTE') NOT NULL DEFAULT 'NORMAL',
  `status` enum('PENDENTE','EM_ANDAMENTO','EM_REVISAO','CONCLUIDA','CANCELADA') DEFAULT 'PENDENTE',
  `progresso` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `criador_id` int(11) NOT NULL,
  `responsavel_id` int(11) NOT NULL,
  `prazo` datetime DEFAULT NULL,
  `concluida_em` datetime DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tarefa`
--

INSERT INTO `tarefa` (`id`, `empresa_id`, `projeto_id`, `titulo`, `descricao`, `prioridade`, `status`, `progresso`, `criador_id`, `responsavel_id`, `prazo`, `concluida_em`, `criado_em`, `atualizado_em`) VALUES
(5, 14, 7, 'Tela Gestão: Atribuir atividades', 'Desenvolver Funcionalidades Tela de Gestão:\r\n\r\n- Atribuir Atividades;\r\n- Visualizar Atividades', 'URGENTE', 'EM_ANDAMENTO', 0, 79, 80, '2025-12-04 23:59:59', NULL, '2025-12-04 19:41:56', '2025-12-04 19:42:57');

-- --------------------------------------------------------

--
-- Estrutura para tabela `token_recuperacao_senha`
--

CREATE TABLE `token_recuperacao_senha` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `codigo` char(4) NOT NULL,
  `expira_em` datetime NOT NULL,
  `usado_em` datetime DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `token_recuperacao_senha`
--

INSERT INTO `token_recuperacao_senha` (`id`, `usuario_id`, `codigo`, `expira_em`, `usado_em`, `criado_em`) VALUES
(1, 79, '8630', '2025-12-03 06:59:27', '2025-12-03 02:45:08', '2025-12-03 05:44:27'),
(2, 80, '6379', '2025-12-03 07:10:34', '2025-12-03 02:55:55', '2025-12-03 05:55:34');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) DEFAULT NULL,
  `equipe_id` int(11) DEFAULT NULL,
  `papel_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(190) NOT NULL,
  `cargo_detalhe` varchar(100) DEFAULT NULL,
  `senha_hash` varchar(255) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `precisa_redefinir_senha` tinyint(1) NOT NULL DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ultima_atividade` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuario`
--

INSERT INTO `usuario` (`id`, `empresa_id`, `equipe_id`, `papel_id`, `nome`, `email`, `cargo_detalhe`, `senha_hash`, `ativo`, `precisa_redefinir_senha`, `criado_em`, `atualizado_em`, `ultima_atividade`) VALUES
(74, 13, 17, 1, 'victor', 'zeniniti@gmail.com', 'CEO', '$2y$10$E0ZA8KjOTq6j1X1twNbEpenY9TMjbyW4L1wT4BCY13Ya5/z0IXjfS', 1, 0, '2025-12-01 04:51:40', '2025-12-03 00:43:01', '2025-12-02 21:43:01'),
(75, 13, 17, 2, 'ana luiza', 'ana@gmail.com', 'teste', '$2y$10$vlCfGuce4H97t6JHamkBEeI9QvMS/8um4S1GNyALvlKPGMLvBY4ke', 1, 1, '2025-12-01 04:52:25', '2025-12-01 05:05:33', NULL),
(77, 13, 17, 3, 'teste', 'funcionario@teste.com', 'a', '$2y$10$Sx78MXJ7CXhLvPYKO6eDxOCs1mfGP17gFHTcza3Z/Au38RidvVJce', 1, 1, '2025-12-01 04:59:29', '2025-12-01 05:05:40', NULL),
(78, 14, 18, 1, 'COWORKING', 'coworking@gmail.com', 'CEO', '$2y$10$QnmeVLJH0yNdjG1XQ0oE8.E8l9gL3i5hZEnbYKMdBmzBxAXBJkli2', 1, 0, '2025-12-03 05:40:45', '2025-12-07 18:05:20', '2025-12-07 14:47:00'),
(79, 14, 19, 2, 'Abner Almeida', '202511240011@ifba.edu.br', 'Gestor', '$2y$10$Csw47bbYwBrUKAedfo.Pueaeb9Tswas0lEPH2OYiv3XDDfxgFh876', 1, 0, '2025-12-03 05:43:34', '2025-12-04 19:30:50', '2025-12-04 16:30:50'),
(80, 14, 18, 3, 'Quételi Tourinho', '202511240018@ifba.edu.br', 'Desenvolvedora', '$2y$10$ywZjhDaAtX/kuYvHb9sNfO3qlBNAqrA0J1LmDTLzYlFcs.U5IH4V6', 1, 0, '2025-12-03 05:47:00', '2025-12-07 18:01:52', '2025-12-03 02:56:01'),
(81, 14, 19, 3, 'victor', 'zeninitid@gmail.com', 'teste', '$2y$10$.AFa4/8szfLHK9pSsobJnO1ZIFn7mSsr1jpWLNBIcSdC1J/.K7s8m', 1, 1, '2025-12-05 00:00:01', '2025-12-05 00:00:01', NULL);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `comentario_tarefa`
--
ALTER TABLE `comentario_tarefa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tarefa` (`tarefa_id`),
  ADD KEY `idx_usuario` (`usuario_id`);

--
-- Índices de tabela `emergencia`
--
ALTER TABLE `emergencia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`),
  ADD KEY `solicitante_id` (`solicitante_id`);

--
-- Índices de tabela `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `equipe`
--
ALTER TABLE `equipe`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_equipe_empresa` (`empresa_id`);

--
-- Índices de tabela `papel`
--
ALTER TABLE `papel`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_papel` (`nome`);

--
-- Índices de tabela `projeto`
--
ALTER TABLE `projeto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_gestor` (`gestor_id`),
  ADD KEY `idx_lider` (`lider_id`),
  ADD KEY `fk_proj_empresa` (`empresa_id`);

--
-- Índices de tabela `projeto_equipe`
--
ALTER TABLE `projeto_equipe`
  ADD PRIMARY KEY (`projeto_id`,`equipe_id`),
  ADD KEY `fk_pe_equipe` (`equipe_id`);

--
-- Índices de tabela `tarefa`
--
ALTER TABLE `tarefa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_proj` (`projeto_id`),
  ADD KEY `idx_resp` (`responsavel_id`),
  ADD KEY `fk_tarefa_criador` (`criador_id`),
  ADD KEY `fk_tarefa_empresa` (`empresa_id`);

--
-- Índices de tabela `token_recuperacao_senha`
--
ALTER TABLE `token_recuperacao_senha`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario` (`usuario_id`);

--
-- Índices de tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_email` (`email`),
  ADD KEY `idx_papel` (`papel_id`),
  ADD KEY `fk_usuario_empresa` (`empresa_id`),
  ADD KEY `fk_usuario_equipe` (`equipe_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `comentario_tarefa`
--
ALTER TABLE `comentario_tarefa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `emergencia`
--
ALTER TABLE `emergencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `empresa`
--
ALTER TABLE `empresa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `equipe`
--
ALTER TABLE `equipe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de tabela `papel`
--
ALTER TABLE `papel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `projeto`
--
ALTER TABLE `projeto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `tarefa`
--
ALTER TABLE `tarefa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `token_recuperacao_senha`
--
ALTER TABLE `token_recuperacao_senha`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `comentario_tarefa`
--
ALTER TABLE `comentario_tarefa`
  ADD CONSTRAINT `fk_coment_tarefa` FOREIGN KEY (`tarefa_id`) REFERENCES `tarefa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_coment_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_comentario_tarefa_tarefa` FOREIGN KEY (`tarefa_id`) REFERENCES `tarefa` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `emergencia`
--
ALTER TABLE `emergencia`
  ADD CONSTRAINT `emergencia_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `emergencia_ibfk_2` FOREIGN KEY (`solicitante_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `equipe`
--
ALTER TABLE `equipe`
  ADD CONSTRAINT `fk_equipe_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `projeto`
--
ALTER TABLE `projeto`
  ADD CONSTRAINT `fk_proj_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_proj_gestor` FOREIGN KEY (`gestor_id`) REFERENCES `usuario` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_proj_lider` FOREIGN KEY (`lider_id`) REFERENCES `usuario` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `projeto_equipe`
--
ALTER TABLE `projeto_equipe`
  ADD CONSTRAINT `fk_pe_equipe` FOREIGN KEY (`equipe_id`) REFERENCES `equipe` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pe_projeto` FOREIGN KEY (`projeto_id`) REFERENCES `projeto` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `tarefa`
--
ALTER TABLE `tarefa`
  ADD CONSTRAINT `fk_tarefa_criador` FOREIGN KEY (`criador_id`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tarefa_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tarefa_proj` FOREIGN KEY (`projeto_id`) REFERENCES `projeto` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tarefa_resp` FOREIGN KEY (`responsavel_id`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `token_recuperacao_senha`
--
ALTER TABLE `token_recuperacao_senha`
  ADD CONSTRAINT `fk_token_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_usuario_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_usuario_equipe` FOREIGN KEY (`equipe_id`) REFERENCES `equipe` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_usuario_papel` FOREIGN KEY (`papel_id`) REFERENCES `papel` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
