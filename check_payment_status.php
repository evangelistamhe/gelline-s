<?php
include 'dbconnection.php';

$payment_id = $_GET['payment_id'];

$paymongo_url = "https://api.paymongo.com/v1/payments/$payment_id";
$secret_key = "sk_test_hoB2uYDekp9jLfnAVmsU9P2M"; // Replace with your actual secret key

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $paymongo_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Basic ' . base64_encode("$secret_key:")
]);

$response = curl_exec($ch);
curl_close($ch);

$response_data = json_decode($response, true);
if (isset($response_data['data']['attributes']['status']) && $response_data['data']['attributes']['status'] === 'paid') {
    echo "Payment successful!";
} else {
    echo "Payment not completed yet.";
}
?>
