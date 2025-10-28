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

$stmt = $db->prepare("
    SELECT a.id, a.nome, a.imovel_id, i.nome AS imovel_nome
    FROM areas a
    JOIN imoveis i ON a.imovel_id = i.id
    WHERE i.usuario_id = :usuario_id
");
$stmt->execute([':usuario_id' => $_SESSION['usuario_id']]);
$areas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($areas);
