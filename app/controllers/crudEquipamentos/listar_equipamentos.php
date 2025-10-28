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
    SELECT e.id, e.nome, e.modelo, e.potencia, e.horas_por_dia, e.area_id, a.nome AS area_nome
    FROM equipamentos e
    JOIN areas a ON e.area_id = a.id
    JOIN imoveis i ON a.imovel_id = i.id
    WHERE i.usuario_id = :usuario_id
");
$stmt->execute([':usuario_id' => $_SESSION['usuario_id']]);
$equipamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($equipamentos);
