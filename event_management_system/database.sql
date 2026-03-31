-- Database: event_db
-- Import this file in phpMyAdmin (event_db)

CREATE DATABASE IF NOT EXISTS event_db;
USE event_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Events table
CREATE TABLE IF NOT EXISTS events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  description TEXT NOT NULL,
  date DATE NOT NULL,
  location VARCHAR(200) NOT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bookings table
CREATE TABLE IF NOT EXISTS bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  event_id INT NOT NULL,
  payment_status ENUM('pending','paid') NOT NULL DEFAULT 'pending',
  booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  razorpay_order_id VARCHAR(100) NULL,
  razorpay_payment_id VARCHAR(100) NULL,
  razorpay_signature VARCHAR(255) NULL,
  CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_bookings_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
  INDEX idx_user (user_id),
  INDEX idx_event (event_id)
);

-- Seed a few sample events (optional)
INSERT INTO events (title, description, date, location, price) VALUES
('Tech Conference 2026', 'A full-day conference with talks, networking, and workshops.', '2026-04-25', 'Mumbai', 499.00),
('Music Night Live', 'Live performances by local bands and artists.', '2026-05-10', 'Pune', 299.00),
('Startup Pitch Day', 'Pitch your startup idea to mentors and investors.', '2026-06-02', 'Bengaluru', 199.00)
ON DUPLICATE KEY UPDATE title=title;

