<?php
$pageTitle = 'Pay Dues';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/paystack.php';
require_login();

$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$_SESSION['student_id']]);
$student = $stmt->fetch();

$stmt = $pdo->prepare("SELECT name FROM departments WHERE code = ?");
$stmt->execute([$_SESSION['department_code']]);
$department = $stmt->fetch();
?>

<div class="dashboard-wrap">
  <div class="panel" style="max-width:480px;margin:0 auto;">
    <h3>Pay Association Dues &mdash; <?php echo htmlspecialchars($department['name']); ?></h3>
    <p class="small-muted">Dues are a flat fee for all departments.</p>

    <div class="total-box text-center">
      Amount Due: GHS <?php echo number_format(DUES_PRICE, 2); ?>
    </div>

    <button class="btn btn-primary btn-block" id="payButton">Pay with Paystack</button>
  </div>
</div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
document.getElementById('payButton').addEventListener('click', function () {
  const amountGHS = <?php echo DUES_PRICE; ?>;

  const handler = PaystackPop.setup({
    key: '<?php echo PAYSTACK_PUBLIC_KEY; ?>',
    email: '<?php echo htmlspecialchars($student['email']); ?>',
    amount: amountGHS * 100,
    currency: '<?php echo PAYSTACK_CURRENCY; ?>',
    ref: 'DUES-' + Math.floor((Math.random() * 1000000000) + 1),
    metadata: {
      student_id: '<?php echo htmlspecialchars($student['student_id']); ?>',
      order_type: 'dues'
    },
    callback: function (response) {
      window.location.href = 'paystack-callback.php?reference=' + response.reference + '&type=dues';
    },
    onClose: function () {
      alert('Payment window closed.');
    }
  });
  handler.openIframe();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
