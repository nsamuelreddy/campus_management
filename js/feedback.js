
// RATING CLICK SYSTEM (FIXED)
// ============================
const ratingItems = document.querySelectorAll('.rating-item');

ratingItems.forEach(item => {
    const radios = item.querySelectorAll('.rating-radio');
    
    radios.forEach((radio) => {
        radio.addEventListener('click', () => {

            // remove previous selection
            radios.forEach(r => r.classList.remove('checked'));

            // add selected style
            radio.classList.add('checked');

            // ✅ store actual selected value (IMPORTANT FIX)
            item.dataset.rating = radio.dataset.value;
        });
    });
});


// ============================
// FORM SUBMISSION (FIXED)
// ============================
document.querySelector('.feedback-form')?.addEventListener('submit', (e) => {
    e.preventDefault();
    
    const ratings = {};
    const categories = {};

    ratingItems.forEach(item => {
         const selected = item.querySelector('.rating-radio.checked');

             if (selected) {
                const value = Number(selected.dataset.value);
                const key = selected.dataset.criteria;

                categories[key] = value;   // for DB columns
                ratings[key] = value;      // for average calculation
            }
    });
    
    const feedbackData = {
        semester: document.querySelector('select[name="semester"]')?.value,
        faculty: document.querySelector('select[name="faculty"]')?.value,
        subject: document.querySelector('select[name="subject"]')?.value,
        ratings: Object.values(ratings),

        // ✅ FORCE VALUES (so PHP never gets undefined)
        teaching_clarity: categories.teaching_clarity || 0,
        subject_knowledge: categories.subject_knowledge || 0,
        interaction: categories.interaction || 0,
        punctuality: categories.punctuality || 0,
        material_quality: categories.material_quality || 0
    };

    // ✅ DEBUG (check in browser console)
    console.log("Sending:", feedbackData);

    fetch('api/feedback.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify(feedbackData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);

            // reset form
            document.querySelector('.feedback-form').reset();

            ratingItems.forEach(item => {
                item.querySelectorAll('.rating-radio').forEach(r => r.classList.remove('checked'));
                delete item.dataset.rating;
            });
        } else {
            console.log("Error:", data.message);
        }
    })
    .catch(err => console.error("Error submitting feedback:", err));
});


// ============================
// RESET BUTTON
// ============================
document.querySelector('.feedback-reset')?.addEventListener('click', () => {
    ratingItems.forEach(item => {
        item.querySelectorAll('.rating-radio').forEach(r => r.classList.remove('checked'));
        delete item.dataset.rating;
    });
});