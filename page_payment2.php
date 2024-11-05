<?php
include 'dbconnection.php';

$payment_id = $_GET['payment_id'];
$total_amount = $_GET['total_amount'];

include 'check_payment_status.php';

echo "<h3>Payment Status:</h3>";
echo $status_message;
?>
