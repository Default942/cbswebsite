<?php
$pageTitle = 'My Profile';
require_once __DIR__ . '/includes/header.php';
require_login();

$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$_SESSION['student_id']]);
$student = $stmt->fetch();

$stmt = $pdo->prepare("SELECT name FROM departments WHERE code = ?");
$stmt->execute([$student['department_code']]);
$department = $stmt->fetch();
?>

<div class="dashboard-wrap">
  <div class="panel" style="max-width:520px;margin:0 auto;">
    <h3>My Profile</h3>
    <div class="profile-grid">
      <div class="label">Student ID</div>
      <div class="value"><?php echo htmlspecialchars($student['student_id']); ?></div>

      <div class="label">Department</div>
      <div class="value"><?php echo htmlspecialchars($department['name']); ?></div>

      <div class="label">Email</div>
      <div class="value"><?php echo htmlspecialchars($student['email']); ?></div>

      <div class="label">Phone</div>
      <div class="value"><?php echo htmlspecialchars($student['phone']); ?></div>

      <div class="label">Joined</div>
      <div class="value"><?php echo date('d M Y', strtotime($student['created_at'])); ?></div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
