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
$nome = trim($_POST['nome'] ?? '');
$cep = trim($_POST['cep'] ?? '');
$endereco = trim($_POST['endereco'] ?? '');
$numero = trim($_POST['numero'] ?? '');
$bairro = trim($_POST['bairro'] ?? '');
$cidade = trim($_POST['cidade'] ?? '');
$estado = trim($_POST['estado'] ?? '');

if (!$id || empty($nome)) {
    echo json_encode(["sucesso" => false, "mensagem" => "Dados inválidos"]);
    exit;
}

$stmt = $db->prepare("UPDATE imoveis SET nome = :nome, cep = :cep, endereco = :endereco, numero = :numero, bairro = :bairro, cidade = :cidade, estado = :estado WHERE id = :id AND usuario_id = :usuario_id");
$stmt->execute([
    ':nome' => $nome,
    ':cep' => $cep,
    ':endereco' => $endereco,
    ':numero' => $numero,
    ':bairro' => $bairro,
    ':cidade' => $cidade,
    ':estado' => $estado,
    ':id' => $id,
    ':usuario_id' => $_SESSION['usuario_id']
]);

echo json_encode(["sucesso" => true, "mensagem" => "Imóvel atualizado com sucesso"]);