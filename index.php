<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCampus - Login</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">🎓</div>
            <h1 class="login-title">Smart Campus</h1>
            <p class="login-subtitle">Campus Management System</p>
            
            <!-- FORM -->
            <form id="loginForm" class="login-form" onsubmit="return false;">

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="text" id="email" name="email" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-input" required>
                </div>
                
                <!-- ROLE -->
                <input type="hidden" id="role" name="role" value="student">

                <div class="form-group">
                    <label class="form-label">Role</label>
                    <div class="role-selection">
                        <button type="button" class="role-btn active" data-role="student">Student</button>
                        <button type="button" class="role-btn" data-role="faculty">Faculty</button>
                        <button type="button" class="role-btn" data-role="admin">Admin</button>
                    </div>
                </div>
                
                <button type="submit" class="login-btn">Sign In</button>

                <div class="separator"><span>OR</span></div>

                <!--  FIXED GOOGLE LOGIN -->
                <a href="api/login.php" class="google-btn">
                    <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg">
                    Sign in with Google
                </a>
            </form>
        </div>
    </div>

    <script src="js/main.js"></script>
</body>
</html>

