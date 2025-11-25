    // Função para abrir e fechar o Modal
const modal = document.getElementById('addMemberModal');

function openModal() {
    modal.style.display = 'flex';
}

function closeModal() {
    modal.style.display = 'none';
}
            
// Fechar o modal clicando fora
window.onclick = function(event) {
    if (event.target === modal) {
        closeModal();
    }
}

// Logout
document.getElementById('btnLogout').addEventListener('click', async (e) => {
    e.preventDefault();
    // A chamada real para logout
    await fetch('../api/logout.php', { method: 'POST' }); 
    window.location.href = 'login.php';
});

// Simulação de Cadastro de Membro (a lógica original da API foi mantida/adaptada)
const formAdd = document.getElementById('formAddMember');
    if (formAdd) {
        formAdd.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = formAdd.querySelector('.botao-primario');
            const msgBox = document.getElementById('msgSucesso');
                        
            btn.textContent = "Salvando..."; btn.disabled = true;
            msgBox.style.display = 'none';

            const papelSistema = formAdd.querySelector('input[name="nivel_acesso"]:checked').value;
                        
            const dados = {
                nome: document.getElementById('novo_nome').value,
                email: document.getElementById('novo_email').value,
                papel: papelSistema, // Usando o papel mapeado
                funcao: document.getElementById('nova_funcao').value
            };

            try {
                // Simulação da chamada de API (mantendo a original do prompt)
                const resp = await fetch('../api/admin_adicionar_membro.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(dados)
                });
            const json = await resp.json();

                if (json.ok) {
                    msgBox.innerHTML = `<strong>✅ Sucesso!</strong> ${dados.nome} cadastrado como ${papelSistema}. Senha padrão: <h2>${json.senha_gerada}</h2>`;
                    msgBox.style.display = 'block';
                    formAdd.reset();
                    // Recarregar a página para mostrar o novo membro
                    // setTimeout(() => window.location.reload(), 2000); 
                } else {
                    alert(json.erro || "Erro ao cadastrar: E-mail já existe ou permissão negada.");
                }
            } catch (err) {
                alert("Erro de conexão ou API não encontrada.");
            } finally {
                btn.textContent = "Adicionar Membro"; btn.disabled = false;
            }
        });
    }