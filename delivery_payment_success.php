<?php
session_start();
require 'dbconnection.php';
require_once('FPDF/fpdf.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];

    // Fetch order details
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo "Order not found.";
        exit;
    }

    
    // Update the payment status to 'Completed' for GCash or COD
    $payment_status = ($payment_method === 'gcash') ? 'Completed' : 'Pending';
    $stmt = $pdo->prepare("UPDATE payments SET payment_status = ? WHERE order_id = ?");
    $stmt->execute([$payment_status, $order_id]);

    // Fetch user details
    $stmt = $pdo->prepare("SELECT first_name, last_name, email FROM users WHERE user_id = ?");
    $stmt->execute([$order['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch order items
    $stmt = $pdo->prepare("SELECT dish_name, quantity, price, total_price FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Define the PDF file path
    $pdf_file_path = 'receipt_pdf/receipt_' . $order_id . '.pdf';

    // Generate the PDF
    $pdf = new FPDF();
    $pdf->AddPage();

    // Logo and Title
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Image('img/gellineslogoo.png', 10, 10, 30);
    $pdf->Cell(0, 20, "Gelline's Sizzling and Restaurant", 0, 1, 'C');
    $pdf->Ln(10);

    // Main Title for Receipt
    $pdf->SetFont('Arial', 'B', 20);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 10, "Order Receipt", 0, 1, 'C');
    $pdf->Ln(10);

    // Table Header
    $pdf->SetFillColor(255, 219, 68);
    $pdf->SetFont('Arial', 'B', 12);
    $tableStartX = ($pdf->GetPageWidth() - 160) / 2;
    $pdf->SetX($tableStartX);
    $pdf->Cell(70, 10, "Details", 1, 0, 'C', true);
    $pdf->Cell(90, 10, "Information", 1, 1, 'C', true);

    // Table Content
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetX($tableStartX);
    $pdf->Cell(70, 10, "Order ID:", 1, 0, 'L', true);
    $pdf->Cell(90, 10, $order_id, 1, 1, 'L', true);

    $pdf->SetX($tableStartX);
    $pdf->Cell(70, 10, "Customer Name:", 1, 0, 'L', true);
    $pdf->Cell(90, 10, $user['first_name'] . ' ' . $user['last_name'], 1, 1, 'L', true);

    $pdf->SetX($tableStartX);
    $pdf->Cell(70, 10, "Order Date:", 1, 0, 'L', true);
    $pdf->Cell(90, 10, date('Y-m-d'), 1, 1, 'L', true);
    $pdf->Ln(5);

    // Order Items Header
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(255, 219, 68);
$table_width = 170; // Adjust the total table width as per your columns

// Calculate the X position to center the table
$tableX = ($pdf->GetPageWidth() - $table_width) / 2;

$pdf->SetX($tableX); // Center the header
$pdf->Cell(60, 10, "Dish Name", 1, 0, 'C', true);
$pdf->Cell(30, 10, "Quantity", 1, 0, 'C', true);
$pdf->Cell(40, 10, "Price (PHP)", 1, 0, 'C', true);
$pdf->Cell(40, 10, "Total (PHP)", 1, 1, 'C', true);

// Order Items
$pdf->SetFont('Arial', '', 12);
$pdf->SetFillColor(255, 255, 255);
foreach ($order_items as $item) {
    $pdf->SetX($tableX); // Center each row
    $pdf->Cell(60, 10, $item['dish_name'], 1);
    $pdf->Cell(30, 10, $item['quantity'], 1, 0, 'C');
    $pdf->Cell(40, 10, number_format($item['price'], 2), 1, 0, 'R');
    $pdf->Cell(40, 10, number_format($item['total_price'], 2), 1, 1, 'R');
}

// Continue with your Total Amount as shown earlier


    // Total Amount
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(220, 20, 60);
    $pdf->Cell(60, 10, "", 0);
    $pdf->Cell(70, 10, "Total Amount:", 1, 0, 'C', true);
    $pdf->Cell(40, 10, "PHP " . number_format($order['total_amount'], 2), 1, 1, 'C', true);
    $pdf->Ln(10);

    // Thank You Message
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', 'I', 12);
    $pdf->Cell(0, 10, "Thank you for dining with us at Gelline's Restaurant!", 0, 1, 'C');
    $pdf->Ln(5);
    $pdf->Cell(0, 10, "We look forward to serving you again soon.", 0, 1, 'C');

    // Save PDF
    $pdf->Output('F', $pdf_file_path);

    // Send Email with PDF
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'gelssizzlingresto@gmail.com';
        $mail->Password = 'xhdh mqrv axlo xsnm';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('gelssizzlingresto@gmail.com', 'Gelline\'s Sizzling and Restaurant');
        $mail->addAddress($user['email'], $user['first_name'] . ' ' . $user['last_name']);
        $mail->addAttachment($pdf_file_path);

        $mail->isHTML(true);
        $mail->Subject = 'Order Confirmation and Receipt';
        $mail->Body = "Dear " . $user['first_name'] . " " . $user['last_name'] . ",<br><br>Thank you for your order. Please find your receipt attached.";

        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }

     // Show alert for COD
    if ($payment_method === 'cod') {
        echo "<script>alert('Your order has been placed successfully via Cash on Delivery!');</script>";
    }

    header('Location: payment_success_landing_page.php?order_id=' . $order_id);
    exit;
}
?>