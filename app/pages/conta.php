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
    <title>Minha Conta</title>
    <link rel="stylesheet" href="../assets/css/conta.css">
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
            <li class="active"><i class="fas fa-home"></i> <a href="../pages/usuario.php">Dashboard</a></li>
            <li><i class="fas fa-user"></i><a href="../pages/conta.php ">Conta</a></li>
            <li><i class="fas fa-map-marker-alt"></i> <span>Localização</span></li>
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
        <h1><i class="fas fa-user-circle"></i> Minha Conta</h1>

        <div class="card">
            <h2>Informações Pessoais</h2>
            <form method="POST" action="atualizar_conta.php">
                <div class="form-group">
                    <label for="nome">Nome Completo</label>
                    <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>">
                </div>
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>">
                </div>
                <div class="form-group">
                    <label for="telefone">Telefone</label>
                    <input type="text" id="telefone" name="telefone"
                        value="<?= htmlspecialchars($usuario['telefone']) ?>">
                </div>
                <button type="submit" class="btn">
                    <i class="fas fa-save"></i> Salvar Alterações
                </button>
            </form>
        </div>

        <div class="card">
            <h2>Alterar Senha</h2>
            <form method="POST" action="alterar_senha.php">
                <div class="form-group">
                    <label for="senha-atual">Senha Atual</label>
                    <input type="password" id="senha-atual" name="senha_atual">
                </div>
                <div class="form-group">
                    <label for="nova-senha">Nova Senha</label>
                    <input type="password" id="nova-senha" name="nova_senha">
                </div>
                <div class="form-group">
                    <label for="confirmar-senha">Confirmar Nova Senha</label>
                    <input type="password" id="confirmar-senha" name="confirmar_senha">
                </div>
                <button type="submit" class="btn">
                    <i class="fas fa-lock"></i> Alterar Senha
                </button>
            </form>
        </div>
    </div>

</body>

</html>