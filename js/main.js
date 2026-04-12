const notificationState = {
    items: [],
    isOpen: false,
    panelEl: null,
    activeBell: null
};

function ensureNotificationStyles() {
    if (document.getElementById('globalNotificationStyles')) return;

    const style = document.createElement('style');
    style.id = 'globalNotificationStyles';
    style.textContent = `
        .notification-panel {
            position: fixed;
            width: 360px;
            max-width: calc(100vw - 24px);
            background: rgba(255, 255, 255, 0.98);
            border: 1px solid #dbe7ff;
            border-radius: 14px;
            box-shadow: 0 22px 40px rgba(15, 23, 42, 0.18);
            backdrop-filter: blur(10px);
            z-index: 9999;
            overflow: hidden;
        }

        .notification-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 14px;
            border-bottom: 1px solid #eaf0ff;
            font-weight: 700;
            color: #0f172a;
        }

        .notification-mark-all {
            background: #eef3ff;
            color: #1f3bbb;
            border: 1px solid #d4e0ff;
            border-radius: 8px;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        .notification-list {
            max-height: 330px;
            overflow: auto;
        }

        .notification-item {
            padding: 12px 14px;
            border-bottom: 1px solid #edf2ff;
            display: flex;
            gap: 10px;
            align-items: flex-start;
            background: #ffffff;
        }

        .notification-item.unread {
            background: #f5f8ff;
        }

        .notification-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            margin-top: 7px;
            flex-shrink: 0;
        }

        .notification-dot.info { background: #3b82f6; }
        .notification-dot.success { background: #10b981; }
        .notification-dot.warning { background: #f59e0b; }
        .notification-dot.error { background: #ef4444; }

        .notification-content {
            flex: 1;
            min-width: 0;
        }

        .notification-title {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 3px;
        }

        .notification-message {
            font-size: 12px;
            color: #475569;
            line-height: 1.4;
            margin-bottom: 6px;
        }

        .notification-meta {
            font-size: 11px;
            color: #64748b;
        }

        .notification-read-btn {
            background: transparent;
            border: 1px solid #d4def9;
            color: #334155;
            border-radius: 7px;
            padding: 4px 8px;
            font-size: 11px;
            cursor: pointer;
            flex-shrink: 0;
        }

        .notification-empty {
            padding: 24px;
            text-align: center;
            color: #64748b;
            font-size: 13px;
        }
    `;

    document.head.appendChild(style);
}

function getTimeAgo(isoString) {
    const ts = new Date(isoString).getTime();
    if (!ts) return 'Just now';

    const diffSec = Math.max(1, Math.floor((Date.now() - ts) / 1000));
    if (diffSec < 60) return 'Just now';
    if (diffSec < 3600) return Math.floor(diffSec / 60) + 'm ago';
    if (diffSec < 86400) return Math.floor(diffSec / 3600) + 'h ago';
    return Math.floor(diffSec / 86400) + 'd ago';
}

function getNotificationType(type) {
    if (type === 'success' || type === 'warning' || type === 'error') return type;
    return 'info';
}

function updateNotificationBadges(unreadCount) {
    document.querySelectorAll('.notification-badge').forEach((badge) => {
        if (unreadCount > 0) {
            badge.textContent = unreadCount > 9 ? '9+' : String(unreadCount);
            badge.style.display = 'flex';
        } else {
            badge.textContent = '';
            badge.style.display = 'none';
        }
    });
}

function closeNotificationPanel() {
    notificationState.isOpen = false;
    notificationState.activeBell = null;
    if (notificationState.panelEl) {
        notificationState.panelEl.remove();
        notificationState.panelEl = null;
    }
}

