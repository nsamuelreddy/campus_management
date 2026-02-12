// Main JavaScript - Common functionality across all pages

// Login functionality
document.getElementById('loginForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const role = document.querySelector('.role-btn.active')?.textContent.trim();
    
    console.log('Login attempt (Frontend only):', { email, role });
    
    // Frontend only - simulate login
    if (email && password) {
        // Store user info in localStorage (frontend only)
        localStorage.setItem('user', JSON.stringify({ email, role }));
        
        // Redirect to dashboard
        window.location.href = 'dashboard.html';
    } else {
        alert('Please fill in all fields');
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
    localStorage.removeItem('user');
    window.location.href = 'index.html';
}

// Run auth check on page load (except for login page)
if (!window.location.pathname.includes('index.html')) {
    checkAuth();
}

// Load user info in navigation
const user = JSON.parse(localStorage.getItem('user') || '{}');
if (user.email) {
    const userInfo = document.querySelector('.user-info h4');
    const userRole = document.querySelector('.user-info p');
    
    if (userInfo) {
        // Get name from email
        const name = user.email.split('@')[0];
        const displayName = name.charAt(0).toUpperCase() + name.slice(1);
        userInfo.textContent = displayName;
    }
    
    if (userRole && user.role) {
        userRole.textContent = user.role;
    }
}
