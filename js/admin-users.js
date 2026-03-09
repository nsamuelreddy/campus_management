
let users = []; 

// Load users data from PHP
function loadUsers() {
    fetch('api/users.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                users = data.users; // Update global array
                renderUsers(); // Draw the table
            }
        })
        .catch(err => console.error("Error loading users:", err));
}

// Render users table
function renderUsers() {
    const tbody = document.getElementById('usersTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = users.map(user => `
        <tr>
            <td>${user.name}</td>
            <td>${user.email}</td>
            <td><span class="role-badge role-${user.role.toLowerCase()}">${user.role}</span></td>
            <td><span class="status-badge status-${user.status.toLowerCase()}">${user.status}</span></td>
            <td>
                <button class="action-btn edit-btn" onclick="editUser(${user.id})">Edit</button>
            </td>
        </tr>
    `).join('');
}
// Edit user (Opens the modal and fills in data)
function editUser(id) {
    const user = users.find(u => parseInt(u.id) === parseInt(id));
    if (user) {
        document.getElementById('editUserId').value = user.id;
        document.getElementById('editName').value = user.name;
        document.getElementById('editEmail').value = user.email;
        document.getElementById('editRole').value = user.role;
        document.getElementById('editStatus').value = user.status;
        
        const modal = document.getElementById('editModal');
        const overlay = document.getElementById('modalOverlay');
        modal.classList.add('active');
        overlay.classList.add('active');
    }
}
// Save edited user to PHP
function saveEditedUser(event) {
    event.preventDefault();
    
    const updatedData = {
        action: 'update',
        id: document.getElementById('editUserId').value,
        name: document.getElementById('editName').value,
        email: document.getElementById('editEmail').value,
        role: document.getElementById('editRole').value,
        status: document.getElementById('editStatus').value
    };
    
    fetch('api/users.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(updatedData)
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            showNotification(data.message);
            closeModal();
            loadUsers(); // Fetch fresh data from PHP
        }
    })
    .catch(err => console.error("Error updating user:", err));
}
// Close modal
function closeModal() {
    const modal = document.getElementById('editModal');
    const overlay = document.getElementById('modalOverlay');
    modal.classList.remove('active');
    overlay.classList.remove('active');
}

// Show notification
function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification success';
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Search functionality
function searchUsers() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const filteredUsers = users.filter(user => 
        user.name.toLowerCase().includes(searchTerm) ||
        user.email.toLowerCase().includes(searchTerm) ||
        user.role.toLowerCase().includes(searchTerm)
    );
    
    const tbody = document.getElementById('usersTableBody');
    tbody.innerHTML = filteredUsers.map(user => `
        <tr>
            <td>${user.name}</td>
            <td>${user.email}</td>
            <td><span class="role-badge role-${user.role.toLowerCase()}">${user.role}</span></td>
            <td><span class="status-badge status-${user.status.toLowerCase()}">${user.status}</span></td>
            <td>
                <button class="action-btn edit-btn" onclick="editUser(${user.id})">Edit</button>
            </td>
        </tr>
    `).join('');
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', loadUsers);
