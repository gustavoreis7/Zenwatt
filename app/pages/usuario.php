<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Usar caminho absoluto para evitar erros
$base_dir = dirname(__DIR__);
require_once $base_dir . '/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Buscar dados do usuário
$query = "SELECT * FROM usuarios WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $_SESSION['usuario_id']);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo "Usuário não encontrado!";
    exit();
}

// Buscar imóveis do usuário
$imoveis = [];
try {
    $query_imoveis = "SELECT * FROM imoveis WHERE usuario_id = :usuario_id";
    $stmt_imoveis = $db->prepare($query_imoveis);
    $stmt_imoveis->bindParam(':usuario_id', $_SESSION['usuario_id']);
    $stmt_imoveis->execute();
    $imoveis = $stmt_imoveis->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erro ao buscar imóveis: " . $e->getMessage());
}

// BUSCAR CONSUMO REAL DE HOJE
$consumo_hoje = 0;
try {
    $query_consumo_hoje = "SELECT consumo_kwh FROM consumos WHERE usuario_id = :usuario_id AND data_registro = CURDATE()";
    $stmt_consumo_hoje = $db->prepare($query_consumo_hoje);
    $stmt_consumo_hoje->bindParam(':usuario_id', $_SESSION['usuario_id']);
    $stmt_consumo_hoje->execute();
    $consumo_hoje_result = $stmt_consumo_hoje->fetch(PDO::FETCH_ASSOC);
    
    if ($consumo_hoje_result) {
        $consumo_hoje = $consumo_hoje_result['consumo_kwh'];
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar consumo de hoje: " . $e->getMessage());
}

// Se não encontrou consumo de hoje, calcular baseado nos equipamentos
if ($consumo_hoje == 0) {
    $equipamentos = [];
    $custo_mensal = 0;

    if (!empty($imoveis)) {
        $imoveis_ids = array_column($imoveis, 'id');
        $placeholders = str_repeat('?,', count($imoveis_ids) - 1) . '?';
        
        try {
            // Buscar áreas dos imóveis
            $query_areas = "SELECT * FROM areas WHERE imovel_id IN ($placeholders)";
            $stmt_areas = $db->prepare($query_areas);
            $stmt_areas->execute($imoveis_ids);
            $areas = $stmt_areas->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($areas)) {
                $areas_ids = array_column($areas, 'id');
                $placeholders_equip = str_repeat('?,', count($areas_ids) - 1) . '?';
                
                // Buscar equipamentos das áreas
                $query_equipamentos = "SELECT * FROM equipamentos WHERE area_id IN ($placeholders_equip)";
                $stmt_equipamentos = $db->prepare($query_equipamentos);
                $stmt_equipamentos->execute($areas_ids);
                $equipamentos = $stmt_equipamentos->fetchAll(PDO::FETCH_ASSOC);
                
                // Calcular consumo baseado nos equipamentos
                if (!empty($equipamentos)) {
                    foreach ($equipamentos as $equipamento) {
                        $dias_uso = isset($equipamento['dias_uso_semana']) ? $equipamento['dias_uso_semana'] : 7;
                        $horas_por_dia = isset($equipamento['horas_por_dia']) ? $equipamento['horas_por_dia'] : 0;
                        $potencia = isset($equipamento['potencia']) ? $equipamento['potencia'] : 0;
                        
                        $consumo_diario = ($potencia * $horas_por_dia * ($dias_uso / 7)) / 1000;
                        $consumo_hoje += $consumo_diario;
                        $custo_mensal += $consumo_diario * 30 * 0.75;
                    }
                    
                    // Arredondar valores
                    $consumo_hoje = round($consumo_hoje, 1);
                    $custo_mensal = round($custo_mensal, 2);
                }
            }
        } catch (PDOException $e) {
            error_log("Erro ao buscar equipamentos: " . $e->getMessage());
        }
    }
    
    // Se ainda não tem consumo, usar valor padrão
    if ($consumo_hoje == 0) {
        $consumo_hoje = 12.4;
        $custo_mensal = 238.90;
    }
} else {
    // Se tem consumo real de hoje, buscar equipamentos apenas para a tabela
    $equipamentos = [];
    if (!empty($imoveis)) {
        $imoveis_ids = array_column($imoveis, 'id');
        $placeholders = str_repeat('?,', count($imoveis_ids) - 1) . '?';
        
        try {
            $query_areas = "SELECT * FROM areas WHERE imovel_id IN ($placeholders)";
            $stmt_areas = $db->prepare($query_areas);
            $stmt_areas->execute($imoveis_ids);
            $areas = $stmt_areas->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($areas)) {
                $areas_ids = array_column($areas, 'id');
                $placeholders_equip = str_repeat('?,', count($areas_ids) - 1) . '?';
                
                $query_equipamentos = "SELECT * FROM equipamentos WHERE area_id IN ($placeholders_equip)";
                $stmt_equipamentos = $db->prepare($query_equipamentos);
                $stmt_equipamentos->execute($areas_ids);
                $equipamentos = $stmt_equipamentos->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            error_log("Erro ao buscar equipamentos: " . $e->getMessage());
        }
    }
}

