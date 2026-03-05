CREATE DATABASE IF NOT EXISTS ared_facility;
USE ared_facility;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'worker') DEFAULT 'worker',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE experiments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    risk_level ENUM('Normal', 'Sensitive', 'High Risk') DEFAULT 'Normal',
    status ENUM('Active', 'Paused', 'Completed') DEFAULT 'Active',
    lead_researcher VARCHAR(100),
    progress INT DEFAULT 0,
    classified_content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    experiment_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (experiment_id) REFERENCES experiments(id) ON DELETE CASCADE
);

CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    experiment_id INT,
    user_id INT,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (experiment_id) REFERENCES experiments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Default Credentials:
-- Admin: admin / ARED_Admin_2026!
-- Worker: j_doe / Password123
INSERT INTO users (username, password, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('j_doe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'worker');

INSERT INTO experiments (name, description, risk_level, status, lead_researcher, progress, classified_content) VALUES
('Project Chimera', 'Genetic sequencing of unknown organic tissue.', 'High Risk', 'Active', 'Dr. Aris Thorne', 45, 'FLAG{REDACTED_BIOWEAPON_DATA}'),
('Ion Drive Alpha', 'Stability testing of low-orbit propulsion.', 'Normal', 'Completed', 'Sarah Vance', 100, 'Thrust efficiency peaked at 98%.');

INSERT INTO assignments (user_id, experiment_id) VALUES (2, 2);