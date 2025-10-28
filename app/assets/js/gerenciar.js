// ==============================
// VARIÁVEIS GLOBAIS
// ==============================
let consumoChart = null;
let currentDeleteId = null;
let currentDeleteType = null;
let allAreas = [];
let allEquipamentos = [];

// ==============================
// INICIALIZAÇÃO
// ==============================
document.addEventListener("DOMContentLoaded", function () {
    console.log("DOM carregadoo!");

    // Inicializar abas
    initTabs();

    // Carregar dados iniciais
    carregarDadosIniciais();

    // Configurar eventos
    setupEventListeners();

    // Calcular consumo automaticamente
    setTimeout(calcularConsumoAuto, 1000);
});

// ==============================
// CONFIGURAÇÃO DE EVENTOS
// ==============================
function setupEventListeners() {
    // Via CEP
    document.getElementById("cep_imovel").addEventListener("blur", consultarCEP);

    // Modal de confirmação
    document.querySelector(".close").addEventListener("click", hideModal);
    document.getElementById("confirmCancel").addEventListener("click", hideModal);
    document.getElementById("confirmOk").addEventListener("click", confirmDelete);

    // Fechar modal ao clicar fora
    document.getElementById("confirmModal").addEventListener("click", function (e) {
        if (e.target === this) hideModal();
    });
}

// ==============================
// SISTEMA DE ABAS
// ==============================
function initTabs() {
    const tabButtons = document.querySelectorAll(".tab-button");

    tabButtons.forEach(button => {
        button.addEventListener("click", function () {
            const tabId = this.getAttribute("data-tab");

            // Remover classe active de todos os botões e painéis
            tabButtons.forEach(btn => btn.classList.remove("active"));
            document.querySelectorAll(".tab-pane").forEach(pane => pane.classList.remove("active"));

            // Adicionar classe active ao botão e painel atual
            this.classList.add("active");
            document.getElementById(`tab-${tabId}`).classList.add("active");

            // Carregar dados específicos da aba se necessário
            if (tabId === 'consumo') {
                calcularConsumoAuto();
            }
        });
    });
}

// ==============================
// CARREGAMENTO DE DADOS
// ==============================
async function carregarDadosIniciais() {
    console.log("=== INICIANDO carregarDadosIniciais ===");

    try {
        console.log("1. Carregando imóveis...");
        await carregarImoveis();

        console.log("2. Carregando filtros...");
        await carregarFiltrosImoveis();

        console.log("3. Carregando áreas...");
        await carregarAreas();

        console.log("4. Carregando equipamentos...");
        await carregarEquipamentos();

        console.log("5. Atualizando resumo...");
        atualizarResumo();

    } catch (error) {
        console.error("Erro ao carregar dados iniciais:", error);
    }
}

function atualizarResumo() {
    // Atualizar contadores
    const totalImoveis = document.querySelectorAll("#tabelaImoveis tbody tr").length;
    const totalAreas = document.querySelectorAll("#tabelaAreas tbody tr").length;
    const totalEquipamentos = document.querySelectorAll("#tabelaEquip tbody tr").length;

    document.getElementById("totalImoveis").textContent = totalImoveis;
    document.getElementById("totalAreas").textContent = totalAreas;
    document.getElementById("totalEquipamentos").textContent = totalEquipamentos;
}

// ==============================
// FILTROS POR IMÓVEL
// ==============================

