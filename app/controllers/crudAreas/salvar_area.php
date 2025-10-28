<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

// DEBUG DETALHADO
error_log("=== SALVAR ÁREA ===");
error_log("SESSION: " . print_r($_SESSION, true));
error_log("POST: " . print_r($_POST, true));
error_log("GET: " . print_r($_GET, true));

if (!isset($_SESSION['usuario_id'])) {
    error_log("Usuário não logado");
    echo json_encode(["sucesso" => false, "mensagem" => "Usuário não logado"]);
    exit;
}

$database = new Database();
$db = $database->pdo;

$imovel_id = $_POST['imovel_id'] ?? null;
$nome = trim($_POST['nome'] ?? '');

error_log("imovel_id: " . $imovel_id);
error_log("nome: " . $nome);

if (!$imovel_id || empty($nome)) {
    error_log("Dados inválidos - imovel_id: " . ($imovel_id ? $imovel_id : 'NULL') . ", nome: " . ($nome ? $nome : 'VAZIO'));
    echo json_encode(["sucesso" => false, "mensagem" => "Dados inválidos"]);
    exit;
}

try {
    // Verificar se o imóvel pertence ao usuário
    $stmtVerifica = $db->prepare("SELECT id FROM imoveis WHERE id = :imovel_id AND usuario_id = :usuario_id");
    $stmtVerifica->execute([
        ':imovel_id' => $imovel_id,
        ':usuario_id' => $_SESSION['usuario_id']
    ]);
    
    if ($stmtVerifica->rowCount() === 0) {
        error_log("Imóvel não pertence ao usuário");
        echo json_encode(["sucesso" => false, "mensagem" => "Imóvel inválido"]);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO areas (imovel_id, nome) VALUES (:imovel_id, :nome)");
    $stmt->execute([':imovel_id' => $imovel_id, ':nome' => $nome]);
    
    error_log("Área cadastrada com sucesso - ID: " . $db->lastInsertId());
    echo json_encode(["sucesso" => true, "mensagem" => "Área cadastrada com sucesso"]);
    
} catch (PDOException $e) {
    error_log("Erro ao salvar área: " . $e->getMessage());
    echo json_encode(["sucesso" => false, "mensagem" => "Erro no banco de dados: " . $e->getMessage()]);
}