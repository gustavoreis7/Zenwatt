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
$area_id = $_POST['area_id'] ?? null;
$nome = trim($_POST['nome'] ?? '');
$modelo = trim($_POST['modelo'] ?? '');
$potencia = $_POST['potencia'] ?? null;
$horas = $_POST['horas_por_dia'] ?? null;

if (!$id || !$area_id || empty($nome) || !$potencia || !$horas) {
    echo json_encode(["sucesso" => false, "mensagem" => "Dados inválidos"]);
    exit;
}

$stmt = $db->prepare("
    UPDATE equipamentos
    SET nome = :nome, modelo = :modelo, potencia = :potencia, horas_por_dia = :horas, area_id = :area_id
    WHERE id = :id
");
$stmt->execute([
    ':nome' => $nome,
    ':modelo' => $modelo,
    ':potencia' => $potencia,
    ':horas' => $horas,
    ':area_id' => $area_id,
    ':id' => $id
]);

echo json_encode(["sucesso" => true, "mensagem" => "Equipamento atualizado com sucesso"]);
