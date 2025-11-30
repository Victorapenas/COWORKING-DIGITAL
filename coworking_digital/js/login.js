// /js/login.js

document.addEventListener('DOMContentLoaded', () => {
    const formLogin = document.getElementById('formLogin');
    if (!formLogin) return;

    const emailInput = document.getElementById('email');
    const senhaInput = document.getElementById('senha');
    const btnEntrar = document.getElementById('btnEntrar');
    const erroGeralDiv = document.getElementById('erroGeral');

    // Função para exibir mensagem de erro simples
    const exibirErroGeral = (mensagem) => {
        erroGeralDiv.textContent = mensagem;
        erroGeralDiv.style.display = 'block';
    };

    formLogin.addEventListener('submit', async (e) => {
        e.preventDefault();
        btnEntrar.disabled = true;
        erroGeralDiv.style.display = 'none';

        const dados = {
            email: emailInput.value,
            senha: senhaInput.value
        };

        try {
            const resposta = await fetch('../api/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            });

            const resultado = await resposta.json();

            if (resultado.ok) {
                // Login com sucesso
                window.location.href = 'painel.php';
            } else if (resultado.primeiro_acesso) {
                // === FLUXO DE PRIMEIRO ACESSO ===
                // O usuário usou a senha padrão corretamente, mas precisa redefinir.
                // Salvamos o e-mail para a próxima tela usar
                sessionStorage.setItem('recuperacao_email', resultado.email);
                
                alert(resultado.mensagem); // "Para sua segurança, valide seu e-mail..."

                // Redireciona para a tela que já existe no seu projeto (verificar_codigo.php)
                // Se for ambiente de teste, passamos o código na URL para facilitar
                let urlDestino = 'verificar_codigo.php';
                if (resultado.codigo_debug) {
                    urlDestino += `?code=${resultado.codigo_debug}`;
                }
                
                window.location.href = urlDestino;

            } else {
                // Erro normal (senha errada, etc.)
                exibirErroGeral(resultado.erro || 'E-mail ou senha incorretos.');
            }

        } catch (erro) {
            exibirErroGeral('Erro de conexão. Tente novamente.');
            console.error('Erro de login:', erro);
        } finally {
            btnEntrar.disabled = false;
        }
    });
});