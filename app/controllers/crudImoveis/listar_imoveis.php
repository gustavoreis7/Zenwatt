<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([]);
    exit;
}

$database = new Database();
$db = $database->pdo;

$stmt = $db->prepare("SELECT id, nome, endereco FROM imoveis WHERE usuario_id = :usuario_id");
$stmt->execute([':usuario_id' => $_SESSION['usuario_id']]);
$imoveis = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($imoveis);
