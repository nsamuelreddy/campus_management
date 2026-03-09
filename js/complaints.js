// Complaints Page JavaScript

// Show/Hide views
function showComplaintForm() {
    document.getElementById('complaintsListView').classList.add('hidden');
    document.getElementById('complaintFormView').classList.remove('hidden');
}

function showComplaintsList() {
    document.getElementById('complaintFormView').classList.add('hidden');
    document.getElementById('complaintsListView').classList.remove('hidden');
}

// Handle form submission and send to PHP
document.getElementById('complaintForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const complaintData = {
        type: document.getElementById('complaintType').value,
        subject: document.getElementById('subject').value,
        description: document.getElementById('description').value
    };
    
    fetch('api/complaints.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(complaintData)
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert(data.message);
            this.reset();
            showComplaintsList();
            loadComplaints(); // Refresh the list
        }
    })
    .catch(err => console.error("Error saving complaint:", err));
});

// File upload preview
document.getElementById('fileInput')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const fileUploadText = document.querySelector('.file-upload-text');
        fileUploadText.textContent = file.name;
    }
});

// Drag and drop for file upload
const fileUpload = document.querySelector('.file-upload');
if (fileUpload) {
    fileUpload.addEventListener('dragover', (e) => {
        e.preventDefault();
        fileUpload.style.borderColor = '#3b82f6';
        fileUpload.style.backgroundColor = '#eff6ff';
    });

    fileUpload.addEventListener('dragleave', (e) => {
        e.preventDefault();
        fileUpload.style.borderColor = '#d1d5db';
        fileUpload.style.backgroundColor = '#f9fafb';
    });

    fileUpload.addEventListener('drop', (e) => {
        e.preventDefault();
        fileUpload.style.borderColor = '#d1d5db';
        fileUpload.style.backgroundColor = '#f9fafb';
        
        const file = e.dataTransfer.files[0];
        if (file) {
            document.getElementById('fileInput').files = e.dataTransfer.files;
            const fileUploadText = document.querySelector('.file-upload-text');
            fileUploadText.textContent = file.name;
        }
    });
}
// Fetch and Display Complaints on Page Load
function loadComplaints() {
    fetch('api/complaints.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const listContainer = document.querySelector('.complaints-list');
                if (!listContainer) return;
                
                listContainer.innerHTML = ''; // Clear hardcoded HTML

                data.complaints.forEach(complaint => {
                    let statusClass = 'pending';
                    if (complaint.status === 'In Progress') statusClass = 'in-progress';
                    if (complaint.status === 'Resolved') statusClass = 'resolved';

                    const html = `
                        <div class="complaint-card">
                            <div class="complaint-header">
                                <span class="complaint-type">${complaint.type}</span>
                                <div class="complaint-status ${statusClass}">${complaint.status}</div>
                            </div>
                            <h3 class="complaint-title">${complaint.subject}</h3>
                            <p class="complaint-description">${complaint.description}</p>
                            <div class="complaint-meta">${complaint.date}</div>
                        </div>
                    `;
                    listContainer.insertAdjacentHTML('beforeend', html);
                });
            }
        })
        .catch(err => console.error("Error loading complaints:", err));
}

// Run when the page loads
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('.complaints-list')) {
        loadComplaints();
    }
});