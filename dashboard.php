<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
require_login();

$departments = get_departments($pdo);
$myDept = $_SESSION['department_code'];
?>

<div class="dashboard-wrap">
  <div class="dashboard-header">
    <div>
      <h2>Welcome, <?php echo htmlspecialchars($_SESSION['student_id']); ?></h2>
      <p>Select your department below to continue.</p>
    </div>
  </div>

  <div class="dept-grid">
    <?php foreach ($departments as $dept): ?>
      <?php $isMine = ($dept['code'] === $myDept); ?>
      <div class="dept-card" style="<?php echo $isMine ? '' : 'opacity:0.5;'; ?>">
        <span class="code-badge"><?php echo $isMine ? 'Your Department' : 'Restricted'; ?></span>
        <h3><?php echo htmlspecialchars($dept['name']); ?></h3>
        <?php if ($isMine): ?>
          <a href="department.php?code=<?php echo urlencode($dept['code']); ?>" class="btn btn-primary btn-block">
            Select Department
          </a>
        <?php else: ?>
          <button class="btn btn-outline btn-block" disabled>Not Your Department</button>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
