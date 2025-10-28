<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Não autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->pdo;
    
    // Receber dados do formulário
    $area_id = $_POST['area_id'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $potencia = $_POST['potencia'] ?? '';
    $horas_por_dia = $_POST['horas_por_dia'] ?? '';
    $usuario_id = $_SESSION['usuario_id'];
    
    // Validações básicas
    if (empty($area_id) || empty($nome) || empty($potencia) || empty($horas_por_dia)) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha todos os campos obrigatórios']);
        exit;
    }
    
    // Verificar se a área pertence ao usuário
    try {
        $stmt = $db->prepare("
            SELECT a.id 
            FROM areas a 
            INNER JOIN imoveis i ON a.imovel_id = i.id 
            WHERE a.id = :area_id AND i.usuario_id = :usuario_id
        ");
        $stmt->execute([
            ':area_id' => $area_id,
            ':usuario_id' => $usuario_id
        ]);
        
        if ($stmt->rowCount() === 0) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Área não encontrada ou não pertence ao usuário']);
            exit;
        }
        
        // Inserir equipamento
        $stmt = $db->prepare("
            INSERT INTO equipamentos (area_id, nome, modelo, potencia, horas_por_dia) 
            VALUES (:area_id, :nome, :modelo, :potencia, :horas_por_dia)
        ");
        $stmt->execute([
            ':area_id' => $area_id,
            ':nome' => $nome,
            ':modelo' => $modelo,
            ':potencia' => $potencia,
            ':horas_por_dia' => $horas_por_dia
        ]);
        
        echo json_encode(['sucesso' => true, 'mensagem' => 'Equipamento cadastrado com sucesso']);
    } catch (PDOException $e) {
        error_log("Erro ao cadastrar equipamento: " . $e->getMessage());
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao cadastrar equipamento: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido']);
}
?>