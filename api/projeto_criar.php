<?php
// ARQUIVO: api/projeto_editar.php
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

    $id = (int)($_POST['id'] ?? 0);
    if (!$id) throw new Exception("ID do projeto inválido.");

    // Coleta dados Básicos
    $nome = trim($_POST['nome'] ?? '');
    $cliente = trim($_POST['cliente'] ?? '');
    $desc = trim($_POST['descricao'] ?? '');
    $inicio = !empty($_POST['data_inicio']) ? $_POST['data_inicio'] : null;
    $fim = !empty($_POST['data_fim']) ? $_POST['data_fim'] : null;
    $status = $_POST['status'] ?? 'PLANEJADO';
    
    // Equipes: Recebe array ou string
    $equipes = [];
    if (isset($_POST['equipes'])) {
        $equipes = is_array($_POST['equipes']) ? $_POST['equipes'] : explode(',', $_POST['equipes']);
    }

    $pdo = conectar_db();

    // 1. Recupera Links/Arquivos Atuais para não perder os antigos
    $stmt = $pdo->prepare("SELECT links_externos, arquivos_privados FROM projeto WHERE id = ?");
    $stmt->execute([$id]);
    $antigo = $stmt->fetch();
    
    $linksAtuais = !empty($antigo['links_externos']) ? json_decode($antigo['links_externos'], true) : [];
    $privadosAtuais = !empty($antigo['arquivos_privados']) ? json_decode($antigo['arquivos_privados'], true) : [];

    // Filtra: Removemos APENAS os links de texto antigos, pois eles serão recriados pelo formulário.
    // Mantemos os arquivos (tipo != 'link') e logos.
    $novosLinks = array_filter($linksAtuais, fn($l) => isset($l['tipo']) && $l['tipo'] !== 'link'); 
    $novosPrivados = array_filter($privadosAtuais, fn($l) => isset($l['tipo']) && $l['tipo'] !== 'link');

    // 2. Adiciona Novos Links de Texto
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
    
    // 3. Upload de Novos Arquivos
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

    // 4. Update SQL Principal
    $sql = "UPDATE projeto SET nome=?, cliente_nome=?, descricao=?, data_inicio=?, data_fim=?, status=?, links_externos=?, arquivos_privados=?, atualizado_em=NOW() WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $nome, 
        $cliente, 
        $desc, 
        $inicio, 
        $fim, 
        $status, 
        json_encode(array_values($novosLinks)), // array_values para reindexar chaves
        json_encode(array_values($novosPrivados)), 
        $id
    ]);

    // 5. Atualiza Equipes (Remove antigas e insere novas)
    $pdo->prepare("DELETE FROM projeto_equipe WHERE projeto_id=?")->execute([$id]);
    
    if (!empty($equipes)) {
        $stmtEq = $pdo->prepare("INSERT INTO projeto_equipe (projeto_id, equipe_id) VALUES (?, ?)");
        foreach ($equipes as $eqId) {
            if($eqId) $stmtEq->execute([$id, $eqId]);
        }
    }

    echo json_encode(['ok' => true, 'mensagem' => 'Projeto atualizado com sucesso!']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}
?>