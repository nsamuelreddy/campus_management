CREATE DATABASE IF NOT EXISTS project;
USE project;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS LostFound;
DROP TABLE IF EXISTS Feedback;
DROP TABLE IF EXISTS Complaints;
DROP TABLE IF EXISTS notices;
DROP TABLE IF EXISTS Users;

SET FOREIGN_KEY_CHECKS = 1;



CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Faculty','Student') DEFAULT 'Student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (role)
);


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


CREATE TABLE Feedback (
    feedback_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    rating INT NOT NULL,
    comments TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE SET NULL
);


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



CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    institutionName VARCHAR(100),
    adminEmail VARCHAR(100),
    emailNotifications BOOLEAN,
    smsAlerts BOOLEAN,
    weeklyReports BOOLEAN
);



CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(255),
    message TEXT,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



INSERT INTO Users (full_name, email, password_hash, role) VALUES 
('System Admin', 'admin1@project.com', 'hash', 'Admin'),
('Dr. Faculty', 'faculty@project.com', 'hash', 'Faculty'),
('Student One', 'student@project.com', 'hash', 'Student');


ALTER TABLE notices ADD COLUMN IF NOT EXISTS category VARCHAR(50) DEFAULT 'general';

INSERT INTO notices (title, content, author_id, expiry_date) VALUES 
('Maintenance Alert', 'cleaning of drinking water tanks.', 1, '2026-03-05');

INSERT INTO Complaints (user_id, subject, description, status) VALUES 
(2, 'Leaking Pipe', 'drainage leakage near mess V.', 'Pending');

INSERT INTO LostFound (item_type, item_name, location, reporter_id) VALUES 
('Found', 'Blue bottle', 'director office near the window', 2);

INSERT INTO Complaints (user_id, subject, description, status) VALUES
(2, 'Water Issue', 'No water in hostel', 'Resolved'),
(2, 'WiFi Issue', 'Slow internet', 'Pending'),
(2, 'Electricity', 'Power cut frequently', 'Resolved'),
(2, 'Mess Food', 'Food quality is poor', 'Pending');

INSERT INTO settings VALUES 
(1, 'SmartCampus University', 'admin@smartcampus.edu', 1, 0, 1);


UPDATE Users SET password_hash='admin123' WHERE email='admin1@project.com';
UPDATE Users SET password_hash='faculty123' WHERE email='faculty@project.com';
UPDATE Users SET password_hash='student123' WHERE email='student@project.com';


SELECT 'Users Created' AS Status, COUNT(*) FROM Users;
SELECT * FROM Users;
select *from notices;
select *from Complaints;
select *from Feedback;
select *from LostFound;
select *from notifications;