function renderNotificationPanel(anchorEl) {
    closeNotificationPanel();

    const panel = document.createElement('div');
    panel.className = 'notification-panel';

    const itemsMarkup = notificationState.items.length
        ? notificationState.items.map((item) => {
            const type = getNotificationType(item.type);
            return `
                <div class="notification-item ${item.read ? '' : 'unread'}" data-id="${item.id}">
                    <span class="notification-dot ${type}"></span>
                    <div class="notification-content">
                        <div class="notification-title">${item.title || 'Notification'}</div>
                        <div class="notification-message">${item.message || ''}</div>
                        <div class="notification-meta">${getTimeAgo(item.createdAt)}</div>
                    </div>
                    ${item.read ? '' : '<button class="notification-read-btn" data-action="read">Mark read</button>'}
                </div>
            `;
        }).join('')
        : '<div class="notification-empty">No notifications right now.</div>';

    panel.innerHTML = `
        <div class="notification-panel-header">
            <span>Notifications</span>
            <button class="notification-mark-all" data-action="read-all">Mark all read</button>
        </div>
        <div class="notification-list">${itemsMarkup}</div>
    `;

    document.body.appendChild(panel);
    notificationState.panelEl = panel;
    notificationState.isOpen = true;
    notificationState.activeBell = anchorEl;

    const bellRect = anchorEl.getBoundingClientRect();
    const panelRect = panel.getBoundingClientRect();
    const desiredLeft = Math.min(
        window.innerWidth - panelRect.width - 8,
        Math.max(8, bellRect.right - panelRect.width)
    );

    panel.style.top = (bellRect.bottom + 10) + 'px';
    panel.style.left = desiredLeft + 'px';

    panel.addEventListener('click', (evt) => {
        const action = evt.target.getAttribute('data-action');
        if (!action) return;

        if (action === 'read-all') {
            fetch('api/notifications.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'mark_all_read' })
            })
            .then((res) => res.json())
            .then((data) => {
                if (data.success) {
                    notificationState.items = notificationState.items.map((i) => ({ ...i, read: true }));
                    updateNotificationBadges(0);
                    renderNotificationPanel(anchorEl);
                }
            })
            .catch((err) => console.error('Failed to mark all notifications as read', err));
            return;
        }

        if (action === 'read') {
            const container = evt.target.closest('.notification-item');
            const notificationId = container?.getAttribute('data-id');
            if (!notificationId) return;

            fetch('api/notifications.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'mark_read', id: notificationId })
            })
            .then((res) => res.json())
            .then((data) => {
                if (data.success) {
                    notificationState.items = notificationState.items.map((i) =>
                        i.id === notificationId ? { ...i, read: true } : i
                    );
                    updateNotificationBadges(data.unread ?? 0);
                    renderNotificationPanel(anchorEl);
                }
            })
            .catch((err) => console.error('Failed to mark notification as read', err));
        }
    });
}

function initNotifications() {
    const bells = document.querySelectorAll('.notification-btn, .notification-icon');
    if (!bells.length) return;

    ensureNotificationStyles();

    fetch('api/notifications.php')
        .then((res) => res.json())
        .then((data) => {
            if (!data.success) return;
            notificationState.items = Array.isArray(data.notifications) ? data.notifications : [];
            updateNotificationBadges(data.unread || 0);
        })
        .catch((err) => console.error('Failed to load notifications', err));

    bells.forEach((bell) => {
        bell.style.cursor = 'pointer';
        bell.addEventListener('click', (evt) => {
            evt.stopPropagation();
            if (notificationState.isOpen && notificationState.activeBell === bell) {
                closeNotificationPanel();
            } else {
                renderNotificationPanel(bell);
            }
        });
    });

    document.addEventListener('click', (evt) => {
        if (!notificationState.isOpen) return;
        if (notificationState.panelEl?.contains(evt.target)) return;
        closeNotificationPanel();
    });

    window.addEventListener('resize', () => {
        if (notificationState.isOpen && notificationState.activeBell) {
            renderNotificationPanel(notificationState.activeBell);
        }
    });
}
// Main JavaScript - Common functionality across all pages

const GOOGLE_CLIENT_ID = document.body?.dataset?.googleClientId || '';

function getSelectedRole() {
    return document.querySelector('.role-btn.active')?.getAttribute('data-role') || 'student';
}

function redirectByRole(role) {
    if (role === 'student') window.location.href = 'dashboard.html';
    else if (role === 'faculty') window.location.href = 'faculty-dashboard.html';
    else if (role === 'admin') window.location.href = 'admin-dashboard.html';
    else window.location.href = 'dashboard.html';
}

function persistUserAndRedirect(user) {
    localStorage.setItem('user', JSON.stringify(user));
    redirectByRole(user.role);
}

