<?php
// https://github.com/jovanzers/Trakteer

error_reporting(0);

$oid = $_GET['oid'];
$url = $_GET['url'];

$input = $oid ?? $url ?? "";

if (empty($input)) {
    echo '<a href="https://github.com/jovanzers/Trakteer">How to use?</a><hr>';
    echo 'ZERS was here!<br>With ❤️ by WinTen Dev';
    exit();
}

if (strpos($input, 'trakteer.id') == false) {
    $input = 'https://trakteer.id/payment-status/' . $input;
}
$ch = curl_init($input);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Referer: https://trakteer.id',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36'
    ]
]);
$response = curl_exec($ch);
curl_close($ch);

$dom = new DOMDocument();
@$dom->loadHTML($response);

$xpath = new DOMXPath($dom);
$orderId = $xpath->query('//*[@id="wrapper"]/div/div/div[3]/div[4]/div[2]/div[2]');
$cendol = $xpath->query('//*[@id="wrapper"]/div/div/div[3]/div[3]/div[2]/div/div[1]/span[2]');
$orderDate = $xpath->query('//*[@id="wrapper"]/div/div/div[3]/div[4]/div[1]/div[1]/div[2]');
$paymentMethod = $xpath->query('//*[@id="wrapper"]/div/div/div[3]/div[4]/div[1]/div[2]/div[2]');
$amount = $xpath->query('//*[@id="wrapper"]/div/div/div[3]/div[3]/div[2]');
$adminFees = $xpath->query('//*[@id="wrapper"]/div/div/div[3]/div[3]/div[2]/div/div[2]/span[2]');

$result = [
    'OrderId' => @$orderId[0]->nodeValue,
    'OrderDate' => date('Y-m-d\TH:i:s\Z', strtotime(str_replace(' WIB', '', @$orderDate[0]->nodeValue))),
    'PaymentMethod' => @$paymentMethod[0]->nodeValue,
    'CendolCount' => (int)preg_replace('/[^0-9]/', '', explode('x', @$cendol[0]->nodeValue)),
    'AdminFees' => (int)preg_replace('/[^0-9]/', '', @$adminFees[0]->nodeValue),
    'Total' => (int)preg_replace('/[^0-9]/', '', @$amount[0]->nodeValue)
];

header('Content-Type: application/json');
echo json_encode($result);
?>
