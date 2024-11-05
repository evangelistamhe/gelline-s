<?php
session_start();
require 'dbconnection.php'; // Database connection

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/vendor/autoload.php'; // Load Composer's autoloader
require_once('FPDF/fpdf.php'); // Only include FPDF once

// Ensure reservation_id is passed
if (!isset($_GET['reservation_id'])) {
    echo "Invalid access. No reservation ID provided.";
    exit;
}

$reservation_id = htmlspecialchars($_GET['reservation_id']); // Sanitize input

// Fetch the reservation details along with table and user information
try {
    $stmt = $pdo->prepare("
        SELECT r.*, t.table_number, t.capacity, t.price_per_person, u.first_name, u.last_name, u.email 
        FROM reservations r 
        JOIN restaurant_tables t ON r.table_number = t.table_number  
        JOIN users u ON r.user_id = u.user_id
        WHERE r.reservation_id = ?
    ");
    $stmt->execute([$reservation_id]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

    // Updated condition to allow for 'pending' or 'reserved' status
    if (!$reservation || !in_array($reservation['status'], ['pending', 'reserved'])) {
        echo "Reservation not found or already completed.";
        exit;
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo "Error fetching reservation: " . $e->getMessage();
    exit;
}

// Define the file path for the receipt PDF
$pdf_file_path = 'receipt_pdf/receipt_' . $reservation_id . '.pdf';

// Generate the PDF for the receipt
$pdf = new FPDF();
$pdf->AddPage();

// Logo and Title
$pdf->SetFont('Arial', 'B', 16);
$pdf->Image('img/gellineslogoo.png', 10, 10, 30); // Adjust path and size if needed
$pdf->Cell(0, 20, "Gelline's Sizzling and Restaurant", 0, 1, 'C');
$pdf->Ln(10);

// Main Title for Receipt
$pdf->SetFont('Arial', 'B', 20);
$pdf->SetTextColor(0, 0, 0); // Black color for title
$pdf->Cell(0, 10, "Reservation Receipt", 0, 1, 'C');
$pdf->Ln(10);

// Set text color to black for table content
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 12);

// Table Header with gold-like color
$pdf->SetFillColor(255, 219, 68); // Light gold background
$pdf->SetFont('Arial', 'B', 12);

// Center the table header
$tableStartX = ($pdf->GetPageWidth() - 160) / 2; // Adjust width based on table size
$pdf->SetX($tableStartX);
$pdf->Cell(70, 10, "Details", 1, 0, 'C', true);
$pdf->Cell(90, 10, "Information", 1, 1, 'C', true); // Moves to next line after header

// Table Content
$pdf->SetFont('Arial', '', 12);
$pdf->SetFillColor(255, 255, 255); // White background for rows

// Center the table on the page
$pdf->SetX($tableStartX);

// Row 1 - Reservation ID
$pdf->Cell(70, 10, "Reservation ID:", 1, 0, 'L', true);
$pdf->Cell(90, 10, $reservation_id, 1, 1, 'L', true);

// Center the remaining rows in the table
$pdf->SetX($tableStartX);

// Row 2 - Name
$pdf->Cell(70, 10, "Name:", 1, 0, 'L', true);
$pdf->Cell(90, 10, $reservation['first_name'] . ' ' . $reservation['last_name'], 1, 1, 'L', true);

// Row 3 - Table Number
$pdf->SetX($tableStartX);
$pdf->Cell(70, 10, "Table Number:", 1, 0, 'L', true);
$pdf->Cell(90, 10, $reservation['table_number'], 1, 1, 'L', true);

// Row 4 - Guests
$pdf->SetX($tableStartX);
$pdf->Cell(70, 10, "Guests:", 1, 0, 'L', true);
$pdf->Cell(90, 10, $reservation['guest_count'], 1, 1, 'L', true);

// Row 5 - Date
$pdf->SetX($tableStartX);
$pdf->Cell(70, 10, "Date:", 1, 0, 'L', true);
$pdf->Cell(90, 10, $reservation['reservation_date'], 1, 1, 'L', true);

// Row 6 - Time In
$pdf->SetX($tableStartX);
$pdf->Cell(70, 10, "Time In:", 1, 0, 'L', true);
$pdf->Cell(90, 10, $reservation['time_in'], 1, 1, 'L', true);

// Row 7 - Time Out
$pdf->SetX($tableStartX);
$pdf->Cell(70, 10, "Time Out:", 1, 0, 'L', true);
$pdf->Cell(90, 10, $reservation['time_out'], 1, 1, 'L', true);

// Center the total amount row
$pdf->SetX($tableStartX);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(220, 20, 60); // Crimson for total
$pdf->Cell(70, 10, "Total Amount:", 1, 0, 'C', true);
$pdf->Cell(90, 10, "PHP " . number_format($reservation['total_price'], 2), 1, 1, 'C', true);
$pdf->Ln(10);

// Thank You Message
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', 'I', 12);
$pdf->Cell(0, 10, "Thank you for dining with us at Gelline's Restaurant!", 0, 1, 'C');
$pdf->Ln(5);
$pdf->Cell(0, 10, "We look forward to serving you again soon.", 0, 1, 'C');

// Save PDF
$pdf->Output('F', $pdf_file_path);


// Handle payment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Insert payment details into respayments table
        $stmt = $pdo->prepare("
            INSERT INTO respayments (reservation_id, amount, respayment_method, respayment_status, paid_at)
            VALUES (?, ?, 'GCash', 'Paid', NOW())
        ");
        $stmt->execute([$reservation_id, $reservation['total_price']]);

        if ($stmt->rowCount() > 0) {
            // Update the reservation status to 'confirmed'
            $update_reservation = $pdo->prepare("UPDATE reservations SET status = 'confirmed' WHERE reservation_id = ?");
            $update_reservation->execute([$reservation_id]);

            // Send email with PDF receipt
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'gelssizzlingresto@gmail.com'; // Replace with your email
                $mail->Password = 'xhdh mqrv axlo xsnm'; // Use environment variables for security
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Set email parameters
                $mail->setFrom('gelssizzlingresto@gmail.com', 'Gelline\'s Restaurant');
                $mail->addAddress($reservation['email'], $reservation['first_name'] . ' ' . $reservation['last_name']);
                $mail->isHTML(true);
                $mail->Subject = 'Reservation Confirmation';

                // Create email body with reservation details
                $mailBody = "
                    <h2>Reservation Confirmed</h2>
                    <p>Dear {$reservation['first_name']} {$reservation['last_name']},</p>
                    <p>Your reservation at Gelline's Restaurant has been successfully made.</p>
                    <p><strong>Details of Your Reservation:</strong></p>
                    <ul>
                        <li><strong>Date:</strong> {$reservation['reservation_date']}</li>
                        <li><strong>Time-In:</strong> {$reservation['time_in']}</li>
                        <li><strong>Time-Out:</strong> {$reservation['time_out']}</li>
                        <li><strong>Table:</strong> Table {$reservation['table_number']}</li>
                        <li><strong>Guests:</strong> {$reservation['guest_count']}</li>
                    </ul>
                    <p>Thank you for choosing Gelline's Restaurant. We look forward to serving you!</p>
                ";

                $mail->Body = $mailBody;

                // Attach PDF Receipt
                $mail->addAttachment($pdf_file_path);

                // Send the email
                $mail->send();

                echo "<script>alert('Payment successful! A confirmation email with the PDF receipt has been sent to {$reservation['email']}'); window.location.href = 'receipt.php?reservation_id=$reservation_id';</script>";

            } catch (Exception $e) {
                error_log("Email sending failed: " . $mail->ErrorInfo); // Log error
                echo "<script>alert('Payment successful, but the confirmation email could not be sent. Please check the error log.'); window.location.href = 'receipt.php?reservation_id=$reservation_id';</script>";
            }
        } else {
            echo "<p>Failed to insert payment details into respayments table.</p>";
        }
    } catch (PDOException $e) {
        echo "Error inserting payment: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400&display=swap" rel="stylesheet">
    <title>Payment - GCash Style</title>
    <style>
        body {
            font-family: 'Rubik', sans-serif;
            margin: 0;
            background-color: #f0f4f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f3f7fe;
        }

        .payment-container {
            background-color: white;
            padding: 20px;
            width: 350px;
            border-radius: 15px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .profile {
            margin-top: 15px;
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background-color: #ccc;
            display: inline-block;
            font-size: 32px;
            color: white;
            line-height: 70px;
            margin-bottom: 10px;
        }

        .g-container {
            background-color: #0052CC;
        }

        h2 {
            font-size: 18px;
            margin: 5px 0;
            color: #fff;
            background-color: #0052CC;
            padding: 10px;
            border-radius: 10px 10px 0 0;
        }

        .header {
            background-color: #0047a9;
            padding: 15px;
            border-radius: 10px 10px 0 0;
            color: white;
            font-size: 20px;
            font-weight: bold;
        }

        .pay-with {
            background-color: #e9f0fb;
            padding: 10px 15px;
            margin-top: 15px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #ddd;
        }

        .pay-with span {
            font-size: 14px;
            color: #333;
        }

        .pay-with .pay-method {
            color: #2d84e3;
            font-weight: bold;
        }

        .amount-section {
            margin-top: 20px;
            text-align: left;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }

        .amount-section p {
            margin: 5px 0;
            font-size: 14px;
            color: #333;
        }

        .amount-section .total-amount {
            font-size: 16px;
            font-weight: bold;
        }

        .total-section {
            background-color: #f3f7fe;
            padding: 10px;
            border-radius: 8px;
            text-align: left;
            margin-top: 15px;
        }

        .total-section p {
            margin: 5px 0;
            font-size: 16px;
            color: #333;
        }

        .total-section .total-amount {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }

        .pay-button {
            margin-top: 20px;
            padding: 15px;
            background-color: #1d75e9;
            color: white;
            font-size: 16px;
            font-weight: bold;
            border: none;
            width: 100%;
            border-radius: 8px;
            cursor: pointer;
        }

        .pay-button:hover {
            background-color: #155bb5;
        }

        .note {
            font-size: 12px;
            margin-top: 10px;
            color: #555;
        }

        .pay-check {
            width: 20px;
            height: 20px;
            background-color: #1d75e9;
            border-radius: 50%;
            display: inline-block;
            position: relative;
        }

        .pay-check::after {
            content: "";
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            display: inline-block;
            position: absolute;
            left: 6px;
            top: 3px;
            transform: rotate(45deg);
        }

    </style>
</head>
<body>
    <div class="payment-container">
        <div class="header">Payment</div>
        <div class="g-container">
        <!-- Profile placeholder -->
        <div class="profile">G</div>
        <h2>Gelline's Sizzling and Restaurant</h2>
        </div>
        <!-- Payment method section -->
        <div class="pay-with">
            <span>PAY WITH</span>
            <div style="display: flex; align-items: center;">
                <span class="pay-method">GCash</span>
                <div class="pay-check" style="margin-left: 10px;"></div>
            </div>
        </div>

        <!-- Amount Details -->
        <div class="amount-section">
            <p>Amount Due</p>
            <p class="total-amount">PHP <?= number_format($reservation['total_price'], 2); ?></p>
            <p class="available-balance">Available Balance</p>
        </div>


        <!-- Total Section -->
        <div class="total-section">
            <p>Total Amount</p>
            <p class="total-amount">PHP <?= number_format($reservation['total_price'], 2); ?></p>
        </div>

        <!-- Pay Button -->
        <form method="post">
            <button type="submit" class="pay-button">Pay PHP <?= number_format($reservation['total_price'], 2); ?></button>
        </form>

        <!-- Note -->
        <p class="note">Please review to ensure the details are correct before you proceed.</p>
    </div>
</body>
</html>