function updateGoogleStatus(message, isError = false) {
    const statusEl = document.getElementById('googleSignInStatus');
    if (!statusEl) return;
    statusEl.textContent = message || '';
    statusEl.classList.toggle('error', Boolean(isError));
}

function handleGoogleCredentialResponse(response) {
    const selectedRole = getSelectedRole();

    if (!response?.credential) {
        updateGoogleStatus('Google sign-in did not return a credential. Please try again.', true);
        return;
    }

    fetch('api/auth.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'google_login',
            idToken: response.credential,
            role: selectedRole,
            clientId: GOOGLE_CLIENT_ID
        })
    })
    .then((res) => res.json())
    .then((data) => {
        if (!data.success) {
            updateGoogleStatus(data.message || 'Google sign-in failed.', true);
            return;
        }

        updateGoogleStatus('Google sign-in successful. Redirecting...');
        persistUserAndRedirect(data.user);
    })
    .catch((err) => {
        console.error('Google login failed', err);
        updateGoogleStatus('Unable to reach authentication service.', true);
    });
}

function initGoogleSignIn() {
    const buttonContainer = document.getElementById('googleSignInButton');
    if (!buttonContainer) return;

    if (!GOOGLE_CLIENT_ID || GOOGLE_CLIENT_ID.includes('YOUR_GOOGLE_CLIENT_ID')) {
        updateGoogleStatus('Set a valid Google Client ID in index.html to enable Google sign-in.', true);
        return;
    }

    if (!window.google?.accounts?.id) {
        updateGoogleStatus('Google Identity Services failed to load. Refresh and try again.', true);
        return;
    }

    window.google.accounts.id.initialize({
        client_id: GOOGLE_CLIENT_ID,
        callback: handleGoogleCredentialResponse
    });

    window.google.accounts.id.renderButton(buttonContainer, {
        type: 'standard',
        theme: 'outline',
        size: 'large',
        text: 'signin_with',
        shape: 'rectangular',
        width: 320
    });
}

// Login functionality
document.getElementById('loginForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const selectedRole = getSelectedRole();
    
    if (email && password && selectedRole) {
        // Send login request to PHP
        fetch('api/auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'login', email: email, password: password, role: selectedRole })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                persistUserAndRedirect(data.user);
            } else {
                alert(data.message || 'Login failed');
            }
        })
        .catch(err => console.error("Login failed", err));
    } else {
        alert('Please fill in all fields and select a role');
    }
});

initNotifications();
if (window.location.pathname.includes('index.html') || window.location.pathname.endsWith('/')) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGoogleSignIn);
    } else {
        initGoogleSignIn();
    }
}

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

function getDisplayName(user) {
    const fullName = String(user?.name || '').trim();
    if (fullName) return fullName;

    const email = String(user?.email || '').trim();
    if (email) return email;

    return 'User';
}

function applyUserProfileUI(user) {
    if (!user || (!user.email && !user.name)) return;

    const displayName = getDisplayName(user);
    const roleLabel = user.role
        ? user.role.charAt(0).toUpperCase() + user.role.slice(1)
        : 'User';
    const avatarChar = displayName.charAt(0).toUpperCase();

    document.querySelectorAll('.user-info h4, .user-name, .user-name-header').forEach((el) => {
        el.textContent = displayName;
    });

    document.querySelectorAll('.user-info p, .user-role, .user-role-header').forEach((el) => {
        el.textContent = roleLabel;
    });

    document.querySelectorAll('.user-avatar, .user-avatar-header').forEach((el) => {
        el.textContent = avatarChar;
    });
}

function syncUserFromSession() {
    if (window.location.pathname.includes('index.html')) return;

    fetch('api/auth.php?action=me')
        .then((res) => res.json())
        .then((data) => {
            if (!data.success || !data.user) return;
            localStorage.setItem('user', JSON.stringify(data.user));
            applyUserProfileUI(data.user);
        })
        .catch((err) => {
            console.error('Unable to sync user profile from session', err);
        });
}

// Load user info in navigation
const user = JSON.parse(localStorage.getItem('user') || '{}');
applyUserProfileUI(user);
syncUserFromSession();

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
