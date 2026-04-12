
// ============================
// LOGIN FUNCTIONALITY
// ============================
document.getElementById('loginForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const email = document.getElementById('email')?.value;
    const password = document.getElementById('password')?.value;
    const selectedRole = document.querySelector('.role-btn.active')?.getAttribute('data-role');

    if (email && password && selectedRole) {

        fetch('/campus_management/api/auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'login',
                email,
                password,
                role: selectedRole
            })
        })
        .then(res => res.text())
        .then(text => {
            console.log("RAW RESPONSE:", text);

            try {
                const data = JSON.parse(text);

                if (data.success) {
                    localStorage.setItem('user', JSON.stringify(data.user));

                    if (selectedRole === 'student') {
                        window.location.href = '/campus_management/dashboard.php';
                    } 
                    else if (selectedRole === 'faculty') {
                        window.location.href = '/campus_management/faculty-dashboard.php';
                    } 
                    else if (selectedRole === 'admin') {
                        window.location.href = '/campus_management/admin-dashboard.php';
                    }

                } else {
                    alert(data.message || 'Login failed');
                }

            } catch (e) {
                console.error("JSON ERROR:", text);
                alert("Server returned invalid response");
            }
        })
        .catch(err => {
            console.error("FETCH ERROR:", err);
            alert("Server error");
        });

    }  // ✅ THIS WAS MISSING (VERY IMPORTANT)
});


// ============================
// ROLE SELECTION
// ============================
document.querySelectorAll('.role-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
    });
});


// ============================
// NAV LINK ACTIVE STATE
// ============================
document.querySelectorAll('.nav-link')?.forEach(link => {
    link.addEventListener('click', function() {
        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
    });
});


// ============================
// LOGOUT FUNCTION
// ============================
function logout() {
    fetch('/campus_management/api/auth.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'logout' })
    })
    .then(() => {
        localStorage.removeItem('user');
        window.location.href = '/campus_management/index.php';
    })
    .catch(() => {
        localStorage.removeItem('user');
        window.location.href = '/campus_management/index.php';
    });
}


// ============================
// USER INFO LOADING
// ============================

let user = {};

try {
    user = JSON.parse(localStorage.getItem('user')) || {};
} catch (e) {
    console.warn("Corrupted user data removed");
    localStorage.removeItem('user');
}
if (user.email) {
    const userInfo = document.querySelector('.user-info h4, .user-name');
    const userRole = document.querySelector('.user-info p, .user-role');

    if (userInfo) {
        let displayName = user.email.split('@')[0];
        displayName = displayName.charAt(0).toUpperCase() + displayName.slice(1);

        if (user.role === 'faculty') {
            displayName = 'Dr. ' + displayName;
        } 
        else if (user.role === 'admin') {
            displayName = displayName + ' Kumar';
        }

        userInfo.textContent = displayName;
    }

    if (userRole && user.role) {
        userRole.textContent = user.role.charAt(0).toUpperCase() + user.role.slice(1);
    }
}


// ============================
// ROLE PERMISSION CHECK
// ============================
function checkRolePermissions() {
    const user = JSON.parse(localStorage.getItem('user') || '{}');

    const role = (user.role || '').toLowerCase().trim();
    const currentPage = window.location.pathname;

    if (currentPage.includes('admin-dashboard.php') && role !== 'admin') {
        alert('Access denied. Admin privileges required.');
        logout();
        return;
    }

    if (currentPage.includes('faculty-dashboard.php') && role !== 'faculty') {
        alert('Access denied. Faculty privileges required.');
        logout();
        return;
    }
}

window.addEventListener('load', () => {
    setTimeout(() => {
        if (
            window.location.pathname.includes('admin-dashboard.php') ||
            window.location.pathname.includes('faculty-dashboard.php')
        ) {
            checkRolePermissions();
        }
    }, 100);
});