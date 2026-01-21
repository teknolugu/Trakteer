<?php
// https://github.com/jovanzers/Trakteer

error_reporting(0);

$oid = $_GET['oid'] ?? null;
$url = $_GET['url'] ?? null;

$input = $oid ?? $url ?? "";

if (empty($input)) {
    echo '<a href="https://github.com/jovanzers/Trakteer">How to use?</a><hr>';
    echo 'ZERS was here!<br>With ❤️ by WinTen Dev';
    exit();
}

if (strpos($input, 'trakteer.id') === false) {
    $input = 'https://trakteer.id/payment-status/' . $input;
}

$ch = curl_init($input);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTPHEADER => [
        'Referer: https://trakteer.id',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
    ]
]);
$response = curl_exec($ch);
curl_close($ch);

if (!$response) {
    echo json_encode(['error' => 'Failed to fetch page']);
    exit;
}

libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTML($response);

$app = $dom->getElementById('app');
if (!$app) {
    echo json_encode(['error' => 'Data container not found']);
    exit;
}

$json = html_entity_decode($app->getAttribute('data-page'));
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(['error' => 'Failed to decode JSON']);
    exit;
}

$payload = $data['props']['payload']['data'] ?? null;

if (!$payload) {
    echo json_encode(['error' => 'Payload not found']);
    exit;
}

$result = [
    'OrderId'        => $payload['orderId'] ?? null,
    'Status'         => $payload['status'] ?? null,
    'OrderDate'      => isset($payload['createdAt']['date']) ? date('Y-m-d\TH:i:s\Z', strtotime($payload['createdAt']['date'])) : null,
    'PaidAt'         => isset($payload['paidAt']['date']) ? date('Y-m-d\TH:i:s\Z', strtotime($payload['paidAt']['date'])) : null,
    'PaymentMethod' => $payload['viaLabel'] ?? null,
    'PaymentGateway'=> $payload['paymentGateway'] ?? null,
    'CendolCount'   => isset($payload['quantity']) ? (int)$payload['quantity'] : 0,
    'AdminFees'     => isset($payload['paymentFee']) ? (int)$payload['paymentFee'] : 0,
    'Total'         => isset($payload['totalPrice']) ? (int)$payload['totalPrice'] : 0,
    'UnitName'      => $payload['unitName'] ?? null,
    'UnitPrice'     => isset($payload['unitPrice']) ? (int)$payload['unitPrice'] : 0,
    'CreatorName'   => $payload['creatorName'] ?? null,
    'CreatorPage'   => $payload['creatorPageUrl'] ?? null,
    'SupporterEmail'=> $payload['supporterEmail'] ?? null,
    'IsGuest'       => $payload['isGuest'] ?? null,
    'IsStreamGift'  => $payload['isStreamGift'] ?? null,
    'ExpiredAt'     => isset($payload['willExpiredAt']['date']) ? date('Y-m-d\TH:i:s\Z', strtotime($payload['willExpiredAt']['date'])) : null
];

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);
?>
