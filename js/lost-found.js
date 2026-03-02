// Lost & Found Page JavaScript

// Search functionality
document.querySelector('.search-section .search-input')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const items = document.querySelectorAll('.item-card');
    
    items.forEach(item => {
        const title = item.querySelector('.item-title').textContent.toLowerCase();
        const description = item.querySelector('.item-description').textContent.toLowerCase();
        
        if (title.includes(searchTerm) || description.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});
// 1. Open the Modal
document.querySelector('.primary-btn')?.addEventListener('click', function() {
    document.getElementById('reportModalOverlay').classList.add('active');
    document.getElementById('reportModal').classList.add('active');
});

// 2. Close the Modal
function closeReportModal() {
    document.getElementById('reportModalOverlay').classList.remove('active');
    document.getElementById('reportModal').classList.remove('active');
}

// 3. Handle Form Submission to PHP
function submitReportItem(event) {
    event.preventDefault();
    
    const itemData = {
        status: document.getElementById('reportStatus').value,
        title: document.getElementById('reportTitle').value,
        description: document.getElementById('reportDescription').value,
        location: document.getElementById('reportLocation').value
    };

    fetch('api/lost-found.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(itemData)
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert(data.message);
            closeReportModal();
            document.getElementById('reportItemForm').reset(); // Clear the form
            loadItems(); // Reload the list to show the new item
        }
    })
    .catch(err => console.error("Error reporting item:", err));
}
// Contact buttons
document.querySelectorAll('.contact-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const card = this.closest('.item-card');
        const itemTitle = card.querySelector('.item-title').textContent;
        alert(`Contact information for "${itemTitle}" would be displayed here (Frontend only - no backend connection)`);
    });
});
// Fetch and Display Items on Page Load
function loadItems() {
    fetch('api/lost-found.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const grid = document.querySelector('.items-grid');
                if (!grid) return;
                
                grid.innerHTML = ''; // Clear out the hardcoded HTML

                data.items.forEach(item => {
                    const statusClass = item.status.toLowerCase() === 'found' ? 'found' : 'lost';
                    
                    const html = `
                        <div class="item-card">
                            <div class="item-status ${statusClass}">${item.status}</div>
                            <h3 class="item-title">${item.title}</h3>
                            <p class="item-description">${item.description}</p>
                            <div class="item-location">${item.location} · ${item.date}</div>
                            <button class="contact-btn" onclick="alert('Contact details for ${item.title} will be sent to your email.')">Contact</button>
                        </div>
                    `;
                    grid.insertAdjacentHTML('beforeend', html);
                });
            }
        })
        .catch(err => console.error("Error loading items:", err));
}

// Run when the page loads
document.addEventListener('DOMContentLoaded', loadItems);