# SmartCampus - Backend Architecture & API Overview

## Architecture Overview
This project currently utilizes a **RESTful Mock API** built with PHP. To facilitate rapid frontend development and testing without a live database, all data is temporarily stored using PHP `$_SESSION` variables. 

This architecture ensures the frontend functions exactly as it will in production (sending asynchronous `fetch` requests and receiving JSON responses). To transition to production, the database team only needs to replace the `$_SESSION` array manipulations in the PHP files with standard SQL queries (e.g., `SELECT`, `INSERT`, `UPDATE`). No changes to the frontend JavaScript or HTML are required.

---

## API Endpoints

All endpoints are located within the `api/` directory.

### 1. Authentication (`api/auth.php`)
* **Purpose:** Manages user login states and role switching.
* **Important Note:** The logout logic intentionally uses `unset($_SESSION['user'])` rather than `session_destroy()`. This prevents the mock database (notices, items, complaints) from being wiped when switching between Admin, Faculty, and Student roles during testing.

### 2. Admin Settings (`api/settings.php`)
* **`GET`:** Returns the current platform settings (Institution Name, Admin Email, and toggle switch states) to populate the admin form.
* **`POST`:** Receives JSON payload from the admin form and updates the session data accordingly.

### 3. Notices Board (`api/notices.php`)
* **`GET`:** Fetches the array of all published notices for the Student dashboard, allowing frontend filtering by category (Academic, Events, Urgent).
* **`POST`:** Allows Faculty to submit a new notice, which is instantly unshifted to the top of the shared session array.

### 4. Lost & Found (`api/lost-found.php`)
* **`GET`:** Loads all reported lost and found items into the frontend grid.
* **`POST`:** Receives form data from the "Report Item" modal (Status, Item Name, Description, Location) and stores it with an automatically generated timestamp.

### 5. Faculty Feedback (`api/feedback.php`)
* **`POST`:** Captures complex form data submitted by students. This includes dropdown selections (Semester, Department, Faculty, Subject) and an array of individual 1-5 star ratings, saving the complete object to the session.

### 6. Dashboard Analytics (`api/dashboard.php`)
* **`GET`:** Acts as the processing engine for the admin dashboard visualizations. It performs the following calculations before returning data to the frontend:
  * Counts total active users.
  * Iterates through complaints to calculate "Resolved" vs. "Pending" issues.
  * Generates an array of percentages used to dynamically animate the "Complaint Trends" CSS bar chart.
  * Calculates exact 360-degree CSS conic-gradient values for the "Feedback Ratings" donut chart based on aggregated student star ratings.

---

## Handoff Notes for Database Integration
* **Data Format:** The frontend strictly expects responses in JSON format: `{"success": true, "data": [...]}`. 
* **Routing:** All frontend `fetch` calls are currently hardcoded to point to the `api/` directory. 
* **Next Steps:** Swap the `$SESSION` variables in each endpoint with your MySQL connection and queries. Ensure the JSON response structure remains identical to maintain frontend compatibility.