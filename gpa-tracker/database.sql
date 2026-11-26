CREATE DATABASE IF NOT EXISTS gpa_tracker_db;
USE gpa_tracker_db;

CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(150) NOT NULL,
    credits DECIMAL(5,2) NOT NULL DEFAULT 3.00,
    grade VARCHAR(10) NOT NULL DEFAULT 'N/A'
);

-- Sample Demo Courses for Portfolio Preview
INSERT INTO courses (course_name, credits, grade) VALUES
('CS 101 Introduction to Computer Science', 3.00, '96.50'),
('ENG 102 Technical & Scientific Writing', 3.00, '91.20'),
('MAT 201 Applied Statistics', 3.00, '85.00'),
('HIST 110 Transfer General Education Credit', 2.67, 'TR'),
('WEB 210 Full-Stack Web Development', 3.00, '98.00'),
('DB 305 Relational Database Design', 3.00, 'N/A');
