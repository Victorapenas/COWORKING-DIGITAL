<?php
// ARQUIVO: api/projeto_criar.php
// ATUALIZADO: Tratamento de erros robusto e limpeza de buffer para evitar JSON inválido

// 1. Inicia o buffer para capturar qualquer saída indesejada (erros/avisos)
ob_start(); 
ini_set('display_errors', 0); 
error_reporting(E_ALL);

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/funcoes_api.php'; 
require_once __DIR__ . '/../includes/seguranca.php';

try {
    if (!esta_logado()) throw new Exception("Sessão expirada.");
    $sessao = $_SESSION[SESSAO_USUARIO_KEY];
    
    if (!in_array($sessao['papel'], ['DONO', 'LIDER', 'GESTOR'])) {
        throw new Exception("Permissão negada.");
    }

    $empresaId = getEmpresaIdLogado($sessao);
    $nome = trim($_POST['nome'] ?? '');
    
    if (empty($nome)) throw new Exception("O nome do projeto é obrigatório.");

    // Tratamento seguro das datas (envia NULL se estiver vazio)
    $inicio = !empty($_POST['data_inicio']) ? $_POST['data_inicio'] : null;
    $fim    = !empty($_POST['data_fim']) ? $_POST['data_fim'] : null;
    
    // Tratamento seguro de Equipes (pode não vir nada, ou vir como array/string)
    $equipes = [];
    if (isset($_POST['equipes'])) {
        if (is_array($_POST['equipes'])) {
            $equipes = $_POST['equipes'];
        } else {
            $equipes = explode(',', $_POST['equipes']);
        }
    }

    $pdo = conectar_db();

    // Arrays vazios para links e arquivos (Projeto Novo)
    $novosLinks = [];
    $novosPrivados = [];

    // 2. Adiciona Novos Links de Texto (se houver)
    if (isset($_POST['link_titulo'])) {
        foreach ($_POST['link_titulo'] as $i => $titulo) {
            if (!empty($titulo)) {
                $novosLinks[] = [
                    'titulo' => $titulo, 
                    'url' => $_POST['link_url'][$i], 
                    'tipo' => 'link'
                ];
            }
        }
    }
    if (isset($_POST['link_priv_titulo'])) {
        foreach ($_POST['link_priv_titulo'] as $i => $titulo) {
            if (!empty($titulo)) {
                $novosPrivados[] = [
                    'titulo' => $titulo, 
                    'url' => $_POST['link_priv_url'][$i], 
                    'tipo' => 'link'
                ];
            }
        }
    }
    
    // 3. Upload de Arquivos
    $uploadDir = __DIR__ . '/../public/uploads/projetos/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    
    // Arquivos Públicos
    if (isset($_FILES['docs_publicos'])) {
        foreach ($_FILES['docs_publicos']['name'] as $i => $name) {
            if ($_FILES['docs_publicos']['error'][$i] === 0) {
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $novoNome = 'doc_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['docs_publicos']['tmp_name'][$i], $uploadDir . $novoNome)) {
                    $novosLinks[] = [
                        'titulo' => $name, 
                        'url' => 'uploads/projetos/' . $novoNome, 
                        'tipo' => 'arquivo'
                    ];
                }
            }
        }
    }
    
    // Arquivos Privados
    if (isset($_FILES['docs_privados'])) {
        foreach ($_FILES['docs_privados']['name'] as $i => $name) {
            if ($_FILES['docs_privados']['error'][$i] === 0) {
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $novoNome = 'priv_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['docs_privados']['tmp_name'][$i], $uploadDir . $novoNome)) {
                    $novosPrivados[] = [
                        'titulo' => $name, 
                        'url' => 'uploads/projetos/' . $novoNome, 
                        'tipo' => 'arquivo'
                    ];
                }
            }
        }
    }

    // 4. INSERT SQL Principal
    $sql = "INSERT INTO projeto 
                (empresa_id, nome, cliente_nome, descricao, data_inicio, data_fim, status, links_externos, arquivos_privados, criado_em, atualizado_em)
            VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
    $stmt = $pdo->prepare($sql);
    $params = [
        $empresaId,
        $nome, 
        trim($_POST['cliente'] ?? ''), 
        trim($_POST['descricao'] ?? ''), 
        $inicio, 
        $fim, 
        $_POST['status'] ?? 'PLANEJADO', 
        json_encode(array_values($novosLinks)), // Envia JSON, mesmo que vazio
        json_encode(array_values($novosPrivados))
    ];

    if (!$stmt->execute($params)) {
        throw new Exception("Erro ao salvar no banco de dados.");
    }

    $novoId = $pdo->lastInsertId();

    // 5. Inserção de Equipes
    if (!empty($equipes)) {
        $stmtEq = $pdo->prepare("INSERT INTO projeto_equipe (projeto_id, equipe_id) VALUES (?, ?)");
        foreach ($equipes as $eqId) {
            if((int)$eqId > 0) $stmtEq->execute([$novoId, $eqId]);
        }
    }

    // Limpa o buffer para garantir que SÓ O JSON seja enviado
    ob_end_clean();
    echo json_encode(['ok' => true, 'id' => $novoId, 'mensagem' => 'Projeto criado com sucesso!']);

} catch (Exception $e) {
    ob_end_clean(); // Limpa buffer de erro também antes de enviar a resposta
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}
?>