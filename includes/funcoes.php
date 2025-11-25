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
    usleep(RATE_LIMIT_DELAY_MS * 1000); // Dorme por N milissegundos
}

/**
 * Retorna uma resposta JSON de erro.
 * @param string $mensagem
 * @param int $http_code
 */
function responder_erro(string $mensagem, int $http_code = 400) {
    http_response_code($http_code);
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'erro' => $mensagem]);
    exit;
}

/**
 * Redireciona o usuário para uma URL.
 * @param string $url
 */
function redirecionar(string $url) {
    header('Location: ' . $url);
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
 * Requer que a coluna 'cargo_detalhe' exista na tabela 'usuario'.
 * @return array Um array associativo: [cargo_detalhe => [membro1, membro2], ...]
 */
function getMembrosFuncionarios(): array {
    $pdo = conectar_db();
    
    // Agora usando a nova coluna 'u.cargo_detalhe'
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
        // Usa o valor da coluna 'cargo_detalhe' como chave para agrupar
        $cargoDetalhe = $membro['cargo_detalhe'] ?? 'Não Definido/Genérico'; 
        
        if (!isset($membrosAgrupados[$cargoDetalhe])) {
            $membrosAgrupados[$cargoDetalhe] = [];
        }
        // Adiciona a coluna papel_display ao array do membro para ser usada no HTML
        $membro['papel_display'] = 'Membro';
        $membrosAgrupados[$cargoDetalhe][] = $membro;
    }
    
    return $membrosAgrupados;
}