<?php
$pageTitle = 'Login';
require_once __DIR__ . '/includes/header.php';

if (!empty($_SESSION['student_id'])) {
    redirect('dashboard.php');
}

$departments = get_departments($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier     = trim($_POST['identifier'] ?? ''); // student ID or email
    $departmentCode = trim($_POST['department_code'] ?? '');
    $password       = $_POST['password'] ?? '';

    $errors = [];

    if ($identifier === '' || $departmentCode === '' || $password === '') {
        $errors[] = 'All fields are required.';
    }

    $validDeptCodes = array_column($departments, 'code');
    if (!in_array($departmentCode, $validDeptCodes, true)) {
        $errors[] = 'Please select a valid department.';
    }

    // Determine if the identifier is an email or a student ID, and confirm
    // the ID (whichever way it was supplied) begins with the department initials.
    $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;

    if (empty($errors)) {
        if ($isEmail) {
            $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ?");
            $stmt->execute([strtolower($identifier)]);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
            $stmt->execute([strtoupper($identifier)]);
        }
        $student = $stmt->fetch();

        if (!$student) {
            $errors[] = 'No account found with the provided Student ID / email.';
        } else {
            // Student ID must match the selected department's initials
            if (!student_id_matches_department($student['student_id'], $departmentCode)) {
                $errors[] = "Your Student ID does not match the selected department.";
            }
            // Selected department must equal the department on file
            elseif ($student['department_code'] !== $departmentCode) {
                $errors[] = 'The selected department does not match our records for this account.';
            }
            elseif (!password_verify($password, $student['password_hash'])) {
                $errors[] = 'Incorrect password.';
            }
        }
    }

    if (empty($errors)) {
        $_SESSION['student_id'] = $student['student_id'];
        $_SESSION['department_code'] = $student['department_code'];
        set_flash('success', 'Welcome back, ' . $student['student_id'] . '!');
        redirect('dashboard.php');
    } else {
        foreach ($errors as $e) {
            set_flash('error', $e);
        }
    }
}
?>

<div class="auth-wrap">
  <h2>Student Login</h2>
  <p class="subtitle">Access your Business School Association account</p>

  <?php foreach (get_flashes() as $flash): ?>
    <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?>">
      <?php echo htmlspecialchars($flash['message']); ?>
    </div>
  <?php endforeach; ?>

  <form method="POST" action="login.php">
    <div class="form-group">
      <label for="identifier">Student ID or Email</label>
      <input type="text" name="identifier" id="identifier" placeholder="ACC10012024 or you@example.com"
             value="<?php echo htmlspecialchars($_POST['identifier'] ?? ''); ?>" required>
    </div>

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
      <label for="password">Password</label>
      <input type="password" name="password" id="password" required>
    </div>

    <button type="submit" class="btn btn-primary btn-block">Login</button>
  </form>

  <div class="form-footer">
    Don't have an account? <a href="signup.php">Sign up</a>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
