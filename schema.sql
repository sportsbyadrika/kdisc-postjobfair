CREATE DATABASE IF NOT EXISTS kdisc_postjobfair;
USE kdisc_postjobfair;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  role ENUM('administrator', 'crm_member') NOT NULL,
  mobile_number VARCHAR(20) NOT NULL UNIQUE,
  email VARCHAR(150) DEFAULT NULL,
  address TEXT,
  password_hash VARCHAR(255) NOT NULL,
  active_status TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  modified_by INT DEFAULT NULL,
  CONSTRAINT fk_users_modified_by FOREIGN KEY (modified_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS activities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  module_name ENUM('project', 'crm', 'report') NOT NULL,
  title VARCHAR(200) NOT NULL,
  details TEXT,
  status ENUM('open', 'in_progress', 'closed') NOT NULL DEFAULT 'open',
  owner_user_id INT NOT NULL,
  active_status TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  modified_by INT DEFAULT NULL,
  CONSTRAINT fk_activities_owner FOREIGN KEY (owner_user_id) REFERENCES users(id),
  CONSTRAINT fk_activities_modified_by FOREIGN KEY (modified_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS login_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  login_at DATETIME NOT NULL,
  logout_at DATETIME DEFAULT NULL,
  ip_address VARCHAR(45) NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  modified_by INT DEFAULT NULL,
  CONSTRAINT fk_login_logs_user FOREIGN KEY (user_id) REFERENCES users(id),
  CONSTRAINT fk_login_logs_modified_by FOREIGN KEY (modified_by) REFERENCES users(id) ON DELETE SET NULL
);

INSERT INTO users (name, role, mobile_number, email, address, password_hash, active_status, created_at, updated_at, modified_by)
SELECT 'System Admin', 'administrator', '9999999999', 'admin@example.com', 'HQ',
       '$2y$12$CZ/rfChWhCBmj34P91HWXOe6wQwsd1NzqpRjQUBu8MgRKbNP07heC', 1, NOW(), NOW(), NULL
WHERE NOT EXISTS (SELECT 1 FROM users WHERE mobile_number = '9999999999');
