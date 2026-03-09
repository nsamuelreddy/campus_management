// Main JavaScript - Common functionality across all pages

// Login functionality
document.getElementById('loginForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value; // We are ignoring password on backend for now
    const selectedRole = document.querySelector('.role-btn.active')?.getAttribute('data-role');
    
    if (email && password && selectedRole) {
        // Send login request to PHP
        fetch('api/auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'login', email: email, role: selectedRole })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                localStorage.setItem('user', JSON.stringify(data.user));
                
                if(selectedRole === 'student') window.location.href = 'dashboard.html';
                else if(selectedRole === 'faculty') window.location.href = 'faculty-dashboard.html';
                else if(selectedRole === 'admin') window.location.href = 'admin-dashboard.html';
            }
        })
        .catch(err => console.error("Login failed", err));
    } else {
        alert('Please fill in all fields and select a role');
    }
});

// Role selection
document.querySelectorAll('.role-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
    });
});

// Check if user is logged in (for protected pages)
function checkAuth() {
    const user = localStorage.getItem('user');
    const currentPage = window.location.pathname;
    
    // If not on login page and not logged in, redirect to login
    if (!currentPage.includes('index.html') && !user && currentPage !== '/') {
        window.location.href = 'index.html';
    }
}

// Logout functionality
function logout() {
    fetch('api/auth.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'logout' })
    }).then(() => {
        localStorage.removeItem('user');
        window.location.href = 'index.html';
    });
}

// Run auth check on page load (except for login page)
if (!window.location.pathname.includes('index.html')) {
    checkAuth();
}

// Load user info in navigation
const user = JSON.parse(localStorage.getItem('user') || '{}');
if (user.email) {
    const userInfo = document.querySelector('.user-info h4, .user-name');
    const userRole = document.querySelector('.user-info p, .user-role');
    
    if (userInfo) {
        // Get name from email or set default names based on role
        let displayName = user.email.split('@')[0];
        displayName = displayName.charAt(0).toUpperCase() + displayName.slice(1);
        
        // Set role-specific default names if needed
        if (user.role === 'faculty') {
            displayName = 'Dr. ' + displayName;
        } else if (user.role === 'admin') {
            displayName = displayName + ' Kumar';
        }
        
        userInfo.textContent = displayName;
    }
    
    if (userRole && user.role) {
        userRole.textContent = user.role.charAt(0).toUpperCase() + user.role.slice(1);
    }
}

// Role-based navigation restrictions
function checkRolePermissions() {
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    const currentPage = window.location.pathname;
    
    // If user tries to access admin dashboard without admin role
    if (currentPage.includes('admin-dashboard.html') && user.role !== 'admin') {
        alert('Access denied. Admin privileges required.');
        logout();
        return;
    }
    
    // If user tries to access faculty dashboard without faculty role
    if (currentPage.includes('faculty-dashboard.html') && user.role !== 'faculty') {
        alert('Access denied. Faculty privileges required.');
        logout();
        return;
    }
}

// Run permission check on protected pages
if (window.location.pathname.includes('admin-dashboard.html') || 
    window.location.pathname.includes('faculty-dashboard.html')) {
    checkRolePermissions();
}
