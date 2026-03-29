// Main JavaScript - Common functionality across all pages

// Login functionality
document.getElementById('loginForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
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
                
                // ✅ FIX: .html → .php
                if(selectedRole === 'student') window.location.href = 'dashboard.php';
                else if(selectedRole === 'faculty') window.location.href = 'faculty-dashboard.php';
                else if(selectedRole === 'admin') window.location.href = 'admin-dashboard.php';
            } else {
                // ✅ FIX: error handling added
                alert('Login failed');
            }
        })
        .catch(err => {
            console.error("Login failed", err);
            alert('Server error');
        });
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
    
    // ✅ FIX: index.php instead of index.html
    if (!currentPage.includes('index.php') && !user && currentPage !== '/') {
        window.location.href = 'index.php';
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
        // ✅ FIX: index.php
        window.location.href = 'index.php';
    });
}

// Run auth check on page load (except for login page)
if (!window.location.pathname.includes('index.php')) {
    checkAuth();
}

// Load user info in navigation
const user = JSON.parse(localStorage.getItem('user') || '{}');
if (user.email) {
    const userInfo = document.querySelector('.user-info h4, .user-name');
    const userRole = document.querySelector('.user-info p, .user-role');
    
    if (userInfo) {
        let displayName = user.email.split('@')[0];
        displayName = displayName.charAt(0).toUpperCase() + displayName.slice(1);
        
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
    
    // ✅ FIX: .html → .php
    if (currentPage.includes('admin-dashboard.php') && user.role !== 'admin') {
        alert('Access denied. Admin privileges required.');
        logout();
        return;
    }
    
    if (currentPage.includes('faculty-dashboard.php') && user.role !== 'faculty') {
        alert('Access denied. Faculty privileges required.');
        logout();
        return;
    }
}

// Run permission check on protected pages
if (window.location.pathname.includes('admin-dashboard.php') || 
    window.location.pathname.includes('faculty-dashboard.php')) {
    checkRolePermissions();
}