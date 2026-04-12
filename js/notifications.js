// Load notifications
function loadNotifications() {

    fetch('api/notifications.php')
        .then(res => res.json())
        .then(response => {

            const container = document.getElementById("notification-list");

            if (!response.success || response.data.length === 0) {
                container.innerHTML = "<p>No notifications found</p>";
                return;
            }

            container.innerHTML = response.data.map(n => `

                <div class="notification">

                    <div>
                        ${n.message}
                        ${n.is_read == 0 ? "<span class='badge'>(New)</span>" : ""}
                    </div>

                    <small>${n.created_at}</small>

                    <div style="margin-top:8px;">
                        <button class="read-btn" onclick="markRead(${n.id})">
                            Mark as Read
                        </button>

                        <button class="delete-btn" onclick="deleteNotification(${n.id})">
                            Delete
                        </button>
                    </div>

                </div>

            `).join('');
        })
        .catch(err => {
            console.error("Error loading notifications:", err);
        });
}

/* -------------------------
   MARK AS READ
------------------------- */
function markRead(id) {

    const formData = new FormData();
    formData.append("action", "mark_read");
    formData.append("id", id);

    fetch('api/notifications.php', {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(() => loadNotifications())
    .catch(err => console.error(err));
}

/* -------------------------
   DELETE NOTIFICATION
------------------------- */
function deleteNotification(id) {

    const formData = new FormData();
    formData.append("action", "delete");
    formData.append("id", id);

    fetch('api/notifications.php', {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(() => loadNotifications())
    .catch(err => console.error(err));
}

/* -------------------------
   INIT
------------------------- */
document.addEventListener("DOMContentLoaded", loadNotifications);