<?php
$pageTitle = 'Department';
require_once __DIR__ . '/includes/header.php';
require_login();

$code = trim($_GET['code'] ?? '');

if ($code !== $_SESSION['department_code']) {
    set_flash('error', 'You can only access your own department.');
    redirect('dashboard.php');
}

$stmt = $pdo->prepare("SELECT * FROM departments WHERE code = ?");
$stmt->execute([$code]);
$department = $stmt->fetch();

if (!$department) {
    set_flash('error', 'Department not found.');
    redirect('dashboard.php');
}
?>

<div class="dashboard-wrap">
  <div class="panel">
    <h3><?php echo htmlspecialchars($department['name']); ?> Department</h3>
    <div class="action-grid">
      <div class="action-card">
        <h4>👕 Buy Cloths</h4>
        <p class="desc">Order departmental cloth by the yard.</p>
        <p class="price-tag">GHS <?php echo number_format(YARD_PRICE, 2); ?> / yard</p>
        <a href="buy-cloths.php" class="btn btn-primary btn-block">Buy Cloths</a>
      </div>
      <div class="action-card">
        <h4>💳 Pay Dues</h4>
        <p class="desc">Settle your association dues for the semester.</p>
        <p class="price-tag">GHS <?php echo number_format(DUES_PRICE, 2); ?></p>
        <a href="pay-dues.php" class="btn btn-primary btn-block">Pay Dues</a>
      </div>
    </div>
  </div>

  <div class="text-center">
    <a href="order-history.php" class="btn btn-outline">View Order History</a>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
