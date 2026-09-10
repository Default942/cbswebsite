<?php
$pageTitle = 'Welcome';
require_once __DIR__ . '/includes/header.php';
?>

<div class="hero">
  <img src="assets/images/logo.png" alt="Logo" class="hero-logo">
  <h2>CU Business School Association Portal</h2>
  <p>
    Your one-stop platform for departmental cloth orders, dues payments and
    membership records — built for students of the Central University
    Business School Association.
  </p>
  <div class="hero-actions">
    <a href="signup.php" class="btn btn-primary">Sign Up</a>
    <a href="login.php" class="btn btn-outline">Login</a>
  </div>
</div>

<div class="features">
  <div class="feature-card">
    <h3>🎓 Departments</h3>
    <p>Accounting, Banking &amp; Finance, Human Resource Management, Management Studies and Marketing — all in one place.</p>
  </div>
  <div class="feature-card">
    <h3>👕 Buy Cloths</h3>
    <p>Order your departmental cloth by the yard at GHS 50 per yard, paid securely via Paystack.</p>
  </div>
  <div class="feature-card">
    <h3>💳 Pay Dues</h3>
    <p>Settle your association dues of GHS 65 in a few clicks and get an instant emailed receipt.</p>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