// Carregar filtros de imóveis
async function carregarFiltrosImoveis() {
    console.log("=== INICIANDO carregarFiltrosImoveis ===");

    // Debug 1: Verificar se os elementos existem neste exato momento
    const filtroArea = document.getElementById("filtroInovelArea");
    const filtroEquip = document.getElementById("filtroInovelEquip");

    console.log("Elementos encontrados:", {
        filtroArea: filtroArea ? "EXISTE" : "NÃO EXISTE",
        filtroEquip: filtroEquip ? "EXISTE" : "NÃO EXISTE"
    });

    if (!filtroArea || !filtroEquip) {
        console.error("Elementos não encontrados no momento da execução");
        return;
    }

    // Debug 2: Verificar o estado atual dos selects
    console.log("Estado ANTES:", {
        areaOptions: filtroArea.innerHTML,
        equipOptions: filtroEquip.innerHTML
    });
    try {

        console.log("Iniciando carregamento de filtros...");

        const res = await fetch("../controllers/crudImoveis/listar_imoveis.php");
        const text = await res.text();
        const imoveis = JSON.parse(text);

        console.log("Imóveis recebidos:", imoveis);

        const filtroArea = document.getElementById("filtroImovelArea");
        const filtroEquip = document.getElementById("filtroImovelEquip");

        console.log("Elementos encontrados:", { filtroArea, filtroEquip });

        if (!filtroArea || !filtroEquip) {
            console.error("Elementos de filtro não encontrados!");
            return;
        }

        // Limpar e adicionar opções
        [filtroArea, filtroEquip].forEach(select => {
            select.innerHTML = '<option value="">Todos os Imóveis</option>';
        });

        // Adicionar imóveis aos filtros
        imoveis.forEach(imovel => {
            [filtroArea, filtroEquip].forEach(select => {
                const option = document.createElement("option");
                option.value = imovel.id;
                option.textContent = imovel.nome;
                select.appendChild(option);
            });
        });

        console.log("Filtros carregados com sucesso!");

    } catch (error) {
        console.error("Erro ao carregar filtros:", error);
    }
}
// Função para debug - verificar se os elementos existem
function verificarElementos() {
    console.log("Filtro Area:", document.getElementById("filtroImovelArea"));
    console.log("Filtro Equip:", document.getElementById("filtroImovelEquip"));

    const imoveisNoSelect = document.querySelectorAll("#filtroImovelArea option");
    console.log("Opções no filtro:", imoveisNoSelect.length);
}
// Filtrar áreas por imóvel
function filtrarAreas() {
    const imovelId = document.getElementById("filtroImovelArea").value;
    const linhas = document.querySelectorAll("#tabelaAreas tbody tr");

    linhas.forEach(linha => {
        const areaImovelId = linha.getAttribute('data-imovel-id');
        if (!imovelId || areaImovelId === imovelId) {
            linha.style.display = "";
        } else {
            linha.style.display = "none";
        }
    });
}
// Adicione esta função
function waitForElement(selector, timeout = 5000) {
    return new Promise((resolve, reject) => {
        const startTime = Date.now();

        function checkElement() {
            const element = document.querySelector(selector);
            if (element) {
                resolve(element);
            } else if (Date.now() - startTime >= timeout) {
                reject(new Error(`Elemento ${selector} não encontrado após ${timeout}ms`));
            } else {
                setTimeout(checkElement, 100);
            }
        }

        checkElement();
    });
}

