<?php
$pageTitle = 'Verify Email';
require_once __DIR__ . '/includes/header.php';

if (empty($_SESSION['pending_signup'])) {
    set_flash('error', 'No pending sign up found. Please sign up first.');
    redirect('signup.php');
}

$pending = $_SESSION['pending_signup'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['otp'] ?? '');

    $stmt = $pdo->prepare(
        "SELECT * FROM otp_codes WHERE email = ? AND code = ? AND purpose = 'signup'
         AND is_used = 0 AND expires_at >= NOW() ORDER BY id DESC LIMIT 1"
    );
    $stmt->execute([$pending['email'], $code]);
    $otpRow = $stmt->fetch();

    if (!$otpRow) {
        set_flash('error', 'Invalid or expired verification code.');
    } else {
        $pdo->beginTransaction();
        try {
            // Mark OTP used
            $pdo->prepare("UPDATE otp_codes SET is_used = 1 WHERE id = ?")->execute([$otpRow['id']]);

            // Create the student account
            $stmt = $pdo->prepare(
                "INSERT INTO students (student_id, department_code, email, phone, password_hash, email_verified)
                 VALUES (?, ?, ?, ?, ?, 1)"
            );
            $stmt->execute([
                $pending['student_id'],
                $pending['department_code'],
                $pending['email'],
                $pending['phone'],
                $pending['password_hash'],
            ]);

            // Mark the eligible_students record as registered
            $pdo->prepare("UPDATE eligible_students SET is_registered = 1 WHERE student_id = ?")
                ->execute([$pending['student_id']]);

            $pdo->commit();

            unset($_SESSION['pending_signup']);
            $_SESSION['student_id'] = $pending['student_id'];
            $_SESSION['department_code'] = $pending['department_code'];

            set_flash('success', 'Account created successfully! Welcome to CU Business School Association.');
            redirect('dashboard.php');
        } catch (Exception $e) {
            $pdo->rollBack();
            set_flash('error', 'Something went wrong creating your account. Please try again.');
        }
    }
}
?>

<div class="auth-wrap">
  <h2>Verify Your Email</h2>
  <p class="subtitle">
    Enter the 6-digit code sent to <strong><?php echo htmlspecialchars($pending['email']); ?></strong>
  </p>

  <?php foreach (get_flashes() as $flash): ?>
    <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?>">
      <?php echo htmlspecialchars($flash['message']); ?>
    </div>
  <?php endforeach; ?>

  <form method="POST" action="verify-otp.php">
    <div class="form-group">
      <label for="otp">Verification Code</label>
      <input type="text" name="otp" id="otp" maxlength="6" placeholder="123456" required autofocus>
    </div>
    <button type="submit" class="btn btn-primary btn-block">Verify &amp; Create Account</button>
  </form>

  <div class="form-footer">
    Didn't get a code? <a href="signup.php">Start sign up again</a>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
