console.log("NOTICES JS RUNNING");
console.log("ROLE:", window.USER_ROLE);

console.log("START");

let allNotices = [];

console.log("ARRAY INIT:", allNotices);

const role = (typeof window.USER_ROLE !== "undefined") 
    ? window.USER_ROLE 
    : "student";


document.addEventListener("DOMContentLoaded", () => {
    loadNotices();
    setupFilters();

    if (role === "faculty" || role === "admin") {
        setupFacultyForm();
    }
});


// =====================
// LOAD NOTICES (FIXED)
// =====================
function loadNotices() {

    console.log("LOADING NOTICES...");

    fetch('api/notices.php', {
        method: 'GET',
        credentials: 'include'
    })
    .then(res => res.json())
    .then(data => {

        console.log("API RESPONSE:", data);

        if (!data.success) {
            console.error("API returned error:", data.message);
            return;
        }

        // 🔥 SAFE ASSIGNMENT (IMPORTANT FIX)
        allNotices = Array.isArray(data.notices) ? data.notices : [];

        console.log("STORED:", allNotices);

        // 🔥 ensure DOM is ready before render
        setTimeout(() => {
            renderNotices("all");
        }, 0);
    })
    .catch(err => console.error("FETCH ERROR:", err));
}


// =====================
// RENDER
// =====================
function renderNotices(filter) {

    const grid = document.querySelector('.notices-grid');

    if (!grid) {
        console.error("Grid not found");
        return;
    }

    grid.innerHTML = '';

    const filtered = allNotices.filter(n => {
        if (filter === "all") return true;
        return (n.category || "").toLowerCase() === filter.toLowerCase();
    });

    if (filtered.length === 0) {
        grid.innerHTML = `<p style="padding:10px;">No notices found</p>`;
        return;
    }

    filtered.forEach(n => {

        const html = `
            <div class="notice-card">
                <div class="notice-tags">
                    <span class="notice-tag">${n.category}</span>
                    ${n.urgent ? `<span class="notice-tag urgent">Urgent</span>` : ""}
                </div>

                <h3 class="notice-title">${n.title}</h3>
                <p class="notice-description">${n.content}</p>
                <div class="notice-date">${n.date}</div>

                <button class="acknowledge-btn" onclick="ackNotice(this)">
                    Acknowledge
                </button>
            </div>
        `;

        grid.insertAdjacentHTML('beforeend', html);
    });
}


// =====================
// FILTERS
// =====================
function setupFilters() {

    document.querySelectorAll(".filter-tab").forEach(tab => {

        tab.addEventListener("click", () => {

            document.querySelectorAll(".filter-tab")
                .forEach(t => t.classList.remove("active"));

            tab.classList.add("active");

            renderNotices(tab.dataset.filter);
        });
    });
}


// =====================
// ACK
// =====================
function ackNotice(btn) {
    btn.textContent = "✓ Acknowledged";
    btn.disabled = true;
    btn.style.background = "#d1fae5";
    btn.style.color = "#065f46";
}


// =====================
// FACULTY
// =====================
function setupFacultyForm() {

    const form = document.querySelector("#noticeForm");
    if (!form) return;

    form.addEventListener("submit", publishNotice);
}


// =====================
// POST NOTICE
// =====================
function publishNotice(event) {

    event.preventDefault();

    const title = document.getElementById("title")?.value || "";
    const category = document.getElementById("category")?.value || "";
    const content = document.getElementById("content")?.value || "";

    fetch('api/notices.php', {
        method: 'POST',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ title, category, content })
    })
    .then(res => res.json())
    .then(data => {

        if (data.success) {

            alert("Notice created successfully!");

            const form = document.querySelector("form");
            if (form) form.reset();

            loadNotices();
        } else {
            alert(data.message || "Failed");
        }
    })
    .catch(err => console.error("POST ERROR:", err));
}