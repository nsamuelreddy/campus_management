<?php
// 1. MUST start the session immediately
session_start();

// 2. Load your working configuration
require_once 'config.php';

// 3. Generate CSRF token if needed
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 4. Redirect if already logged in
if (isset($_SESSION['user_token'])) {
  header("Location: ../dashboard.php"); // Adjust path if needed
  exit();
}
?>

<form method="POST" action="process.php">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
</form>

<?php
// 5. Echo the link
echo "<a href='".$client->createAuthUrl()."'>Login with Google</a>";
?>