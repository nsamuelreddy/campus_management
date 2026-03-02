// Load settings from PHP
function loadSettings() {
    fetch('api/settings.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const settings = data.settings;
                
                // Populate text inputs
                document.getElementById('institutionName').value = settings.institutionName;
                document.getElementById('adminEmail').value = settings.adminEmail;
                
                // Populate toggle switches
                document.getElementById('emailNotifications').checked = settings.emailNotifications;
                document.getElementById('smsAlerts').checked = settings.smsAlerts;
                document.getElementById('weeklyReports').checked = settings.weeklyReports;

                // Update the visual blue background on the toggles based on their checked state
                document.querySelectorAll('.toggle-switch input').forEach(toggle => {
                    toggle.parentElement.classList.toggle('active', toggle.checked);
                });
            }
        })
        .catch(err => console.error("Error loading settings:", err));
}

// Save settings to PHP
function saveSettings(event) {
    if (event) event.preventDefault();
    
    const settingsData = {
        institutionName: document.getElementById('institutionName').value,
        adminEmail: document.getElementById('adminEmail').value,
        emailNotifications: document.getElementById('emailNotifications').checked,
        smsAlerts: document.getElementById('smsAlerts').checked,
        weeklyReports: document.getElementById('weeklyReports').checked
    };
    
    fetch('api/settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(settingsData)
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            showNotification(data.message);
        }
    })
    .catch(err => console.error("Error saving settings:", err));
}

// Toggle switch functionality
function setupToggles() {
    const toggles = document.querySelectorAll('.toggle-switch input');
    toggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            this.parentElement.classList.toggle('active', this.checked);
        });
    });
}

// Show notification
function showNotification(message) {
    const notification = document.createElement('div');
    notification.textContent = message;
    
    // Add inline styling to guarantee the popup looks good and is visible
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #10b981;
        color: white;
        padding: 16px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 9999;
        font-weight: 600;
        transition: opacity 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Fade out and remove after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadSettings();
    setupToggles();
    
    // Make sure the save button is listening for the click
    const saveBtn = document.querySelector('button[onclick="saveSettings(event)"]') || document.querySelector('button[type="submit"]');
    if (saveBtn) {
        saveBtn.onclick = saveSettings;
    }
});