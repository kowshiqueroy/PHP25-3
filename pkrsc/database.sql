CREATE DATABASE IF NOT EXISTS school_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE school_db;

-- Users Table (RBAC support)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'student') DEFAULT 'student',
    full_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Site Settings (For Dynamic Titles, Logo, Principal Speech)
CREATE TABLE site_settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT
);

-- Dynamic Menus
CREATE TABLE menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title_bn VARCHAR(100) NOT NULL, -- Bangla Name
    link VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT 1
);

-- Notices
CREATE TABLE notices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    publish_date DATE,
    file_path VARCHAR(255)
);

-- Insert Default Menus (Bangla)
INSERT INTO menus (title_bn, link, sort_order) VALUES 
('প্রচ্ছদ', 'index.php', 1),
('ভর্তি', 'admission.php', 2),
('একাডেমিক', 'academic.php', 3),
('শিক্ষকবৃন্দ', 'teachers.php', 4),
('গ্যালারি', 'gallery.php', 5),
('নোটিশ', 'notice.php', 6);

-- Insert Default Settings
INSERT INTO site_settings (setting_key, setting_value) VALUES 
('institute_name', 'সরকারি বিজ্ঞান কলেজ'),
('phone', '+8801700000000'),
('emis_code', '123456');