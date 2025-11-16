// /js/login.js

document.addEventListener('DOMContentLoaded', () => {
    const formLogin = document.getElementById('formLogin');
    if (!formLogin) return;

    const emailInput = document.getElementById('email');
    const senhaInput = document.getElementById('senha');
    const btnEntrar = document.getElementById('btnEntrar');
    const erroGeralDiv = document.getElementById('erroGeral');

    // Função para exibir mensagem de erro específica de campo
    const exibirErroCampo = (inputElement, mensagem) => {
        const campo = inputElement.closest('.campo-form');
        let erroDiv = campo.querySelector('.mensagem-erro');
        if (!erroDiv) {
            erroDiv = document.createElement('div');
            erroDiv.classList.add('mensagem-erro');
            campo.appendChild(erroDiv);
        }
        campo.classList.add('erro');
        erroDiv.textContent = mensagem;
    };

    // Função para limpar erro de campo
    const limparErroCampo = (inputElement) => {
        const campo = inputElement.closest('.campo-form');
        campo.classList.remove('erro');
        const erroDiv = campo.querySelector('.mensagem-erro');
        if (erroDiv) erroDiv.textContent = '';
    };

    // Função para exibir erro geral (não de campo específico)
    const exibirErroGeral = (mensagem) => {
        erroGeralDiv.textContent = mensagem;
        erroGeralDiv.style.display = 'block';
    };

    // Validação básica do lado do cliente
    const validarFormulario = () => {
        let valido = true;

        limparErroCampo(emailInput);
        limparErroCampo(senhaInput);
        erroGeralDiv.style.display = 'none';

        if (!emailInput.value.trim() || !emailInput.value.includes('@')) {
            exibirErroCampo(emailInput, 'E-mail inválido.');
            valido = false;
        }
        if (senhaInput.value.length < 8) {
            exibirErroCampo(senhaInput, 'Senha deve ter no mínimo 8 caracteres.');
            valido = false;
        }

        return valido;
    };

    formLogin.addEventListener('submit', async (e) => {
        e.preventDefault();
        btnEntrar.disabled = true;

        if (!validarFormulario()) {
            btnEntrar.disabled = false;
            return;
        }

        const dados = {
            email: emailInput.value,
            senha: senhaInput.value
        };

        try {
            const resposta = await fetch('/api/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            });

            const resultado = await resposta.json();

            if (resultado.ok) {
                // Sucesso, redireciona para o painel
                window.location.href = 'painel.php';
            } else {
                // Falha de login (e-mail ou senha incorretos, inativo, etc.)
                exibirErroGeral(resultado.erro || 'E-mail ou senha incorretos.');
            }

        } catch (erro) {
            exibirErroGeral('Ocorreu um erro de rede. Tente novamente.');
            console.error('Erro de login:', erro);
        } finally {
            btnEntrar.disabled = false;
        }
    });
});