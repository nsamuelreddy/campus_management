// Dashboard Page JavaScript

// Global search functionality
document.querySelector('.top-nav .search-input')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    console.log('Searching for:', searchTerm);
});

// Notification button
document.querySelector('.notification-btn')?.addEventListener('click', function() {
    alert('Notifications would be displayed here (Frontend only - no backend connection)');
});

// User profile dropdown (optional)
document.querySelector('.user-profile')?.addEventListener('click', function() {
    console.log('User profile clicked');
});

// Smooth navigation
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
    });
});

// Fetch dynamic stats from PHP
function loadDashboardStats() {
    fetch('api/dashboard.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {

                const totalEl = document.getElementById('stat-total');
                const pendingEl = document.getElementById('stat-pending');
                const resolvedEl = document.getElementById('stat-resolved');
                const usersEl = document.getElementById('stat-users');

                if (totalEl) totalEl.textContent = data.stats.totalComplaints;
                if (pendingEl) pendingEl.textContent = data.stats.pendingIssues;
                if (resolvedEl) resolvedEl.textContent = data.stats.resolvedIssues;
                if (usersEl) usersEl.textContent = data.stats.users;

                const bars = document.querySelectorAll('.bar');
                if (bars.length > 0 && data.charts.trends) {
                    data.charts.trends.forEach((percentage, index) => {
                        if (bars[index]) {
                            bars[index].style.height = `${percentage}%`; 
                        }
                    });
                }

                const donut = document.querySelector('.donut');
                if (donut && data.charts.feedback) {
                    const f = data.charts.feedback;
                    
                    const exDeg = (f.excellent / 100) * 360;
                    const gdDeg = exDeg + ((f.good / 100) * 360);
                    const avDeg = gdDeg + ((f.average / 100) * 360);
                    
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

// Notifications
function loadNotifications() {
    fetch('api/notifications.php')
        .then(res => res.json())
        .then(data => {
            const count = data.filter(n => !n.is_read).length;
            if (count > 0) {
                const badge = document.querySelector('.notification-badge');
                if (badge) {
                    badge.textContent = count;
                }
            }
        });
}

// Run when dashboard loads
document.addEventListener('DOMContentLoaded', loadDashboardStats);


// ===============================
// ✅ MOVED FROM feedback.js
// FACULTY DASHBOARD FUNCTION
// ===============================

function loadFacultyStats() {
    fetch('api/feedback.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let feedbackList = data.feedback;

            let totalResponses = feedbackList.length;
            let totalElement = document.getElementById('total-responses');

            if (totalElement) {
                totalElement.innerText = totalResponses;
            }

            if (totalResponses === 0) return;

            let sum = 0;
            let count = 0;

            feedbackList.forEach(fb => {
                let ratings = fb.ratings;

                for (let key in ratings) {
                    let val = parseInt(ratings[key]);
                    if (val > 0) {
                        sum += val;
                        count++;
                    }
                }
            });

            let avg = sum / count;

            let ratingElement = document.getElementById('overall-rating');
            let label = document.getElementById('rating-label');

            if (ratingElement) {
                ratingElement.innerText = avg.toFixed(1);
            }

            if (label) {
                if (avg >= 4) {
                    label.innerText = "Excellent";
                    label.style.color = "#10b981";
                } else if (avg >= 3) {
                    label.innerText = "Good";
                    label.style.color = "#3b82f6";
                } else {
                    label.innerText = "Needs Improvement";
                    label.style.color = "#ef4444";
                }
            }
        }
    })
    .catch(err => console.log(err));
}

// Run faculty stats also on dashboard
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('overall-rating')) {
        loadFacultyStats();
    }
});