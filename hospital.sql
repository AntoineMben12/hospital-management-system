-- ============================================================
-- Hospital Management System - Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS hospital_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hospital_db;

-- ============================================================
-- Table: users (Admin authentication)
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin','staff') DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Table: patients
-- ============================================================
CREATE TABLE IF NOT EXISTS patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    gender ENUM('Male','Female','Other') NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Table: doctors
-- ============================================================
CREATE TABLE IF NOT EXISTS doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    specialty VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Table: appointments
-- ============================================================
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('Scheduled','Completed','Cancelled') DEFAULT 'Scheduled',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    UNIQUE KEY no_double_booking (doctor_id, appointment_date, appointment_time)
) ENGINE=InnoDB;

-- ============================================================
-- Table: medical_records
-- ============================================================
CREATE TABLE IF NOT EXISTS medical_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    diagnosis VARCHAR(255) NOT NULL,
    treatment TEXT NOT NULL,
    record_date DATE NOT NULL,
    doctor_id INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- Sample Data
-- ============================================================

-- Default admin user: admin / admin123
INSERT INTO users (username, password, full_name, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin'),
('staff1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Nurse', 'staff');

-- Sample Doctors
INSERT INTO doctors (name, specialty, phone, email) VALUES
('Dr. Samuel Carter', 'Cardiology', '+1-555-0101', 'samuel.carter@hospital.com'),
('Dr. Maria Chen', 'Neurology', '+1-555-0102', 'maria.chen@hospital.com'),
('Dr. James Okonkwo', 'Orthopedics', '+1-555-0103', 'james.okonkwo@hospital.com'),
('Dr. Aisha Patel', 'Pediatrics', '+1-555-0104', 'aisha.patel@hospital.com'),
('Dr. Robert Hughes', 'General Surgery', '+1-555-0105', 'robert.hughes@hospital.com');

-- Sample Patients
INSERT INTO patients (name, age, gender, phone, address) VALUES
('Alice Johnson', 34, 'Female', '+1-555-1001', '123 Maple Street, Springfield'),
('Bob Martinez', 52, 'Male', '+1-555-1002', '456 Oak Avenue, Shelbyville'),
('Carol Williams', 28, 'Female', '+1-555-1003', '789 Pine Road, Capital City'),
('David Brown', 67, 'Male', '+1-555-1004', '321 Elm Drive, Ogdenville'),
('Emma Davis', 41, 'Female', '+1-555-1005', '654 Birch Lane, North Haverbrook'),
('Frank Wilson', 15, 'Male', '+1-555-1006', '987 Cedar Court, Brockway');

-- Sample Appointments
INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status, notes) VALUES
(1, 1, CURDATE(), '09:00:00', 'Scheduled', 'Routine cardiac checkup'),
(2, 2, CURDATE(), '10:30:00', 'Scheduled', 'Follow-up for migraines'),
(3, 4, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '11:00:00', 'Scheduled', 'Annual pediatric exam'),
(4, 3, DATE_SUB(CURDATE(), INTERVAL 2 DAY), '14:00:00', 'Completed', 'Knee pain consultation'),
(5, 5, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '15:30:00', 'Completed', 'Pre-operative assessment'),
(6, 4, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '09:30:00', 'Scheduled', 'Vaccination');

-- Sample Medical Records
INSERT INTO medical_records (patient_id, doctor_id, diagnosis, treatment, record_date, notes) VALUES
(4, 3, 'Osteoarthritis - Right Knee', 'Prescribed NSAIDs, physiotherapy twice a week', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Patient reports significant improvement'),
(5, 5, 'Appendicitis', 'Laparoscopic appendectomy performed successfully', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Post-op recovery normal'),
(1, 1, 'Hypertension Stage 1', 'Amlodipine 5mg daily, low-sodium diet', DATE_SUB(CURDATE(), INTERVAL 30 DAY), 'Monitor BP weekly'),
(2, 2, 'Chronic Migraine', 'Sumatriptan as needed, preventive topiramate', DATE_SUB(CURDATE(), INTERVAL 15 DAY), 'Triggered by stress and sleep deprivation');
