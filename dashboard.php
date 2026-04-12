<?php
session_start();

// if user not logged in → go to login page
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCampus - Student Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<script>
window.USER_ROLE = "<?php echo $_SESSION['user']['role']; ?>";
</script>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">🎓</div>
                <span class="sidebar-title">SmartCampus</span>
            </div>
            <nav>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link active">
                            <span class="nav-icon">📊</span>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="notices.html" class="nav-link">
                            <span class="nav-icon">📢</span>
                            Notices
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="complaints.html" class="nav-link">
                            <span class="nav-icon">📝</span>
                            Complaints
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="feedback.html" class="nav-link">
                            <span class="nav-icon">⭐</span>
                            Feedback
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="lost-found.html" class="nav-link">
                            <span class="nav-icon">🔍</span>
                            Lost & Found
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="emergency.html" class="nav-link">
                            <span class="nav-icon">🚨</span>
                            Emergency
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation -->
            <div class="top-nav">
                <div class="search-bar">
                    <input type="text" class="search-input" placeholder="Search...">
                    <span class="search-icon">🔍</span>
                </div>

                <div class="top-nav-right">
                    <button class="notification-btn">
                        🔔
                        <span class="notification-badge"></span>
                    </button>

                    <div class="user-profile">
                        <div class="user-avatar">A</div>
                        <div class="user-info">
                            <h4>Arjun Sharma</h4>
                            <p>Student</p>
                        </div>
                    </div>

                    <!--  LOGOUT ADDED HERE -->
                    <a href="logout.php"
                       class="logout-btn"
                       style="margin-left: 16px;
                              background: transparent;
                              color: #64748b;
                              border: 1px solid #e2e8f0;
                              padding: 8px 16px;
                              border-radius: 8px;
                              text-decoration:none;
                              font-size: 14px;
                              font-weight: 600;
                              transition: all 0.2s ease;"
                       onmouseover="this.style.background='#f1f5f9'; this.style.color='#334155';"
                       onmouseout="this.style.background='transparent'; this.style.color='#64748b';">
                        Logout
                    </a>
                    
                </div>
            </div>

            <!-- Page Content -->
            <div class="content-area">
                <div class="page-header">
                    <h1 class="page-title">Student Dashboard</h1>
                    <p class="page-subtitle">Welcome back! Here's what's happening on campus.</p>
                </div>

                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card notices">
                        <div class="stat-icon">📢</div>
                        <div class="stat-number" id="student-notices">0</div>
                        <div class="stat-label">Total Notices</div>
                        <div class="stat-change">Updated live</div>
                    </div>
                    <div class="stat-card complaints">
                        <div class="stat-icon">📝</div>
                        <div class="stat-number" id="student-complaints">0</div>
                        <div class="stat-label">Total Complaints</div>
                    </div>
                    <div class="stat-card resolved">
                        <div class="stat-icon">✅</div>
                        <div class="stat-number" id="student-resolved">0</div>
                        <div class="stat-label">Resolved</div>
                    </div>
                    <div class="stat-card pending">
                        <div class="stat-icon">⏰</div>
                        <div class="stat-number" id="student-pending">0</div>
                        <div class="stat-label">Pending</div>
                    </div>
                </div>

                <!-- Dashboard Sections -->
                <div class="dashboard-sections">
                    <!-- Recent Notices -->
                    <div class="section-card">
                        <div class="section-header">
                            <h3 class="section-title">Recent Notices</h3>
                        </div>
                        <div class="section-content">
                        </div>
                    </div>

                    <!-- Complaint Status -->
                    <div class="section-card">
                        <div class="section-header">
                            <h3 class="section-title">Complaint Status</h3>
                        </div>
                        <div class="section-content">
                            <div class="complaint-status-item">
                                <div class="notice-content">
                                
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="js/main.js"></script>
    <script src="js/dashboard.js"></script>
</body>
</html>