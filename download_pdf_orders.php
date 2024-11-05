<?php
session_start();
require 'dbconnection.php';
require 'fpdf/fpdf.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

// Fetch delivered and cancelled orders
$delivered_orders = $pdo->query("
    SELECT o.order_id, o.total_amount, o.order_date, u.first_name, u.last_name
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    WHERE o.order_status = 'delivered'
    ORDER BY o.order_date DESC
")->fetchAll();

$cancelled_orders = $pdo->query("
    SELECT o.order_id, o.total_amount, o.order_date, u.first_name, u.last_name
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    WHERE o.order_status = 'cancelled'
    ORDER BY o.order_date DESC
")->fetchAll();

// Create PDF with styling
$pdf = new FPDF();
$pdf->AddPage();

// Add title with background color and custom font
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetFillColor(255, 219, 68); // Yellow background similar to #ffdb44
$pdf->Cell(0, 12, 'Completed Orders Report', 0, 1, 'C', true);
$pdf->Ln(8);

// Delivered Orders Section
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(0, 0, 0); // Black text
$pdf->Cell(0, 10, 'Delivered Orders', 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);

// Table Header with background color
$pdf->SetFillColor(255, 219, 68);
$pdf->Cell(30, 10, 'Order ID', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Customer Name', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Total Amount', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Order Date', 1, 1, 'C', true);

// Table Rows with alternating row colors
$fill = false;
foreach ($delivered_orders as $order) {
    $pdf->SetFillColor(240, 240, 240); // Light gray for alternate rows
    $pdf->Cell(30, 10, $order['order_id'], 1, 0, 'C', $fill);
    $pdf->Cell(50, 10, $order['first_name'] . ' ' . $order['last_name'], 1, 0, 'C', $fill);
    $pdf->Cell(30, 10, 'PHP ' . number_format($order['total_amount'], 2), 1, 0, 'C', $fill);
    $pdf->Cell(50, 10, $order['order_date'], 1, 1, 'C', $fill);
    $fill = !$fill; // Toggle fill color for alternating rows
}

// Add spacing between sections
$pdf->Ln(10);

// Cancelled Orders Section
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 10, 'Cancelled Orders', 0, 1, 'L');
$pdf->SetFont('Arial', '', 10);

// Table Header
$pdf->SetFillColor(255, 219, 68);
$pdf->Cell(30, 10, 'Order ID', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Customer Name', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Total Amount', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Order Date', 1, 1, 'C', true);

// Table Rows with alternating row colors
$fill = false;
foreach ($cancelled_orders as $order) {
    $pdf->SetFillColor(240, 240, 240); // Light gray for alternate rows
    $pdf->Cell(30, 10, $order['order_id'], 1, 0, 'C', $fill);
    $pdf->Cell(50, 10, $order['first_name'] . ' ' . $order['last_name'], 1, 0, 'C', $fill);
    $pdf->Cell(30, 10, 'PHP ' . number_format($order['total_amount'], 2), 1, 0, 'C', $fill);
    $pdf->Cell(50, 10, $order['order_date'], 1, 1, 'C', $fill);
    $fill = !$fill; // Toggle fill color for alternating rows
}

// Output the PDF
$pdf->Output('D', 'Completed_Orders_Report.pdf');
exit;
?>