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
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(complaintData)
    })
    .then(response => response.json())
    .then(data => {
        console.log("SUBMIT RESPONSE:", data);

        if(data.success) {
            alert(data.message || "Complaint submitted successfully");
            this.reset();
            showComplaintsList();
            loadComplaints();
        } else {
            alert(data.message || "Something went wrong");
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

// Drag and drop
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

// Fetch and Display Complaints
function loadComplaints() {
    fetch('api/complaints.php', {
        method: 'GET',
        credentials: 'include'
    })
    .then(response => response.json())
    .then(data => {
        console.log("LOAD DATA:", data);

        if (data.success) {
            const listContainer = document.querySelector('.complaints-list');
            if (!listContainer) return;
            
            listContainer.innerHTML = '';

            data.complaints.forEach(complaint => {

                //  FIX: ensure correct id mapping
                const complaintId = complaint.id || complaint.complaint_id;

                //  FIX: normalize status
                const status = (complaint.status || '').toLowerCase();

                let statusClass = 'pending';
                if (status === 'in progress') statusClass = 'in-progress';
                if (status === 'resolved') statusClass = 'resolved';

                //  FIX: safe date
                const date = complaint.date || complaint.created_at;

                const isStaff = (data.role === 'admin' || data.role === 'faculty');

                const html = `
                    <div class="complaint-card">
                        <div class="complaint-header">
                             <span class="complaint-type">${complaint.type}</span>
                             <div class="complaint-status ${statusClass}">${complaint.status}</div>
                         </div>

                         <h3 class="complaint-title">${complaint.subject}</h3>
                         <p class="complaint-description">${complaint.description}</p>
                         <div class="complaint-meta">${date}</div>

                        ${
                            isStaff ? `
                            <div class="complaint-actions">
                                 <button onclick="updateStatus(${complaintId}, 'In Progress')">In Progress</button>
                                 <button onclick="updateStatus(${complaintId}, 'Resolved')">Resolved</button>
                            </div>
                           ` : ''
                         }
                    </div>
                `;
                listContainer.insertAdjacentHTML('beforeend', html);
            });
        } else {
            console.log("ERROR:", data.message);
        }
    })
    .catch(err => console.error("Error loading complaints:", err));
}

// Update Status
async function updateStatus(id, status) {
    try {
        const res = await fetch('api/complaints.php', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'update_status',
                id: id,
                status: status
            })
        });

        const data = await res.json();
        console.log("UPDATE:", data);

        if (data.success) {
            loadComplaints(); // refresh
        } else {
            alert(data.message);
        }

    } catch (err) {
        console.error("Update error:", err);
    }
}

// Run on load
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('.complaints-list')) {
        loadComplaints();
    }
});