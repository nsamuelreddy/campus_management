// Feedback Page JavaScript

// Rating system
const ratingItems = document.querySelectorAll('.rating-item');

function getFeedbackSelectValues() {
    const selects = document.querySelectorAll('.feedback-selects .form-select');
    const semesterSelect = selects[0] || null;
    const subjectSelect = selects[1] || null;
    const facultySelect = selects[2] || null;

    const semester = semesterSelect ? (semesterSelect.selectedOptions[0]?.textContent?.trim() || semesterSelect.value || '') : '';
    const subject = subjectSelect ? (subjectSelect.selectedOptions[0]?.textContent?.trim() || subjectSelect.value || '') : '';
    const facultyName = facultySelect ? (facultySelect.selectedOptions[0]?.textContent?.trim() || facultySelect.getAttribute('data-selected-name') || '') : '';
    const facultyEmail = facultySelect ? (facultySelect.value || '') : '';

    return { semester, subject, facultyName, facultyEmail };
}

function loadFacultyOptions() {
    const selects = document.querySelectorAll('.feedback-selects .form-select');
    const facultySelect = selects[2] || null;
    if (!facultySelect) return;

    fetch('api/users.php')
        .then((response) => response.json())
        .then((data) => {
            if (!data.success || !Array.isArray(data.users)) return;

            const facultyUsers = data.users.filter((u) => String(u.role).toLowerCase() === 'faculty');
            if (!facultyUsers.length) return;

            facultySelect.innerHTML = '<option value="">Select faculty</option>';
            facultyUsers.forEach((faculty) => {
                const option = document.createElement('option');
                option.value = faculty.email;
                option.textContent = faculty.name;
                facultySelect.appendChild(option);
            });

            facultySelect.addEventListener('change', () => {
                const selectedOption = facultySelect.selectedOptions[0];
                if (selectedOption) {
                    facultySelect.setAttribute('data-selected-name', selectedOption.textContent.trim());
                }
            });
        })
        .catch((err) => console.error('Error loading faculty options:', err));
}

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

    const hasMissingRating = Object.values(ratings).some(value => value === 'Not rated');
    if (hasMissingRating) {
        alert('Please rate all criteria before submitting.');
        return;
    }
    
    const selects = getFeedbackSelectValues();

    if (!selects.facultyEmail) {
        alert('Please select a faculty before submitting.');
        return;
    }

    // Get select values
    const feedbackData = {
        semester: selects.semester,
        department: '',
        faculty: selects.facultyName,
        facultyEmail: selects.facultyEmail,
        subject: selects.subject,
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
        if (data.success) {
            alert(data.message);

            // Reset form visually
            document.querySelector('.feedback-form').reset();
            ratingItems.forEach(item => {
                item.querySelectorAll('.rating-radio').forEach(r => r.classList.remove('checked'));
                delete item.dataset.rating;
            });
            return;
        }

        alert(data.message || 'Unable to submit feedback.');
    })
    .catch(err => {
        console.error("Error submitting feedback:", err);
        alert('Unable to submit feedback due to network/server error.');
    });
});
// Reset button
document.querySelector('.feedback-reset')?.addEventListener('click', () => {
    ratingItems.forEach(item => {
        item.querySelectorAll('.rating-radio').forEach(r => r.classList.remove('checked'));
        delete item.dataset.rating;
    });
});

document.addEventListener('DOMContentLoaded', loadFacultyOptions);
