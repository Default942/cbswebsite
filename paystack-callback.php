<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/receipt.php';
require_once __DIR__ . '/config/paystack.php';
require_login();

$reference = trim($_GET['reference'] ?? '');
$type      = trim($_GET['type'] ?? '');
$yards     = isset($_GET['yards']) ? (int) $_GET['yards'] : null;

if ($reference === '' || !in_array($type, ['cloth', 'dues'], true)) {
    set_flash('error', 'Invalid payment request.');
    redirect('dashboard.php');
}

/**
 * Verify the transaction with Paystack's API.
 * Docs: https://paystack.com/docs/api/transaction/#verify
 */
function verify_paystack_transaction($reference) {
    $ch = curl_init("https://api.paystack.co/transaction/verify/" . rawurlencode($reference));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
        "Cache-Control: no-cache",
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err || !$response) {
        return null;
    }
    return json_decode($response, true);
}

$verification = verify_paystack_transaction($reference);
$paymentOk = $verification && ($verification['data']['status'] ?? '') === 'success';

// Fetch student & department
$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$_SESSION['student_id']]);
$student = $stmt->fetch();

$stmt = $pdo->prepare("SELECT name FROM departments WHERE code = ?");
$stmt->execute([$student['department_code']]);
$department = $stmt->fetch();

$unitPrice = $type === 'cloth' ? YARD_PRICE : DUES_PRICE;
$amount = $type === 'cloth' ? ($unitPrice * max(1, $yards)) : $unitPrice;

$stmt = $pdo->prepare(
    "INSERT INTO orders (student_id, department_code, order_type, yards, unit_price, amount, paystack_reference, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->execute([
    $student['student_id'],
    $student['department_code'],
    $type,
    $type === 'cloth' ? max(1, $yards) : null,
    $unitPrice,
    $amount,
    $reference,
    $paymentOk ? 'paid' : 'failed',
]);
$orderId = $pdo->lastInsertId();

if ($paymentOk) {
    // Fetch the freshly created order and email a receipt
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    $receiptHtml = generate_receipt_html($order, $student, $department['name']);
    send_email($student['email'], 'Your CU Business School Payment Receipt', $receiptHtml);

    set_flash('success', 'Payment successful! A receipt has been sent to your email.');
} else {
    set_flash('error', 'Payment could not be verified. If you were charged, please contact support with reference: ' . htmlspecialchars($reference));
}

redirect('order-history.php');
