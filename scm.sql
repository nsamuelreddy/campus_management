-- 1. Setup and Cleanup
-- Disabling checks allows us to drop tables regardless of foreign key dependencies
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS Feedback, LostFound, Complaints, notices, Users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE DATABASE IF NOT EXISTS project;
USE project;

-- 2. Users Table
-- Added an index on 'role' as you'll often filter users by their permissions
CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Faculty','Student') DEFAULT 'Student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (role)
);

-- 3. Notices Table
CREATE TABLE notices (
    notice_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    author_id INT,
    expiry_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES Users(user_id) ON DELETE SET NULL,
    INDEX (expiry_date) 
);

-- 4. Complaints Table
CREATE TABLE Complaints (
    complaint_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50),
    subject VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('Pending', 'In Progress', 'Resolved') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    INDEX (status),
    INDEX (user_id)
);

-- 5. Feedback Table
CREATE TABLE Feedback (
    feedback_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    rating INT NOT NULL,
    comments TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_rating CHECK (rating BETWEEN 1 AND 5),
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE SET NULL
);

-- 6. Lost & Found Table
CREATE TABLE LostFound (
    item_id INT PRIMARY KEY AUTO_INCREMENT,
    item_type ENUM('Lost', 'Found') NOT NULL,
    item_name VARCHAR(100) NOT NULL,
    description TEXT,
    location VARCHAR(200),
    reporter_id INT,
    is_claimed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    INDEX (item_type),
    INDEX (is_claimed)
);


--7.settings
CREATE TABLE settings (
    id INT PRIMARY KEY,
    institutionName VARCHAR(100),
    adminEmail VARCHAR(100),
    emailNotifications BOOLEAN,
    smsAlerts BOOLEAN,
    weeklyReports BOOLEAN
);


--8.notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(255),
    message TEXT,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- 9. Seed Data (For Testing)
-- ==========================================


INSERT INTO Users (full_name, email, password_hash, role) VALUES 
('System Admin', 'admin1@project.com', 'hash', 'Admin'),
('Dr. Faculty', 'faculty@project.com', 'hash', 'Faculty'),
('Student One', 'student@project.com', 'hash', 'Student');

INSERT INTO notices (title, content, author_id, expiry_date) VALUES 
('Maintenance Alert', 'cleaning of drinking water tanks.', 1, '2026-03-05');

INSERT INTO Complaints (user_id, subject, description, status) VALUES 
(2, 'Leaking Pipe', 'drainage leakage near mess V.', 'Pending');

INSERT INTO Complaints (user_id, subject, description, status, created_at) VALUES
(2, 'Water Issue', 'No water in hostel', 'Resolved', '2026-01-10'),
(2, 'WiFi Issue', 'Slow internet', 'Pending', '2026-02-15'),
(2, 'Electricity', 'Power cut frequently', 'Resolved', '2026-03-05'),
(2, 'Mess Food', 'Food quality is poor', 'Pending', '2026-04-01');

INSERT INTO LostFound (item_type, item_name, location, reporter_id) VALUES 
('Found', 'Blue bottle', 'director office near the window', 2);

INSERT INTO settings VALUES 
(1, 'SmartCampus University', 'admin@smartcampus.edu', 1, 0, 1);

ALTER TABLE notices ADD category VARCHAR(50) DEFAULT 'general';


ALTER TABLE Feedback DROP COLUMN comments;


ALTER TABLE Feedback MODIFY rating DECIMAL(3,1);


ALTER TABLE Feedback 
ADD COLUMN teaching_clarity INT,
ADD COLUMN subject_knowledge INT,
ADD COLUMN interaction INT,
ADD COLUMN punctuality INT,
ADD COLUMN material_quality INT;


INSERT INTO Users (full_name, email, password_hash, role) VALUES
('Arjun Sharma', 'arjun@campus.edu', '123', 'Student'),
('Dr. Priya Mehta', 'priya@campus.edu', '123', 'Faculty'),
('Sneha R.', 'sneha@campus.edu', '123', 'Student'),
('Rohit K.', 'rohit@campus.edu', '123', 'Student'),
('Rajesh Kumar', 'admin@campus.edu', '123', 'Admin');


-- Verify results
SELECT 'Users Created' AS Status, COUNT(*) FROM Users;
SELECT * FROM Users;
select *from notices;
select *from Complaints;
select *from Feedback;
select *from LostFound;
select *from notifications;

