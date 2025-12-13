<?php
//atualização
// ARQUIVO: public/fix_senha.php
require_once __DIR__ . '/../config/conexao.php';

try {
    $pdo = conectar_db();
    
    // Senha que você quer usar
    $novaSenha = 'Mudar@123';
    $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
    
    // Atualiza TODOS os usuários para essa senha
    $pdo->query("UPDATE usuario SET senha_hash = '$hash'");
    
    echo "<h1 style='color:green'>Sucesso!</h1>";
    echo "<p>A senha de TODOS os usuários foi redefinida para: <strong>$novaSenha</strong></p>";
    echo "<a href='login.php'>Ir para Login</a>";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>