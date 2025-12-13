<?php
//atualização
// /config/config.php

// Constantes de Configuração do Projeto
define('DB_HOST', 'localhost');
define('DB_NAME', 'coworking');
define('DB_USER', 'root'); // Mude para seu usuário de DB
define('DB_PASS', '');     // Mude para sua senha de DB

// FLAGS de Funcionalidade
// Define se a tela de registro de novos usuários estará disponível
define('PERMITIR_CADASTRO', true); 

// Tempo de expiração do código de recuperação de senha (em minutos)
define('CODIGO_EXPIRACAO_MINUTOS', 15);

// URL Base para chamadas de API
define('BASE_API_URL', '/api');

// Delay para Rate Limiting simples (em milissegundos)
define('RATE_LIMIT_DELAY_MS', 800); 

// Chave para identificar o usuário na sessão (segurança)
define('SESSAO_USUARIO_KEY', 'usuario_logado');