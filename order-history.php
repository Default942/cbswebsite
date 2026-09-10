<?php
$pageTitle = 'Order History';
require_once __DIR__ . '/includes/header.php';
require_login();

$stmt = $pdo->prepare("SELECT * FROM orders WHERE student_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['student_id']]);
$orders = $stmt->fetchAll();
?>

<div class="dashboard-wrap">
  <div class="panel">
    <h3>Order History</h3>

    <?php if (empty($orders)): ?>
      <p class="small-muted">You have not made any purchases yet.</p>
    <?php else: ?>
      <table class="data-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Item</th>
            <th>Amount</th>
            <th>Reference</th>
            <th>Status</th>
            <th>Receipt</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $order): ?>
            <tr>
              <td><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></td>
              <td>
                <?php
                  echo $order['order_type'] === 'cloth'
                    ? 'Cloth (' . $order['yards'] . ' yard' . ($order['yards'] > 1 ? 's' : '') . ')'
                    : 'Association Dues';
                ?>
              </td>
              <td><?php echo money($order['amount']); ?></td>
              <td><?php echo htmlspecialchars($order['paystack_reference']); ?></td>
              <td><span class="status-pill status-<?php echo htmlspecialchars($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span></td>
              <td>
                <?php if ($order['status'] === 'paid'): ?>
                  <a href="receipt-view.php?id=<?php echo $order['id']; ?>" class="btn btn-outline" style="padding:6px 12px;font-size:12px;">View</a>
                <?php else: ?>
                  &mdash;
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
