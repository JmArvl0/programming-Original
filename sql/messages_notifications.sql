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
);

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
);
