<?php
if (empty($_GET['url'])) {
    echo 'ZERS was here!<br>With ❤️ by WinTen Dev';
    exit();
}

$url = $_GET['url'];
$ch = curl_init($url);
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
$cendol = $xpath->query('//*[@id="wrapper"]/div/div/div[3]/div[3]/div[1]/div[1]/span');
$date = $xpath->query('//*[@id="wrapper"]/div/div/div[3]/div[4]/div[1]/div[1]/div[2]');
$paymentMethod = $xpath->query('//*[@id="wrapper"]/div/div/div[3]/div[4]/div[1]/div[2]/div[2]');
$amount = $xpath->query('//*[@id="wrapper"]/div/div/div[3]/div[3]/div[2]');
$adminFees = $xpath->query('//*[@id="wrapper"]/div/div/div[3]/div[3]/div[1]/div[4]/span[2]');

$result = [
    'OrderId' => $orderId[0]->nodeValue,
    'OrderDate' => $date[0]->nodeValue,
    'PaymentMethod' => $paymentMethod[0]->nodeValue,
    'CendolCount' => (int) preg_replace('/[^0-9]/', '', $cendol[0]->nodeValue),
    'AdminFees' => (int) preg_replace('/[^0-9]/', '', $adminFees[0]->nodeValue),
    'Total' => (int) preg_replace('/[^0-9]/', '', $amount[0]->nodeValue)
];

header('Content-Type: application/json');
echo json_encode($result);
?>
