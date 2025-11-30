<?php
// /includes/funcoes.php
require_once __DIR__ . '/../config/conexao.php';

/**
 * Normaliza e valida um e-mail.
 * @param string $email
 * @return string|false E-mail normalizado ou false se inválido.
 */
function validar_email(string $email): string|false {
    $email = strtolower(trim($email));
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
}

/**
 * Valida o formato da senha (mínimo 8 caracteres).
 * @param string $senha
 * @return bool
 */
function validar_senha(string $senha): bool {
    // Pode adicionar regras mais complexas aqui (ex: maiúsculas, números, etc.)
    return strlen($senha) >= 8;
}

/**
 * Valida o formato do código de 4 dígitos.
 * @param string $codigo
 * @return bool
 */
function validar_codigo_recuperacao(string $codigo): bool {
    return is_numeric($codigo) && strlen($codigo) === 4;
}

/**
 * Simples rate limiting para API em caso de falha de login.
 */
function aplicar_rate_limit() {
    // RATE_LIMIT_DELAY_MS deve estar definido em algum config.php
    if (defined('RATE_LIMIT_DELAY_MS')) {
        usleep(RATE_LIMIT_DELAY_MS * 1000); // Dorme por N milissegundos
    }
}

/**
 * Retorna uma resposta JSON de erro.
 * @param string $mensagem
 * @param int $http_code
 */
function responder_erro(string $mensagem, int $http_code = 400) {
    http_response_code($http_code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'erro' => $mensagem], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Redireciona o usuário para uma URL.
 *
 * @param string $url URL absoluta ou relativa
 * @param bool   $permanente Se true, usa status 301; senão, 302
 * @return void
 */
function redirecionar(string $url, bool $permanente = false): void
{
    if (headers_sent()) {
        // Fallback caso os headers já tenham sido enviados
        echo "<script>window.location.href = '" . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "';</script>";
        exit;
    }

    header('Location: ' . $url, true, $permanente ? 301 : 302);
    exit;
}

/**
 * Busca todos os usuários com papel GESTOR e DONO (Liderança Executiva).
 * @return array Lista de membros
 */
function getMembrosLideranca(): array {
    $pdo = conectar_db();
    $sql = "SELECT u.nome, u.email, p.nome as papel_nome, u.criado_em 
            FROM usuario u
            JOIN papel p ON u.papel_id = p.id
            WHERE p.nome IN ('DONO', 'GESTOR') AND u.ativo = 1
            ORDER BY p.nivel_hierarquia DESC, u.nome ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Busca todos os usuários com papel FUNCIONARIO e os agrupa pelo Cargo Detalhado.
 * @return array Um array associativo: [cargo_detalhe => [membro1, membro2], ...]
 */
function getMembrosFuncionarios(): array {
    $pdo = conectar_db();
    
    $sql = "SELECT u.nome, u.email, u.cargo_detalhe, p.nome as papel_nome, u.criado_em 
            FROM usuario u
            JOIN papel p ON u.papel_id = p.id
            WHERE p.nome = 'FUNCIONARIO' AND u.ativo = 1
            ORDER BY u.cargo_detalhe ASC, u.nome ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $membrosAgrupados = [];
    foreach ($resultados as $membro) {
        $cargoDetalhe = $membro['cargo_detalhe'] ?? 'Não Definido/Genérico'; 
        
        if (!isset($membrosAgrupados[$cargoDetalhe])) {
            $membrosAgrupados[$cargoDetalhe] = [];
        }
        $membro['papel_display'] = 'Membro';
        $membrosAgrupados[$cargoDetalhe][] = $membro;
    }
    
    return $membrosAgrupados;
}

/**
 * Busca usuário pelo ID.
 */
function buscarUsuarioPorId(int $id): ?array {
    $pdo = conectar_db();
    $sql = "SELECT id, nome, email, cargo_detalhe, empresa_id
            FROM usuario
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    return $usuario ?: null;
}

/**
 * Atualiza os dados básicos do usuário na tela de configurações.
 */
function atualizarUsuarioBasico(int $id, string $nome, ?string $cargoDetalhe = null): bool {
    $pdo = conectar_db();
    $sql = "UPDATE usuario
            SET nome = ?, cargo_detalhe = ?
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$nome, $cargoDetalhe, $id]);
}

/**
 * Busca os dados da empresa pelo ID.
 */
function buscarEmpresaPorId(int $empresaId): ?array {
    $pdo = conectar_db();
    $sql = "SELECT id, nome
            FROM empresa
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$empresaId]);
    $empresa = $stmt->fetch(PDO::FETCH_ASSOC);
    return $empresa ?: null;
}

/**
 * Atualiza o nome da empresa na tela de configurações.
 */
function atualizarNomeEmpresa(int $empresaId, string $novoNome): bool {
    $pdo = conectar_db();
    $sql = "UPDATE empresa
            SET nome = ?
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$novoNome, $empresaId]);
}
