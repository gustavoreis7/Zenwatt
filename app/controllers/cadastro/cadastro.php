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

// Dados do formulário
$nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email_cadastro', FILTER_SANITIZE_EMAIL);
$telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING);
$senha = $_POST['senha_cadastro'] ?? '';
$confirmar_senha = $_POST['confirmar_senha'] ?? '';
$tensao = $_POST['tensao'] ?? '127'; // Valor padrão

// Validações
if (empty($nome) || empty($email) || empty($senha)) {
    echo json_encode(["sucesso" => false, "mensagem" => "Todos os campos são obrigatórios"]);
    exit;
}

if ($senha !== $confirmar_senha) {
    echo json_encode(["sucesso" => false, "mensagem" => "As senhas não coincidem"]);
    exit;
}

if (strlen($senha) < 6) {
    echo json_encode(["sucesso" => false, "mensagem" => "A senha deve ter pelo menos 6 caracteres"]);
    exit;
}

try {
    // Verificar se email já existe
    $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => $email]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(["sucesso" => false, "mensagem" => "Este email já está cadastrado"]);
        exit;
    }

    // Criptografar senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Inserir usuário
    $stmt = $db->prepare("INSERT INTO usuarios (nome, email, telefone, senha, tensao) VALUES (:nome, :email, :telefone, :senha, :tensao)");
    $stmt->execute([
        ':nome' => $nome,
        ':email' => $email,
        ':telefone' => $telefone,
        ':senha' => $senha_hash,
        ':tensao' => $tensao
    ]);

    // Login automático após cadastro
    $_SESSION['usuario_id'] = $db->lastInsertId();
    $_SESSION['usuario_nome'] = $nome;
    $_SESSION['usuario_email'] = $email;

    echo json_encode([
        "sucesso" => true, 
        "mensagem" => "Cadastro realizado com sucesso",
        "redirect" => "gerenciar.php"
    ]);

} catch (PDOException $e) {
    error_log("Erro no cadastro: " . $e->getMessage());
    echo json_encode(["sucesso" => false, "mensagem" => "Erro interno do servidor"]);
}