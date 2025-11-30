-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 25/11/2025 às 06:16
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
-- Estrutura para tabela `empresa`
--

CREATE TABLE `empresa` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `empresa`
--

INSERT INTO `empresa` (`id`, `nome`, `criado_em`) VALUES
(1, 'Empresa Demo', '2025-11-20 17:23:32'),
(2, 'MBVcompany', '2025-11-20 17:35:26'),
(3, 'MBVcompany', '2025-11-20 17:54:44'),
(4, 'MBVcompany', '2025-11-20 18:56:57'),
(5, 'mbvcompany', '2025-11-20 19:06:37'),
(6, 'MBVcompany', '2025-11-20 19:13:50'),
(7, 'mbvcompany', '2025-11-20 19:26:16'),
(8, 'mbvcompany', '2025-11-20 19:26:16'),
(9, 'mbvcompany', '2025-11-20 20:00:47'),
(10, 'Dani', '2025-11-20 22:30:20');

-- --------------------------------------------------------

--
-- Estrutura para tabela `papel`
--

CREATE TABLE `papel` (
  `id` int(11) NOT NULL,
  `nome` enum('DONO','GESTOR','FUNCIONARIO') NOT NULL,
  `nivel_hierarquia` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `papel`
--

INSERT INTO `papel` (`id`, `nome`, `nivel_hierarquia`) VALUES
(1, 'DONO', 100),
(2, 'GESTOR', 50),
(3, 'FUNCIONARIO', 10);

-- --------------------------------------------------------

--
-- Estrutura para tabela `projeto`
--

CREATE TABLE `projeto` (
  `id` int(11) NOT NULL,
  `nome` varchar(160) NOT NULL,
  `descricao` text DEFAULT NULL,
  `gestor_id` int(11) DEFAULT NULL,
  `status` enum('PLANEJADO','EM_ANDAMENTO','CONCLUIDO','CANCELADO') NOT NULL DEFAULT 'PLANEJADO',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tarefa`
--

CREATE TABLE `tarefa` (
  `id` int(11) NOT NULL,
  `projeto_id` int(11) DEFAULT NULL,
  `titulo` varchar(180) NOT NULL,
  `descricao` text DEFAULT NULL,
  `prioridade` enum('NORMAL','URGENTE') NOT NULL DEFAULT 'NORMAL',
  `status` enum('PENDENTE','EM_ANDAMENTO','EM_REVISAO','CONCLUIDA') NOT NULL DEFAULT 'PENDENTE',
  `progresso` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `criador_id` int(11) NOT NULL,
  `responsavel_id` int(11) NOT NULL,
  `prazo` datetime DEFAULT NULL,
  `concluida_em` datetime DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(131, 59, '6346', '2025-11-20 23:47:06', '2025-11-20 19:32:37', '2025-11-20 22:32:06'),
(132, 59, '3439', '2025-11-25 03:58:42', '2025-11-24 23:44:04', '2025-11-25 02:43:42');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) DEFAULT NULL,
  `papel_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(190) NOT NULL,
  `cargo_detalhe` varchar(100) DEFAULT NULL,
  `senha_hash` varchar(255) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `precisa_redefinir_senha` tinyint(1) NOT NULL DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuario`
--

INSERT INTO `usuario` (`id`, `empresa_id`, `papel_id`, `nome`, `email`, `cargo_detalhe`, `senha_hash`, `ativo`, `precisa_redefinir_senha`, `criado_em`, `atualizado_em`) VALUES
(58, 10, 1, 'Dani', 'abner_jhon@outlook.com', NULL, '$2y$10$sgVImZ..4O3xt7CrlbBexORWB1XsRi3I2Kfwbzo5DJoKYQlO5PSLW', 1, 0, '2025-11-20 22:30:20', '2025-11-20 22:30:20'),
(59, 10, 3, 'Jhon Abner', 'jhonabnertrabalho@gmail.com', NULL, '$2y$10$JhTx/ygkERmR5tpfhwKG1u0Wu2XqBWGdJdRQnYKpPOlV1Q6cuS1um', 1, 0, '2025-11-20 22:31:04', '2025-11-25 02:44:04'),
(60, 10, 3, 'Abner Santos', '202013600025@ifba.edu.br', NULL, '$2y$10$9rcq7cxqoRRJr7IK2qml2e0nQ/MJwYY6BSW3s/jNTsHi/B8nLcFOC', 1, 1, '2025-11-25 04:26:35', '2025-11-25 04:26:35');

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
-- Índices de tabela `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`id`);

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
  ADD KEY `idx_gestor` (`gestor_id`);

--
-- Índices de tabela `tarefa`
--
ALTER TABLE `tarefa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_proj` (`projeto_id`),
  ADD KEY `idx_resp` (`responsavel_id`),
  ADD KEY `fk_tarefa_criador` (`criador_id`);

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
  ADD KEY `fk_usuario_empresa` (`empresa_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `comentario_tarefa`
--
ALTER TABLE `comentario_tarefa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `empresa`
--
ALTER TABLE `empresa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `papel`
--
ALTER TABLE `papel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `projeto`
--
ALTER TABLE `projeto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tarefa`
--
ALTER TABLE `tarefa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `token_recuperacao_senha`
--
ALTER TABLE `token_recuperacao_senha`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT de tabela `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `comentario_tarefa`
--
ALTER TABLE `comentario_tarefa`
  ADD CONSTRAINT `fk_coment_tarefa` FOREIGN KEY (`tarefa_id`) REFERENCES `tarefa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_coment_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `projeto`
--
ALTER TABLE `projeto`
  ADD CONSTRAINT `fk_proj_gestor` FOREIGN KEY (`gestor_id`) REFERENCES `usuario` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `tarefa`
--
ALTER TABLE `tarefa`
  ADD CONSTRAINT `fk_tarefa_criador` FOREIGN KEY (`criador_id`) REFERENCES `usuario` (`id`) ON UPDATE CASCADE,
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
  ADD CONSTRAINT `fk_usuario_papel` FOREIGN KEY (`papel_id`) REFERENCES `papel` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
