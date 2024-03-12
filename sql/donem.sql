-- Users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(255) NOT NULL,
    lastname VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(255) NOT NULL,
    address VARCHAR(255) NOT NULL,
    phoneVerified tinyint(1),
    otp VARCHAR(255),
    password VARCHAR(255),
    status ENUM('active', 'banned', 'blocked') NOT NULL DEFAULT 'active',
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
    UNIQUE KEY unique_email (email)
);

-- Shipments table
CREATE TABLE IF NOT EXISTS shipments (
    shipment_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tracking_id VARCHAR(20) NOT NULL UNIQUE,
    package_description TEXT,
    current_status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Payments table
CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    shipment_id INT NOT NULL UNIQUE,
    amount DECIMAL(10, 2) NOT NULL,
    payment_status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    payment_date TIMESTAMP,
    FOREIGN KEY (shipment_id) REFERENCES shipments(shipment_id) ON DELETE CASCADE
);

-- Admins table
CREATE TABLE IF NOT EXISTS admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_admin_username (username),
    UNIQUE KEY unique_admin_email (email)
);

-- Notifications table
CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Reports table
CREATE TABLE IF NOT EXISTS reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    report_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Settings table
CREATE TABLE IF NOT EXISTS settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    setting_name VARCHAR(255) NOT NULL,
    setting_value TEXT
);

-- Shipping settings table
CREATE TABLE IF NOT EXISTS shipping_settings (
    shipping_setting_id INT AUTO_INCREMENT PRIMARY KEY,
    shipping_option VARCHAR(255) NOT NULL,
    shipping_cost DECIMAL(10, 2) NOT NULL
);

CREATE TABLE IF NOT EXISTS oauth_access_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    access_token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Password Reset Tokens table
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- OAuth Personal Access Clients table
CREATE TABLE IF NOT EXISTS oauth_personal_access_clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- OAuth Refresh Tokens table
CREATE TABLE IF NOT EXISTS oauth_refresh_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    access_token_id INT NOT NULL,
    refresh_token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (access_token_id) REFERENCES oauth_access_tokens(id) ON DELETE CASCADE
);




-- -- Insert sample data into the 'users' table
-- INSERT INTO users (username, email, password, role) VALUES
--     ('john_doe', 'john@example.com', 'hashed_password', 'user'),
--     ('admin_user', 'admin@example.com', 'admin_hashed_password', 'admin'),
--     ('test_user', 'test@example.com', 'test_hashed_password', 'user');

-- Insert sample data into the 'shipments' table
INSERT INTO shipments (user_id, tracking_id, package_description, current_status) VALUES
    (1, 'TRK123', 'Electronics', 'In Transit'),
    (1, 'TRK456', 'Clothing', 'Delivered'),
    (2, 'TRK789', 'Books', 'Pending');

-- Insert sample data into the 'payments' table
INSERT INTO payments (shipment_id, amount, payment_status, payment_date) VALUES
    (1, 50.00, 'approved', '2022-03-03 12:00:00'),
    (2, 30.00, 'approved', '2022-03-02 10:30:00'),
    (3, 20.00, 'pending', NULL);

-- Insert sample data into the 'admins' table
INSERT INTO admins (username, email, password) VALUES
    ('admin1', 'admin1@example.com', 'admin1_hashed_password'),
    ('admin2', 'admin2@example.com', 'admin2_hashed_password');

-- Insert sample data into the 'notifications' table
INSERT INTO notifications (user_id, message) VALUES
    (1, 'Your shipment has been dispatched.'),
    (2, 'Payment for your order is pending.'),
    (3, 'New notification for test user.');

-- Insert sample data into the 'reports' table
INSERT INTO reports (user_id, report_text) VALUES
    (1, 'Issue with tracking ID TRK123.'),
    (2, 'Received damaged package.'),
    (3, 'Feedback for testing purposes.');

-- Insert sample data into the 'settings' table
INSERT INTO settings (setting_name, setting_value) VALUES
    ('site_name', 'LogisticsHub'),
    ('timezone', 'UTC');

-- Insert sample data into the 'shipping_settings' table
INSERT INTO shipping_settings (shipping_option, shipping_cost) VALUES
    ('Standard', 5.00),
    ('Express', 10.00);
