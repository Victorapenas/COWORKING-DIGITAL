// /js/login.js

document.addEventListener('DOMContentLoaded', () => {
    const formLogin = document.getElementById('formLogin');
    if (!formLogin) return;

    const emailInput = document.getElementById('email');
    const senhaInput = document.getElementById('senha');
    const btnEntrar = document.getElementById('btnEntrar');
    const erroGeralDiv = document.getElementById('erroGeral');

    const exibirErroGeral = (mensagem) => {
        erroGeralDiv.textContent = mensagem;
        erroGeralDiv.style.display = 'block';
    };

    formLogin.addEventListener('submit', async (e) => {
        e.preventDefault();
        btnEntrar.disabled = true;
        btnEntrar.textContent = "Entrando..."; // Feedback visual
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
                // --- AQUI ESTÁ A MUDANÇA: Vai para equipes.php ---
                window.location.href = 'equipes.php';
            } else if (resultado.primeiro_acesso) {
                sessionStorage.setItem('recuperacao_email', resultado.email);
                alert(resultado.mensagem);
                let urlDestino = 'verificar_codigo.php';
                if (resultado.codigo_debug) {
                    urlDestino += `?code=${resultado.codigo_debug}`;
                }
                window.location.href = urlDestino;
            } else {
                exibirErroGeral(resultado.erro || 'E-mail ou senha incorretos.');
            }

        } catch (erro) {
            exibirErroGeral('Erro de conexão ou servidor.');
            console.error('Erro de login:', erro);
        } finally {
            btnEntrar.disabled = false;
            btnEntrar.textContent = "Entrar";
        }
    });
});