CREATE DATABASE IF NOT EXISTS beyond_the_map
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE beyond_the_map;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'ae', 'staff', 'customer') NOT NULL,
    email VARCHAR(150) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS staff_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    team ENUM('account_executive', 'passport', 'facilities', 'logistics', 'finance', 'admin') NOT NULL,
    phone VARCHAR(30) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_staff_profiles_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NULL,
    phone VARCHAR(30) NULL,
    destination VARCHAR(150) NULL,
    tier ENUM('new', 'silver', 'gold', 'vip') NOT NULL DEFAULT 'new',
    status ENUM('processing', 'pending', 'cancelled', 'finished') NOT NULL DEFAULT 'pending',
    payment_status ENUM('paid', 'unpaid', 'overdue', 'partially paid') NOT NULL DEFAULT 'unpaid',
    admission_status ENUM('admitted', 'pending') NOT NULL DEFAULT 'pending',
    progress TINYINT UNSIGNED NOT NULL DEFAULT 0,
    refund_flag TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_contacted_at DATETIME NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_customers_tier (tier),
    INDEX idx_customers_status (status),
    INDEX idx_customers_payment (payment_status),
    CONSTRAINT fk_customers_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS crm_interactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    ae_staff_id INT NULL,
    interaction_type ENUM('inquiry', 'follow_up', 'clarification', 'note', 'booking') NOT NULL,
    details TEXT NOT NULL,
    next_action_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_crm_interactions_customer (customer_id),
    CONSTRAINT fk_crm_interactions_customer
        FOREIGN KEY (customer_id) REFERENCES customers(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_crm_interactions_staff
        FOREIGN KEY (ae_staff_id) REFERENCES staff_profiles(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS passport_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    passport_number VARCHAR(50) NULL,
    country VARCHAR(120) NULL,
    documents_status ENUM('approved', 'submitted', 'missing', 'rejected', 'not started') NOT NULL DEFAULT 'not started',
    application_status ENUM('visa issued', 'approved', 'processing', 'under review', 'pending', 'action required', 'not started') NOT NULL DEFAULT 'not started',
    priority ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'low',
    submission_date DATE NULL,
    remarks TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_passport_customer (customer_id),
    INDEX idx_passport_status (application_status),
    CONSTRAINT fk_passport_customer
        FOREIGN KEY (customer_id) REFERENCES customers(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS passport_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    passport_application_id INT NOT NULL,
    document_type VARCHAR(120) NOT NULL,
    file_path VARCHAR(255) NULL,
    status ENUM('submitted', 'approved', 'missing', 'rejected') NOT NULL DEFAULT 'submitted',
    reviewed_by_staff_id INT NULL,
    reviewed_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_passport_documents_app (passport_application_id),
    CONSTRAINT fk_passport_documents_application
        FOREIGN KEY (passport_application_id) REFERENCES passport_applications(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_passport_documents_staff
        FOREIGN KEY (reviewed_by_staff_id) REFERENCES staff_profiles(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tour_name VARCHAR(180) NOT NULL,
    destination VARCHAR(150) NOT NULL,
    rate DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    capacity INT NOT NULL DEFAULT 0,
    departure_time TIME NULL,
    tour_date DATE NOT NULL,
    status ENUM('open', 'closed', 'full', 'upcoming', 'limited') NOT NULL DEFAULT 'open',
    duration_days INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tours_date (tour_date),
    INDEX idx_tours_destination (destination)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS guests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NULL,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NULL,
    phone VARCHAR(30) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_guests_customer
        FOREIGN KEY (customer_id) REFERENCES customers(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tour_id INT NOT NULL,
    guest_id INT NOT NULL,
    booking_date DATE NOT NULL,
    booking_status ENUM('confirmed', 'reserved', 'cancelled', 'completed') NOT NULL DEFAULT 'reserved',
    seat_number VARCHAR(30) NULL,
    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    payment_status ENUM('paid', 'partial', 'pending', 'overdue') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_bookings_date (booking_date),
    INDEX idx_bookings_status (booking_status),
    INDEX idx_bookings_tour (tour_id),
    INDEX idx_bookings_guest (guest_id),
    CONSTRAINT fk_bookings_tour
        FOREIGN KEY (tour_id) REFERENCES tours(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_bookings_guest
        FOREIGN KEY (guest_id) REFERENCES guests(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS facilities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    facility_name VARCHAR(150) NOT NULL,
    facility_type VARCHAR(120) NOT NULL,
    capacity INT NOT NULL DEFAULT 0,
    location VARCHAR(150) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_facilities_type (facility_type)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS facility_reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    booking_id INT NULL,
    facility_id INT NULL,
    booking_reference VARCHAR(50) NOT NULL,
    service_type VARCHAR(120) NOT NULL,
    reservation_date DATE NOT NULL,
    priority ENUM('low', 'normal', 'high') NOT NULL DEFAULT 'normal',
    status ENUM('requested', 'approved', 'assigned', 'in progress', 'completed', 'cancelled') NOT NULL DEFAULT 'requested',
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_facility_reservations_customer (customer_id),
    INDEX idx_facility_reservations_status (status),
    INDEX idx_facility_reservations_date (reservation_date),
    CONSTRAINT fk_facility_reservations_customer
        FOREIGN KEY (customer_id) REFERENCES customers(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_facility_reservations_booking
        FOREIGN KEY (booking_id) REFERENCES bookings(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_facility_reservations_facility
        FOREIGN KEY (facility_id) REFERENCES facilities(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS facility_coordination_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    facility_reservation_id INT NOT NULL,
    assigned_staff_id INT NULL,
    logistics_status ENUM('queued', 'dispatched', 'en route', 'arrived', 'completed') NOT NULL DEFAULT 'queued',
    completion_time DATETIME NULL,
    remarks TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_facility_coordination_reservation (facility_reservation_id),
    INDEX idx_facility_coordination_status (logistics_status),
    CONSTRAINT fk_facility_coordination_reservation
        FOREIGN KEY (facility_reservation_id) REFERENCES facility_reservations(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_facility_coordination_staff
        FOREIGN KEY (assigned_staff_id) REFERENCES staff_profiles(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    booking_id INT NULL,
    amount DECIMAL(12,2) NOT NULL,
    due_date DATE NULL,
    paid_at DATETIME NULL,
    status ENUM('paid', 'pending', 'partial', 'overdue', 'cancelled') NOT NULL DEFAULT 'pending',
    reference_no VARCHAR(80) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_payments_customer (customer_id),
    INDEX idx_payments_status (status),
    INDEX idx_payments_due (due_date),
    CONSTRAINT fk_payments_customer
        FOREIGN KEY (customer_id) REFERENCES customers(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_payments_booking
        FOREIGN KEY (booking_id) REFERENCES bookings(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payment_reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payment_id INT NOT NULL,
    reminder_type ENUM('email', 'sms', 'in_app') NOT NULL DEFAULT 'in_app',
    reminder_status ENUM('queued', 'sent', 'failed') NOT NULL DEFAULT 'queued',
    sent_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_payment_reminders_payment (payment_id),
    CONSTRAINT fk_payment_reminders_payment
        FOREIGN KEY (payment_id) REFERENCES payments(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_role ENUM('customer', 'ae', 'staff') NOT NULL,
    sender_id INT NOT NULL,
    receiver_role ENUM('customer', 'ae', 'staff') NOT NULL,
    receiver_id INT NOT NULL,
    module_origin VARCHAR(100) NOT NULL,
    message_text TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_messages_receiver (receiver_role, receiver_id, is_read),
    INDEX idx_messages_created (created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(100) NOT NULL,
    module_source VARCHAR(100) NOT NULL,
    related_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_notifications_read (is_read),
    INDEX idx_notifications_module (module_source),
    INDEX idx_notifications_created (created_at)
) ENGINE=InnoDB;