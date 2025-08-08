<?php
// Webhook para receber notificações da API SyncPay
header('Content-Type: application/json');

// Log das notificações recebidas
$logFile = 'webhook_log.txt';
$timestamp = date('Y-m-d H:i:s');
$input = file_get_contents('php://input');

// Log da requisição
$logData = "[$timestamp] Webhook recebido:\n";
$logData .= "Headers: " . json_encode(getallheaders()) . "\n";
$logData .= "Body: " . $input . "\n";
$logData .= "----------------------------------------\n";

file_put_contents($logFile, $logData, FILE_APPEND);

// Decodificar dados recebidos
$data = json_decode($input, true);

if ($data) {
    // Verificar status da transação
    $status = $data['status'] ?? '';
    $idTransaction = $data['idTransaction'] ?? '';
    $amount = $data['amount'] ?? 0;
    
    // Log do processamento
    $processLog = "[$timestamp] Processando transação:\n";
    $processLog .= "ID: $idTransaction\n";
    $processLog .= "Status: $status\n";
    $processLog .= "Valor: $amount\n";
    $processLog .= "----------------------------------------\n";
    
    file_put_contents($logFile, $processLog, FILE_APPEND);
    
    // Se o pagamento foi aprovado
    if ($status === 'APPROVED' || $status === 'PAID') {
        // Aqui você pode implementar a lógica para:
        // 1. Enviar e-mail de confirmação
        // 2. Atualizar banco de dados
        // 3. Processar entrega dos diamantes
        // 4. Notificar o usuário
        
        $successLog = "[$timestamp] Pagamento aprovado para transação: $idTransaction\n";
        file_put_contents($logFile, $successLog, FILE_APPEND);
        
        // Responder com sucesso
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => 'Webhook processado com sucesso'
        ]);
    } else {
        // Pagamento não aprovado
        $pendingLog = "[$timestamp] Pagamento pendente para transação: $idTransaction (Status: $status)\n";
        file_put_contents($logFile, $pendingLog, FILE_APPEND);
        
        http_response_code(200);
        echo json_encode([
            'status' => 'pending',
            'message' => 'Pagamento ainda não foi aprovado'
        ]);
    }
} else {
    // Dados inválidos
    $errorLog = "[$timestamp] Erro: Dados inválidos recebidos\n";
    file_put_contents($logFile, $errorLog, FILE_APPEND);
    
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Dados inválidos'
    ]);
}
?>
