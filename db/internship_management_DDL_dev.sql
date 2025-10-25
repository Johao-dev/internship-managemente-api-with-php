DROP DATABASE IF EXISTS internship_management_dev;
CREATE DATABASE IF NOT EXISTS internship_management_dev 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_general_ci;

USE internship_management_dev;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    institutional_email VARCHAR(255) UNIQUE NOT NULL,
    role ENUM('INTERN', 'SUPERVISOR', 'ADMIN') NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP(6) DEFAULT CURRENT_TIMESTAMP(6),
    updated_at TIMESTAMP(6) DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
    active TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE supervisors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    area VARCHAR(255) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE interns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    university VARCHAR(128) NOT NULL,
    career VARCHAR(128) NOT NULL,
    internship_start_date DATE NOT NULL,
    internship_end_date DATE DEFAULT NULL,
    supervisor_id INT DEFAULT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP(6) DEFAULT CURRENT_TIMESTAMP(6),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (supervisor_id) REFERENCES supervisors(id) ON DELETE SET NULL
);

CREATE TABLE meetings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    start_datetime TIMESTAMP NOT NULL,
    estimated_duration INT DEFAULT NULL,
    type ENUM('presential', 'virtual') NOT NULL,
    organizer_id INT NULL,
    created_at TIMESTAMP(6) DEFAULT CURRENT_TIMESTAMP(6),
    FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE meeting_attendees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id INT NOT NULL,
    user_id INT NOT NULL,
    attended TINYINT(1) NOT NULL DEFAULT 0,
    comments TEXT,
    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    remitent_id INT NULL,
    recipient_type ENUM('intern', 'supervisor', 'all') NOT NULL,
    send_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    FOREIGN KEY (remitent_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE message_recipients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message_id INT NOT NULL,
    user_id INT NOT NULL,
    readed TINYINT(1) NOT NULL DEFAULT 0,
    read_date DATETIME DEFAULT NULL,
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE activity_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    intern_id INT NOT NULL,
    supervisor_id INT DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    send_date TIMESTAMP NOT NULL,
    revision_date TIMESTAMP NULL DEFAULT NULL,
    revision_state ENUM('Pending','Reviewed','revision_required') NOT NULL DEFAULT 'Pending',
    supervisor_comment TEXT,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    updated_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
    FOREIGN KEY (supervisor_id) REFERENCES supervisors(id) ON DELETE SET NULL,
    FOREIGN KEY (intern_id) REFERENCES interns(id) ON DELETE CASCADE
);

CREATE TABLE documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_name VARCHAR(255) NOT NULL,
    path VARCHAR(500) NOT NULL,
    document_type ENUM('CV','certificate','report','other') NOT NULL,
    description TEXT,
    up_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    up_by_id INT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    FOREIGN KEY (up_by_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE intern_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_id INT NOT NULL,
    intern_id INT NOT NULL,
    relation_type VARCHAR(100) NOT NULL,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (intern_id) REFERENCES interns(id) ON DELETE CASCADE
);