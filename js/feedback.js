// Feedback Page JavaScript

// Rating system
const ratingItems = document.querySelectorAll('.rating-item');

ratingItems.forEach(item => {
    const radios = item.querySelectorAll('.rating-radio');
    
    radios.forEach((radio, index) => {
        radio.addEventListener('click', () => {
            // Remove checked from all radios in this item
            radios.forEach(r => r.classList.remove('checked'));
            // Add checked to clicked radio
            radio.classList.add('checked');
            
            // Store rating value (could use data attributes)
            item.dataset.rating = index + 1;
        });
    });
});

// Form submission to PHP Backend
document.querySelector('.feedback-form')?.addEventListener('submit', (e) => {
    e.preventDefault();
    
    // Collect all ratings
    const ratings = {};
    ratingItems.forEach(item => {
        const label = item.querySelector('.rating-label').textContent;
        const rating = item.dataset.rating || 'Not rated';
        ratings[label] = rating;
    });
    
    // Get select values
    const feedbackData = {
        semester: document.querySelector('select[name="semester"]')?.value,
        department: document.querySelector('select[name="department"]')?.value,
        faculty: document.querySelector('select[name="faculty"]')?.value,
        subject: document.querySelector('select[name="subject"]')?.value,
        ratings: ratings
    };
    
    // Send data to PHP
    fetch('api/feedback.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(feedbackData)
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert(data.message);
            
            // Reset form visually
            document.querySelector('.feedback-form').reset();
            ratingItems.forEach(item => {
                item.querySelectorAll('.rating-radio').forEach(r => r.classList.remove('checked'));
                delete item.dataset.rating;
            });
        }
    })
    .catch(err => console.error("Error submitting feedback:", err));
});
// Reset button
document.querySelector('.feedback-reset')?.addEventListener('click', () => {
    ratingItems.forEach(item => {
        item.querySelectorAll('.rating-radio').forEach(r => r.classList.remove('checked'));
        delete item.dataset.rating;
    });
});
