<?php
include 'dbconnection.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if ($data['data']['attributes']['status'] === 'paid') {
    $order_id = $data['data']['attributes']['reference_number'];

    $update_query = "UPDATE order_items SET status = 'paid' WHERE order_id = $order_id";
    mysqli_query($conn, $update_query);

    http_response_code(200);
} else {
    http_response_code(400);
}
?>
