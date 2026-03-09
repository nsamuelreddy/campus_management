// Dashboard Page JavaScript

// Global search functionality
document.querySelector('.top-nav .search-input')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    console.log('Searching for:', searchTerm);
    // Frontend only - search functionality would filter content
});

// Notification button
document.querySelector('.notification-btn')?.addEventListener('click', function() {
    alert('Notifications would be displayed here (Frontend only - no backend connection)');
});

// User profile dropdown (optional)
document.querySelector('.user-profile')?.addEventListener('click', function() {
    console.log('User profile clicked');
    // Could add a dropdown menu here
});

// Smooth navigation
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
        // Remove active class from all links
        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
        // Add active class to clicked link
        this.classList.add('active');
    });
});
// Fetch dynamic stats from PHP
function loadDashboardStats() {
    fetch('api/dashboard.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 1. Update the Number Cards
                const totalEl = document.getElementById('stat-total');
                const pendingEl = document.getElementById('stat-pending');
                const resolvedEl = document.getElementById('stat-resolved');
                const usersEl = document.getElementById('stat-users');

                if (totalEl) totalEl.textContent = data.stats.totalComplaints;
                if (pendingEl) pendingEl.textContent = data.stats.pendingIssues;
                if (resolvedEl) resolvedEl.textContent = data.stats.resolvedIssues;
                if (usersEl) usersEl.textContent = data.stats.users;

                // 2. Animate the Bar Chart (Complaint Trends)
                const bars = document.querySelectorAll('.bar');
                if (bars.length > 0 && data.charts.trends) {
                    data.charts.trends.forEach((percentage, index) => {
                        if (bars[index]) {
                            // Override the CSS file's hardcoded height
                            bars[index].style.height = `${percentage}%`; 
                        }
                    });
                }

                // 3. Animate the Donut Chart (Feedback Ratings)
                const donut = document.querySelector('.donut');
                if (donut && data.charts.feedback) {
                    const f = data.charts.feedback;
                    
                    // Convert percentages to 360 degrees for the CSS conic-gradient
                    const exDeg = (f.excellent / 100) * 360;
                    const gdDeg = exDeg + ((f.good / 100) * 360);
                    const avDeg = gdDeg + ((f.average / 100) * 360);
                    
                    // Inject the new dynamically calculated gradient
                    donut.style.background = `conic-gradient(
                        #10b981 0deg ${exDeg}deg,
                        #3b82f6 ${exDeg}deg ${gdDeg}deg,
                        #f59e0b ${gdDeg}deg ${avDeg}deg,
                        #ef4444 ${avDeg}deg 360deg
                    )`;
                }
            }
        })
        .catch(err => console.error("Error loading dashboard stats:", err));
}

// Run when the dashboard loads
document.addEventListener('DOMContentLoaded', loadDashboardStats);
