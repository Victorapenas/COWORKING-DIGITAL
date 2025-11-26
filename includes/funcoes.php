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
 * Tenta obter o ID da empresa da sessão. Se não conseguir, busca no banco 
 * usando o ID do usuário logado como contingência.
 *
 * NOTA: Esta função inclui 'conexao.php' APENAS se precisar consultar o banco.
 * * @param array $usuario O array da sessão contendo os dados do usuário.
 * @return int O ID da empresa logada (ou 0 em caso de falha).
 */
function getEmpresaIdLogado(array $usuario): int {
    // 1. Tenta obter o ID da sessão com fallback para 0 (zero) se a chave não existir.
    $empresaId = (int) ($usuario['empresa_id'] ?? 0); 

    // 2. Lógica de contingência: Se o ID não veio da sessão, busca no banco
    if ($empresaId === 0 && isset($usuario['id'])) {
        // Incluir conexao.php APENAS aqui, para acesso ao conectar_db()

        $pdo = conectar_db();
        
        $stmt = $pdo->prepare("SELECT empresa_id FROM usuario WHERE id = ?");
        $stmt->execute([$usuario['id']]);
        
        // Converte o resultado para inteiro.
        $empresaId = (int) $stmt->fetchColumn(); 
        
        // Opcional: Atualiza a sessão para uso futuro
        if ($empresaId > 0) {
            $_SESSION[SESSAO_USUARIO_KEY]['empresa_id'] = $empresaId;
        }
    }

    return $empresaId;
}


/**
 * Busca todos os usuários com papel GESTOR e DONO (Liderança Executiva)
 * para uma empresa específica.
 *
 * @param int $empresaId O ID da empresa para filtrar os membros.
 * @return array Lista de membros
 */
function getMembrosLideranca(int $empresaId): array {
    // A função conectar_db() deve ser acessível neste escopo (assumindo que conexao.php 
    // está incluído onde funcoes.php é usado ou a conexão é global/importada).
    // Se conectar_db() estiver em conexao.php, ele deve ser incluído aqui
    // se esta função for chamada diretamente.
    $pdo = conectar_db();
    
    // ... (restante da sua query)
    $sql = "SELECT u.nome, u.email, p.nome as papel_nome, u.criado_em 
            FROM usuario u
            JOIN papel p ON u.papel_id = p.id
            WHERE p.nome IN ('DONO', 'GESTOR') 
            AND u.ativo = 1
            AND u.empresa_id = :empresaId
            ORDER BY p.nivel_hierarquia DESC, u.nome ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':empresaId', $empresaId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


/**
 * Busca todos os usuários com papel FUNCIONARIO de uma empresa específica e
 * os agrupa pelo Cargo Detalhado.
 *
 * @param int $empresaId O ID da empresa para filtrar os membros.
 * @return array Um array associativo: [cargo_detalhe => [membro1, membro2], ...]
 */
function getMembrosFuncionarios(int $empresaId): array {
    // Incluir conexão se não for garantida globalmente
    $pdo = conectar_db();
    
    // ... (restante da sua query)
    $sql = "SELECT u.nome, u.email, u.cargo_detalhe, p.nome as papel_nome, u.criado_em 
            FROM usuario u
            JOIN papel p ON u.papel_id = p.id
            WHERE p.nome = 'FUNCIONARIO' 
            AND u.ativo = 1
            AND u.empresa_id = :empresaId
            ORDER BY u.cargo_detalhe ASC, u.nome ASC";
    
    // ... (restante da lógica de agrupamento)
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':empresaId', $empresaId, PDO::PARAM_INT);
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

?>