// E modifique o carregarFiltrosImoveis para usar:
async function carregarFiltrosImoveis() {
    try {
        console.log("Aguardando elementos de filtro...");

        // Aguardar os elementos existirem no DOM
        const [filtroArea, filtroEquip] = await Promise.all([
            waitForElement('#filtroImovelArea'),
            waitForElement('#filtroImovelEquip')
        ]);

        console.log("Elementos encontrados, carregando imóveis...");

        const res = await fetch("../controllers/crudImoveis/listar_imoveis.php");
        const text = await res.text();
        const imoveis = JSON.parse(text);

        // Limpar e adicionar opções
        [filtroArea, filtroEquip].forEach(select => {
            select.innerHTML = '<option value="">Todos os Imóveis</option>';
        });

        // Adicionar imóveis aos filtros
        imoveis.forEach(imovel => {
            [filtroArea, filtroEquip].forEach(select => {
                const option = document.createElement("option");
                option.value = imovel.id;
                option.textContent = imovel.nome;
                select.appendChild(option);
            });
        });

        console.log(`Filtros carregados com ${imoveis.length} imóveis`);

    } catch (error) {
        console.error("Erro ao carregar filtros:", error);
    }
}
function criarFiltrosSeNecessario() {
    let filtroArea = document.getElementById('filtroImovelArea');
    let filtroEquip = document.getElementById('filtroImovelEquip');

    // Se não existirem, criar dinamicamente
    if (!filtroArea) {
        console.log('Criando filtroImovelArea dinamicamente...');
        const cardHeaderArea = document.querySelector('#tab-areas .card-header');
        if (cardHeaderArea) {
            const div = cardHeaderArea.querySelector('div');
            filtroArea = document.createElement('select');
            filtroArea.id = 'filtroImovelArea';
            filtroArea.className = 'form-control';
            filtroArea.style.width = '200px';
            filtroArea.onchange = filtrarAreas;
            div.insertBefore(filtroArea, div.querySelector('button'));
        }
    }

    if (!filtroEquip) {
        console.log('Criando filtroImovelEquip dinamicamente...');
        const cardHeaderEquip = document.querySelector('#tab-equipamentos .card-header');
        if (cardHeaderEquip) {
            const div = cardHeaderEquip.querySelector('div');
            filtroEquip = document.createElement('select');
            filtroEquip.id = 'filtroImovelEquip';
            filtroEquip.className = 'form-control';
            filtroEquip.style.width = '200px';
            filtroEquip.onchange = filtrarEquipamentos;
            div.insertBefore(filtroEquip, div.querySelector('button'));
        }
    }

    return { filtroArea, filtroEquip };
}
// Filtrar equipamentos por imóvel
function filtrarEquipamentos() {
    const imovelId = document.getElementById("filtroImovelEquip").value;
    const linhas = document.querySelectorAll("#tabelaEquip tbody tr");

    linhas.forEach(linha => {
        const equipImovelId = linha.getAttribute('data-imovel-id');
        if (!imovelId || equipImovelId === imovelId) {
            linha.style.display = "";
        } else {
            linha.style.display = "none";
        }
    });
}

// ==============================
// GERENCIAMENTO DE FORMULÁRIOS
// ==============================
function toggleForm(formId) {
    const form = document.getElementById(formId);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function resetForm(formId) {
    document.getElementById(formId).reset();
    document.querySelectorAll(`#${formId} input[type="hidden"]`).forEach(input => input.value = "");
    toggleForm(formId);
}

// ==============================
// IMÓVEIS - CRUD
// ==============================
async function carregarImoveis() {
    try {
        showLoading('tabelaImoveis');
        const res = await fetch("../controllers/crudImoveis/listar_imoveis.php");
        const text = await res.text();

        const imoveis = JSON.parse(text);
        const tbody = document.querySelector("#tabelaImoveis tbody");
        const selectArea = document.querySelector("#imovel_area");

        tbody.innerHTML = "";
        // Manter apenas a primeira opção
        const firstOption = selectArea.querySelector('option[value=""]');
        selectArea.innerHTML = '';
        selectArea.appendChild(firstOption);

        imoveis.forEach(imovel => {
            // Preencher tabela
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>
                    <div class="item-info">
                        <i class="fas fa-home"></i>
                        <div>
                            <strong>${imovel.nome}</strong>
                            <small>${imovel.cidade || ''}${imovel.estado ? `, ${imovel.estado}` : ''}</small>
                        </div>
                    </div>
                </td>
                <td>${imovel.endereco || 'Não informado'}</td>
                <td><span class="status-badge active">Ativo</span></td>
                <td>
                    <div class="action-buttons">
                        <button onclick="editarImovel(${imovel.id}, '${imovel.nome.replace(/'/g, "\\'")}', '${(imovel.endereco || '').replace(/'/g, "\\'")}', '${imovel.cep || ''}', '${imovel.numero || ''}', '${(imovel.bairro || '').replace(/'/g, "\\'")}', '${(imovel.cidade || '').replace(/'/g, "\\'")}', '${imovel.estado || ''}')" class="btn btn-sm btn-secondary" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="solicitarExclusao(${imovel.id}, 'imovel')" class="btn btn-sm btn-danger" title="Excluir">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);

            // Preencher select de áreas
            const option = document.createElement("option");
            option.value = imovel.id;
            option.textContent = imovel.nome;
            selectArea.appendChild(option);
        });

        hideLoading('tabelaImoveis');
    } catch (error) {
        console.error("Erro ao carregar imóveis:", error);
        hideLoading('tabelaImoveis');
    }
}

