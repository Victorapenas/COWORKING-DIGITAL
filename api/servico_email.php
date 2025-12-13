<?php
//atualização
// ARQUIVO: api/servico_email.php

function enviar_email_codigo($destinatario, $nome, $codigo) {
    
    // --- 1. TENTATIVA DE ENVIO REAL (PHP MAIL) ---
    // Nota: Em localhost (XAMPP), isso requer configuração do sendmail.ini e php.ini
    // para funcionar com Gmail/Outlook. Em servidor real (hospedagem), funciona nativamente.
    
    $assunto = "Seu codigo de acesso - Coworking Digital";
    
    $mensagem = "
    <html>
    <head>
      <title>Código de Verificação</title>
    </head>
    <body style='font-family: Arial, sans-serif;'>
      <div style='padding: 20px; border: 1px solid #ddd; border-radius: 10px; max-width: 500px;'>
        <h2 style='color: #6A66FF;'>Olá, $nome!</h2>
        <p>Use o código abaixo para liberar seu acesso:</p>
        <h1 style='background: #f4f5f7; padding: 10px; text-align: center; letter-spacing: 5px; border-radius: 5px;'>$codigo</h1>
        <p style='color: #777; font-size: 12px;'>Se você não solicitou este código, ignore este e-mail.</p>
      </div>
    </body>
    </html>
    ";

    // Cabeçalhos para e-mail HTML correto
    $headers  = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    $headers .= "From: Coworking Digital <no-reply@coworking.com>" . "\r\n";

    // Tenta enviar (silencia erros com @ para não quebrar a API se falhar)
    $enviou = @mail($destinatario, $assunto, $mensagem, $headers);

    // --- 2. BACKUP: SALVAR NO LOG (ESSENCIAL PARA DESENVOLVIMENTO) ---
    // Como o envio real falha em 99% dos localhosts sem configuração complexa,
    // salvamos aqui para você (desenvolvedor) não ficar travado.
    try {
        $pasta_logs = __DIR__ . '/../logs';
        if (!is_dir($pasta_logs)) {
            mkdir($pasta_logs, 0777, true);
        }
        
        $arquivo_log = $pasta_logs . '/emails_enviados.txt';
        $data_hora = date('d/m/Y H:i:s');
        
        // Se o mail() retornou false, avisamos no log
        $status_envio = $enviou ? "[ENVIADO]" : "[FALHA NO ENVIO REAL - REQUER SMTP]";
        
        $conteudo = "$status_envio [$data_hora] Para: $destinatario | Código: $codigo" . PHP_EOL;
        
        file_put_contents($arquivo_log, $conteudo, FILE_APPEND);
    } catch (Exception $e) {
        // Ignora erro de arquivo
    }

    return true; // Retorna true para o front-end prosseguir para a tela de código
}
?>