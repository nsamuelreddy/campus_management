// GLOBAL EVENTS (SAFE)

document.querySelector('.search-input')?.addEventListener('input', function (e) {
    console.log("Searching:", e.target.value);
});

document.querySelector('.notification-icon')?.addEventListener('click', function () {
    alert("Notifications coming soon");
});


// ROLE DETECTION (FIXED)

function getUserRole() {
    if (window.USER_ROLE) return window.USER_ROLE;
    return null;
}


// STUDENT DASHBOARD


function loadStudentDashboard() {

    fetch('/campus_management/api/dashboard.php', {
        credentials: 'include'
    })
    .then(res => res.json())
    .then(data => {

        if (!data.success) return;

        let stats = data.stats || {};

        document.getElementById('student-complaints') &&
            (document.getElementById('student-complaints').innerText = stats.totalComplaints || 0);

        document.getElementById('student-resolved') &&
            (document.getElementById('student-resolved').innerText = stats.resolved || 0);

        document.getElementById('student-pending') &&
            (document.getElementById('student-pending').innerText =
                (stats.totalComplaints || 0) - (stats.resolved || 0));

        document.getElementById('student-notices') &&
            (document.getElementById('student-notices').innerText = stats.totalNotices || 0);



        
        // FIX: Recent Notices (SAFE)
        
        const noticeBox = document.querySelector('.section-card .section-content');

        if (noticeBox && Array.isArray(data.notices)) {
            noticeBox.innerHTML = "";

            data.notices.forEach(n => {
                noticeBox.innerHTML += `
                    <div class="notice-item">
                        <div class="notice-indicator normal"></div>
                        <div class="notice-content">
                            <div class="notice-title">${n.title || ''}</div>
                            <div class="notice-meta">
                                <span>${n.created_at || ''}</span>
                            </div>
                        </div>
                    </div>`;
            });
        }



        
        // FIX: Recent Complaints (SAFE + FIX STATUS CLASS)
        
        const allSections = document.querySelectorAll('.section-card .section-content');
        const complaintBox = allSections[1];

        if (complaintBox && Array.isArray(data.recentComplaints)) {
            complaintBox.innerHTML = "";

            data.recentComplaints.forEach(c => {

                const statusClass = (c.status || '')
                    .toLowerCase()
                    .replace(/\s+/g, '-');

                complaintBox.innerHTML += `
                    <div class="complaint-status-item">
                        <div class="notice-content">
                            <div class="notice-title">${c.subject || ''}</div>
                            <div class="notice-meta">
                                <span>${c.created_at || ''}</span>
                            </div>
                        </div>
                        <div class="complaint-status ${statusClass}">
                            ${c.status || ''}
                        </div>
                    </div>`;
            });
        }

    })
    .catch(err => console.log("Student dashboard error:", err));
}




