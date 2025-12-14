// ARQUIVO: js/projetos.js
// ATUALIZADO: Correção visual, lógica de abas e adição de links

document.addEventListener('DOMContentLoaded', () => {
    const modalProjeto = document.getElementById('modalProjeto');
    const modalDel = document.getElementById('modalExcluir');

    // --- FUNÇÃO PARA ABRIR MODAL DE NOVO PROJETO ---
    window.openModal = function() { 
        if (modalProjeto) {
            modalProjeto.style.display = 'flex';
            
            // 1. Reset do Formulário
            const form = document.getElementById('formCriarProjeto');
            if(form) form.reset();
            
            // 2. Limpa elementos dinâmicos
            document.querySelectorAll('.input-remove-file').forEach(e => e.remove());
            const logoPrev = document.getElementById('logo_preview');
            if(logoPrev) logoPrev.innerText = "";

            const linkPub = document.getElementById('containerLinksPublicos');
            if(linkPub) linkPub.innerHTML = "";
            const arqPub = document.getElementById('arquivosAtuaisPublicos');
            if(arqPub) arqPub.innerHTML = "";
            
            const contPriv = document.getElementById('containerLinksPrivados');
            if(contPriv) contPriv.innerHTML = "";
            const arqPriv = document.getElementById('arquivosAtuaisPrivados');
            if(arqPriv) arqPriv.innerHTML = "";

            // 3. Reset Dropdown Equipes
            const hiddenInputs = document.getElementById('hiddenEquipesInputs');
            if(hiddenInputs) hiddenInputs.innerHTML = ""; 
            
            const triggerText = document.getElementById('equipeTriggerText');
            if(triggerText) {
                triggerText.textContent = "Selecione as equipes...";
                triggerText.style.color = "#999";
                triggerText.style.fontWeight = "400";
            }
            document.querySelectorAll('.custom-option').forEach(op => op.classList.remove('selected'));

            // 4. Configuração Inicial
            const title = document.getElementById('modalTitle');
            if(title) title.innerText = "Novo Projeto";
            
            const pId = document.getElementById('projId');
            if(pId) pId.value = ""; 
            
            // 5. Vai para a aba inicial
            const firstTab = document.querySelector('.modal-tab[data-target="tab-info"]');
            if(firstTab) switchFormTab('info', firstTab);
        }
    }

    // --- FUNÇÃO PARA ABRIR MODAL DE EDIÇÃO ---
    window.abrirModalEditarProjeto = function(proj, abaInicial = 'info', tituloContexto = 'editar') {
        if (modalProjeto) {
            openModal(); // Reseta tudo primeiro
            
            const title = document.getElementById('modalTitle');
            if (title) {
                title.innerText = (tituloContexto === 'adicionar_arquivos') ? "Adicionar Arquivos/Links" : "Editar Projeto";
            }
            
            // Preencher Campos
            const setVal = (id, val) => { const el = document.getElementById(id); if(el) el.value = val; };
            setVal('projId', proj.id);
            setVal('projNome', proj.nome);
            setVal('projCliente', proj.cliente_nome || '');
            setVal('projDesc', proj.descricao || '');
            setVal('projInicio', proj.data_inicio || '');
            setVal('projFim', proj.data_fim || '');
            setVal('projStatus', proj.status);

            // Preencher Equipes
            if (proj.equipes && Array.isArray(proj.equipes)) {
                proj.equipes.forEach(eq => {
                    const idBusca = (typeof eq === 'object') ? eq.id : eq;
                    const option = document.querySelector(`.custom-option[data-value="${idBusca}"]`);
                    if(option) option.classList.add('selected');
                });
                if(typeof atualizarInputsEquipe === 'function') atualizarInputsEquipe(); 
            }

            // Renderizar Listas Existentes
            renderizarArquivosExistentes(proj.links, 'arquivosAtuaisPublicos');
            renderizarArquivosExistentes(proj.privados, 'arquivosAtuaisPrivados');

            // Recriar Links de Texto (Públicos e Privados)
            if (proj.links) proj.links.forEach(l => { if(l.tipo === 'link') addLinkInput('containerLinksPublicos', false, l.titulo, l.url); });
            if (proj.privados) proj.privados.forEach(l => { if(l.tipo === 'link') addLinkInput('containerLinksPrivados', true, l.titulo, l.url); });
            
            // Selecionar aba específica
            const targetTabElement = document.querySelector(`.modal-tab[data-target="tab-${abaInicial}"]`);
            if (targetTabElement) switchFormTab(abaInicial, targetTabElement);
        }
    }

    // --- LÓGICA DE ABAS (CORRIGIDA) ---
    window.switchFormTab = function(tabName, btn) {
        // Remove active de todas as abas e painéis
        document.querySelectorAll('#modalProjeto .tab-panel').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('#modalProjeto .modal-tab').forEach(b => b.classList.remove('active'));
        
        // Ativa o alvo
        const targetPanel = document.getElementById('tab-' + tabName);
        if(targetPanel) targetPanel.classList.add('active');
        
        if(btn) btn.classList.add('active');
    };

    // --- FUNÇÃO PARA ADICIONAR LINKS (ESTILO BONITINHO) ---
    window.addLinkInput = function(containerId, isPrivado = false, valTitulo = '', valUrl = '') {
        const container = document.getElementById(containerId);
        if(!container) return; 

        const nameTit = isPrivado ? 'link_priv_titulo[]' : 'link_titulo[]';
        const nameUrl = isPrivado ? 'link_priv_url[]' : 'link_url[]';
        
        const div = document.createElement('div');
        div.className = 'link-row'; // Usa classe CSS definida no PHP
        
        div.innerHTML = `
            <div style="flex:1;">
                <input type="text" name="${nameTit}" value="${valTitulo}" class="campo-padrao" placeholder="Título do Link (Ex: Drive)" style="margin-bottom:5px!important;">
                <input type="text" name="${nameUrl}" value="${valUrl}" class="campo-padrao" placeholder="https://..." style="margin-bottom:0!important;">
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="btn-remove-link" title="Remover">&times;</button>
        `;
        container.appendChild(div);
    }

    // --- LISTAR ARQUIVOS EXISTENTES (Visual Bonito) ---
    window.renderizarArquivosExistentes = function(lista, containerId) {
        const container = document.getElementById(containerId);
        if(!container) return;
        container.innerHTML = ''; 
        
        if (lista && Array.isArray(lista)) {
            let filesFound = false;
            lista.forEach(item => {
                if (item.tipo === 'arquivo' || item.tipo === 'logo') {
                    filesFound = true;
                    const div = document.createElement('div');
                    div.style.cssText = 'display:flex; justify-content:space-between; align-items:center; background:white; padding:10px; margin-bottom:5px; border:1px solid #e0e5f2; border-radius:8px; font-size:0.9rem;';
                    
                    div.innerHTML = `
                        <div style="display:flex; align-items:center; gap:10px; overflow:hidden;">
                            <span style="color:#6A66FF; font-size:1.2rem;">📄</span>
                            <a href="${item.url}" target="_blank" style="text-decoration:none; color:#2b3674; font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:250px;">
                                ${item.titulo}
                            </a>
                        </div>
                        <button type="button" class="btn-remove-file" style="color:#e74c3c; background:none; border:none; cursor:pointer; font-weight:bold;">Excluir</button>
                    `;

                    div.querySelector('.btn-remove-file').onclick = function() {
                        div.style.opacity = '0.5';
                        div.style.textDecoration = 'line-through';
                        this.remove();
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'remover_arquivos[]';
                        input.className = 'input-remove-file';
                        input.value = item.url;
                        document.getElementById('formCriarProjeto').appendChild(input);
                    };
                    container.appendChild(div);
                }
            });
            
            if(!filesFound) {
                container.innerHTML = '<small style="color:#aaa; display:block; padding:5px;">Nenhum arquivo anexado.</small>';
            }
        }
    }

    // --- UI HELPERS ---
    window.closeModal = function(id) { 
        const modal = document.getElementById(id);
        if(modal) modal.style.display = 'none'; 
    }
    window.fecharModalExcluir = function() { 
        const modal = document.getElementById('modalExcluir');
        if(modal) modal.style.display = 'none'; 
    }

    window.switchMainTab = function(tab, btn) {
        document.querySelectorAll('.main-tab-content').forEach(d => d.style.display = 'none');
        const view = document.getElementById('view-'+tab);
        if(view) view.style.display = 'block';
        document.querySelectorAll('.tabs-header .tab-btn').forEach(b => b.classList.remove('active'));
        if(btn) btn.classList.add('active');
    }
    window.previewFile = function(input) {
        const preview = document.getElementById('logo_preview');
        if(preview && input.files && input.files[0]) preview.innerText = "Selecionado: " + input.files[0].name;
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
            
            const btnSoft = document.getElementById('btnSoftDelete');
            const btnHard = document.getElementById('btnHardDelete');
            
            if (acao === 'active_context') {
                if(btnSoft) btnSoft.style.display = 'flex';
                if(btnHard) btnHard.style.display = 'flex'; 
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

    // --- Lógica do Dropdown Multi-Select ---
    const selectWrapper = document.querySelector('.custom-select-wrapper');
    if (selectWrapper) {
        selectWrapper.addEventListener('click', function() {
            this.querySelector('.custom-select').classList.toggle('open');
        });
    }
    document.querySelectorAll('.custom-option').forEach(option => {
        option.addEventListener('click', function() {
            this.classList.toggle('selected');
            atualizarInputsEquipe();
        });
    });

    window.atualizarInputsEquipe = function() {
        const selected = document.querySelectorAll('.custom-option.selected');
        const container = document.getElementById('hiddenEquipesInputs');
        const text = document.getElementById('equipeTriggerText');
        
        if(!container || !text) return;

        container.innerHTML = '';
        let names = [];
        selected.forEach(opt => {
            names.push(opt.textContent.trim());
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'equipes[]';
            input.value = opt.getAttribute('data-value');
            container.appendChild(input);
        });
        
        if(names.length > 0) {
            text.textContent = names.join(', ');
            text.style.color = '#333';
            text.style.fontWeight = '600';
        } else {
            text.textContent = 'Selecione as equipes...';
            text.style.color = '#999';
            text.style.fontWeight = '400';
        }
    }

    // --- SUBMISSÃO ---
    const formProj = document.getElementById('formCriarProjeto');
    if (formProj) {
        formProj.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = formProj.querySelector('button[type="submit"]');
            const txtOriginal = btn.textContent;
            btn.disabled = true; btn.textContent = "Salvando...";

            try {
                const formData = new FormData(formProj);
                const id = document.getElementById('projId').value;
                const endpoint = id ? '../api/projeto_editar.php' : '../api/projeto_criar.php';

                const resp = await fetch(endpoint, { method: 'POST', body: formData });
                
                // Tenta decodificar o JSON com tratamento de erro
                let json;
                const textResp = await resp.text();
                try {
                    json = JSON.parse(textResp);
                } catch (parseErr) {
                    console.error("Resposta bruta do servidor:", textResp);
                    throw new Error("Resposta inválida do servidor (provavelmente erro PHP). Verifique o console.");
                }

                if (json.ok) {
                    window.location.reload();
                } else {
                    alert(json.erro || "Erro ao salvar.");
                }
            } catch (err) { alert("Erro: " + err.message); } 
            finally { btn.disabled = false; btn.textContent = txtOriginal; }
        });
    }
});