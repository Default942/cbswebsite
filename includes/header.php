<?php
require_once __DIR__ . '/functions.php';
$isLoggedIn = !empty($_SESSION['student_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : ''; ?>CU Business School Association</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="icon" href="assets/images/logo.png">
</head>
<body>

<div class="navbar">
  <a href="<?php echo $isLoggedIn ? 'dashboard.php' : 'index.php'; ?>" class="brand">
    <img src="assets/images/logo.png" alt="CU Business School Logo">
    <div class="brand-text">
      <h1>Central University</h1>
      <p>Business School Association</p>
    </div>
  </a>
  <div class="nav-actions">
    <?php if ($isLoggedIn): ?>
      <span class="small-muted" style="color:#f6ede2; margin-right:10px;">
        <?php echo htmlspecialchars($_SESSION['student_id']); ?>
      </span>
      <a href="profile.php" class="btn btn-outline">Profile</a>
      <a href="logout.php" class="btn btn-danger">Logout</a>
    <?php else: ?>
      <a href="login.php" class="btn btn-outline">Login</a>
      <a href="signup.php" class="btn btn-primary">Sign Up</a>
    <?php endif; ?>
  </div>
</div>

<?php foreach (get_flashes() as $flash): ?>
  <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?>" style="max-width:640px;margin:20px auto 0;">
    <?php echo htmlspecialchars($flash['message']); ?>
  </div>
<?php endforeach; ?>
