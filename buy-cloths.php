<?php
$pageTitle = 'Buy Cloths';
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
    <h3>Buy Departmental Cloth &mdash; <?php echo htmlspecialchars($department['name']); ?></h3>

    <p class="small-muted">Each yard costs <strong>GHS <?php echo number_format(YARD_PRICE, 2); ?></strong>. Choose how many yards you'd like to purchase.</p>

    <div class="yard-selector">
      <button type="button" class="btn btn-outline" id="decreaseYards">-</button>
      <input type="number" id="yards" min="1" step="1" value="1">
      <button type="button" class="btn btn-outline" id="increaseYards">+</button>
    </div>

    <div class="total-box text-center">
      Total: GHS <span id="totalAmount"><?php echo number_format(YARD_PRICE, 2); ?></span>
    </div>

    <button class="btn btn-primary btn-block" id="payButton">Pay with Paystack</button>
  </div>
</div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
const YARD_PRICE = <?php echo YARD_PRICE; ?>;
const yardsInput = document.getElementById('yards');
const totalAmountEl = document.getElementById('totalAmount');

function updateTotal() {
  let yards = parseInt(yardsInput.value) || 1;
  if (yards < 1) { yards = 1; yardsInput.value = 1; }
  totalAmountEl.textContent = (yards * YARD_PRICE).toFixed(2);
}

document.getElementById('increaseYards').addEventListener('click', () => {
  yardsInput.value = (parseInt(yardsInput.value) || 1) + 1;
  updateTotal();
});
document.getElementById('decreaseYards').addEventListener('click', () => {
  yardsInput.value = Math.max(1, (parseInt(yardsInput.value) || 1) - 1);
  updateTotal();
});
yardsInput.addEventListener('input', updateTotal);

document.getElementById('payButton').addEventListener('click', function () {
  const yards = parseInt(yardsInput.value) || 1;
  const amountGHS = yards * YARD_PRICE;

  const handler = PaystackPop.setup({
    key: '<?php echo PAYSTACK_PUBLIC_KEY; ?>',
    email: '<?php echo htmlspecialchars($student['email']); ?>',
    amount: amountGHS * 100, // Paystack expects amount in pesewas (kobo equivalent)
    currency: '<?php echo PAYSTACK_CURRENCY; ?>',
    ref: 'CLOTH-' + Math.floor((Math.random() * 1000000000) + 1),
    metadata: {
      student_id: '<?php echo htmlspecialchars($student['student_id']); ?>',
      order_type: 'cloth',
      yards: yards
    },
    callback: function (response) {
      window.location.href = 'paystack-callback.php?reference=' + response.reference
        + '&type=cloth&yards=' + yards;
    },
    onClose: function () {
      alert('Payment window closed.');
    }
  });
  handler.openIframe();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
