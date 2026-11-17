CREATE DATABASE IF NOT EXISTS kids_party_planner
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE kids_party_planner;

-- TABLE 1: USERS
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('parent', 'admin') NOT NULL DEFAULT 'parent',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- TABLE 2: VENDORS
CREATE TABLE vendors (
    vendor_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    service_type VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    contact_email VARCHAR(150) NOT NULL
);

-- TABLE 3: PARTIES
CREATE TABLE parties (
    party_id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NOT NULL,
    child_name VARCHAR(100) NOT NULL,
    party_date DATE NOT NULL,
    theme VARCHAR(100) NOT NULL,
    budget DECIMAL(10,2) NOT NULL,

    CONSTRAINT fk_parties_parent
        FOREIGN KEY (parent_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- TABLE 4: BOOKINGS
CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    party_id INT NOT NULL,
    vendor_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('Pending', 'Confirmed', 'Cancelled')
        NOT NULL DEFAULT 'Pending',

    CONSTRAINT fk_bookings_party
        FOREIGN KEY (party_id)
        REFERENCES parties(party_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_bookings_vendor
        FOREIGN KEY (vendor_id)
        REFERENCES vendors(vendor_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

-- INDEXES
CREATE INDEX idx_parties_parent ON parties(parent_id);
CREATE INDEX idx_parties_date ON parties(party_date);
CREATE INDEX idx_bookings_party ON bookings(party_id);
CREATE INDEX idx_bookings_vendor ON bookings(vendor_id);