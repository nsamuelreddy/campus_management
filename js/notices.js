// Notices Page JavaScript

// Filter functionality
const filterTabs = document.querySelectorAll('.filter-tab');
const noticeCards = document.querySelectorAll('.notice-card');

filterTabs.forEach(tab => {
    tab.addEventListener('click', () => {
        // Update active tab
        filterTabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        
        const filter = tab.dataset.filter;
        
        // Filter notices
        noticeCards.forEach(card => {
            if (filter === 'all') {
                card.style.display = 'block';
            } else {
                const tags = card.querySelectorAll('.notice-tag');
                let hasMatch = false;
                
                tags.forEach(tag => {
                    if (tag.textContent.toLowerCase().includes(filter)) {
                        hasMatch = true;
                    }
                });
                
                card.style.display = hasMatch ? 'block' : 'none';
            }
        });
    });
});

// Acknowledge button
document.querySelectorAll('.acknowledge-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const card = this.closest('.notice-card');
        const title = card.querySelector('.notice-title').textContent;
        
        this.textContent = '✓ Acknowledged';
        this.style.background = '#d1fae5';
        this.style.color = '#065f46';
        this.disabled = true;
        
        console.log('Acknowledged notice:', title);
        // Frontend only - would normally send to backend
    });
});
