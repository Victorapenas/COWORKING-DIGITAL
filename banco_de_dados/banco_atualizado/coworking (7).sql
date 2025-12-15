-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 15/12/2025 às 23:16
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

--
-- Despejando dados para a tabela `comentario_tarefa`
--

INSERT INTO `comentario_tarefa` (`id`, `tarefa_id`, `usuario_id`, `mensagem`, `criado_em`) VALUES
(1, 20, 103, '[Mudou status para: EM REVISAO] eu fiz algo legal\n\n[ARQUIVO_ANEXO]:uploads/entregas/entrega_20_693cb3f29038d.png:4-Topaz-Gigapixel-escala-2x.png', '2025-12-13 00:31:46'),
(2, 20, 103, '[Mudou status para: CONCLUIDA]', '2025-12-13 02:08:30'),
(3, 20, 103, '[Mudou status para: PENDENTE]', '2025-12-13 02:09:15'),
(4, 20, 103, 'fiz algo top', '2025-12-13 02:45:57'),
(5, 20, 102, '✅ Aprovou e concluiu a tarefa.', '2025-12-13 02:46:36'),
(6, 1, 101, '🚀 Enviou para revisão.', '2025-12-13 21:28:11'),
(7, 1, 101, '✅ Aprovou e concluiu a tarefa. Aprovado pelo gestor.', '2025-12-13 21:28:33'),
(8, 23, 103, '🚀 Enviou para revisão. aaaa', '2025-12-13 21:52:25'),
(9, 23, 102, '⚠️ Devolveu para ajustes.\n\n[FEEDBACK DO GESTOR]: ta errado', '2025-12-13 21:53:01'),
(10, 23, 103, '🚀 Enviou para revisão. 123456789', '2025-12-13 21:59:42'),
(11, 23, 102, '✅ Aprovou e concluiu a tarefa. Aprovado pelo gestor.', '2025-12-13 21:59:56'),
(12, 23, 103, '🚀 Enviou para revisão. 123456', '2025-12-13 22:01:29'),
(13, 23, 102, '⚠️ Devolveu para ajustes. Tarefa devolvida para ajustes.\n\n[FEEDBACK DO GESTOR]: [GESTOR SOLICITOU AJUSTE]: melhore isso', '2025-12-13 22:10:30'),
(14, 23, 103, '🚀 Enviou para revisão. 1111', '2025-12-13 22:10:54'),
(15, 23, 102, '✅ Aprovou e concluiu a tarefa. Aprovado pelo gestor.', '2025-12-13 22:11:05'),
(16, 23, 101, '🚀 Enviou para revisão. Tarefa devolvida pelo líder para revisão.', '2025-12-13 22:11:25'),
(17, 23, 102, '✅ Aprovou e concluiu a tarefa. Aprovado pelo gestor.', '2025-12-13 22:12:47'),
(18, 23, 101, '🚀 Enviou para revisão. Tarefa devolvida pelo líder para revisão.', '2025-12-13 22:13:10'),
(19, 23, 102, '✅ Aprovou e concluiu a tarefa. Aprovado pelo gestor.', '2025-12-13 22:17:03'),
(20, 23, 101, '🚀 Enviou para revisão. Tarefa devolvida pelo líder para revisão.', '2025-12-13 22:17:38'),
(21, 23, 102, '✅ Aprovou e concluiu a tarefa. Aprovado pelo gestor.', '2025-12-13 22:28:03'),
(22, 23, 101, '⚠️ Líder solicitou refação. Tarefa devolvida pelo líder para revisão.\n\n[MOTIVO/FEEDBACK]: [LÍDER SOLICITOU REFAÇÃO]: olhe os docs', '2025-12-13 22:28:19'),
(23, 22, 103, '🚀 Enviou para revisão. Tarefa finalizada e enviada para revisão.', '2025-12-13 22:39:52'),
(24, 19, 103, '🚀 Enviou para revisão. Tarefa finalizada e enviada para revisão.', '2025-12-13 22:39:55'),
(25, 19, 103, 'Tarefa finalizada e enviada para revisão.', '2025-12-13 22:39:59'),
(26, 23, 101, '⚠️ Devolveu para ajustes. Tarefa devolvida para ajustes.\n\n[MOTIVO/FEEDBACK]: [GESTOR SOLICITOU AJUSTE]: [LÍDER SOLICITOU REFAÇÃO]: olhe os docs\r\n\r\nFavor ajustar conforme solicitado acima.', '2025-12-13 22:40:23'),
(27, 22, 101, '⚠️ Devolveu para ajustes. Tarefa devolvida para ajustes.\n\n[MOTIVO/FEEDBACK]: [GESTOR SOLICITOU AJUSTE]: aaaa', '2025-12-13 22:40:27'),
(28, 19, 101, '⚠️ Devolveu para ajustes. Tarefa devolvida para ajustes.\n\n[MOTIVO/FEEDBACK]: [GESTOR SOLICITOU AJUSTE]: aaaaaaaaaaa', '2025-12-13 22:40:31'),
(29, 24, 103, '🚀 Enviou para revisão. feito como solicitado', '2025-12-13 23:20:46'),
(30, 24, 102, '⚠️ Devolveu para ajustes. Tarefa devolvida para ajustes.\n\n[MOTIVO/FEEDBACK]: [GESTOR SOLICITOU AJUSTE]: o link não esta funcionado', '2025-12-13 23:21:16'),
(31, 24, 103, '🚀 Enviou para revisão. feito', '2025-12-13 23:21:40'),
(32, 24, 102, '✅ Aprovou e concluiu a tarefa. Aprovado pelo gestor.', '2025-12-13 23:21:56'),
(33, 24, 101, '⚠️ Líder solicitou refação. Tarefa devolvida pelo líder para revisão.\n\n[MOTIVO/FEEDBACK]: [LÍDER SOLICITOU REFAÇÃO]: preciso que vc melhore isso', '2025-12-13 23:22:23'),
(34, 24, 101, '⚠️ Devolveu para ajustes. Tarefa devolvida para ajustes.\n\n[MOTIVO/FEEDBACK]: [GESTOR SOLICITOU AJUSTE]: [LÍDER SOLICITOU REFAÇÃO]: preciso que vc melhore isso\r\n\r\nFavor ajustar conforme solicitado acima.', '2025-12-13 23:22:42'),
(35, 18, 102, '🚀 Enviou para revisão.', '2025-12-14 00:09:17'),
(36, 18, 102, '[ARQUIVO_ANEXO]:uploads/entregas/entrega_18_693e0035b0f2c.png:Gemini_Generated_Image_uob89yuob89yuob8.png', '2025-12-14 00:09:25'),
(37, 18, 102, '✅ Aprovou e concluiu a tarefa. Aprovado pelo gestor.', '2025-12-14 00:09:38'),
(38, 23, 103, '🚀 Enviou para revisão. feito com sucesso', '2025-12-15 21:49:41');

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
(1, 'Coworking Digital', '2025-12-07 20:07:11', 'uploads/logos/logo_14_1765130704.svg', 'Mudar@123'),
(2, 'teste', '2025-12-15 21:55:43', NULL, '@NomeEmpresa123');

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
(10, 1, 'Board', 'Diretoria e Clientes', '2025-12-07 20:36:04'),
(11, 1, 'Tecnologia', 'Desenvolvimento Fullstack, Backend e Front', '2025-12-07 20:36:04'),
(12, 1, 'Produto & Design', 'Documentação, UX/UI e Requisitos', '2025-12-07 20:36:04'),
(13, 2, 'Geral', NULL, '2025-12-15 21:55:43');

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
(50, 1, 'Plataforma Coworking', 'IFBA', 'Sistema central de gestão de tarefas.', 102, 101, 'EM_ANDAMENTO', '2025-12-07 20:36:04', '2025-12-12 05:37:10', '[{\"titulo\":\"Barema PI 2025.2 (1).docx\",\"url\":\"uploads\\/projetos\\/doc_693ba9692d704.docx\",\"tipo\":\"arquivo\"},{\"titulo\":\"Video demonstrativo\",\"url\":\"https:\\/\\/youtu.be\\/V9PVRfjEBTI?si=Z_BO0PeXB3Gb6nZ7\",\"tipo\":\"link\"}]', '[]', '2025-10-15', NULL, '2025-12-13', 1);

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
(50, 10),
(50, 11),
(50, 12);

