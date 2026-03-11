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
    role ENUM('Admin', 'User') DEFAULT 'User',
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
    user_id INT,
    subject VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('Pending', 'In Progress', 'Resolved') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    INDEX (status)
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

-- ==========================================
-- 7. Seed Data (For Testing)
-- ==========================================

INSERT INTO Users (full_name, email, password_hash, role) VALUES 
('System Admin', 'admin1@project.com', '$2y$10$abcdefghijklmnopqrstuv', 'Admin'),
('Meghana', 'meghana@example.com', '$2y$10$xyz1234567890filledhash', 'User');

INSERT INTO notices (title, content, author_id, expiry_date) VALUES 
('Maintenance Alert', 'cleaning of drinking water tanks.', 1, '2026-03-05');

INSERT INTO Complaints (user_id, subject, description, status) VALUES 
(2, 'Leaking Pipe', 'drainage leakage near mess V.', 'Pending');

INSERT INTO LostFound (item_type, item_name, location, reporter_id) VALUES 
('Found', 'Blue bottle', 'director office near the window', 2);

-- Verify results
SELECT 'Users Created' AS Status, COUNT(*) FROM Users;
SELECT * FROM Users;
select *from notices;
select *from Complaints;
select *from Feedback;
select *from LostFound;