// Admin Settings JavaScript (Frontend Only)

// Load settings from localStorage
function loadSettings() {
    const settings = JSON.parse(localStorage.getItem('adminSettings') || '{}');
    
    // Load general settings
    if (settings.institutionName) {
        document.getElementById('institutionName').value = settings.institutionName;
    }
    if (settings.adminEmail) {
        document.getElementById('adminEmail').value = settings.adminEmail;
    }
    
    // Load notification toggles
    document.getElementById('emailNotifications').checked = settings.emailNotifications !== false;
    document.getElementById('smsAlerts').checked = settings.smsAlerts !== false;
    document.getElementById('weeklyReports').checked = settings.weeklyReports !== false;
}

// Save settings
function saveSettings(event) {
    event.preventDefault();
    
    const settings = {
        institutionName: document.getElementById('institutionName').value,
        adminEmail: document.getElementById('adminEmail').value,
        emailNotifications: document.getElementById('emailNotifications').checked,
        smsAlerts: document.getElementById('smsAlerts').checked,
        weeklyReports: document.getElementById('weeklyReports').checked
    };
    
    localStorage.setItem('adminSettings', JSON.stringify(settings));
    showNotification('Settings saved successfully!');
}

// Toggle switch functionality
function setupToggles() {
    const toggles = document.querySelectorAll('.toggle-switch input');
    toggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            // Visual feedback
            this.parentElement.classList.toggle('active', this.checked);
        });
    });
}

// Show notification
function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification success';
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #10b981;
        color: white;
        padding: 16px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        font-weight: 600;
        animation: slideIn 0.3s ease;
    `;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadSettings();
    setupToggles();
    
    // Add form submit handler
    const form = document.getElementById('settingsForm');
    if (form) {
        form.addEventListener('submit', saveSettings);
    }
});
