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

// Handle form submission
document.getElementById('complaintForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get form values
    const complaintType = document.getElementById('complaintType').value;
    const subject = document.getElementById('subject').value;
    const description = document.getElementById('description').value;
    
    // Show success message
    alert('Complaint submitted successfully! (Frontend only - no backend connection)');
    
    // Reset form and go back to list
    this.reset();
    showComplaintsList();
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
