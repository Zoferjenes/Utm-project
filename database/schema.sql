CREATE DATABASE IF NOT EXISTS fixit_arcade
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE fixit_arcade;

DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS job_status_logs;
DROP TABLE IF EXISTS jobs;
DROP TABLE IF EXISTS provider_categories;
DROP TABLE IF EXISTS provider_profiles;
DROP TABLE IF EXISTS service_categories;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('customer', 'provider', 'admin') NOT NULL,
  phone VARCHAR(30),
  status ENUM('active', 'blocked') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE service_categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  description TEXT,
  icon VARCHAR(50) NOT NULL DEFAULT 'tool',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE provider_profiles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  bio TEXT NOT NULL,
  location VARCHAR(180) NOT NULL,
  latitude DECIMAL(10,7),
  longitude DECIMAL(10,7),
  service_radius_km DECIMAL(6,2) NOT NULL DEFAULT 10.00,
  base_rate DECIMAL(10,2) NOT NULL DEFAULT 0,
  photo_url VARCHAR(255),
  is_verified TINYINT(1) NOT NULL DEFAULT 0,
  kyc_doc_url VARCHAR(255),
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_provider_geo (latitude, longitude),
  CONSTRAINT fk_provider_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE provider_categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  provider_id INT NOT NULL,
  category_id INT NOT NULL,
  UNIQUE KEY uq_provider_category (provider_id, category_id),
  CONSTRAINT fk_pc_provider
    FOREIGN KEY (provider_id) REFERENCES provider_profiles(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_pc_category
    FOREIGN KEY (category_id) REFERENCES service_categories(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE jobs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  provider_id INT NOT NULL,
  category_id INT NOT NULL,
  status ENUM('requested', 'accepted', 'rejected', 'in_progress', 'completed', 'reviewed') NOT NULL DEFAULT 'requested',
  scheduled_at DATETIME NOT NULL,
  address TEXT NOT NULL,
  description TEXT NOT NULL,
  total DECIMAL(10,2) NOT NULL DEFAULT 0,
  final_cost DECIMAL(10,2),
  final_cost_confirmed TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_job_customer
    FOREIGN KEY (customer_id) REFERENCES users(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_job_provider
    FOREIGN KEY (provider_id) REFERENCES provider_profiles(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_job_category
    FOREIGN KEY (category_id) REFERENCES service_categories(id)
    ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE job_status_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  job_id INT NOT NULL,
  status VARCHAR(40) NOT NULL,
  changed_by INT NOT NULL,
  changed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_log_job
    FOREIGN KEY (job_id) REFERENCES jobs(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_log_user
    FOREIGN KEY (changed_by) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  job_id INT NOT NULL,
  sender_id INT NOT NULL,
  body TEXT NOT NULL,
  sent_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_message_job
    FOREIGN KEY (job_id) REFERENCES jobs(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_message_sender
    FOREIGN KEY (sender_id) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  job_id INT NOT NULL UNIQUE,
  rating INT NOT NULL,
  comment TEXT,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT chk_rating CHECK (rating BETWEEN 1 AND 5),
  CONSTRAINT fk_review_job
    FOREIGN KEY (job_id) REFERENCES jobs(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;
