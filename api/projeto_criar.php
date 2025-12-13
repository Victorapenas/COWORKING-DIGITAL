<?php
//atualização
// ARQUIVO: api/projeto_criar.php
ob_start(); 
ini_set('display_errors', 0); 
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/funcoes_api.php'; 
require_once __DIR__ . '/../includes/seguranca.php';

try {
    if (!esta_logado()) throw new Exception("Sessão expirada.");
    $sessao = $_SESSION[SESSAO_USUARIO_KEY];
    
    // Permissões
    if (!in_array($sessao['papel'], ['DONO', 'LIDER', 'GESTOR'])) {
        throw new Exception("Permissão negada.");
    }

    // AÇÃO: CRIAÇÃO. NENHUM ID é necessário neste ponto.

    // Coleta dados Básicos
    $empresaId = getEmpresaIdLogado($sessao); // ID da empresa para vinculação
    $nome = trim($_POST['nome'] ?? '');
    $cliente = trim($_POST['cliente'] ?? '');
    $desc = trim($_POST['descricao'] ?? '');
    $inicio = !empty($_POST['data_inicio']) ? $_POST['data_inicio'] : null;
    $fim = !empty($_POST['data_fim']) ? $_POST['data_fim'] : null;
    $status = $_POST['status'] ?? 'PLANEJADO';

    // Validação Mínima
    if (empty($nome)) throw new Exception("O nome do projeto é obrigatório.");
    
    // Equipes: Recebe array ou string
    $equipes = [];
    if (isset($_POST['equipes'])) {
        $equipes = is_array($_POST['equipes']) ? $_POST['equipes'] : explode(',', $_POST['equipes']);
    }

    $pdo = conectar_db();

    // 1. Inicializa Links e Arquivos (Projeto Novo)
    // Não precisa buscar dados antigos. Começa com arrays vazios.
    $novosLinks = [];
    $novosPrivados = [];

    // 2. Adiciona Novos Links de Texto (Lógica reusada)
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
    
    // 3. Upload de Novos Arquivos (Lógica reusada)
    $uploadDir = __DIR__ . '/../public/uploads/projetos/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    
    if (isset($_FILES['docs_publicos'])) {
        foreach ($_FILES['docs_publicos']['name'] as $i => $name) {
            if ($_FILES['docs_publicos']['error'][$i] === 0) {
                $novoNome = 'doc_' . uniqid() . '.' . pathinfo($name, PATHINFO_EXTENSION);
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
    if (isset($_FILES['docs_privados'])) {
        foreach ($_FILES['docs_privados']['name'] as $i => $name) {
            if ($_FILES['docs_privados']['error'][$i] === 0) {
                $novoNome = 'priv_' . uniqid() . '.' . pathinfo($name, PATHINFO_EXTENSION);
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

    // 4. INSERT SQL Principal (MUDANÇA AQUI)
    // Insere os dados e as strings JSON dos links/arquivos
    $sql = "INSERT INTO projeto 
                (empresa_id, nome, cliente_nome, descricao, data_inicio, data_fim, status, links_externos, arquivos_privados, criado_em, atualizado_em)
            VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $empresaId, // Adicionado para vincular à empresa
        $nome, 
        $cliente, 
        $desc, 
        $inicio, 
        $fim, 
        $status, 
        json_encode(array_values($novosLinks)),
        json_encode(array_values($novosPrivados))
    ]);

    // 5. OBTÉM o ID recém-criado (MUDANÇA AQUI)
    $novoId = $pdo->lastInsertId();
    if (!$novoId) throw new Exception("Falha ao obter ID do novo projeto.");

    // 6. Insere Equipes usando o NOVO ID (MUDANÇA AQUI)
    // Não precisa de DELETE, pois o projeto é novo.
    if (!empty($equipes)) {
        $stmtEq = $pdo->prepare("INSERT INTO projeto_equipe (projeto_id, equipe_id) VALUES (?, ?)");
        foreach ($equipes as $eqId) {
            if($eqId) $stmtEq->execute([$novoId, $eqId]); // Usa $novoId
        }
    }

    echo json_encode(['ok' => true, 'id' => $novoId, 'mensagem' => 'Projeto criado com sucesso!']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}
?>