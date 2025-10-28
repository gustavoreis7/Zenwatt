// =============================================
// DADOS GLOBAIS
// =============================================
window.consumoData = window.consumoData || {
  hoje: 12.4,
  custo: 238.90,
  economia: 18,
  equipamentos: []
};

// =============================================
// GERENCIADOR DE VISUALIZAÇÕES
// =============================================
const VisualizacaoManager = {
  init() {
    console.log('📊 Inicializando visualizações...');
    this.criarTodasVisualizacoes();
  },

  criarTodasVisualizacoes() {
    this.criarBarrasConsumo('lineChart');
    this.criarComparativoMensal('barChart');
    this.criarDistribuicaoConsumo('doughnutChart');
    this.criarPicosHorarios('areaChart');
    this.criarParticipacaoEquipamentos('equipamentosChart');
    this.criarProjecaoConsumo('projectionChart');
  },

  criarBarrasConsumo(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const dados = this.gerarDadosAleatorios(30, 8, 20);
    const maxValor = Math.max(...dados);

    let html = `
            <div class="barras-horizontais">
                <div class="barras-header">
                    <span>Consumo Diário (últimos 30 dias)</span>
                    <div class="legenda">
                        <span class="min">8 kWh</span>
                        <span class="max">${maxValor} kWh</span>
                    </div>
                </div>
                <div class="barras-container">
        `;

    dados.forEach((valor, index) => {
      const porcentagem = (valor / maxValor) * 100;
      const data = new Date();
      data.setDate(data.getDate() - (29 - index));
      const label = data.getDate() + '/' + (data.getMonth() + 1);

      html += `
                <div class="barra-item">
                    <div class="barra-label">${label}</div>
                    <div class="barra">
                        <div class="barra-preenchimento" style="width: ${porcentagem}%">
                            <span class="barra-valor">${valor}</span>
                        </div>
                    </div>
                </div>
            `;
    });

    html += `</div></div>`;
    container.innerHTML = html;
  },

  criarComparativoMensal(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'];
    const dados = [320, 290, 310, 280, 270, 260];
    const maxValor = Math.max(...dados);

    let html = `
            <div class="comparativo-mensal">
                <div class="comparativo-header">
                    <span>Comparativo Mensal (kWh)</span>
                </div>
                <div class="comparativo-barras">
        `;

    meses.forEach((mes, index) => {
      const valor = dados[index];
      const porcentagem = (valor / maxValor) * 100;
      const variacao = index > 0 ? ((valor - dados[index - 1]) / dados[index - 1] * 100).toFixed(1) : 0;

      html += `
                <div class="mes-item">
                    <div class="mes-nome">${mes}</div>
                    <div class="mes-barra">
                        <div class="mes-barra-preenchimento" style="height: ${porcentagem}%">
                            <span class="mes-valor">${valor}kWh</span>
                        </div>
                    </div>
                    <div class="mes-variacao ${variacao >= 0 ? 'positivo' : 'negativo'}">
                        ${variacao > 0 ? '+' : ''}${variacao}%
                    </div>
                </div>
            `;
    });

    html += `</div></div>`;
    container.innerHTML = html;
  },

  criarDistribuicaoConsumo(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const categorias = [
      { nome: 'Iluminação', valor: 25, cor: '#2E7D32' },
      { nome: 'Eletrodomésticos', valor: 35, cor: '#4CAF50' },
      { nome: 'Ar Condicionado', valor: 20, cor: '#8BC34A' },
      { nome: 'Aquecimento', valor: 10, cor: '#CDDC39' },
      { nome: 'Outros', valor: 10, cor: '#FFC107' }
    ];

    let html = `
            <div class="distribuicao-consumo">
                <div class="distribuicao-header">
                    <span>Consumo por Finalidade</span>
                </div>
                <div class="distribuicao-lista">
        `;

    categorias.forEach(categoria => {
      html += `
                <div class="categoria-item">
                    <div class="categoria-info">
                        <div class="categoria-cor" style="background: ${categoria.cor}"></div>
                        <span class="categoria-nome">${categoria.nome}</span>
                    </div>
                    <div class="categoria-valor">
                        <span class="porcentagem">${categoria.valor}%</span>
                        <div class="categoria-barra">
                            <div class="categoria-barra-preenchimento" style="width: ${categoria.valor}%; background: ${categoria.cor}"></div>
                        </div>
                    </div>
                </div>
            `;
    });

    html += `</div></div>`;
    container.innerHTML = html;
  },

  criarPicosHorarios(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const horarios = ['00h', '04h', '08h', '12h', '16h', '20h'];
    const dados = [2.1, 1.8, 4.2, 6.8, 5.2, 7.1];
    const maxValor = Math.max(...dados);

    let html = `
            <div class="picos-horarios">
                <div class="picos-header">
                    <span>Picos por Faixa Horária</span>
                </div>
                <div class="picos-container">
        `;

    horarios.forEach((horario, index) => {
      const valor = dados[index];
      const porcentagem = (valor / maxValor) * 100;
      const intensidade = valor > 5 ? 'alto' : valor > 3 ? 'medio' : 'baixo';

      html += `
                <div class="pico-item">
                    <div class="pico-horario">${horario}</div>
                    <div class="pico-barra ${intensidade}" style="height: ${porcentagem}%">
                        <span class="pico-valor">${valor}kWh</span>
                    </div>
                </div>
            `;
    });

    html += `</div></div>`;
    container.innerHTML = html;
  },

  criarParticipacaoEquipamentos(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    let equipamentos;
    if (window.consumoData?.equipamentos?.length > 0) {
      equipamentos = window.consumoData.equipamentos.map(e => ({
        nome: e.nome,
        consumo: (e.potencia * e.horas_por_dia * (e.dias_uso_semana || 7) * 4.33) / 1000,
        cor: this.gerarCor()
      }));
    } else {
      equipamentos = [
        { nome: 'Geladeira', consumo: 30, cor: '#2E7D32' },
        { nome: 'TV', consumo: 15, cor: '#4CAF50' },
        { nome: 'Ar Condicionado', consumo: 25, cor: '#8BC34A' },
        { nome: 'Máquina de Lavar', consumo: 12, cor: '#CDDC39' },
        { nome: 'Computador', consumo: 18, cor: '#FFC107' }
      ];
    }

    const totalConsumo = equipamentos.reduce((sum, e) => sum + e.consumo, 0);

    let html = `
            <div class="participacao-equipamentos">
                <div class="participacao-header">
                    <span>Participação por Equipamento</span>
                </div>
                <div class="equipamentos-lista">
        `;

    equipamentos.forEach(equipamento => {
      const porcentagem = ((equipamento.consumo / totalConsumo) * 100).toFixed(1);

      html += `
                <div class="equipamento-item">
                    <div class="equipamento-info">
                        <div class="equipamento-cor" style="background: ${equipamento.cor}"></div>
                        <span class="equipamento-nome">${equipamento.nome}</span>
                    </div>
                    <div class="equipamento-detalhes">
                        <span class="equipamento-consumo">${equipamento.consumo.toFixed(1)} kWh/mês</span>
                        <span class="equipamento-porcentagem">${porcentagem}%</span>
                    </div>
                </div>
            `;
    });

    html += `</div></div>`;
    container.innerHTML = html;
  },

  criarProjecaoConsumo(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const meses = ['Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
    const projecao = [250, 270, 260, 280, 300, 320];
    const meta = [240, 235, 230, 225, 220, 215];
    const maxValor = Math.max(...projecao, ...meta);

    let html = `
            <div class="projecao-consumo">
                <div class="projecao-header">
                    <span>Projeção de Consumo (próx. 6 meses)</span>
                    <div class="projecao-legenda">
                        <div class="legenda-item">
                            <div class="cor-projecao"></div>
                            <span>Projeção</span>
                        </div>
                        <div class="legenda-item">
                            <div class="cor-meta"></div>
                            <span>Meta</span>
                        </div>
                    </div>
                </div>
                <div class="projecao-grafico">
        `;

    meses.forEach((mes, index) => {
      const alturaProjecao = (projecao[index] / maxValor) * 100;
      const alturaMeta = (meta[index] / maxValor) * 100;
      const status = projecao[index] <= meta[index] ? 'dentro-meta' : 'acima-meta';

      html += `
                <div class="projecao-mes">
                    <div class="mes-nome">${mes}</div>
                    <div class="projecao-barras">
                        <div class="barra-meta" style="height: ${alturaMeta}%"></div>
                        <div class="barra-projecao ${status}" style="height: ${alturaProjecao}%">
                            <span class="projecao-valor">${projecao[index]}</span>
                        </div>
                    </div>
                    <div class="projecao-status ${status}">
                        ${projecao[index] <= meta[index] ? '✓' : '⚠'}
                    </div>
                </div>
            `;
    });

    html += `</div></div>`;
    container.innerHTML = html;
  },

  gerarDadosAleatorios(quantidade, min, max) {
    return Array(quantidade).fill().map(() =>
      Math.floor(Math.random() * (max - min + 1)) + min
    );
  },

  gerarCor() {
    const cores = ['#2E7D32', '#4CAF50', '#8BC34A', '#CDDC39', '#FFC107', '#FF9800', '#2196F3', '#9C27B0'];
    return cores[Math.floor(Math.random() * cores.length)];
  }
};

// =============================================
// GERENCIADOR DE CALENDÁRIO
// =============================================
const CalendarioManager = {
  init() {
    this.criarCalendario();
  },

  criarCalendario() {
    const calendarGrid = document.getElementById('calendarGrid');
    if (!calendarGrid) return;

    try {
      const today = new Date();
      const year = today.getFullYear();
      const month = today.getMonth();

      const firstDay = new Date(year, month, 1).getDay();
      const daysInMonth = new Date(year, month + 1, 0).getDate();
      const weekdays = ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'];

      calendarGrid.innerHTML = '';

      // Dias da semana
      weekdays.forEach(day => {
        const dayElement = document.createElement('div');
        dayElement.textContent = day;
        if (day === 'D') dayElement.style.color = '#f44336';
        calendarGrid.appendChild(dayElement);
      });

      // Dias vazios
      for (let i = 0; i < firstDay; i++) {
        calendarGrid.appendChild(document.createElement('div'));
      }

      // Dias do mês
      for (let day = 1; day <= daysInMonth; day++) {
        const dayElement = document.createElement('div');
        dayElement.className = 'day';
        dayElement.textContent = day;

        if (day === today.getDate() && month === today.getMonth()) {
          dayElement.classList.add('active');
        }

        dayElement.addEventListener('click', () => this.abrirModalDia(day, month, year));
        calendarGrid.appendChild(dayElement);
      }

      console.log('✅ Calendário criado');
    } catch (error) {
      console.error('💥 Erro no calendário:', error);
    }
  },

  abrirModalDia(dia, mes, ano) {
    const modal = document.getElementById('calendarModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalText = document.getElementById('modalText');

    if (!modal || !modalTitle || !modalText) return;

    const data = new Date(ano, mes, dia);
    modalTitle.textContent = `Consumo - ${data.toLocaleDateString('pt-BR')}`;

    const consumoDia = (Math.random() * 15 + 5).toFixed(1);
    const custoDia = (consumoDia * 0.75).toFixed(2);

    modalText.innerHTML = `
            <p><strong>Consumo:</strong> ${consumoDia} kWh</p>
            <p><strong>Custo estimado:</strong> R$ ${custoDia}</p>
            <p><strong>Status:</strong> Dentro da média esperada</p>
            <p><strong>Dica:</strong> Reduza o uso do ar condicionado para economizar.</p>
        `;

    modal.style.display = 'flex';
  }
};

// =============================================
// GERENCIADOR DE NOTIFICAÇÕES
// =============================================
const NotificacaoManager = {
  mostrar(mensagem, tipo = 'success') {
    const notificacao = document.createElement('div');
    notificacao.className = `notificacao ${tipo}`;
    notificacao.innerHTML = `
            <div class="notificacao-conteudo">
                <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${mensagem}</span>
            </div>
        `;

    document.body.appendChild(notificacao);

    setTimeout(() => {
      notificacao.style.animation = 'slideOutRight 0.3s ease-in';
      setTimeout(() => {
        if (notificacao.parentNode) {
          notificacao.parentNode.removeChild(notificacao);
        }
      }, 300);
    }, 3000);
  }
};

// =============================================
// DASHBOARD PRINCIPAL
// =============================================
const DashboardManager = {
  init() {
    console.log('🚀 Iniciando Dashboard ZenWatt...');

    this.inicializarEventos();
    CalendarioManager.init();
    VisualizacaoManager.init();

    this.inicializarAtualizacaoTempoReal();
    this.atualizarVariacaoHoje();

    console.log('✅ Dashboard pronto!');
  },

  inicializarEventos() {
    console.log('🎯 Configurando eventos...');

    // Botões de atualizar
    this.configurarEvento('btnAtualizar1', 'click', () => this.atualizarVisualizacaoComLoading('lineChart'));
    this.configurarEvento('btnAtualizar2', 'click', () => this.atualizarVisualizacaoComLoading('barChart'));

    // Modal
    this.configurarEvento('closeModal', 'click', this.fecharModal);

    // Fechar modal ao clicar fora
    document.addEventListener('click', (e) => {
      const modal = document.getElementById('calendarModal');
      if (modal && e.target === modal) {
        this.fecharModal();
      }
    });

    // Seletor de imóvel
    this.configurarEvento('selecionarImovel', 'change', (e) => this.carregarDadosImovel(e.target.value));

    // Pesquisa
    this.configurarEvento('searchInput', 'input', (e) => this.pesquisar(e.target.value));
  },

  configurarEvento(elementId, eventType, handler) {
    const element = document.getElementById(elementId);
    if (element) {
      element.addEventListener(eventType, handler);
    }
  },

  atualizarVisualizacaoComLoading(visualizacaoId) {
    const btn = document.getElementById(`btnAtualizar${visualizacaoId === 'lineChart' ? '1' : '2'}`);
    if (!btn) return;

    const textoOriginal = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> atualizando';
    btn.disabled = true;

    setTimeout(() => {
      // Recriar a visualização
      if (visualizacaoId === 'lineChart') {
        VisualizacaoManager.criarBarrasConsumo(visualizacaoId);
      } else if (visualizacaoId === 'barChart') {
        VisualizacaoManager.criarComparativoMensal(visualizacaoId);
      }

      btn.innerHTML = textoOriginal;
      btn.disabled = false;
      NotificacaoManager.mostrar('Visualização atualizada!', 'success');
    }, 800);
  },

  carregarDadosImovel(imovelId) {
    console.log(`🏠 Carregando imóvel: ${imovelId}`);

    // Mostrar loading
    document.querySelectorAll('.kpi').forEach(kpi => {
      kpi.classList.add('loading');
    });

    setTimeout(() => {
      const dadosSimulados = {
        consumo_hoje: (Math.random() * 10 + 8).toFixed(1),
        custo_mensal: (Math.random() * 100 + 200).toFixed(2),
        economia: Math.floor(Math.random() * 30)
      };

      this.atualizarKPIs(dadosSimulados);

      // Remover loading
      document.querySelectorAll('.kpi').forEach(kpi => {
        kpi.classList.remove('loading');
      });

      NotificacaoManager.mostrar('Dados do imóvel carregados!', 'success');
    }, 800);
  },

  atualizarKPIs(dados) {
    this.atualizarElemento('kpiHoje', `${dados.consumo_hoje} kWh`);
    this.atualizarElemento('kpiCusto', `R$ ${dados.custo_mensal}`);
    this.atualizarElemento('kpiEconomia', `${dados.economia}%`);
  },

  atualizarElemento(elementId, conteudo) {
    const element = document.getElementById(elementId);
    if (element) element.textContent = conteudo;
  },

  atualizarVariacaoHoje() {
    const element = document.getElementById('variacaoHoje');
    if (!element) return;

    const variacao = (Math.random() - 0.5) * 10;
    element.textContent = `${variacao > 0 ? '+' : ''}${variacao.toFixed(1)}% vs ontem`;
    element.style.color = variacao > 0 ? '#f44336' : '#2E7D32';
  },

  fecharModal() {
    const modal = document.getElementById('calendarModal');
    if (modal) modal.style.display = 'none';
  },

  pesquisar(termo) {
    if (termo.length > 2) {
      console.log(`🔍 Pesquisando: ${termo}`);
    }
  },

  inicializarAtualizacaoTempoReal() {
    setInterval(() => {
      this.atualizarVariacaoHoje();
    }, 30000);
  }
};

// =============================================
// INICIALIZAÇÃO
// =============================================
document.addEventListener('DOMContentLoaded', function () {
  DashboardManager.init();
});

// =============================================
// FUNÇÕES GLOBAIS
// =============================================
function mostrarFormEquipamentos() {
  NotificacaoManager.mostrar('Redirecionando para equipamentos...', 'success');
  setTimeout(() => {
    window.location.href = '../pages/gerenciar.php';
  }, 1000);
}

console.log('📄 usuario.js carregado!');