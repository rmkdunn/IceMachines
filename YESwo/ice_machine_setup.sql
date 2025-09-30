-- Ice Machine Maintenance System Database Setup
-- Run this in phpMyAdmin or MySQL to create the required tables

USE YESwo;

-- Table for ice machines
CREATE TABLE IF NOT EXISTS ice_machines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    machine_name VARCHAR(100) NOT NULL,
    location VARCHAR(100) NOT NULL,
    model VARCHAR(100),
    serial_number VARCHAR(100),
    installation_date DATE,
    last_maintenance DATE,
    next_maintenance_due DATE,
    status ENUM('operational', 'needs_maintenance', 'out_of_order', 'scheduled_maintenance') DEFAULT 'operational',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for maintenance records
CREATE TABLE IF NOT EXISTS maintenance_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    machine_id INT NOT NULL,
    maintenance_date DATE NOT NULL,
    maintenance_type ENUM('routine', 'repair', 'cleaning', 'inspection', 'emergency') NOT NULL,
    performed_by VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    parts_used TEXT,
    cost DECIMAL(10,2),
    next_service_date DATE,
    status ENUM('completed', 'in_progress', 'scheduled') DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (machine_id) REFERENCES ice_machines(id) ON DELETE CASCADE
);

-- Insert some sample data
INSERT INTO ice_machines (machine_name, location, model, serial_number, installation_date, status) VALUES
('Ice Machine 01', 'Kitchen - Main Floor', 'Manitowoc IY-0454A', 'IC001234', '2023-01-15', 'operational'),
('Ice Machine 02', 'Bar Area', 'Hoshizaki KM-260BAH', 'IC001235', '2022-08-20', 'operational'),
('Ice Machine 03', 'Staff Break Room', 'Scotsman CU50GA', 'IC001236', '2023-03-10', 'needs_maintenance');

-- Insert some sample maintenance records
INSERT INTO maintenance_records (machine_id, maintenance_date, maintenance_type, performed_by, description, cost) VALUES
(1, '2024-09-01', 'routine', 'John Smith', 'Monthly cleaning and sanitization', 0.00),
(1, '2024-06-15', 'repair', 'Jane Doe', 'Replaced water filter and cleaned condenser coils', 45.50),
(2, '2024-09-05', 'cleaning', 'Mike Johnson', 'Deep clean and descaling', 0.00),
(3, '2024-08-20', 'inspection', 'John Smith', 'Found ice buildup in freezer section, needs repair', 0.00);