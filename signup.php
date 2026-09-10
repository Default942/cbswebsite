<?php
$pageTitle = 'Sign Up';
require_once __DIR__ . '/includes/header.php';

if (!empty($_SESSION['student_id'])) {
    redirect('dashboard.php');
}

$departments = get_departments($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $departmentCode = trim($_POST['department_code'] ?? '');
    $studentId      = strtoupper(trim($_POST['student_id'] ?? ''));
    $email          = strtolower(trim($_POST['email'] ?? ''));
    $phone          = trim($_POST['phone'] ?? '');
    $password       = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $errors = [];

    // Basic presence checks
    if ($departmentCode === '' || $studentId === '' || $email === '' || $phone === '' || $password === '') {
        $errors[] = 'All fields are required.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email address.';
    }

    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Password and Confirm Password do not match.';
    }

    // Confirm department code is a real department
    $validDeptCodes = array_column($departments, 'code');
    if (!in_array($departmentCode, $validDeptCodes, true)) {
        $errors[] = 'Please select a valid department.';
    }

    // Student ID must begin with the selected department's initials
    if (empty($errors) && !student_id_matches_department($studentId, $departmentCode)) {
        $errors[] = "Your Student ID does not match the selected department's format.";
    }

    // Check eligibility: student_id + email must exist together in eligible_students,
    // belong to the chosen department, and not already be registered.
    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "SELECT * FROM eligible_students WHERE student_id = ? AND email = ?"
        );
        $stmt->execute([$studentId, $email]);
        $eligible = $stmt->fetch();

        if (!$eligible) {
            $errors[] = 'We could not verify your Student ID and email against our records. Please contact the school administration.';
        } elseif ($eligible['department_code'] !== $departmentCode) {
            $errors[] = 'The department selected does not match our records for this Student ID.';
        } elseif ((int) $eligible['is_registered'] === 1) {
            $errors[] = 'An account already exists for this Student ID. Please log in instead.';
        }
    }

    if (empty($errors)) {
        // Generate & store OTP, then stash pending signup details in session.
        $otp = generate_otp();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        $stmt = $pdo->prepare(
            "INSERT INTO otp_codes (email, code, purpose, expires_at) VALUES (?, ?, 'signup', ?)"
        );
        $stmt->execute([$email, $otp, $expiresAt]);

        $_SESSION['pending_signup'] = [
            'department_code' => $departmentCode,
            'student_id'      => $studentId,
            'email'           => $email,
            'phone'           => $phone,
            'password_hash'   => password_hash($password, PASSWORD_DEFAULT),
        ];

        send_email(
            $email,
            'Your CU Business School Verification Code',
            "<p>Hello,</p><p>Your verification code is: <strong style='font-size:20px;'>{$otp}</strong></p>
             <p>This code expires in 10 minutes.</p>"
        );

        set_flash('info', 'A verification code has been sent to your email address.');
        redirect('verify-otp.php');
    } else {
        foreach ($errors as $e) {
            set_flash('error', $e);
        }
    }
}
?>

<div class="auth-wrap">
  <h2>Student Sign Up</h2>
  <p class="subtitle">Create your Business School Association account</p>

  <?php foreach (get_flashes() as $flash): ?>
    <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?>">
      <?php echo htmlspecialchars($flash['message']); ?>
    </div>
  <?php endforeach; ?>

  <form method="POST" action="signup.php">
    <div class="form-group">
      <label for="department_code">Department</label>
      <select name="department_code" id="department_code" required>
        <option value="">-- Select your department --</option>
        <?php foreach ($departments as $dept): ?>
          <option value="<?php echo htmlspecialchars($dept['code']); ?>"
            <?php echo (($_POST['department_code'] ?? '') === $dept['code']) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($dept['name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label for="student_id">Student ID</label>
      <input type="text" name="student_id" id="student_id" placeholder="e.g. ACC10012024"
             value="<?php echo htmlspecialchars($_POST['student_id'] ?? ''); ?>" required>
    </div>

    <div class="form-group">
      <label for="email">Student Email</label>
      <input type="email" name="email" id="email" placeholder="you@example.com"
             value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
    </div>

    <div class="form-group">
      <label for="phone">Phone Number</label>
      <input type="tel" name="phone" id="phone" placeholder="024xxxxxxx"
             value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
    </div>

    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" name="password" id="password" minlength="6" required>
    </div>

    <div class="form-group">
      <label for="confirm_password">Confirm Password</label>
      <input type="password" name="confirm_password" id="confirm_password" minlength="6" required>
    </div>

    <button type="submit" class="btn btn-primary btn-block">Sign Up</button>
  </form>

  <div class="form-footer">
    Already have an account? <a href="login.php">Log in</a>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
