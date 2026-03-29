// Notices Page JavaScript
// Data state and loading
let allNotices = [];

function loadNotices() {
    fetch('api/notices.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allNotices = data.notices;
                renderNotices('all');
            }
        })
        .catch(err => console.error("Error loading notices:", err));
}

function renderNotices(filterType) {
    const grid = document.querySelector('.notices-grid');
    if (!grid) return;
    grid.innerHTML = '';

    const filteredNotices = filterType === 'all' 
        ? allNotices 
        : allNotices.filter(n => n.category.toLowerCase() === filterType.toLowerCase() || (filterType === 'urgent' && n.urgent));

    filteredNotices.forEach(notice => {
        let tagsHtml = `<span class="notice-tag ${notice.category === 'urgent' ? 'urgent' : ''}">${notice.category}</span>`;
        if (notice.urgent && notice.category !== 'urgent') tagsHtml += ` <span class="notice-tag urgent">Urgent</span>`;

        const html = `
            <div class="notice-card">
                <div class="notice-tags">${tagsHtml}</div>
                <h3 class="notice-title">${notice.title}</h3>
                <p class="notice-description">${notice.content}</p>
                <div class="notice-date">${notice.date}</div>
                <button class="acknowledge-btn" onclick="acknowledgeNotice(this)">Acknowledge</button>
            </div>
        `;
        grid.insertAdjacentHTML('beforeend', html);
    });
}
// Filter functionality
const filterTabs = document.querySelectorAll('.filter-tab');

filterTabs.forEach(tab => {
    tab.addEventListener('click', () => {
        filterTabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        
        // Use our new render function instead of hiding HTML elements manually
        renderNotices(tab.dataset.filter); 
    });
});

// Acknowledge button function (called directly from the HTML we generated)
function acknowledgeNotice(btn) {
    btn.textContent = '✓ Acknowledged';
    btn.style.background = '#d1fae5';
    btn.style.color = '#065f46';
    btn.disabled = true;
}

// Load notices when the page starts
document.addEventListener('DOMContentLoaded', loadNotices);
