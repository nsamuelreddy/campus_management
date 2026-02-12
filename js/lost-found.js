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

// Report Item button
document.querySelector('.primary-btn')?.addEventListener('click', function() {
    alert('Report Item form would open here (Frontend only - no backend connection)');
});

// Contact buttons
document.querySelectorAll('.contact-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const card = this.closest('.item-card');
        const itemTitle = card.querySelector('.item-title').textContent;
        alert(`Contact information for "${itemTitle}" would be displayed here (Frontend only - no backend connection)`);
    });
});
