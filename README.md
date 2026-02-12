# SmartCampus - Frontend Application

A modern campus management system frontend built with HTML, CSS, and JavaScript.

## 🎓 Features

- **Login System** - Role-based login (Student/Faculty/Admin)
- **Dashboard** - Overview of notices, complaints, and statistics
- **Notices** - View and acknowledge campus announcements with filtering
- **Complaints** - Submit and track service requests
- **Feedback** - Faculty feedback form with rating system
- **Lost & Found** - Report and find lost items on campus

## 📁 Project Structure

```
campus/
├── index.html          # Login page
├── dashboard.html      # Main dashboard
├── notices.html        # Notices page
├── complaints.html     # Complaints page
├── feedback.html       # Faculty feedback form
├── lost-found.html     # Lost & Found page
├── css/
│   └── styles.css      # All styles
└── js/
    ├── main.js         # Common functionality & auth
    ├── dashboard.js    # Dashboard interactions
    ├── notices.js      # Notices filtering
    ├── complaints.js   # Complaint form handling
    ├── feedback.js     # Feedback rating system
    └── lost-found.js   # Lost & Found search
```

## 🚀 Getting Started

1. Open `index.html` in a web browser
2. Use any email and password to login (demo mode)
3. Select your role: Student, Faculty, or Admin
4. Click "Sign In" to access the dashboard

## 💡 Features Overview

### Login Page
- Clean, modern design with gradient background
- Role selection (Student/Faculty/Admin)
- Form validation
- LocalStorage-based session management (frontend only)

### Dashboard
- Statistics cards showing key metrics
- Recent notices preview
- Complaint status overview
- Quick navigation to all sections

### Notices
- Filter by category (Academic, Hostel, Emergency, Events, Admin)
- Acknowledge notices
- Urgent/Normal priority indicators
- Responsive card layout

### Complaints
- Submit new complaints with type selection
- File attachment support (drag & drop)
- View complaint history with status badges
- Form validation

### Feedback
- Faculty rating system (1-5 scale)
- Multiple criteria evaluation:
  - Teaching Clarity
  - Subject Knowledge
  - Interaction with Students
  - Punctuality
  - Course Material Quality
- Semester/Subject/Faculty selection
- Submit and Reset functionality

### Lost & Found
- Search functionality
- Lost/Found status badges
- Item cards with descriptions
- Location and date information
- Contact buttons

## 🎨 Design

The design matches the provided SmartCampus mockups with:
- Modern, clean interface
- Blue color scheme (#3b82f6 primary)
- Card-based layouts
- Responsive design
- Intuitive navigation
- Status badges and indicators

## ⚠️ Important Notes

**Frontend Only**: This is a frontend-only implementation
- No backend server or database
- Uses LocalStorage for session management
- Form submissions show alerts (no data persistence)
- All interactions are client-side only

## 🔧 Technical Details

- **HTML5** - Semantic markup
- **CSS3** - Modern styling with flexbox/grid
- **Vanilla JavaScript** - No frameworks required
- **Responsive** - Mobile-friendly design
- **No Dependencies** - Pure HTML/CSS/JS

## 📱 Browser Support

Works on all modern browsers:
- Chrome/Edge (recommended)
- Firefox
- Safari
- Opera

## 🧪 Testing

To test the application:
1. Open `index.html` in a browser
2. Login with any credentials
3. Navigate through all pages
4. Test form submissions
5. Try filtering and search features

## 📝 Workflow

1. **Login** → Select role and credentials
2. **Dashboard** → View overview
3. **Notices** → Read and acknowledge announcements
4. **Complaints** → Submit issues
5. **Feedback** → Rate faculty
6. **Lost & Found** → Report/Find items

## 🎯 Key Interactions

- **Search**: Top navigation search bar (all pages)
- **Notifications**: Bell icon in top navigation
- **User Profile**: Displays logged-in user info
- **Navigation**: Sidebar menu with active page indicator
- **Forms**: Validation and feedback messages
- **Filters**: Dynamic content filtering (Notices)
- **File Upload**: Drag & drop and click to upload

---

**Note**: This is a frontend demonstration. Backend implementation with database, API endpoints, authentication, and data persistence would be required for production use.
