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

try {
    // Buscar todos os equipamentos do usuário com informações do imóvel
    $sql = "SELECT e.*, i.estado, i.cidade, a.nome as area_nome, i.nome as imovel_nome
            FROM equipamentos e
            INNER JOIN areas a ON e.area_id = a.id
            INNER JOIN imoveis i ON a.imovel_id = i.id
            WHERE i.usuario_id = :usuario_id";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([':usuario_id' => $_SESSION['usuario_id']]);
    $equipamentos = $stmt->fetchAll();

    $consumo_total = [
        'diario_kwh' => 0,
        'mensal_kwh' => 0,
        'custo_mensal' => 0,
        'equipamentos' => []
    ];

    foreach ($equipamentos as $equip) {
        // Buscar tarifa do estado
        $stmt_tarifa = $db->prepare("SELECT custo_kwh FROM tarifas_energia WHERE estado = :estado ORDER BY vigencia_inicio DESC LIMIT 1");
        $stmt_tarifa->execute([':estado' => $equip['estado']]);
        $tarifa = $stmt_tarifa->fetch();
        
        $custo_kwh = $tarifa ? $tarifa['custo_kwh'] : 0.85; // Valor padrão
        
        // Cálculos
        $consumo_diario_kwh = ($equip['potencia'] * $equip['horas_por_dia']) / 1000;
        $consumo_mensal_kwh = $consumo_diario_kwh * 30;
        $custo_mensal = $consumo_mensal_kwh * $custo_kwh;
        
        $equipamento_calculado = [
            'id' => $equip['id'],
            'nome' => $equip['nome'],
            'modelo' => $equip['modelo'],
            'potencia' => $equip['potencia'],
            'horas_por_dia' => $equip['horas_por_dia'],
            'area_nome' => $equip['area_nome'],
            'imovel_nome' => $equip['imovel_nome'],
            'estado' => $equip['estado'],
            'custo_kwh' => $custo_kwh,
            'consumo_diario_kwh' => round($consumo_diario_kwh, 4),
            'consumo_mensal_kwh' => round($consumo_mensal_kwh, 2),
            'custo_mensal' => round($custo_mensal, 2)
        ];
        
        $consumo_total['diario_kwh'] += $consumo_diario_kwh;
        $consumo_total['mensal_kwh'] += $consumo_mensal_kwh;
        $consumo_total['custo_mensal'] += $custo_mensal;
        $consumo_total['equipamentos'][] = $equipamento_calculado;
        
        // Atualizar o equipamento com os cálculos
        $stmt_update = $db->prepare("UPDATE equipamentos SET consumo_diario_kwh = :diario, consumo_mensal_kwh = :mensal, custo_mensal = :custo WHERE id = :id");
        $stmt_update->execute([
            ':diario' => $consumo_diario_kwh,
            ':mensal' => $consumo_mensal_kwh,
            ':custo' => $custo_mensal,
            ':id' => $equip['id']
        ]);
    }
    
    $consumo_total['diario_kwh'] = round($consumo_total['diario_kwh'], 2);
    $consumo_total['mensal_kwh'] = round($consumo_total['mensal_kwh'], 2);
    $consumo_total['custo_mensal'] = round($consumo_total['custo_mensal'], 2);
    
    echo json_encode(["sucesso" => true, "consumo" => $consumo_total]);
    
} catch (PDOException $e) {
    error_log("Erro ao calcular consumo: " . $e->getMessage());
    echo json_encode(["sucesso" => false, "mensagem" => "Erro ao calcular consumo"]);
}