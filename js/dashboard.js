// Dashboard Page JavaScript

// Global search functionality
document.querySelector('.top-nav .search-input')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    console.log('Searching for:', searchTerm);
    // Frontend only - search functionality would filter content
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

// Format date to relative time (e.g., "2 hours ago")
function getRelativeTime(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'just now';
    if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
    if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

// --- STUDENT DASHBOARD LOGIC ---

function loadStudentDashboard() {
    // First, check if we are actually on the Student Dashboard page!
    // If this ID doesn't exist, it means we are on the Admin page, so we stop the code here.
    let noticesBox = document.getElementById('student-notices');
    if (!noticesBox) {
        return; 
    }

    // Ask PHP for the dashboard data
    fetch('api/dashboard.php')
    .then(function(response) {
        return response.json(); // Turn response into JSON
    })
    .then(function(data) {
        if (data.success == true) {
            let stats = data.stats;
            
            // 1. Get live totals from backend
            let noticesCount = Number(stats.activeNotices || 0);
            let totalComplaints = Number(stats.totalComplaints || 0);
            let resolvedCount = Number(stats.resolvedIssues || 0);
            let pendingCount = Number(stats.pendingIssues || 0);

            // 2. Update the HTML stat cards on the screen
            noticesBox.innerText = noticesCount;
            document.getElementById('student-complaints').innerText = totalComplaints;
            document.getElementById('student-resolved').innerText = resolvedCount;
            document.getElementById('student-pending').innerText = pendingCount;

            // 3. Populate Recent Notices section
            const noticesContainer = document.getElementById('recent-notices');
            if (noticesContainer && data.notices && data.notices.length > 0) {
                noticesContainer.innerHTML = '';
                data.notices.forEach(notice => {
                    const categoryTag = notice.category ? notice.category.charAt(0).toUpperCase() + notice.category.slice(1) : 'General';
                    const noticeHTML = `
                        <div class="notice-item">
                            <div class="notice-indicator normal"></div>
                            <div class="notice-content">
                                <div class="notice-title">${notice.title}</div>
                                <div class="notice-meta">
                                    <span class="notice-tag">${categoryTag}</span>
                                    <span>${getRelativeTime(notice.created_at)}</span>
                                </div>
                            </div>
                        </div>
                    `;
                    noticesContainer.insertAdjacentHTML('beforeend', noticeHTML);
                });
            } else if (noticesContainer) {
                noticesContainer.innerHTML = '<div style="padding: 20px; text-align: center; color: #999;">No recent notices</div>';
            }

            // 4. Populate Complaint Status section
            const complaintsContainer = document.getElementById('complaint-status');
            if (complaintsContainer && data.complaints && data.complaints.length > 0) {
                complaintsContainer.innerHTML = '';
                data.complaints.forEach(complaint => {
                    let statusClass = 'pending';
                    if (complaint.status === 'In Progress') statusClass = 'in-progress';
                    if (complaint.status === 'Resolved') statusClass = 'resolved';

                    const complaintHTML = `
                        <div class="complaint-status-item">
                            <div class="notice-content">
                                <div class="notice-title">${complaint.subject}</div>
                                <div class="notice-meta">
                                    <span>${complaint.type ? complaint.type.charAt(0).toUpperCase() + complaint.type.slice(1) : 'Other'}</span>
                                    <span>${getRelativeTime(complaint.created_at)}</span>
                                </div>
                            </div>
                            <div class="complaint-status ${statusClass}">${complaint.status}</div>
                        </div>
                    `;
                    complaintsContainer.insertAdjacentHTML('beforeend', complaintHTML);
                });
            } else if (complaintsContainer) {
                complaintsContainer.innerHTML = '<div style="padding: 20px; text-align: center; color: #999;">No complaints submitted yet</div>';
            }
        }
    })
    .catch(function(error) {
        console.log("Error loading student stats: " + error);
    });
}

window.addEventListener('load', function() {
    loadStudentDashboard();
});