// Salvar/Editar Imóvel
document.getElementById("formImovel").addEventListener("submit", async function (e) {
    e.preventDefault();

    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
    submitBtn.disabled = true;

    const formData = new FormData(this);
    const id = document.getElementById("imovel_id").value;
    const url = id ? "../controllers/crudImoveis/atualizar_imovel.php" : "../controllers/crudImoveis/salvar_imovel.php";

    try {
        const res = await fetch(url, { method: "POST", body: formData });
        const text = await res.text();
        const data = JSON.parse(text);

        showNotification(data.mensagem, data.sucesso ? 'success' : 'error');

        if (data.sucesso) {
            resetForm("formImovel");
            await carregarImoveis();
            await carregarAreas();
            await carregarEquipamentos();
            await carregarFiltrosImoveis();
            atualizarResumo();
        }
    } catch (error) {
        console.error("Erro ao salvar imóvel:", error);
        showNotification("Erro ao processar a solicitação.", 'error');
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});

// Editar Imóvel
function editarImovel(id, nome, endereco, cep, numero, bairro, cidade, estado) {
    document.getElementById("imovel_id").value = id;
    document.getElementById("nome_imovel").value = nome;
    document.getElementById("cep_imovel").value = cep;
    document.getElementById("endereco_imovel").value = endereco;
    document.getElementById("numero_imovel").value = numero;
    document.getElementById("bairro_imovel").value = bairro;
    document.getElementById("cidade_imovel").value = cidade;
    document.getElementById("estado_imovel").value = estado;

    toggleForm('formImovel');
    document.getElementById('formImovel').scrollIntoView({ behavior: 'smooth' });
}

// ==============================
// ÁREAS - CRUD
// ==============================
async function carregarAreas() {
    try {
        showLoading('tabelaAreas');
        const res = await fetch("../controllers/crudAreas/listar_areas.php");
        const areas = await res.json();

        allAreas = areas; // Guardar todas as áreas para filtros

        const tbody = document.querySelector("#tabelaAreas tbody");
        const selectEquip = document.querySelector("#area_equip");

        tbody.innerHTML = "";
        // Manter apenas a primeira opção
        const firstOption = selectEquip.querySelector('option[value=""]');
        selectEquip.innerHTML = '';
        selectEquip.appendChild(firstOption);

        areas.forEach(area => {
            // Preencher tabela
            const tr = document.createElement("tr");
            tr.setAttribute('data-imovel-id', area.imovel_id);
            tr.innerHTML = `
                <td>
                    <div class="item-info">
                        <i class="fas fa-layer-group"></i>
                        <div>
                            <strong>${area.nome}</strong>
                        </div>
                    </div>
                </td>
                <td>${area.imovel_nome}</td>
                <td><span class="badge">${area.total_equipamentos || 0} equip.</span></td>
                <td>
                    <div class="action-buttons">
                        <button onclick="editarArea(${area.id}, '${area.nome.replace(/'/g, "\\'")}', ${area.imovel_id})" class="btn btn-sm btn-secondary" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="solicitarExclusao(${area.id}, 'area')" class="btn btn-sm btn-danger" title="Excluir">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);

            // Preencher select de equipamentos
            const option = document.createElement("option");
            option.value = area.id;
            option.textContent = `${area.nome} (${area.imovel_nome})`;
            option.setAttribute('data-imovel-id', area.imovel_id);
            selectEquip.appendChild(option);
        });

        hideLoading('tabelaAreas');
    } catch (error) {
        console.error("Erro ao carregar áreas:", error);
        hideLoading('tabelaAreas');
    }
}

// Salvar/Editar Área
document.getElementById("formArea").addEventListener("submit", async function (e) {
    e.preventDefault();

    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
    submitBtn.disabled = true;

    const formData = new FormData(this);
    const id = document.getElementById("area_id").value;
    const url = id ? "../controllers/crudAreas/atualizar_area.php" : "../controllers/crudAreas/salvar_area.php";

    try {
        const res = await fetch(url, { method: "POST", body: formData });
        const data = await res.json();

        showNotification(data.mensagem, data.sucesso ? 'success' : 'error');

        if (data.sucesso) {
            resetForm("formArea");
            await carregarAreas();
            await carregarEquipamentos();
            await carregarFiltrosImoveis();
            atualizarResumo();
        }
    } catch (error) {
        console.error("Erro ao salvar área:", error);
        showNotification("Erro ao processar a solicitação.", 'error');
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});

// Editar Área
function editarArea(id, nome, imovelId) {
    document.getElementById("area_id").value = id;
    document.getElementById("nome_area").value = nome;
    document.getElementById("imovel_area").value = imovelId;

    toggleForm('formArea');
    document.getElementById('formArea').scrollIntoView({ behavior: 'smooth' });
}

// ==============================
// EQUIPAMENTOS - CRUD
// ==============================
async function carregarEquipamentos() {
    try {
        showLoading('tabelaEquip');
        const res = await fetch("../controllers/crudEquipamentos/listar_equipamentos.php");
        const equipamentos = await res.json();

        allEquipamentos = equipamentos; // Guardar todos os equipamentos para filtros

        const tbody = document.querySelector("#tabelaEquip tbody");
        tbody.innerHTML = "";

        equipamentos.forEach(equip => {
            const tr = document.createElement("tr");
            tr.setAttribute('data-imovel-id', equip.imovel_id);
            tr.setAttribute('data-area-id', equip.area_id);
            tr.innerHTML = `
                <td>
                    <div class="item-info">
                        <i class="fas fa-plug"></i>
                        <div>
                            <strong>${equip.nome}</strong>
                            <small>${equip.modelo || 'Sem modelo'}</small>
                        </div>
                    </div>
                </td>
                <td>${equip.modelo || '-'}</td>
                <td>${equip.potencia} W</td>
                <td>${equip.horas_por_dia}h/dia</td>
                <td>${equip.area_nome} (${equip.imovel_nome})</td>
                <td>
                    <div class="action-buttons">
                        <button onclick="editarEquipamento(${equip.id}, '${equip.nome.replace(/'/g, "\\'")}', '${(equip.modelo || '').replace(/'/g, "\\'")}', ${equip.potencia}, ${equip.horas_por_dia}, ${equip.area_id})" class="btn btn-sm btn-secondary" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="solicitarExclusao(${equip.id}, 'equipamento')" class="btn btn-sm btn-danger" title="Excluir">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });

        hideLoading('tabelaEquip');
    } catch (error) {
        console.error("Erro ao carregar equipamentos:", error);
        hideLoading('tabelaEquip');
    }
}

// Salvar/Editar Equipamento
document.getElementById("formEquipamento").addEventListener("submit", async function (e) {
    e.preventDefault();

    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
    submitBtn.disabled = true;

    const formData = new FormData(this);
    const id = document.getElementById("equip_id").value;
    const url = id ? "../controllers/crudEquipamentos/atualizar_equipamento.php" : "../controllers/crudEquipamentos/salvar_equipamento.php";

    try {
        const res = await fetch(url, { method: "POST", body: formData });
        const data = await res.json();

        showNotification(data.mensagem, data.sucesso ? 'success' : 'error');

        if (data.sucesso) {
            resetForm("formEquipamento");
            await carregarEquipamentos();
            await carregarAreas();
            atualizarResumo();
        }
    } catch (error) {
        console.error("Erro ao salvar equipamento:", error);
        showNotification("Erro ao processar a solicitação.", 'error');
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});

// Editar Equipamento
function editarEquipamento(id, nome, modelo, potencia, horas, areaId) {
    document.getElementById("equip_id").value = id;
    document.getElementById("nome_equip").value = nome;
    document.getElementById("modelo_equip").value = modelo;
    document.getElementById("potencia_equip").value = potencia;
    document.getElementById("horas_equip").value = horas;
    document.getElementById("area_equip").value = areaId;

    toggleForm('formEquipamento');
    document.getElementById('formEquipamento').scrollIntoView({ behavior: 'smooth' });
}

// ==============================
// EXCLUSÃO COM MODAL DE CONFIRMAÇÃO
// ==============================
function solicitarExclusao(id, tipo) {
    currentDeleteId = id;
    currentDeleteType = tipo;

    const messages = {
        'imovel': 'Tem certeza que deseja excluir este imóvel? Todas as áreas e equipamentos associados também serão removidos.',
        'area': 'Tem certeza que deseja excluir esta área? Todos os equipamentos associados também serão removidos.',
        'equipamento': 'Tem certeza que deseja excluir este equipamento?'
    };

    document.getElementById('confirmMessage').textContent = messages[tipo] || 'Tem certeza que deseja excluir este item?';
    showModal();
}

async function confirmDelete() {
    hideModal();

    let url;
    switch (currentDeleteType) {
        case 'imovel':
            url = "../controllers/crudImoveis/excluir_imovel.php";
            break;
        case 'area':
            url = "../controllers/crudAreas/excluir_area.php";
            break;
        case 'equipamento':
            url = "../controllers/crudEquipamentos/excluir_equipamento.php";
            break;
        default:
            return;
    }

    const formData = new FormData();
    formData.append("id", currentDeleteId);

    try {
        const res = await fetch(url, { method: "POST", body: formData });
        const data = await res.json();

        showNotification(data.mensagem, data.sucesso ? 'success' : 'error');

        if (data.sucesso) {
            // Recarregar os dados necessários
            await carregarDadosIniciais();
        }
    } catch (error) {
        console.error(`Erro ao excluir ${currentDeleteType}:`, error);
        showNotification("Erro ao processar a solicitação.", 'error');
    }
}

// ==============================
// MAPEAMENTO DE CONSUMO
// ==============================
async function calcularConsumo() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Calculando...';
    btn.disabled = true;

    try {
        const res = await fetch("../controllers/consumo/consumo.php");
        const data = await res.json();

        if (data.sucesso) {
            exibirResultadosConsumo(data.consumo);
            showNotification("Consumo calculado com sucesso!", 'success');
        } else {
            showNotification("Erro: " + data.mensagem, 'error');
        }
    } catch (error) {
        console.error("Erro ao calcular consumo:", error);
        showNotification("Erro ao calcular consumo", 'error');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

async function calcularConsumoAuto() {
    try {
        const res = await fetch("../controllers/consumo/consumo.php");
        const data = await res.json();

        if (data.sucesso) {
            exibirResultadosConsumo(data.consumo);
        }
    } catch (error) {
        console.error("Erro ao calcular consumo automático:", error);
    }
}

function exibirResultadosConsumo(consumo) {
    // Atualizar estatísticas
    document.getElementById('consumoDiario').textContent = consumo.diario_kwh + ' kWh';
    document.getElementById('consumoMensal').textContent = consumo.mensal_kwh + ' kWh';
    document.getElementById('custoMensal').textContent = 'R$ ' + consumo.custo_mensal.toFixed(2);

    // Atualizar resumo
    document.getElementById('consumoTotal').textContent = consumo.mensal_kwh + ' kWh';

    // Mostrar seção de resultados
    document.getElementById('consumoStats').style.display = 'grid';

    // Preencher tabela
    const tbody = document.getElementById('corpoTabelaConsumo');
    tbody.innerHTML = '';

    consumo.equipamentos.forEach(equip => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><strong>${equip.nome}</strong>${equip.modelo ? '<br><small>' + equip.modelo + '</small>' : ''}</td>
            <td>${equip.potencia} W</td>
            <td>${equip.horas_por_dia}h/dia</td>
            <td>${equip.consumo_diario_kwh} kWh</td>
            <td>${equip.consumo_mensal_kwh} kWh</td>
            <td><strong>R$ ${equip.custo_mensal.toFixed(2)}</strong></td>
            <td>${equip.area_nome} (${equip.imovel_nome})</td>
        `;
        tbody.appendChild(tr);
    });

    // Criar gráfico
    criarGraficoConsumo(consumo.equipamentos);
}

function criarGraficoConsumo(equipamentos) {
    const ctx = document.getElementById('consumoChart').getContext('2d');

    // Destruir gráfico anterior se existir
    if (consumoChart) {
        consumoChart.destroy();
    }

    // Ordenar equipamentos por consumo (maior primeiro)
    equipamentos.sort((a, b) => b.custo_mensal - a.custo_mensal);

    const labels = equipamentos.map(e => e.nome);
    const dataCusto = equipamentos.map(e => e.custo_mensal);
    const dataConsumo = equipamentos.map(e => e.consumo_mensal_kwh);

    consumoChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Custo Mensal (R$)',
                    data: dataCusto,
                    backgroundColor: 'rgba(58, 134, 255, 0.8)',
                    borderColor: 'rgba(58, 134, 255, 1)',
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    label: 'Consumo Mensal (kWh)',
                    data: dataConsumo,
                    backgroundColor: 'rgba(4, 182, 0, 0.8)',
                    borderColor: 'rgba(4, 182, 0, 1)',
                    borderWidth: 1,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Custo (R$)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Consumo (kWh)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Consumo de Energia por Equipamento'
                },
                tooltip: {
                    callbacks: {
                        afterBody: function (context) {
                            const index = context[0].dataIndex;
                            const equip = equipamentos[index];
                            return [
                                `Potência: ${equip.potencia}W`,
                                `Uso diário: ${equip.horas_por_dia}h`,
                                `Local: ${equip.area_nome}`
                            ];
                        }
                    }
                }
            }
        }
    });
}

// ==============================
// SERVIÇOS EXTERNOS
// ==============================
async function consultarCEP() {
    const cep = this.value.replace(/\D/g, '');
    if (cep.length !== 8) return;

    try {
        const res = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
        const data = await res.json();

        if (!data.erro) {
            document.getElementById("endereco_imovel").value = data.logradouro || '';
            document.getElementById("bairro_imovel").value = data.bairro || '';
            document.getElementById("cidade_imovel").value = data.localidade || '';
            document.getElementById("estado_imovel").value = data.uf || '';
        } else {
            showNotification("CEP não encontrado.", 'warning');
        }
    } catch (error) {
        console.error("Erro ao consultar CEP:", error);
        showNotification("Erro ao consultar CEP.", 'error');
    }
}

// ==============================
// UTILITÁRIOS
// ==============================
function showModal() {
    document.getElementById('confirmModal').classList.add('show');
}

function hideModal() {
    document.getElementById('confirmModal').classList.remove('show');
}

function showNotification(message, type = 'info') {
    // Criar elemento de notificação
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
        </div>
    `;

    // Adicionar ao corpo
    document.body.appendChild(notification);

    // Mostrar com animação
    setTimeout(() => notification.classList.add('show'), 100);

    // Remover após 5 segundos
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

function getNotificationIcon(type) {
    const icons = {
        'success': 'check-circle',
        'error': 'exclamation-circle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    };
    return icons[type] || 'info-circle';
}

function showLoading(tableId) {
    const tbody = document.querySelector(`#${tableId} tbody`);
    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="loading-cell">
                <i class="fas fa-spinner fa-spin"></i> Carregando...
            </td>
        </tr>
    `;
}

function hideLoading(tableId) {
    // A remoção é feita automaticamente quando os dados são carregados
}

// Adicionar estilos para notificações
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        transform: translateX(400px);
        transition: transform 0.3s ease;
        border-left: 4px solid #ccc;
        min-width: 300px;
    }
    
    .notification.show {
        transform: translateX(0);
    }
    
    .notification.success {
        border-left-color: #38a169;
    }
    
    .notification.error {
        border-left-color: #e53e3e;
    }
    
    .notification.warning {
        border-left-color: #dd6b20;
    }
    
    .notification.info {
        border-left-color: #3182ce;
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .notification i {
        font-size: 1.2rem;
    }
    
    .notification.success i { color: #38a169; }
    .notification.error i { color: #e53e3e; }
    .notification.warning i { color: #dd6b20; }
    .notification.info i { color: #3182ce; }
    
    .loading-cell {
        text-align: center;
        padding: 2rem;
        color: var(--text-secondary);
    }
    
    .item-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .item-info i {
        font-size: 1.25rem;
        color: var(--primary-color);
    }
    
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .status-badge.active {
        background: #c6f6d5;
        color: #276749;
    }
    
    .badge {
        background: var(--bg-secondary);
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-size: 0.8rem;
        color: var(--text-secondary);
    }
    
    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }
    
    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 0.9rem;
        transition: var(--transition);
    }
    
    .form-control:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(4, 182, 0, 0.1);
    }
`;
document.head.appendChild(notificationStyles);