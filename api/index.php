<?php
// This checks if they are logged in AND enforces role access
include 'auth_check.php'; 
?>

<!DOCTYPE html>
<html>
<body>
    <h1>Welcome to your Dashboard</h1>

    <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <a href="manage_users.php">Manage Users</a>
        <a href="system_settings.php">System Settings</a>
    <?php endif; ?>

    <?php if ($_SESSION['user_role'] === 'faculty' || $_SESSION['user_role'] === 'admin'): ?>
        <a href="grade_entry.php">Submit Grades</a>
    <?php endif; ?>

    <?php if ($_SESSION['user_role'] === 'student' || $_SESSION['user_role'] === 'admin'): ?>
        <a href="my_courses.php">View My Courses</a>
    <?php endif; ?>

    <script src="dashboard.js"></script>
</body>
</html>