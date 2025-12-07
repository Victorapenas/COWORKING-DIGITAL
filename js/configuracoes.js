document.addEventListener('DOMContentLoaded', () => {
    
    // --- ATUALIZAR PERFIL ---
    const fPerfil = document.getElementById('formPerfil');
    if(fPerfil) {
        fPerfil.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = fPerfil.querySelector('button');
            const txtOriginal = btn.innerText;
            btn.disabled = true; btn.innerText = "Salvando...";

            const s1 = document.getElementById('p_senha1').value;
            const s2 = document.getElementById('p_senha2').value;
            
            if(s1 && s1 !== s2) { 
                alert('As senhas não conferem!'); 
                btn.disabled = false; btn.innerText = txtOriginal;
                return; 
            }
            
            const dados = {
                nome: document.getElementById('p_nome').value,
                email: document.getElementById('p_email').value,
                senha: s1 // Envia vazio se não for alterar
            };

            try {
                const resp = await fetch('../api/perfil_editar.php', {
                    method: 'POST', 
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify(dados)
                });
                const json = await resp.json();
                
                if(json.ok) {
                    alert('Perfil atualizado com sucesso!');
                    window.location.reload(); // Recarrega para atualizar nome no topo
                } else {
                    alert(json.erro || "Erro ao atualizar perfil.");
                }
            } catch(e) { 
                alert('Erro de conexão com o servidor.'); 
                console.error(e);
            } finally {
                btn.disabled = false; btn.innerText = txtOriginal;
            }
        });
    }

    // --- ATUALIZAR EMPRESA ---
    const fEmpresa = document.getElementById('formEmpresa');
    if(fEmpresa) {
        fEmpresa.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = fEmpresa.querySelector('button');
            const txtOriginal = btn.innerText;
            btn.disabled = true; btn.innerText = "Enviando...";

            const formData = new FormData(fEmpresa);
            
            try {
                const resp = await fetch('../api/empresa_editar.php', {
                    method: 'POST', 
                    body: formData // FormData não precisa de Header Content-Type manual
                });
                const json = await resp.json();
                
                if(json.ok) {
                    alert('Dados da empresa atualizados!');
                    window.location.reload(); // Recarrega para mostrar a nova logo
                } else {
                    alert(json.erro || "Erro ao atualizar empresa.");
                }
            } catch(e) { 
                alert('Erro ao salvar dados da empresa.');
            } finally {
                btn.disabled = false; btn.innerText = txtOriginal;
            }
        });
    }
});