// FACULTY DASHBOARD
function loadFacultyDashboard() {

    fetch('/campus_management/api/feedback.php', {
        credentials: 'include'
    })
    .then(res => res.json())
    .then(data => {

        if (!data.success) return;

        // ✅ DIRECT FROM DB (NO RE-CALCULATION)
        const total = data.total || 0;
        const avg = data.overall || 0;

        const rating = document.getElementById('overall-rating');
        const responses = document.getElementById('total-responses');

        if (rating) rating.innerText = avg;
        if (responses) responses.innerText = total;

        // OPTIONAL: breakdown update (if you want)
        const r = data.rating_breakdown || {};

        const setBar = (num, val) => {
            const fill = document.querySelector('.rating-' + num);
            const text = fill?.parentElement?.nextElementSibling;

            if (fill) fill.style.width = (val || 0) + "%";
            if (text) text.innerText = (val || 0) + "%";
        };

        setBar(1, r[1]);
        setBar(2, r[2]);
        setBar(3, r[3]);
        setBar(4, r[4]);
        setBar(5, r[5]);


        // =========================
        // ✅ CATEGORY UPDATE (ADDED)
        // =========================
        const c = data.categories || {};

        document.querySelector('.category-item:nth-child(1) .category-score') &&
        (document.querySelector('.category-item:nth-child(1) .category-score').innerText = (c.teaching_clarity ?? 0) + " /5");

        document.querySelector('.category-item:nth-child(2) .category-score') &&
        (document.querySelector('.category-item:nth-child(2) .category-score').innerText = (c.subject_knowledge ?? 0) + " /5");

        document.querySelector('.category-item:nth-child(3) .category-score') &&
        (document.querySelector('.category-item:nth-child(3) .category-score').innerText = (c.interaction ?? 0) + " /5");

        document.querySelector('.category-item:nth-child(4) .category-score') &&
        (document.querySelector('.category-item:nth-child(4) .category-score').innerText = (c.punctuality ?? 0) + " /5");

        document.querySelector('.category-item:nth-child(5) .category-score') &&
        (document.querySelector('.category-item:nth-child(5) .category-score').innerText = (c.material_quality ?? 0) + " /5");


        document.querySelectorAll('.category-fill')[0] &&
        (document.querySelectorAll('.category-fill')[0].style.width = (c.teaching_clarity * 20) + "%");

        document.querySelectorAll('.category-fill')[1] &&
        (document.querySelectorAll('.category-fill')[1].style.width = (c.subject_knowledge * 20) + "%");

        document.querySelectorAll('.category-fill')[2] &&
        (document.querySelectorAll('.category-fill')[2].style.width = (c.interaction * 20) + "%");

        document.querySelectorAll('.category-fill')[3] &&
        (document.querySelectorAll('.category-fill')[3].style.width = (c.punctuality * 20) + "%");

        document.querySelectorAll('.category-fill')[4] &&
        (document.querySelectorAll('.category-fill')[4].style.width = (c.material_quality * 20) + "%");

    })
    .catch(err => console.log("Faculty dashboard error:", err));
}
// ADMIN DASHBOARD


function loadAdminDashboard() {

    fetch('/campus_management/api/dashboard.php', {
        credentials: 'include'
    })
    .then(res => res.json())
    .then(data => {

        if (!data.success) return;

        let stats = data.stats || {};

        // ======================
        // STATS CARDS
        // ======================
        document.getElementById('stat-total') &&
            (document.getElementById('stat-total').innerText = stats.totalComplaints || 0);

        document.getElementById('stat-pending') &&
            (document.getElementById('stat-pending').innerText = stats.pending || 0);

        document.getElementById('stat-resolved') &&
            (document.getElementById('stat-resolved').innerText = stats.resolved || 0);

        document.getElementById('stat-users') &&
            (document.getElementById('stat-users').innerText = stats.activeUsers || 0);


        // ======================
        // BAR CHART (Complaints)
        // ======================

        const labels = data.complaintChart.map(i => i.month);
        const values = data.complaintChart.map(i => i.count);

        if (window.barChart) window.barChart.destroy();

        window.barChart = new Chart(
            document.getElementById('complaintTrendsChart'),
            {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Complaints',
                        data: values,
                        backgroundColor: '#3b82f6'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            }
        );


        // ======================
        // PIE CHART (Feedback)
        // ======================

        const f = data.feedbackPie;

        if (window.pieChart) window.pieChart.destroy();

        window.pieChart = new Chart(
            document.getElementById('feedbackChart'),
            {
                type: 'pie',
                data: {
                    labels: ['Excellent', 'Good', 'Average', 'Poor'],
                    datasets: [{
                        data: [
                            f.excellent,
                            f.good,
                            f.average,
                            f.poor
                        ],
                        backgroundColor: [
                            '#10b981',
                            '#3b82f6',
                            '#f59e0b',
                            '#ef4444'
                        ]
                    }]
                }
            }
        );

    })
    .catch(err => console.log("Admin dashboard error:", err));
}

// ============================
// INIT
// ============================

window.addEventListener('load', function () {

    let role = getUserRole()?.toLowerCase();

    console.log("Detected role:", role);

    if (!role) {
        console.log("No role found - login issue");
        return;
    }

    if (role === 'student') {
        loadStudentDashboard();
    }
    else if (role === 'faculty') {
        loadFacultyDashboard();
    }
    else if (role === 'admin') {
        loadAdminDashboard();
    }
});