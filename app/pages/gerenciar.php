<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: usuario.php');
    exit();
}

require_once '../config/database.php';

$database = new Database();
$db = $database->pdo;

// Buscar dados do usuário
$query = "SELECT * FROM usuarios WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $_SESSION['usuario_id']);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    session_destroy();
    header('Location: usuario.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Painel do Usuário - ZenWatt</title>
  <link rel="stylesheet" href="../assets/css/gerenciar.css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
  <aside class="sidebar">
    <div class="profile">
      <img src="../assets/images/fav-zen.png" alt="Foto do Usuário">
      <h3><?php echo htmlspecialchars($usuario['nome']); ?></h3>
      <p><?php echo htmlspecialchars($usuario['email']); ?></p>
    </div>
    <ul class="menu">
      <li><a href="usuario.php"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
      <li><a href="conta.php"><i class="fas fa-user"></i> <span>Conta</span></a></li>
      <li class="active"><i class="fas fa-building"></i> <span>Gerenciar</span></li>
      <li><i class="fas fa-map-marker-alt"></i> <span>Localização</span></li>
      <li><i class="fas fa-comment"></i> <span>Chat</span></li>
      <li><i class="fas fa-star"></i> <span>Favoritos</span></li>
      <li><i class="fas fa-cog"></i> <span>Configurações</span></li>
      <li class="logout">
        <a href="logout.php" style="color: inherit; text-decoration: none;">
          <i class="fas fa-sign-out-alt"></i> <span>Sair</span>
        </a>
      </li>
    </ul>
  </aside>

  <main class="main-content">
    <header class="topbar">
      <div class="topbar-left">
        <h1><i class="fas fa-building"></i> Gerenciar Propriedades</h1>
        <p>Gerencie seus imóveis, áreas e equipamentos</p>
      </div>
      <div class="topbar-right">
        <div class="search-box">
          <input type="text" id="searchInput" placeholder="Pesquisar...">
          <i class="fas fa-search"></i>
        </div>
        <div class="top-icons">
          <i class="fas fa-bell"></i>
          <i class="fas fa-user"></i>
        </div>
      </div>
    </header>

    <section class="dashboard">
      <!-- Cards de Resumo -->
      <div class="summary-cards">
        <div class="summary-card">
          <div class="summary-icon">
            <i class="fas fa-home"></i>
          </div>
          <div class="summary-info">
            <h3 id="totalImoveis">0</h3>
            <p>Imóveis</p>
          </div>
        </div>
        <div class="summary-card">
          <div class="summary-icon">
            <i class="fas fa-layer-group"></i>
          </div>
          <div class="summary-info">
            <h3 id="totalAreas">0</h3>
            <p>Áreas</p>
          </div>
        </div>
        <div class="summary-card">
          <div class="summary-icon">
            <i class="fas fa-plug"></i>
          </div>
          <div class="summary-info">
            <h3 id="totalEquipamentos">0</h3>
            <p>Equipamentos</p>
          </div>
        </div>
        <div class="summary-card">
          <div class="summary-icon">
            <i class="fas fa-bolt"></i>
          </div>
          <div class="summary-info">
            <h3 id="consumoTotal">0 kWh</h3>
            <p>Consumo Mensal</p>
          </div>
        </div>
      </div>

      <!-- Abas de Navegação -->
      <div class="tabs-container">
        <div class="tabs">
          <button class="tab-button active" data-tab="imoveis">
            <i class="fas fa-home"></i> Imóveis
          </button>
          <button class="tab-button" data-tab="areas">
            <i class="fas fa-layer-group"></i> Áreas
          </button>
          <button class="tab-button" data-tab="equipamentos">
            <i class="fas fa-plug"></i> Equipamentos
          </button>
          <button class="tab-button" data-tab="consumo">
            <i class="fas fa-chart-bar"></i> Consumo
          </button>
        </div>

        <!-- Conteúdo das Abas -->
        <div class="tab-content">
          <!-- Aba Imóveis -->
          <div id="tab-imoveis" class="tab-pane active">
            <div class="card">
              <div class="card-header">
                <h3><i class="fas fa-home"></i> Gerenciar Imóveis</h3>
                <button class="btn btn-primary" onclick="toggleForm('formImovel')">
                  <i class="fas fa-plus"></i> Novo Imóvel
                </button>
              </div>

              <form id="formImovel" class="aparelho-form" style="display: none;">
                <input type="hidden" id="imovel_id" name="id">
                <div class="form-grid">
                  <div class="field">
                    <label>Nome do Imóvel</label>
                    <input type="text" id="nome_imovel" name="nome" placeholder="Ex: Casa Principal" required />
                  </div>
                  <div class="field">
                    <label>CEP</label>
                    <input type="text" id="cep_imovel" name="cep" placeholder="Ex: 00000-000" maxlength="9" />
                  </div>
                  <div class="field">
                    <label>Endereço</label>
                    <input type="text" id="endereco_imovel" name="endereco" placeholder="Rua, nº, bairro" />
                  </div>
                  <div class="field">
                    <label>Número</label>
                    <input type="text" id="numero_imovel" name="numero" placeholder="Ex: 123" />
                  </div>
                  <div class="field">
                    <label>Bairro</label>
                    <input type="text" id="bairro_imovel" name="bairro" placeholder="Ex: Centro" />
                  </div>
                  <div class="field">
                    <label>Cidade</label>
                    <input type="text" id="cidade_imovel" name="cidade" placeholder="Ex: São Paulo" />
                  </div>
                  <div class="field">
                    <label>Estado</label>
                    <input type="text" id="estado_imovel" name="estado" placeholder="Ex: SP" maxlength="2" />
                  </div>
                </div>
                <div class="form-actions">
                  <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Salvar
                  </button>
                  <button type="button" class="btn btn-secondary" onclick="toggleForm('formImovel')">
                    <i class="fas fa-times"></i> Cancelar
                  </button>
                </div>
              </form>

              <div class="table-container">
                <table class="table" id="tabelaImoveis">
                  <thead>
                    <tr>
                      <th>Nome</th>
                      <th>Endereço</th>
                      <th>Status</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- Dados carregados via JavaScript -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Aba Áreas -->
          <div id="tab-areas" class="tab-pane">
            <div class="card">
              <div class="card-header">
                <h3><i class="fas fa-layer-group"></i> Gerenciar Áreas</h3>
                <div style="display: flex; gap: 1rem; align-items: center;">
                  <select id="filtroImovelArea" class="form-control" style="width: 200px;" onchange="filtrarAreas()">
                    <option value="">Todos os Imóveis</option>
                  </select>
                  <button class="btn btn-primary" onclick="toggleForm('formArea')">
                    <i class="fas fa-plus"></i> Nova Área
                  </button>
                </div>
              </div>

              <form id="formArea" class="aparelho-form" style="display: none;">
                <input type="hidden" id="area_id" name="id">
                <div class="form-grid">
                  <div class="field">
                    <label>Imóvel</label>
                    <select id="imovel_area" name="imovel_id" class="form-control" required>
                      <option value="">Selecione um imóvel</option>
                    </select>
                  </div>
                  <div class="field">
                    <label>Nome da Área</label>
                    <input type="text" id="nome_area" name="nome" placeholder="Ex: Sala de Estar" required />
                  </div>
                </div>
                <div class="form-actions">
                  <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Salvar
                  </button>
                  <button type="button" class="btn btn-secondary" onclick="toggleForm('formArea')">
                    <i class="fas fa-times"></i> Cancelar
                  </button>
                </div>
              </form>

              <div class="table-container">
                <table class="table" id="tabelaAreas">
                  <thead>
                    <tr>
                      <th>Área</th>
                      <th>Imóvel</th>
                      <th>Equipamentos</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- Dados carregados via JavaScript -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Aba Equipamentos -->
          <div id="tab-equipamentos" class="tab-pane">
            <div class="card">
              <div class="card-header">
                <h3><i class="fas fa-plug"></i> Gerenciar Equipamentos</h3>
                <div style="display: flex; gap: 1rem; align-items: center;">
                  <select id="filtroImovelEquip" class="form-control" style="width: 200px;" onchange="filtrarEquipamentos()">
                    <option value="">Todos os Imóveis</option>
                  </select>
                  <button class="btn btn-primary" onclick="toggleForm('formEquipamento')">
                    <i class="fas fa-plus"></i> Novo Equipamento
                  </button>
                </div>
              </div>

              <form id="formEquipamento" class="aparelho-form" style="display: none;">
                <input type="hidden" id="equip_id" name="id">
                <div class="form-grid">
                  <div class="field">
                    <label>Área</label>
                    <select id="area_equip" name="area_id" class="form-control" required>
                      <option value="">Selecione uma área</option>
                    </select>
                  </div>
                  <div class="field">
                    <label>Nome do Equipamento</label>
                    <input type="text" id="nome_equip" name="nome" placeholder="Ex: Ar Condicionado" required />
                  </div>
                  <div class="field">
                    <label>Modelo</label>
                    <input type="text" id="modelo_equip" name="modelo" placeholder="Ex: Split 12.000 BTUs" />
                  </div>
                  <div class="field">
                    <label>Potência (W)</label>
                    <input type="number" step="0.01" id="potencia_equip" name="potencia" placeholder="Ex: 1200" required />
                  </div>
                  <div class="field">
                    <label>Horas por Dia</label>
                    <input type="number" step="0.1" id="horas_equip" name="horas_por_dia" placeholder="Ex: 4.5" required />
                  </div>
                </div>
                <div class="form-actions">
                  <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Salvar
                  </button>
                  <button type="button" class="btn btn-secondary" onclick="toggleForm('formEquipamento')">
                    <i class="fas fa-times"></i> Cancelar
                  </button>
                </div>
              </form>

              <div class="table-container">
                <table class="table" id="tabelaEquip">
                  <thead>
                    <tr>
                      <th>Equipamento</th>
                      <th>Modelo</th>
                      <th>Potência</th>
                      <th>Uso Diário</th>
                      <th>Localização</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- Dados carregados via JavaScript -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Aba Consumo -->
          <div id="tab-consumo" class="tab-pane">
            <div class="card">
              <div class="card-header">
                <h3><i class="fas fa-chart-bar"></i> Mapeamento de Consumo</h3>
                <button class="btn btn-primary" onclick="calcularConsumo()">
                  <i class="fas fa-calculator"></i> Calcular Consumo
                </button>
              </div>

              <div class="consumo-stats" id="consumoStats" style="display: none;">
                <div class="stat-card">
                  <div class="stat-icon">
                    <i class="fas fa-bolt"></i>
                  </div>
                  <div class="stat-info">
                    <h4>Consumo Total Diário</h4>
                    <span class="stat-value" id="consumoDiario">0 kWh</span>
                  </div>
                </div>

                <div class="stat-card">
                  <div class="stat-icon">
                    <i class="fas fa-calendar-alt"></i>
                  </div>
                  <div class="stat-info">
                    <h4>Consumo Total Mensal</h4>
                    <span class="stat-value" id="consumoMensal">0 kWh</span>
                  </div>
                </div>

                <div class="stat-card">
                  <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                  </div>
                  <div class="stat-info">
                    <h4>Custo Mensal Estimado</h4>
                    <span class="stat-value" id="custoMensal">R$ 0,00</span>
                  </div>
                </div>
              </div>

              <div class="chart-container">
                <canvas id="consumoChart" width="400" height="200"></canvas>
              </div>

              <div class="table-container">
                <table class="table" id="tabelaConsumo">
                  <thead>
                    <tr>
                      <th>Equipamento</th>
                      <th>Potência (W)</th>
                      <th>Uso Diário</th>
                      <th>Consumo Diário</th>
                      <th>Consumo Mensal</th>
                      <th>Custo Mensal</th>
                      <th>Local</th>
                    </tr>
                  </thead>
                  <tbody id="corpoTabelaConsumo">
                    <!-- Dados serão preenchidos via JavaScript -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Modal de Confirmação -->
  <div id="confirmModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Confirmação</h3>
        <span class="close">&times;</span>
      </div>
      <div class="modal-body">
        <p id="confirmMessage">Tem certeza que deseja excluir este item?</p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" id="confirmCancel">Cancelar</button>
        <button class="btn btn-danger" id="confirmOk">Excluir</button>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="../assets/js/gerenciar.js"></script>
</body>
</html>