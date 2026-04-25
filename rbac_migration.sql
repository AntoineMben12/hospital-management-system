-- ============================================================
-- rbac_migration.sql
-- Run this on your existing hospital_db to add RBAC support.
-- ============================================================

USE hospital;

-- ── Step 1: Expand the role ENUM on the users table ────────
ALTER TABLE users
    MODIFY COLUMN role ENUM('admin','receptionist','doctor') NOT NULL DEFAULT 'receptionist';

-- ── Step 2: Link doctors table to a user account ───────────
--   (NULL = doctor has no login yet; set after creating their user)
ALTER TABLE doctors
    ADD COLUMN user_id INT NULL DEFAULT NULL AFTER id,
    ADD CONSTRAINT fk_doctor_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;

-- ── Step 3: Ensure existing users stay as 'admin' ──────────
UPDATE users SET role = 'admin' WHERE username IN ('admin');
UPDATE users SET role = 'staff'  WHERE username = 'staff1';   -- will be removed below

-- Remove old generic 'staff' user if present (role no longer valid)
DELETE FROM users WHERE username = 'staff1';

-- ── Step 4: Insert sample role accounts ────────────────────
--   All passwords = "password"  (bcrypt via PHP password_hash())

-- Admin (already exists — just confirming role)
-- admin / password  →  role: admin

-- Receptionist account
INSERT INTO users (username, password, full_name, role) VALUES
('reception1',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'Mary Receptionist', 'receptionist')
ON DUPLICATE KEY UPDATE role = 'receptionist';

-- Doctor accounts (linked to existing doctors via user_id update below)
INSERT INTO users (username, password, full_name, role) VALUES
('dr.carter',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'Dr. Samuel Carter', 'doctor'),
('dr.chen',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'Dr. Maria Chen', 'doctor')
ON DUPLICATE KEY UPDATE role = 'doctor';

-- Link doctor users to their doctors table rows
UPDATE doctors SET user_id = (SELECT id FROM users WHERE username = 'dr.carter')
WHERE name = 'Dr. Samuel Carter';

UPDATE doctors SET user_id = (SELECT id FROM users WHERE username = 'dr.chen')
WHERE name = 'Dr. Maria Chen';

-- ── Summary of credentials ──────────────────────────────────
-- Role          | Username      | Password
-- --------------|---------------|----------
-- admin         | admin         | password
-- receptionist  | reception1    | password
-- doctor        | dr.carter     | password
-- doctor        | dr.chen       | password
