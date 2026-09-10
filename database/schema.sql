-- ============================================================
-- Central University Business School Association Portal
-- Database Schema (MySQL)
-- ============================================================

CREATE DATABASE IF NOT EXISTS cu_business_school
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE cu_business_school;

-- ------------------------------------------------------------
-- 1. DEPARTMENTS (reference table)
-- ------------------------------------------------------------
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(5) NOT NULL UNIQUE   -- e.g. ACC, BKF, HRM, MGT, MKT
) ENGINE=InnoDB;

INSERT INTO departments (name, code) VALUES
('Accounting', 'ACC'),
('Banking and Finance', 'BKF'),
('Human Resource Management', 'HRM'),
('Management Studies', 'MGT'),
('Marketing', 'MKT');

-- ------------------------------------------------------------
-- 2. ELIGIBLE STUDENTS (pre-loaded allow-list maintained by the
--    school administration). A student can only sign up if a
--    matching student_id + email pair already exists here.
-- ------------------------------------------------------------
CREATE TABLE eligible_students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    department_code VARCHAR(5) NOT NULL,
    is_registered TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (department_code) REFERENCES departments(code)
) ENGINE=InnoDB;

-- Sample seed data for testing the prototype.
-- Student IDs are prefixed with the department initials as required.
INSERT INTO eligible_students (student_id, email, department_code) VALUES
('ACC10012024', 'ama.owusu@example.com', 'ACC'),
('BKF10022024', 'kojo.mensah@example.com', 'BKF'),
('HRM10032024', 'efua.boateng@example.com', 'HRM'),
('MGT10042024', 'yaw.asante@example.com', 'MGT'),
('MKT10052024', 'abena.darko@example.com', 'MKT');

-- ------------------------------------------------------------
-- 3. STUDENTS (registered user accounts)
-- ------------------------------------------------------------
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL UNIQUE,
    department_code VARCHAR(5) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email_verified TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_code) REFERENCES departments(code),
    FOREIGN KEY (student_id) REFERENCES eligible_students(student_id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 4. OTP / VERIFICATION CODES (sent to email during signup)
-- ------------------------------------------------------------
CREATE TABLE otp_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    code VARCHAR(6) NOT NULL,
    purpose VARCHAR(20) NOT NULL DEFAULT 'signup',
    is_used TINYINT(1) NOT NULL DEFAULT 0,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 5. ORDERS (cloth purchases and dues payments)
-- ------------------------------------------------------------
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL,
    department_code VARCHAR(5) NOT NULL,
    order_type ENUM('cloth','dues') NOT NULL,
    yards DECIMAL(6,2) DEFAULT NULL,        -- only for cloth orders
    unit_price DECIMAL(10,2) NOT NULL,      -- 50 for cloth (per yard), 65 for dues
    amount DECIMAL(10,2) NOT NULL,          -- total amount charged
    paystack_reference VARCHAR(100) DEFAULT NULL,
    status ENUM('pending','paid','failed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id)
) ENGINE=InnoDB;