// Calcular custo mensal baseado no consumo de hoje
if (!isset($custo_mensal)) {
    $custo_mensal = $consumo_hoje * 30 * 0.75;
}

$economia_percentual = 18;

// Buscar dados de consumo para gráficos (últimos 30 dias)
$consumos = [];
$labels_consumo = [];
$dados_consumo = [];

try {
    $query_consumo = "SELECT * FROM consumos WHERE usuario_id = :usuario_id ORDER BY data_registro DESC LIMIT 30";
    $stmt_consumo = $db->prepare($query_consumo);
    $stmt_consumo->bindParam(':usuario_id', $_SESSION['usuario_id']);
    $stmt_consumo->execute();
    $consumos = $stmt_consumo->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($consumos)) {
        foreach ($consumos as $consumo) {
            $labels_consumo[] = date('d/m', strtotime($consumo['data_registro']));
            $dados_consumo[] = $consumo['consumo_kwh'];
        }
        $labels_consumo = array_reverse($labels_consumo);
        $dados_consumo = array_reverse($dados_consumo);
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar consumos: " . $e->getMessage());
}

// Se não houver dados de consumo, usar dados de exemplo
if (empty($dados_consumo)) {
    for ($i = 29; $i >= 0; $i--) {
        $labels_consumo[] = date('d/m', strtotime("-$i days"));
        $dados_consumo[] = rand(8, 20);
    }
}

