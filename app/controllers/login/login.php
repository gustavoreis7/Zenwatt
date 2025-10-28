<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["sucesso" => false, "mensagem" => "Método não permitido"]);
    exit;
}

$database = new Database();
$db = $database->pdo;

$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$senha = $_POST['senha'] ?? '';

if (empty($email) || empty($senha)) {
    echo json_encode(["sucesso" => false, "mensagem" => "Email e senha são obrigatórios"]);
    exit;
}

try {
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_email'] = $usuario['email'];
        
        echo json_encode([
            "sucesso" => true, 
            "mensagem" => "Login realizado com sucesso",
            "redirect" => "gerenciar.php"
        ]);
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => "Email ou senha incorretos"]);
    }
} catch (PDOException $e) {
    error_log("Erro no login: " . $e->getMessage());
    echo json_encode(["sucesso" => false, "mensagem" => "Erro interno do servidor"]);
}