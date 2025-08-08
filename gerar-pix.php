<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Receber dados do frontend
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Dados inválidos']);
    exit;
}

// Validar dados obrigatórios
if (empty($data['amount']) || empty($data['customer']['name']) || empty($data['customer']['email']) || empty($data['customer']['cpf'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Dados obrigatórios não fornecidos']);
    exit;
}

// Preparar dados para API SyncPay
$apiData = [
    'amount' => floatval($data['amount']),
    'customer' => [
        'name' => $data['customer']['name'],
        'email' => $data['customer']['email'],
        'cpf' => $data['customer']['cpf']
    ],
    'postbackUrl' => 'https://recarga-jogos.onl/webhook.php'
];

// Configurar requisição cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.syncpay.pro/v1/gateway/api/');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($apiData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Basic ' . base64_encode('d0bb944b4e93470dfc084a95:'),
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

// Fazer requisição
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Log da requisição
$logFile = 'api_log.txt';
$timestamp = date('Y-m-d H:i:s');
$logData = "[$timestamp] Requisição para API SyncPay:\n";
$logData .= "Dados enviados: " . json_encode($apiData) . "\n";
$logData .= "HTTP Code: $httpCode\n";
$logData .= "Resposta: $response\n";
$logData .= "Erro cURL: $error\n";
$logData .= "----------------------------------------\n";
file_put_contents($logFile, $logData, FILE_APPEND);

// Verificar erros
if ($error) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro de conexão: ' . $error]);
    exit;
}

// Verificar código HTTP
if ($httpCode !== 200) {
    http_response_code($httpCode);
    echo json_encode(['error' => 'Erro da API: HTTP ' . $httpCode, 'response' => $response]);
    exit;
}

// Decodificar resposta
$responseData = json_decode($response, true);

if (!$responseData) {
    http_response_code(500);
    echo json_encode(['error' => 'Resposta inválida da API']);
    exit;
}

// Retornar resposta da API
echo json_encode($responseData);
?>
