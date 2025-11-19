// /js/recuperar.js

document.addEventListener('DOMContentLoaded', () => {
    // --- Funções de Ajuda ---
    const getElement = (id) => document.getElementById(id);

    const exibirErroCampo = (inputElement, mensagem) => {
        const campo = inputElement.closest('.campo-form') || inputElement.closest('.campo-nova-senha');
        if (!campo) return;
        let erroDiv = campo.querySelector('.mensagem-erro');
        if (!erroDiv) {
            erroDiv = document.createElement('div');
            erroDiv.classList.add('mensagem-erro');
            campo.appendChild(erroDiv);
        }
        campo.classList.add('erro');
        erroDiv.textContent = mensagem;
    };

    const limparErroCampo = (inputElement) => {
        const campo = inputElement.closest('.campo-form') || inputElement.closest('.campo-nova-senha');
        if (!campo) return;
        campo.classList.remove('erro');
        const erroDiv = campo.querySelector('.mensagem-erro');
        if (erroDiv) erroDiv.textContent = '';
    };

    const exibirErroGeral = (mensagem) => {
        const erroDiv = getElement('erroGeral');
        if (erroDiv) {
            erroDiv.textContent = mensagem;
            erroDiv.style.display = 'block';
        }
    };

    // --- Lógica de Recuperação de Senha (esqueci_senha.php) ---
    const formEsqueciSenha = getElement('formEsqueciSenha');
    if (formEsqueciSenha) {
        formEsqueciSenha.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = getElement('btnContinuar');
            const emailInput = getElement('email');
            btn.disabled = true;
            limparErroCampo(emailInput);
            exibirErroGeral('');

            if (!emailInput.value.trim() || !emailInput.value.includes('@')) {
                exibirErroCampo(emailInput, 'E-mail inválido.');
                btn.disabled = false;
                return;
            }

            try {
                const resposta = await fetch('../api/senha/solicitar.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: emailInput.value })
                });

                const resultado = await resposta.json();

                if (resultado.ok) {
                    // Guarda o email na sessão do navegador (ou localStorage)
                    sessionStorage.setItem('recuperacao_email', emailInput.value);

                    // Redireciona para a tela de verificação
                    const url = 'verificar_codigo.php';
                    const debugCode = resultado.codigo_debug ? `?code=${resultado.codigo_debug}` : '';
                    window.location.href = url + debugCode;
                } else {
                    exibirErroGeral(resultado.erro || 'E-mail não encontrado ou erro no servidor.');
                }
            } catch (erro) {
                exibirErroGeral('Erro de rede ao solicitar código.');
            } finally {
                btn.disabled = false;
            }
        });
    }

    // --- Lógica de Verificação de Código (verificar_codigo.php) ---

    // 1. Inputs de Código
    const inputsCodigo = document.querySelectorAll('.input-codigo');
    if (inputsCodigo.length === 4) {
        inputsCodigo.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                const valor = e.target.value;
                if (valor.length === 1 && index < 3) {
                    inputsCodigo[index + 1].focus();
                }
                // Permite colar um código de 4 dígitos em qualquer campo
                if (valor.length === 4 && /^\d{4}$/.test(valor)) {
                    inputsCodigo.forEach((inp, i) => inp.value = valor[i]);
                    inputsCodigo[3].focus(); // Move o foco para o último
                    verificarCodigoComTimer();
                } else if (valor.length > 1) {
                    // Se digitou mais de 1, pega só o primeiro e avança
                    e.target.value = valor[0];
                    if (index < 3) inputsCodigo[index + 1].focus();
                }
            });

            input.addEventListener('keydown', (e) => {
                // Voltar com Backspace se o campo estiver vazio
                if (e.key === 'Backspace' && e.target.value === '' && index > 0) {
                    inputsCodigo[index - 1].focus();
                }
            });
        });

        // Autofocus no primeiro campo
        inputsCodigo[0].focus();
    }


    // 2. Timer e Reenvio
    const timerElement = getElement('timer');
    const btnReenviar = getElement('btnReenviar');
    const btnContinuarVerificacao = getElement('btnContinuar');
    const emailRecuperacao = sessionStorage.getItem('recuperacao_email');
    let intervaloTimer;
    let tempoRestante; // Inicializado por startTimer

    const formatarTempo = (segundos) => {
        const min = String(Math.floor(segundos / 60)).padStart(2, '0');
        const sec = String(segundos % 60).padStart(2, '0');
        return `${min}:${sec}`;
    };

    const startTimer = (duracaoSegundos = 30) => {
        tempoRestante = duracaoSegundos;
        if (btnReenviar) {
            btnReenviar.classList.add('disabled');
            btnReenviar.onclick = null; // Remove o evento de clique
        }

        if (intervaloTimer) clearInterval(intervaloTimer);

        intervaloTimer = setInterval(() => {
            if (timerElement) timerElement.textContent = formatarTempo(tempoRestante);
            
            if (tempoRestante <= 0) {
                clearInterval(intervaloTimer);
                if (btnReenviar) {
                    btnReenviar.classList.remove('disabled');
                    btnReenviar.onclick = reencaminharCodigo;
                }
                if (timerElement) timerElement.textContent = '00:00';
            } else {
                tempoRestante--;
            }
        }, 1000);
    };

    const reencaminharCodigo = async (e) => {
        e.preventDefault();
        
        if (!emailRecuperacao) {
            exibirErroGeral('E-mail de recuperação não encontrado. Volte ao login.');
            return;
        }

        if (btnReenviar && btnReenviar.classList.contains('disabled')) return;

        // Desabilita e inicia um timer simples visualmente para o reenvio
        if (btnReenviar) {
            btnReenviar.classList.add('disabled');
        }
        
        exibirErroGeral(''); // Limpa erros anteriores
        
        try {
            const resposta = await fetch('../api/senha/solicitar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: emailRecuperacao })
            });

            const resultado = await resposta.json();

            if (resultado.ok) {
                // Reinicia o timer e exibe o novo código (em dev)
                startTimer(30);
                alert(`NOVO CÓDIGO (DEBUG): ${resultado.codigo_debug}`);
            } else {
                exibirErroGeral(resultado.erro || 'Falha ao reenviar código.');
            }
        } catch (erro) {
            exibirErroGeral('Erro de rede ao reenviar código.');
        } finally {
            // O timer em 'startTimer' reabilita o botão ao final.
        }
    };

    // Inicia o timer se estiver na tela de verificação
    if (timerElement && emailRecuperacao) {
        startTimer(30);
    } else if (timerElement && !emailRecuperacao) {
        // Protege contra acesso direto
        window.location.href = 'esqueci_senha.php'; 
    }

    // 3. Submissão do Código
    const verificarCodigoComTimer = async () => {
        if (btnContinuarVerificacao) {
            btnContinuarVerificacao.disabled = true;
        }

        const codigo = Array.from(inputsCodigo).map(input => input.value).join('');
        const email = emailRecuperacao;
        const erroGeral = getElement('erroGeral');
        erroGeral.style.display = 'none';

        if (codigo.length !== 4) {
            exibirErroGeral('Código deve ter 4 dígitos.');
            if (btnContinuarVerificacao) btnContinuarVerificacao.disabled = false;
            return;
        }

        try {
            const resposta = await fetch('../api/senha/verificar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: email, codigo: codigo })
            });

            const resultado = await resposta.json();

            if (resultado.ok) {
                // Código OK, redireciona para redefinição
                sessionStorage.setItem('recuperacao_codigo', codigo); // Guarda o código validado
                window.location.href = 'redefinir_senha.php';
            } else {
                // Falha na verificação
                exibirErroGeral(resultado.erro || 'Código incorreto ou expirado.');
                // Limpa os campos para nova tentativa
                inputsCodigo.forEach(input => input.value = '');
                inputsCodigo[0].focus();
            }
        } catch (erro) {
            exibirErroGeral('Erro de rede ao verificar código.');
        } finally {
            if (btnContinuarVerificacao) btnContinuarVerificacao.disabled = false;
        }
    };

    if (btnContinuarVerificacao) {
        btnContinuarVerificacao.addEventListener('click', verificarCodigoComTimer);
    }

    // --- Lógica de Redefinir Senha (redefinir_senha.php) ---
    const formRedefinirSenha = getElement('formRedefinirSenha');
    if (formRedefinirSenha) {
        const novaSenhaInput = getElement('nova_senha');
        const confirmaSenhaInput = getElement('confirma_senha');
        const email = sessionStorage.getItem('recuperacao_email');
        const codigo = sessionStorage.getItem('recuperacao_codigo');
        const btnRedefinir = getElement('btnRedefinir');

        // Protege contra acesso direto
        if (!email || !codigo) {
            window.location.href = 'esqueci_senha.php';
            return;
        }
        
        // Limpa a sessão após o acesso (por segurança, o PHP fará a validação final)
        // sessionStorage.removeItem('recuperacao_email');
        // sessionStorage.removeItem('recuperacao_codigo');

        formRedefinirSenha.addEventListener('submit', async (e) => {
            e.preventDefault();
            btnRedefinir.disabled = true;
            limparErroCampo(novaSenhaInput);
            limparErroCampo(confirmaSenhaInput);
            exibirErroGeral('');

            let valido = true;
            if (novaSenhaInput.value.length < 8) {
                exibirErroCampo(novaSenhaInput, 'A senha deve ter no mínimo 8 caracteres.');
                valido = false;
            }
            if (novaSenhaInput.value !== confirmaSenhaInput.value) {
                exibirErroCampo(confirmaSenhaInput, 'As senhas não coincidem.');
                valido = false;
            }

            if (!valido) {
                btnRedefinir.disabled = false;
                return;
            }

            try {
                const resposta = await fetch('../api/senha/redefinir.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        email: email, 
                        codigo: codigo, 
                        nova_senha: novaSenhaInput.value 
                    })
                });

                const resultado = await resposta.json();

                if (resultado.ok) {
                    sessionStorage.removeItem('recuperacao_email');
                    sessionStorage.removeItem('recuperacao_codigo');
                    window.location.href = 'senha_redefinida.php';
                } else {
                    exibirErroGeral(resultado.erro || 'Falha ao redefinir. Tente novamente.');
                }
            } catch (erro) {
                exibirErroGeral('Erro de rede ao redefinir senha.');
            } finally {
                btnRedefinir.disabled = false;
            }
        });
    }

    // --- Lógica de Registro (registrar.php) ---
    const formRegistro = getElement('formRegistro');
    if (formRegistro) {
        const nomeInput = getElement('nome');
        const emailInput = getElement('email');
        const senhaInput = getElement('senha');
        const btnCadastrar = getElement('btnCadastrar');

        formRegistro.addEventListener('submit', async (e) => {
            e.preventDefault();
            btnCadastrar.disabled = true;
            
            limparErroCampo(nomeInput);
            limparErroCampo(emailInput);
            limparErroCampo(senhaInput);
            exibirErroGeral('');

            let valido = true;
            if (nomeInput.value.trim().length < 3) {
                exibirErroCampo(nomeInput, 'Nome deve ter no mínimo 3 caracteres.');
                valido = false;
            }
            if (!emailInput.value.trim() || !emailInput.value.includes('@')) {
                exibirErroCampo(emailInput, 'E-mail inválido.');
                valido = false;
            }
            if (senhaInput.value.length < 8) {
                exibirErroCampo(senhaInput, 'Senha deve ter no mínimo 8 caracteres.');
                valido = false;
            }
            
            if (!valido) {
                btnCadastrar.disabled = false;
                return;
            }
            
            const dados = {
                nome: nomeInput.value,
                email: emailInput.value,
                senha: senhaInput.value
            };

            try {
                const resposta = await fetch('../api/registrar.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(dados)
                });

                const resultado = await resposta.json();

                if (resultado.ok) {
                    // Registro bem-sucedido, redireciona para o painel (ou login)
                    alert('Cadastro realizado com sucesso! Você será redirecionado para o login.');
                    window.location.href = 'login.php';
                } else {
                    exibirErroGeral(resultado.erro || 'Falha ao registrar. E-mail já em uso?');
                }
            } catch (erro) {
                exibirErroGeral('Erro de rede ao registrar.');
            } finally {
                btnCadastrar.disabled = false;
            }
        });
    }
});