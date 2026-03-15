<?php
// Generate a CSRF token if one doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<form method="POST" action="process.php">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    </form>


<?php
require_once 'config.php';

if (isset($_SESSION['user_token'])) {
  header("Location: dashboard.php");
  exit();
}

echo "<a href='".$client->createAuthUrl()."'>Login with Google</a>";
?>