<?php
session_start();

// Se não estiver logado, redireciona para login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Conexão com o banco
$host = "localhost";
$dbname = "zenwatt";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Buscar informações do usuário logado
    $sql = "SELECT nome, email, telefone FROM usuarios WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erro no banco: " . $e->getMessage());
    die("Erro de conexão.");
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Localização</title>
    <link rel="stylesheet" href="../assets/css/localizacao.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
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
            <li><i class="fas fa-user"></i> <a href="../pages/conta.php">Conta</a></li>
            <li><i class="fas fa-map-marker-alt"></i> <a href="../pages/localizacao.php">Localização</a></li>
            <li><i class="fas fa-comment"></i> <span>Chat</span></li>
            <li><i class="fas fa-star"></i> <span>Favoritos</span></li>
            <li><i class="fas fa-cog"></i> <span>Configurações</span></li>
            <li><i class="fas fa-lock"></i> <span>Privacidade</span></li>
            <li class="logout">
                <a href="logout.php" style="color: inherit; text-decoration: none;">
                    <i class="fas fa-sign-out-alt"></i> <span>Sair</span>
                </a>
            </li>
        </ul>
    </aside>

    <div class="container">
        <h1><i class="fas fa-map-marker-alt"></i> Localização</h1>

        <!-- Card de dados atuais -->
        <div class="card">
            <h2>Dados de Localização</h2>
            <form method="POST" action="atualizar_localizacao.php">
                <div class="form-group">
                    <label for="cep">CEP</label>
                    <input type="text" id="cep" name="cep" onblur="buscarCEP(this.value)">
                </div>

                <div class="form-group">
                    <label for="estado">Estado</label>
                    <input type="text" id="estado" name="estado">
                </div>

                <div class="form-group">
                    <label for="cidade">Cidade</label>
                    <input type="text" id="cidade" name="cidade">
                </div>

                <div class="form-group">
                    <label for="endereco">Endereço</label>
                    <input type="text" id="endereco" name="endereco">
                </div>
                <button type="submit" class="btn">
                    <i class="fas fa-save"></i> Salvar Localização
                </button>
            </form>
        </div>

        <!-- Card de simulação -->
        <div class="card">
            <h2>Bandeira Tarifária Atual</h2>
            <p><strong>Estado:</strong> <?= htmlspecialchars($usuarioLocalizacao['estado']) ?></p>
            <p><strong>Bandeira:</strong> <span class="tag verde">Verde</span></p>
            <p><strong>Custo médio kWh:</strong> R$ 0,75</p>
            <p><strong>Estimativa mensal:</strong> R$ 238,90</p>
        </div>

        <!-- Card com mapa -->
        <div class="card">
            <h2>Mapa de Localização</h2>
            <div class="mapa">
                <!-- Exemplo com Google Maps Embed -->
                <iframe
                    src="https://www.google.com/maps?q=&output=embed"
                    width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy">
                </iframe>
            </div>
        </div>
    </div>

</body>

<script src="../assets/js/localizacao.js"></script>

</html>