<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Método não permitido']);
  exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
  http_response_code(400);
  echo json_encode(['error' => 'Dados inválidos']);
  exit;
}

if (empty($data['amount']) || empty($data['customer']['name']) || empty($data['customer']['email']) || empty($data['customer']['cpf'])) {
  http_response_code(400);
  echo json_encode(['error' => 'Dados obrigatórios não fornecidos']);
  exit;
}

// Montagem do payload conforme documentação (formato simplificado)
$apiData = [
  'amount' => floatval($data['amount']),
  'customer' => [
    'name'  => $data['customer']['name'],
    'email' => $data['customer']['email'],
    'cpf'   => preg_replace('/\D/', '', $data['customer']['cpf'])
  ],
  // Ajuste este postback para a sua URL pública com PHP
  'postbackUrl' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/webhook.php'
];

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

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
  http_response_code(500);
  echo json_encode(['error' => 'Erro de conexão: ' . $error]);
  exit;
}

http_response_code($httpCode ?: 200);
echo $response ?: json_encode(['error' => 'Resposta vazia da API']);