// CALCULAR VARIAÇÃO vs ONTEM
$variacao_hoje = 0;
try {
    $query_consumo_ontem = "SELECT consumo_kwh FROM consumos WHERE usuario_id = :usuario_id AND data_registro = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
    $stmt_consumo_ontem = $db->prepare($query_consumo_ontem);
    $stmt_consumo_ontem->bindParam(':usuario_id', $_SESSION['usuario_id']);
    $stmt_consumo_ontem->execute();
    $consumo_ontem_result = $stmt_consumo_ontem->fetch(PDO::FETCH_ASSOC);
    
    if ($consumo_ontem_result && $consumo_ontem_result['consumo_kwh'] > 0) {
        $consumo_ontem = $consumo_ontem_result['consumo_kwh'];
        $variacao_hoje = (($consumo_hoje - $consumo_ontem) / $consumo_ontem) * 100;
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar consumo de ontem: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Painel do Usuário - ZenWatt</title>
  <link rel="stylesheet" href="../assets/css/dashboard.css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <!-- Favicons -->
  <link rel="apple-touch-icon-precomposed" sizes="57x57" href="/favicon/apple-touch-icon-57x57.png" />
  <link rel="apple-touch-icon-precomposed" sizes="114x114" href="/favicon/apple-touch-icon-114x114.png" />
  <link rel="apple-touch-icon-precomposed" sizes="72x72" href="/favicon/apple-touch-icon-72x72.png" />
  <link rel="apple-touch-icon-precomposed" sizes="144x144" href="/favicon/apple-touch-icon-144x144.png" />
  <link rel="apple-touch-icon-precomposed" sizes="60x60" href="/favicon/apple-touch-icon-60x60.png" />
  <link rel="apple-touch-icon-precomposed" sizes="120x120" href="/favicon/apple-touch-icon-120x120.png" />
  <link rel="apple-touch-icon-precomposed" sizes="76x76" href="/favicon/apple-touch-icon-76x76.png" />
  <link rel="apple-touch-icon-precomposed" sizes="152x152" href="/favicon/apple-touch-icon-152x152.png" />
  <link rel="icon" type="image/png" href="/favicon/favicon-196x196.png" sizes="196x196" />
  <link rel="icon" type="image/png" href="/favicon/favicon-96x96.png" sizes="96x96" />
  <link rel="icon" type="image/png" href="/favicon/favicon-32x32.png" sizes="32x32" />
  <link rel="icon" type="image/png" href="/favicon/favicon-16x16.png" sizes="16x16" />
  <link rel="icon" type="image/png" href="/favicon/favicon-128.png" sizes="128x128" />
</head>
<body>

  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="profile">
      <img src="../assets/images/fav-zen.png" alt="Foto do Usuário">
      <h3><?php echo htmlspecialchars($usuario['nome']); ?></h3>
      <p><?php echo htmlspecialchars($usuario['email']); ?></p>
    </div>
    <ul class="menu">
      <li class="active"><i class="fas fa-home"></i> <span>Dashboard</span></li>
      <li><i class="fas fa-user"></i> <a href="../pages/gerenciar.php">Gerenciar</a></li>
      <li><i class="fas fa-map-marker-alt"></i> <a href="../pages/localizacao.php">Localização</a></li>
      <li><i class="fas fa-comment"></i> <span>Chat</span></li>
      <li><i class="fas fa-star"></i> <span>Favoritos</span></li>
      <li><i class="fas fa-cog"></i> <span>Configurações</span></li>
      <li><i class="fas fa-lock"></i> <span>Privacidade</span></li>
      <li class="logout">
        <a href="../pages/logout.php" style="color: inherit; text-decoration: none;">
          <i class="fas fa-sign-out-alt"></i> <span>Sair</span>
        </a>
      </li>
    </ul>
  </aside>

  <!-- Main -->
  <main class="main-content">
    <!-- Topbar -->
    <header class="topbar">
      <div class="search-box">
        <input type="text" id="searchInput" placeholder="Pesquisar...">
        <i class="fas fa-search"></i>
      </div>
      <div class="user-info">
        <?php if (!empty($imoveis)): ?>
          <select id="selecionarImovel" class="imovel-selector">
            <?php foreach ($imoveis as $imovel): ?>
              <option value="<?php echo $imovel['id']; ?>">
                <?php echo htmlspecialchars($imovel['endereco']) . ', ' . htmlspecialchars($imovel['cidade']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        <?php else: ?>
          <span class="no-imoveis">Nenhum imóvel cadastrado</span>
          <a href="../pages/gerenciar.php" class="btn-small">Cadastrar Imóvel</a>
        <?php endif; ?>
      </div>
      <div class="top-icons">
        <i class="fas fa-bell"></i>
        <i class="fas fa-user"></i>
        <i class="fas fa-ellipsis-h"></i>
      </div>
    </header>

    <!-- Dashboard -->
    <section class="dashboard">
      <!-- Alertas do sistema -->
      <?php if (empty($imoveis) || empty($equipamentos)): ?>
        <div class="card wide alert-card">
          <div class="alert-content">
            <i class="fas fa-info-circle"></i>
            <div>
              <h3>Configuração Inicial Necessária</h3>
              <p>Para uma experiência completa, cadastre seus imóveis e equipamentos.</p>
              <div class="alert-actions">
                <a href="../pages/gerenciar.php" class="btn">Cadastrar Imóvel</a>
                <button class="btn ghost" onclick="mostrarFormEquipamentos()">Adicionar Equipamentos</button>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- KPIs -->
      <div class="card small kpi">
        <div class="kpi-icon"><i class="fa-solid fa-bolt"></i></div>
        <div class="kpi-content">
          <h3>Consumo Hoje</h3>
          <p class="big-number" id="kpiHoje"><?php echo number_format($consumo_hoje, 1); ?> kWh</p>
          <span class="kpi-sub" id="variacaoHoje">
            <?php if ($variacao_hoje != 0): ?>
              <?php echo ($variacao_hoje > 0 ? '+' : '') . number_format($variacao_hoje, 1); ?>% vs ontem
            <?php else: ?>
              Dados de ontem não disponíveis
            <?php endif; ?>
          </span>
        </div>
      </div>

      <div class="card small kpi">
        <div class="kpi-icon"><i class="fa-solid fa-money-bill-wave"></i></div>
        <div class="kpi-content">
          <h3>Custo Estimado (mês)</h3>
          <p class="big-number" id="kpiCusto">R$ <?php echo number_format($custo_mensal, 2, ',', '.'); ?></p>
          <span class="kpi-sub">Bandeira: Verde</span>
        </div>
      </div>

      <div class="card small kpi">
        <div class="kpi-icon"><i class="fa-solid fa-seedling"></i></div>
        <div class="kpi-content">
          <h3>Economia</h3>
          <p class="big-number" id="kpiEconomia"><?php echo $economia_percentual; ?>%</p>
          <span class="kpi-sub">vs média dos últimos 3 meses</span>
        </div>
      </div>

      <!-- ... (restante do código permanece igual) ... -->

      <!-- Visualizações principais -->
      <div class="card chart">
        <div class="card-header">
          <h3>Consumo Diário (últimos 30 dias)</h3>
          <div class="actions">
            <button class="btn ghost" id="btnAtualizar1"><i class="fa-solid fa-rotate"></i> atualizar</button>
          </div>
        </div>
        <div id="lineChart"></div>
      </div>

      <div class="card chart">
        <div class="card-header">
          <h3>Comparativo Mensal (kWh)</h3>
          <div class="actions">
            <button class="btn ghost" id="btnAtualizar2"><i class="fa-solid fa-rotate"></i> atualizar</button>
          </div>
        </div>
        <div id="barChart"></div>
      </div>

      <div class="card chart">
        <div class="card-header">
          <h3>Consumo por Finalidade</h3>
        </div>
        <div id="doughnutChart"></div>
      </div>

      <div class="card chart">
        <div class="card-header">
          <h3>Picos por Faixa Horária</h3>
        </div>
        <div id="areaChart"></div>
      </div>

      <div class="card chart">
        <div class="card-header">
          <h3>Participação por Equipamento</h3>
        </div>
        <div id="equipamentosChart"></div>
      </div>

      <div class="card chart">
        <div class="card-header">
          <h3>Projeção de Consumo (próx. 6 meses)</h3>
        </div>
        <div id="projectionChart"></div>
      </div>

      <!-- Calendário -->
      <div class="card calendar">
        <div class="card-header calendar-header">
          <h3>Calendário</h3>
          <div class="calendar-cta">
            <span class="hint"><i class="fa-solid fa-clock-rotate-left"></i> Dica: acompanhe o <strong>Histórico de consumo</strong> por dia.</span>
            <a href="historico.php" class="btn">Ver Histórico</a>
          </div>
        </div>
        <div class="calendar-grid" id="calendarGrid">
          <!-- Gerado via JavaScript -->
        </div>
      </div>

      <!-- Tabela de equipamentos -->
      <div class="card wide" id="equipamentosSection">
        <div class="card-header">
          <h3>Meus Equipamentos</h3>
          <?php if (empty($equipamentos)): ?>
            <span class="badge">Nenhum equipamento</span>
          <?php else: ?>
            <span class="badge"><?php echo count($equipamentos); ?> equipamentos</span>
          <?php endif; ?>
        </div>

        <div class="table-wrap">
          <table class="table" id="tabelaEquipamentos">
            <thead>
              <tr>
                <th>Equipamento</th>
                <th>Modelo</th>
                <th>Potência (W)</th>
                <th>Horas/dia</th>
                <th>Dias/semana</th>
                <th>kWh/mês</th>
                <th>Custo/mês</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($equipamentos)): ?>
                <?php foreach ($equipamentos as $equipamento): ?>
                  <?php
                  // Calcular consumo mensal
                  $dias_uso = isset($equipamento['dias_uso_semana']) ? $equipamento['dias_uso_semana'] : 7;
                  $horas_por_dia = isset($equipamento['horas_por_dia']) ? $equipamento['horas_por_dia'] : 0;
                  $potencia = isset($equipamento['potencia']) ? $equipamento['potencia'] : 0;
                  
                  $consumo_mensal_kwh = ($potencia * $horas_por_dia * $dias_uso * 4.33) / 1000;
                  $custo_mensal = $consumo_mensal_kwh * 0.75;
                  ?>
                  <tr>
                    <td><?php echo htmlspecialchars($equipamento['nome']); ?></td>
                    <td><?php echo isset($equipamento['modelo']) ? htmlspecialchars($equipamento['modelo']) : '-'; ?></td>
                    <td><?php echo number_format($potencia, 0); ?></td>
                    <td><?php echo number_format($horas_por_dia, 1); ?></td>
                    <td><?php echo $dias_uso; ?></td>
                    <td><?php echo number_format($consumo_mensal_kwh, 1); ?></td>
                    <td>R$ <?php echo number_format($custo_mensal, 2, ',', '.'); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="no-data">Nenhum equipamento cadastrado</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </main>

  <!-- Modal do calendário -->
  <div class="modal" id="calendarModal">
    <div class="modal-content">
      <span class="close-btn" id="closeModal">&times;</span>
      <h2 id="modalTitle">Consumo do Dia</h2>
      <p id="modalText">Carregando dados do consumo...</p>
      <div class="modal-actions">
        <a class="btn" href="historico.php">Abrir histórico</a>
      </div>
    </div>
  </div>

  <script>
    // Dados do PHP para JavaScript
    window.consumoData = {
        labels: <?php echo json_encode($labels_consumo); ?>,
        valores: <?php echo json_encode($dados_consumo); ?>,
        hoje: <?php echo $consumo_hoje; ?>,
        custo: <?php echo $custo_mensal; ?>,
        economia: <?php echo $economia_percentual; ?>,
        equipamentos: <?php echo !empty($equipamentos) ? json_encode($equipamentos) : '[]'; ?>
    };
  </script>
  <script src="../assets/js/usuario.js"></script>
</body>
</html>