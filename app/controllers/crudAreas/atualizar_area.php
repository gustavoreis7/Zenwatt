<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(["sucesso" => false, "mensagem" => "Usuário não logado"]);
    exit;
}

$database = new Database();
$db = $database->pdo;

$id = $_POST['id'] ?? null;
$imovel_id = $_POST['imovel_id'] ?? null;
$nome = trim($_POST['nome'] ?? '');

if (!$id || !$imovel_id || empty($nome)) {
    echo json_encode(["sucesso" => false, "mensagem" => "Dados inválidos"]);
    exit;
}

$stmt = $db->prepare("
    UPDATE areas 
    SET nome = :nome, imovel_id = :imovel_id 
    WHERE id = :id
");
$stmt->execute([
    ':nome' => $nome,
    ':imovel_id' => $imovel_id,
    ':id' => $id
]);

echo json_encode(["sucesso" => true, "mensagem" => "Área atualizada com sucesso"]);
