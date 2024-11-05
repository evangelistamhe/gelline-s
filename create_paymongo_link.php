<?php
require_once('vendor/autoload.php');

// Function to generate PayMongo payment link with multiple payment types
function createPayMongoLink($amount, $description) {
    $client = new \GuzzleHttp\Client();

    // Replace 'sk_test_uZmyFAdFPDEqhTmswuwnzcmv' with your actual PayMongo secret key
    $response = $client->request('POST', 'https://api.paymongo.com/v1/links', [
        'body' => json_encode([
            "data" => [
                "attributes" => [
                    "amount" => $amount, // Amount in centavos
                    "description" => $description,
                    "payment_method_types" => [
                        "gcash",       // GCash
                        "card",        // Credit/Debit Card
                        "paymaya",     // PayMaya
                        "bank_transfer", // Bank Transfers
                        "grab_pay"     // GrabPay
                    ] // Payment methods allowed
                ]
            ]
        ]),
        'headers' => [
            'accept' => 'application/json',
            'content-type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode('sk_test_uZmyFAdFPDEqhTmswuwnzcmv:') // Your actual secret key
        ],
    ]);

    // Parse the response and get the payment link
    $responseBody = json_decode($response->getBody(), true);
    return $responseBody['data']['attributes']['checkout_url'];
}

// Example: Generate a payment link
$paymentLink = createPayMongoLink(10000, 'Payment for Order #12345');
echo "Payment Link: " . $paymentLink;
