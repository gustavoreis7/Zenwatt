<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');

// Desativar exibição de erros na resposta
ini_set('display_errors', 0);
error_reporting(0);

try {
    if (!isset($_SESSION['usuario_id'])) {
        throw new Exception("Usuário não logado");
    }

    $database = new Database();
    $db = $database->pdo;

    $nome = trim($_POST['nome'] ?? '');
    $cep = trim($_POST['cep'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
    $bairro = trim($_POST['bairro'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $estado = trim($_POST['estado'] ?? '');

    if (empty($nome)) {
        throw new Exception("O nome do imóvel é obrigatório");
    }

    $stmt = $db->prepare("INSERT INTO imoveis (usuario_id, nome, cep, endereco, numero, bairro, cidade, estado) VALUES (:usuario_id, :nome, :cep, :endereco, :numero, :bairro, :cidade, :estado)");
    
    $success = $stmt->execute([
        ':usuario_id' => $_SESSION['usuario_id'],
        ':nome' => $nome,
        ':cep' => $cep,
        ':endereco' => $endereco,
        ':numero' => $numero,
        ':bairro' => $bairro,
        ':cidade' => $cidade,
        ':estado' => $estado
    ]);

    if ($success) {
        echo json_encode([
            "sucesso" => true, 
            "mensagem" => "Imóvel cadastrado com sucesso",
            "id" => $db->lastInsertId()
        ]);
    } else {
        throw new Exception("Erro ao inserir no banco de dados");
    }

} catch (Exception $e) {
    echo json_encode([
        "sucesso" => false, 
        "mensagem" => $e->getMessage(),
        "erro" => "Erro no servidor"
    ]);
}