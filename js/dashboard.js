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


function loadStudentDashboard() {

    let noticesBox = document.getElementById('student-notices');
    if (!noticesBox) return;

    fetch('api/dashboard.php')
    .then(res => res.json())
    .then(data => {
        if (data.success) {

            let stats = data.stats;

            let totalComplaints = stats.myComplaints || 0;
            let resolved = stats.myResolved || 0;
            let pending = totalComplaints - resolved;

            document.getElementById('student-notices').innerText = 0;
            document.getElementById('student-complaints').innerText = totalComplaints;
            document.getElementById('student-resolved').innerText = resolved;
            document.getElementById('student-pending').innerText = pending;
        }
    })
    .catch(err => console.log(err));
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



// --- STUDENT DASHBOARD LOGIC ---



window.addEventListener('load', function() {
    loadStudentDashboard();
    
});