-- --------------------------------------------------------

--
-- Estrutura para tabela `solicitacao`
--

CREATE TABLE `solicitacao` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `projeto_id` int(11) DEFAULT NULL,
  `solicitante_id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `status` enum('PENDENTE','EM_ANDAMENTO','RESOLVIDO','RECUSADO') DEFAULT 'PENDENTE',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolvido_em` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `arquivos_tarefa` text DEFAULT NULL,
  `checklist` longtext DEFAULT NULL,
  `feedback_revisao` text DEFAULT NULL,
  `concluida_em` datetime DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tempo_total_minutos` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tarefa`
--

INSERT INTO `tarefa` (`id`, `empresa_id`, `projeto_id`, `titulo`, `descricao`, `prioridade`, `status`, `progresso`, `criador_id`, `responsavel_id`, `prazo`, `arquivos_tarefa`, `checklist`, `feedback_revisao`, `concluida_em`, `criado_em`, `atualizado_em`, `tempo_total_minutos`) VALUES
(1, 1, 50, 'Backend: Login Seguro', 'Implementar Auth e Sessão.', 'URGENTE', 'CONCLUIDA', 100, 101, 101, '2025-12-10 23:59:00', NULL, NULL, NULL, '2025-12-13 18:28:33', '2025-12-07 20:36:04', '2025-12-13 21:28:33', 0),
(2, 1, 50, 'Front-end: Dashboard', 'Criar gráficos e KPIs.', 'URGENTE', 'CONCLUIDA', 0, 101, 102, '2025-12-10 23:59:00', NULL, NULL, NULL, '2025-12-07 17:36:04', '2025-12-07 20:36:04', '2025-12-07 20:36:04', 0),
(3, 1, 50, 'Docs: PDF Final', 'Escrever relatório técnico.', 'NORMAL', 'EM_ANDAMENTO', 0, 102, 104, '2025-12-12 18:00:00', NULL, NULL, NULL, NULL, '2025-12-07 20:36:04', '2025-12-07 20:36:04', 0),
(18, 1, 50, 'Tela Gestão: Atribuir atividades', 'sfsdfsad', 'IMPORTANTE', 'CONCLUIDA', 100, 102, 102, '2025-12-12 23:59:59', NULL, '[{\"id\":1,\"descricao\":\"cscscsc\",\"concluido\":0},{\"id\":2,\"descricao\":\"Desenvolver Aba Checklist\",\"concluido\":0}]', NULL, '2025-12-13 21:09:38', '2025-12-12 08:45:09', '2025-12-14 00:09:38', 0),
(19, 1, 50, 'AULÂO', 'sdasda', 'NORMAL', 'EM_ANDAMENTO', 0, 102, 103, '2025-12-13 23:59:59', NULL, '[{\"id\":1,\"descricao\":\"sdasdsads\",\"concluido\":0}]', '[GESTOR SOLICITOU AJUSTE]: aaaaaaaaaaa', NULL, '2025-12-12 23:36:31', '2025-12-13 22:40:31', 0),
(20, 1, 50, 'teste', 'faça algo interessante para apresentação', 'IMPORTANTE', 'CONCLUIDA', 100, 102, 103, '2025-12-13 23:59:59', NULL, NULL, NULL, '2025-12-12 23:46:36', '2025-12-13 00:30:26', '2025-12-13 02:46:36', 0),
(21, 1, 50, 'casa', 'dasdasd', 'IMPORTANTE', '', 0, 102, 103, '2025-12-14 23:59:59', NULL, '[{\"id\":1,\"descricao\":\"sdasdsads\",\"concluido\":0},{\"id\":2,\"descricao\":\"sdasdsads\",\"concluido\":0}]', NULL, NULL, '2025-12-13 02:55:54', '2025-12-13 02:55:54', 0),
(22, 1, 50, 'Tela Gestão: Atribuir atividadess', 'sssssssssssssssssssss', 'IMPORTANTE', 'EM_ANDAMENTO', 0, 102, 103, '2025-12-12 23:59:59', NULL, NULL, '[GESTOR SOLICITOU AJUSTE]: aaaa', NULL, '2025-12-13 03:00:09', '2025-12-15 21:49:14', 0),
(23, 1, 50, 'TESTE DE CHECKLIST', 'Faça para mim um teste do checklist por gentileza', 'IMPORTANTE', 'EM_REVISAO', 100, 102, 103, '2025-12-13 23:59:59', NULL, '[{\"id\":1,\"descricao\":\"uma foto de como ta\",\"tipo_evidencia\":\"arquivo\",\"formatos\":\"pfvr em png\",\"concluido\":1,\"evidencia_url\":\"uploads\\/checklist\\/chk_23_0_693df2e6e4cbc.png\",\"evidencia_nome\":\"4-Topaz-Gigapixel-escala-2x.png\"},{\"id\":2,\"descricao\":\"me entregue qual foi a experiência em docs\",\"tipo_evidencia\":\"arquivo\",\"formatos\":\"PDF\",\"concluido\":1,\"evidencia_url\":\"uploads\\/checklist\\/chk_23_1_693df2f0e80ee.docx\",\"evidencia_nome\":\"doc_693ba9692d704.docx\"}]', '[GESTOR SOLICITOU AJUSTE]: [LÍDER SOLICITOU REFAÇÃO]: olhe os docs\r\n\r\nFavor ajustar conforme solicitado acima.', '2025-12-13 19:28:03', '2025-12-13 21:09:38', '2025-12-15 21:49:41', 0),
(24, 1, 50, 'crie uma atualização funcional para o logout', 'precisa ser feito bla bla bla', 'URGENTE', 'EM_ANDAMENTO', 100, 102, 103, '2025-12-13 23:59:59', NULL, '[{\"id\":1,\"descricao\":\"mande o link do aquivo atualizado\",\"tipo_evidencia\":\"link\",\"formatos\":\"\",\"concluido\":1,\"evidencia_url\":\"https:\\/\\/gemini.google.com\\/app\\/e177577918b6332c?utm_source=app_launcher&utm_medium=owned&utm_campaign=base_all\",\"evidencia_nome\":\"Link Externo\"}]', '[GESTOR SOLICITOU AJUSTE]: [LÍDER SOLICITOU REFAÇÃO]: preciso que vc melhore isso\r\n\r\nFavor ajustar conforme solicitado acima.', '2025-12-13 20:21:56', '2025-12-13 23:20:07', '2025-12-13 23:22:42', 0),
(25, 1, 50, 'faça apresentação', 'crie apresentação para o PI', 'IMPORTANTE', 'PENDENTE', 0, 101, 103, '2025-12-15 23:59:00', NULL, '[{\"id\":1,\"descricao\":\"Mande o link da apresentação\",\"tipo_evidencia\":\"link\",\"formatos\":\"\",\"concluido\":0,\"evidencia_url\":null,\"evidencia_nome\":null},{\"id\":2,\"descricao\":\"Mande o docs falando sobre o relato de criar a apresentação\",\"tipo_evidencia\":\"arquivo\",\"formatos\":\"PDF\",\"concluido\":0,\"evidencia_url\":null,\"evidencia_nome\":null}]', NULL, NULL, '2025-12-15 21:53:45', '2025-12-15 21:53:45', 0);

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
(1, 105, '9608', '2025-12-13 01:22:56', '2025-12-12 21:08:24', '2025-12-13 00:07:56');

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
(99, 1, 10, 1, 'Coworking Admin', 'admin@coworking.com', 'Sponsor / Product Owner', '$2y$10$E0ZA8KjOTq6j1X1twNbEpenY9TMjbyW4L1wT4BCY13Ya5/z0IXjfS', 1, 0, '2025-12-07 20:36:04', '2025-12-13 21:51:34', '2025-12-13 18:51:34'),
(101, 1, 11, 1, 'Victor Hugo Santana', 'victor@coworking.com', 'Tech Lead / Head of Dev', '$2y$10$kb7hXgKE7kN.U8eNHxfeYullgIdUcdsf7FPxY/Vkx.5HJfOpvcbmC', 1, 0, '2025-12-07 20:36:04', '2025-12-15 21:56:04', '2025-12-15 18:56:04'),
(102, 1, 12, 2, 'Jhon Abner Santos', 'jhon@coworking.com', 'Head of Product / Front', '$2y$10$E0ZA8KjOTq6j1X1twNbEpenY9TMjbyW4L1wT4BCY13Ya5/z0IXjfS', 1, 0, '2025-12-07 20:36:04', '2025-12-15 22:14:31', '2025-12-15 19:14:31'),
(103, 1, 11, 3, 'Queteli Tourinho', 'queteli@coworking.com', 'Dev Frontend', '$2y$10$E0ZA8KjOTq6j1X1twNbEpenY9TMjbyW4L1wT4BCY13Ya5/z0IXjfS', 1, 0, '2025-12-07 20:36:04', '2025-12-15 22:13:03', '2025-12-15 19:13:03'),
(104, 1, 12, 3, 'Maria Eduarda Lima', 'duda@coworking.com', 'Analista de Docs', '$2y$10$E0ZA8KjOTq6j1X1twNbEpenY9TMjbyW4L1wT4BCY13Ya5/z0IXjfS', 1, 0, '2025-12-07 20:36:04', '2025-12-07 20:36:04', NULL),
(105, 1, 12, 3, 'Ian Marcos Rocha', 'ian@coworking.com', 'Product Designer', '$2y$10$sv4Kp03TkedS7N8SdNURFu529PlsYPlOwIJ5Ylt9bnutL2jyFU.dO', 1, 0, '2025-12-07 20:36:04', '2025-12-15 21:45:10', '2025-12-12 21:08:31'),
(107, 2, 13, 1, 'teste', 'teste@teste.com', 'CEO', '$2y$10$ZlNNxPca7Gjci94fz9.wgeNfrKs2fVPe9XpzLMNlBB3hk0OD3LwGG', 1, 0, '2025-12-15 21:55:43', '2025-12-15 21:55:50', '2025-12-15 18:55:50');

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
-- Índices de tabela `solicitacao`
--
ALTER TABLE `solicitacao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`),
  ADD KEY `solicitante_id` (`solicitante_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de tabela `emergencia`
--
ALTER TABLE `emergencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `empresa`
--
ALTER TABLE `empresa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `equipe`
--
ALTER TABLE `equipe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `papel`
--
ALTER TABLE `papel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `projeto`
--
ALTER TABLE `projeto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de tabela `solicitacao`
--
ALTER TABLE `solicitacao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tarefa`
--
ALTER TABLE `tarefa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de tabela `token_recuperacao_senha`
--
ALTER TABLE `token_recuperacao_senha`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

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
-- Restrições para tabelas `solicitacao`
--
ALTER TABLE `solicitacao`
  ADD CONSTRAINT `solicitacao_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `solicitacao_ibfk_2` FOREIGN KEY (`solicitante_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE;

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
