-- Database schema for Wedding Event Rundown Management System

CREATE DATABASE IF NOT EXISTS wedding_rundown;
USE wedding_rundown;

-- Table: admin_users
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: clients
CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    groom_name VARCHAR(100) NOT NULL,
    bride_name VARCHAR(100) NOT NULL,
    groom_photo VARCHAR(255),
    bride_photo VARCHAR(255),
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    event_location VARCHAR(255) NOT NULL,
    event_theme VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: rundown_segments
CREATE TABLE rundown_segments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    segment_name ENUM('Loading', 'Akad Nikah', 'Temu Keluarga', 'Resepsi') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    notes TEXT,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

-- Table: rundown_jobs
CREATE TABLE rundown_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    segment_id INT NOT NULL,
    job_description TEXT NOT NULL,
    assigned_to VARCHAR(100),
    FOREIGN KEY (segment_id) REFERENCES rundown_segments(id) ON DELETE CASCADE
);

-- Table: vendors
CREATE TABLE vendors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact VARCHAR(100),
    service_type VARCHAR(50),
    facilities TEXT
);

-- Table: team_members
CREATE TABLE team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(50),
    contact VARCHAR(100)
);

-- Table: family_coordinators
CREATE TABLE family_coordinators (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(100),
    phone VARCHAR(50)
);

-- Table: moodboard_images
CREATE TABLE moodboard_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    description TEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

-- Table: moodboard_comments
CREATE TABLE moodboard_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    moodboard_image_id INT NOT NULL,
    commenter VARCHAR(100),
    comment TEXT NOT NULL,
    commented_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (moodboard_image_id) REFERENCES moodboard_images(id) ON DELETE CASCADE
);

-- Table: music_list
CREATE TABLE music_list (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    artist VARCHAR(100),
    link VARCHAR(255),
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

-- Table: photo_sessions
CREATE TABLE photo_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    session_description VARCHAR(255),
    participants TEXT,
    order_number INT,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

-- Table: event_locations
CREATE TABLE event_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    google_maps_link VARCHAR(255)
);
