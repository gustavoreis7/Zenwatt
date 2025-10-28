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

if (!$id) {
    echo json_encode(["sucesso" => false, "mensagem" => "ID inválido"]);
    exit;
}

$stmt = $db->prepare("DELETE FROM areas WHERE id = :id");
$stmt->execute([':id' => $id]);

echo json_encode(["sucesso" => true, "mensagem" => "Área excluída com sucesso"]);
