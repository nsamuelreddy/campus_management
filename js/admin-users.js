// Admin User Management JavaScript (Frontend Only)

// Sample user data (stored in frontend only)
let users = [
    { id: 1, name: 'Arjun Sharma', email: 'arjun@campus.edu', role: 'Student', status: 'Active' },
    { id: 2, name: 'Dr. Priya Mehta', email: 'priya@campus.edu', role: 'Faculty', status: 'Active' },
    { id: 3, name: 'Sneha R.', email: 'sneha@campus.edu', role: 'Student', status: 'Active' },
    { id: 4, name: 'Rohit K.', email: 'rohit@campus.edu', role: 'Student', status: 'Inactive' },
    { id: 5, name: 'Dr. Anand', email: 'anand@campus.edu', role: 'Faculty', status: 'Active' }
];

// Load users data from localStorage if available
function loadUsers() {
    const stored = localStorage.getItem('users');
    if (stored) {
        users = JSON.parse(stored);
    }
    renderUsers();
}

// Save users to localStorage
function saveUsers() {
    localStorage.setItem('users', JSON.stringify(users));
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

// Edit user function
function editUser(userId) {
    const user = users.find(u => u.id === userId);
    if (!user) return;
    
    const modal = document.getElementById('editModal');
    const overlay = document.getElementById('modalOverlay');
    
    document.getElementById('editName').value = user.name;
    document.getElementById('editEmail').value = user.email;
    document.getElementById('editRole').value = user.role;
    document.getElementById('editStatus').value = user.status;
    document.getElementById('editUserId').value = user.id;
    
    modal.classList.add('active');
    overlay.classList.add('active');
}

// Close modal
function closeModal() {
    const modal = document.getElementById('editModal');
    const overlay = document.getElementById('modalOverlay');
    modal.classList.remove('active');
    overlay.classList.remove('active');
}

// Save edited user
function saveEditedUser(event) {
    event.preventDefault();
    
    const id = parseInt(document.getElementById('editUserId').value);
    const user = users.find(u => u.id === id);
    
    if (user) {
        user.name = document.getElementById('editName').value;
        user.email = document.getElementById('editEmail').value;
        user.role = document.getElementById('editRole').value;
        user.status = document.getElementById('editStatus').value;
        
        saveUsers();
        renderUsers();
        closeModal();
        showNotification('User updated successfully!');
    }
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
