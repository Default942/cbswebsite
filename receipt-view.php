<?php
$pageTitle = 'Receipt';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/receipt.php';
require_login();

$orderId = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND student_id = ?");
$stmt->execute([$orderId, $_SESSION['student_id']]);
$order = $stmt->fetch();

if (!$order) {
    set_flash('error', 'Receipt not found.');
    redirect('order-history.php');
}

$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$_SESSION['student_id']]);
$student = $stmt->fetch();

$stmt = $pdo->prepare("SELECT name FROM departments WHERE code = ?");
$stmt->execute([$student['department_code']]);
$department = $stmt->fetch();
?>

<div class="dashboard-wrap">
  <?php echo generate_receipt_html($order, $student, $department['name']); ?>
  <div class="text-center mt-20">
    <a href="order-history.php" class="btn btn-outline">Back to Order History</a>
    <button onclick="window.print()" class="btn btn-primary">Print Receipt</button>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
