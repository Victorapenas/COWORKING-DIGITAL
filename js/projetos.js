// ARQUIVO: js/projetos.js

document.addEventListener('DOMContentLoaded', () => {
    const modalProjeto = document.getElementById('modalProjeto');
    const modalDel = document.getElementById('modalExcluir');

    window.openModal = function() { 
        if (modalProjeto) {
            modalProjeto.style.display = 'flex';
            const form = document.getElementById('formCriarProjeto');
            if(form) form.reset();
            document.getElementById('modalTitle').innerText = "Novo Projeto";
            document.getElementById('projId').value = ""; 
            document.getElementById('logo_preview').innerText = "";
            document.getElementById('containerLinksPublicos').innerHTML = "";
            document.getElementById('containerLinksPrivados').innerHTML = "";
            document.getElementById('containerEquipesDinamicas').innerHTML = ""; 
            switchFormTab('info', document.querySelector('.modal-tab'));
        }
    }

    window.abrirModalEditarProjeto = function(proj) {
        if (modalProjeto) {
            modalProjeto.style.display = 'flex';
            document.getElementById('modalTitle').innerText = "Editar Projeto";
            
            // Campos Básicos
            document.getElementById('projId').value = proj.id;
            document.getElementById('projNome').value = proj.nome;
            document.getElementById('projCliente').value = proj.cliente_nome || '';
            document.getElementById('projDesc').value = proj.descricao || '';
            document.getElementById('projInicio').value = proj.data_inicio || '';
            document.getElementById('projFim').value = proj.data_fim || '';
            document.getElementById('projStatus').value = proj.status;

            // Equipes Dinâmicas
            const containerEq = document.getElementById('containerEquipesDinamicas');
            containerEq.innerHTML = "";
            if (proj.equipes && Array.isArray(proj.equipes)) {
                proj.equipes.forEach(eq => {
                    // Verifica se é objeto ou ID e adiciona
                    const val = (typeof eq === 'object') ? eq.id : eq;
                    addEquipeInput(val);
                });
            }

            // Links
            const containerPub = document.getElementById('containerLinksPublicos');
            containerPub.innerHTML = "";
            if (proj.links && Array.isArray(proj.links)) {
                proj.links.forEach(l => {
                    if (l.tipo === 'link') addLinkInput('containerLinksPublicos', false, l.titulo, l.url);
                });
            }

            const containerPriv = document.getElementById('containerLinksPrivados');
            if (containerPriv) {
                containerPriv.innerHTML = "";
                if (proj.privados && Array.isArray(proj.privados)) {
                    proj.privados.forEach(l => {
                        if (l.tipo === 'link') addLinkInput('containerLinksPrivados', true, l.titulo, l.url);
                    });
                }
            }
            switchFormTab('info', document.querySelector('.modal-tab'));
        }
    }

    window.closeModal = function() { if (modalProjeto) modalProjeto.style.display = 'none'; }
    window.fecharModalExcluir = function() { if (modalDel) modalDel.style.display = 'none'; }

    window.switchMainTab = function(tab, btn) {
        document.querySelectorAll('.main-tab-content').forEach(d => d.style.display = 'none');
        document.getElementById('view-'+tab).style.display = 'block';
        document.querySelectorAll('.tabs-header .tab-btn').forEach(b => b.classList.remove('active'));
        if(btn) btn.classList.add('active');
    }

    window.switchFormTab = function(tab, btn) {
        document.querySelectorAll('#formCriarProjeto .tab-panel').forEach(p => p.classList.remove('active'));
        document.getElementById('tab-'+tab).classList.add('active');
        document.querySelectorAll('#formCriarProjeto .modal-tab').forEach(b => b.classList.remove('active'));
        if(btn) btn.classList.add('active');
    }

    window.previewFile = function(input) {
        if(input.files && input.files[0]) document.getElementById('logo_preview').innerText = "Arquivo: " + input.files[0].name;
    }

    window.filtrarProjetos = function() {
        const term = document.getElementById('searchBox').value.toLowerCase();
        document.querySelectorAll('.proj-card').forEach(card => {
            card.style.display = card.innerText.toLowerCase().includes(term) ? 'flex' : 'none';
        });
    }

    window.abrirModalAcao = function(id, nome, acao) {
        if (modalDel) {
            document.getElementById('idProjetoExcluir').value = id;
            document.getElementById('msgExcluir').innerText = `O que deseja fazer com o projeto "${nome}"?`;
            if (acao === 'active_context') {
                document.getElementById('btnSoftDelete').style.display = 'flex';
                document.getElementById('btnHardDelete').style.display = 'flex'; 
            } 
            modalDel.style.display = 'flex';
        }
    }

    window.confirmarAcaoProjeto = async function(id, tipo) {
        if (tipo === 'hard' && !confirm("ATENÇÃO: A exclusão definitiva apagará tudo. Tem certeza?")) return;
        if (!id) id = document.getElementById('idProjetoExcluir').value;

        try {
            const resp = await fetch('../api/projeto_excluir.php', {
                method: 'POST', headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ id: id, tipo: tipo })
            });
            const json = await resp.json();
            if (json.ok) window.location.reload();
            else alert(json.erro || "Erro ao processar.");
        } catch (err) { alert("Erro de conexão."); } finally { window.fecharModalExcluir(); }
    }

    // ADICIONAR EQUIPE (DINÂMICO)
    window.addEquipeInput = function(selectedId = '') {
        const container = document.getElementById('containerEquipesDinamicas');
        const model = document.getElementById('modelEquipeSelect');
        if(!model) return;

        const div = document.createElement('div');
        div.className = 'dynamic-select-row';
        
        const clone = model.cloneNode(true);
        clone.id = ''; // Remove ID duplicado
        clone.style.display = 'block';
        clone.name = 'equipes[]'; // Nome array para o PHP pegar
        if(selectedId) clone.value = selectedId;
        
        const btnRemove = document.createElement('button');
        btnRemove.type = 'button';
        btnRemove.innerHTML = '&times;';
        btnRemove.style.cssText = "border:none; background:none; color:#e74c3c; font-weight:bold; cursor:pointer; font-size:1.2rem;";
        btnRemove.onclick = function() { div.remove(); };

        div.appendChild(clone);
        div.appendChild(btnRemove);
        container.appendChild(div);
    }

    window.addLinkInput = function(containerId, isPrivado = false, valTitulo = '', valUrl = '') {
        const container = document.getElementById(containerId);
        const nameTit = isPrivado ? 'link_priv_titulo[]' : 'link_titulo[]';
        const nameUrl = isPrivado ? 'link_priv_url[]' : 'link_url[]';
        const div = document.createElement('div');
        div.style.cssText = 'display:flex; gap:10px; margin-bottom:5px; align-items:center;';
        div.innerHTML = `
            <input type="text" name="${nameTit}" value="${valTitulo}" placeholder="Título" style="flex:1; padding:10px; border:1px solid #eee; border-radius:8px;">
            <input type="text" name="${nameUrl}" value="${valUrl}" placeholder="URL" style="flex:2; padding:10px; border:1px solid #eee; border-radius:8px;">
            <button type="button" onclick="this.parentElement.remove()" style="border:none; background:none; cursor:pointer; color:#e74c3c; font-size:1.2rem;">&times;</button>
        `;
        container.appendChild(div);
    }

    const formProj = document.getElementById('formCriarProjeto');
    if (formProj) {
        formProj.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = formProj.querySelector('button[type="submit"]');
            const txtOriginal = btn.textContent;
            btn.disabled = true; btn.textContent = "Salvando...";

            try {
                const formData = new FormData(formProj);
                // Equipes já são pegas automaticamente porque os selects tem name="equipes[]"
                
                const id = document.getElementById('projId').value;
                const endpoint = id ? '../api/projeto_editar.php' : '../api/projeto_criar.php';

                const resp = await fetch(endpoint, { method: 'POST', body: formData });
                let json;
                try { json = await resp.json(); } catch (parseErr) { throw new Error("Resposta inválida do servidor."); }

                if (json.ok) window.location.reload();
                else alert(json.erro || "Erro ao salvar projeto.");
            } catch (err) { alert("Erro: " + err.message); } finally {
                btn.disabled = false; btn.textContent = txtOriginal;
            }
        });
    }
});