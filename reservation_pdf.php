<?php
session_start();
require 'dbconnection.php';
require 'FPDF/fpdf.php'; // Ensure FPDF library is included

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

// Get the filter status from the query parameter
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$statusCondition = '';
if ($statusFilter !== 'all') {
    $statusCondition = "AND reservations.status = :status";
}

// Prepare the query with the selected filter
$query = "
    SELECT 
        reservations.reservation_id, 
        users.first_name, 
        users.last_name, 
        reservations.table_number, 
        reservations.reservation_date, 
        reservations.time_in, 
        reservations.time_out, 
        reservations.guest_count, 
        reservations.created_at, 
        reservations.status, 
        reservations.total_price
    FROM 
        reservations
    JOIN 
        users 
    ON 
        reservations.user_id = users.user_id
    WHERE 1=1 $statusCondition
    ORDER BY reservations.reservation_date DESC
";

$stmt = $pdo->prepare($query);

// Bind parameters if filtering by status
if ($statusFilter !== 'all') {
    $stmt->bindParam(':status', $statusFilter);
}

$stmt->execute();
$reservations = $stmt->fetchAll();

// Initialize FPDF and set up the PDF document
$pdf = new FPDF();
$pdf->AddPage();

// Header design
$pdf->SetFillColor(255, 219, 68); // Yellow background color for title
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(190, 15, 'Reservation Report', 0, 1, 'C', true);
$pdf->Ln(5);

// Display filter info
$filterText = ucfirst($statusFilter) . ' Reservations';
$pdf->SetFont('Arial', 'I', 12);
$pdf->Cell(190, 10, $filterText, 0, 1, 'C');
$pdf->Ln(10);

// Table Header design
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(245, 245, 245); // Light grey background for headers
$pdf->SetTextColor(0); // Black text color
$pdf->SetDrawColor(220, 220, 220); // Light grey border

// Header cells
$pdf->Cell(20, 10, 'ID', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'First Name', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Last Name', 1, 0, 'C', true);
$pdf->Cell(20, 10, 'Table', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Date', 1, 0, 'C', true);
$pdf->Cell(20, 10, 'Status', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Total Price', 1, 1, 'C', true);

// Table Rows design
$pdf->SetFont('Arial', '', 10);
$pdf->SetFillColor(255, 255, 255); // White background for rows
$pdf->SetTextColor(0); // Black text color
$fill = false; // Toggle fill color for alternating row colors

foreach ($reservations as $reservation) {
    $pdf->Cell(20, 10, $reservation['reservation_id'], 1, 0, 'C', $fill);
    $pdf->Cell(30, 10, $reservation['first_name'], 1, 0, 'C', $fill);
    $pdf->Cell(30, 10, $reservation['last_name'], 1, 0, 'C', $fill);
    $pdf->Cell(20, 10, $reservation['table_number'], 1, 0, 'C', $fill);
    $pdf->Cell(30, 10, $reservation['reservation_date'], 1, 0, 'C', $fill);
    $pdf->Cell(20, 10, ucfirst($reservation['status']), 1, 0, 'C', $fill);
    $pdf->Cell(30, 10, 'PHP ' . number_format($reservation['total_price'], 2), 1, 1, 'C', $fill);
    $fill = !$fill; // Alternate row color
}

// Output PDF to browser
$pdf->Output('D', 'Reservation_Report.pdf'); // Download file with filename 'Reservation_Report.pdf'
?>