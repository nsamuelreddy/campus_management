<?php
session_start();

// if user not logged in → go to login page
if (!isset($_SESSION['user_email'])) {
    header("Location: index.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCampus</title>
    <style>
        /* ✅ SAME CSS (UNCHANGED) */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            background: #f4f6f9;
            color: #1a2332;
            display: flex;
            min-height: 100vh;
        }

        .app-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: #1e2a3a;
            color: white;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 24px;
            border-bottom: 1px solid #2d3a4a;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo {
            background: #3b82f6;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .logo-text {
            font-size: 18px;
            font-weight: 600;
            color: white;
        }

        .sidebar-nav {
            flex: 1;
            padding: 24px 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 24px;
            color: #8b9bb3;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .nav-link.active {
            background: #3b82f6;
            color: white;
        }

        .nav-link:hover:not(.active) {
            background: #2d3a4a;
            color: white;
        }

        .user-profile {
            padding: 24px;
            border-top: 1px solid #2d3a4a;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: #3b82f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: white;
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .content-area {
            flex: 1;
            padding: 32px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
        }

        /* TOP NAV */
        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .search-input {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            width: 250px;
        }

        .notification-btn {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            position: relative;
        }

        .notification-badge {
            background: red;
            color: white;
            border-radius: 50%;
            font-size: 12px;
            padding: 2px 6px;
            position: absolute;
            top: -5px;
            right: -5px;
        }
    </style>
</head>

<body>

<div class="app-container">

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo-container">
                <div class="logo">🎓</div>
                <div class="logo-text">SmartCampus</div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="faculty-dashboard.html" class="nav-link active">Dashboard</a>
        </nav>

        <div class="user-profile">
            <div class="user-avatar">D</div>
            <div>
                <div>Dr. Priya Mehta</div>
                <div>Faculty</div>
            </div>
        </div>
    </div>

    <!-- Main -->
    <div class="main-content">

        <div class="content-area">

            <!-- TOP NAV + LOGOUT ADDED -->
            <div class="top-nav">
                <input type="text" class="search-input" placeholder="Search...">

                <div style="display:flex; align-items:center; gap:12px;">

                    <button class="notification-btn">
                        🔔
                        <span class="notification-badge">0</span>
                    </button>

                    <!-- ✅ LOGOUT ADDED HERE -->
                    <a href="logout.php"
                       style="background: transparent;
                              border: 1px solid #ccc;
                              padding: 8px 14px;
                              border-radius: 8px;
                              text-decoration: none;
                              color: #64748b;
                              font-weight: 600;">
                        Logout
                    </a>

                </div>
            </div>

            <h1>Faculty Feedback Analysis</h1>

            <div class="stats-grid">

                <!-- Rating -->
                <div class="stat-card">
                    <h3>Overall Rating</h3>
                    <div class="stat-value">
                        <span id="overall-rating">0.0</span> / 5
                    </div>
                    <div id="rating-label">Waiting for data...</div>
                </div>

                <!-- Responses -->
                <div class="stat-card">
                    <h3>Total Responses</h3>
                    <div class="stat-value" id="total-responses">0</div>
                </div>

            </div>

        </div>

    </div>

</div>

<!-- JS -->
<script src="js/main.js"></script>
<script src="js/dashboard.js"></script>

</body>
</html>
