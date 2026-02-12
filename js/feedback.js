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

// Form submission
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
    const semester = document.querySelector('select[name="semester"]')?.value;
    const department = document.querySelector('select[name="department"]')?.value;
    const faculty = document.querySelector('select[name="faculty"]')?.value;
    const subject = document.querySelector('select[name="subject"]')?.value;
    
    console.log('Feedback submitted:', { semester, department, faculty, subject, ratings });
    
    alert('Feedback submitted successfully! (Frontend only - no backend connection)');
    
    // Reset form
    document.querySelector('.feedback-form').reset();
    ratingItems.forEach(item => {
        item.querySelectorAll('.rating-radio').forEach(r => r.classList.remove('checked'));
        delete item.dataset.rating;
    });
});

// Reset button
document.querySelector('.feedback-reset')?.addEventListener('click', () => {
    ratingItems.forEach(item => {
        item.querySelectorAll('.rating-radio').forEach(r => r.classList.remove('checked'));
        delete item.dataset.rating;
    });
});
