SET NAMES utf8mb4;
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) UNIQUE NOT NULL,
  email VARCHAR(190) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','builder','user') DEFAULT 'user',
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS dashboards (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(140) NOT NULL,
  slug VARCHAR(160) UNIQUE NOT NULL,
  description TEXT,
  is_public TINYINT(1) DEFAULT 0,
  created_by INT NOT NULL,
  main_button_label VARCHAR(80) DEFAULT 'Update Data',
  onboarding_json JSON NULL,
  settings_json JSON NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS dashboard_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dashboard_id INT NOT NULL,
  user_id INT NOT NULL,
  can_edit TINYINT(1) DEFAULT 0,
  can_trigger TINYINT(1) DEFAULT 1,
  UNIQUE KEY (dashboard_id, user_id),
  FOREIGN KEY (dashboard_id) REFERENCES dashboards(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS widgets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dashboard_id INT NOT NULL,
  type ENUM('app','data','text','image','chart','file') NOT NULL,
  title VARCHAR(160) NOT NULL,
  position_x INT DEFAULT 0,
  position_y INT DEFAULT 0,
  width INT DEFAULT 2,
  height INT DEFAULT 2,
  include_in_main TINYINT(1) DEFAULT 0,
  autorun_on_load TINYINT(1) DEFAULT 0,
  style_json JSON NULL,
  config_json JSON NULL,
  last_output_json JSON NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (dashboard_id) REFERENCES dashboards(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS jobs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  widget_id INT NOT NULL,
  user_id INT NULL,
  status ENUM('queued','running','completed','failed') DEFAULT 'queued',
  current_step VARCHAR(160) NULL,
  progress TINYINT NULL,
  result_json JSON NULL,
  error_text TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (widget_id) REFERENCES widgets(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS job_events (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  job_id BIGINT NOT NULL,
  step VARCHAR(160) NULL,
  progress TINYINT NULL,
  message TEXT NULL,
  payload_json JSON NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS api_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  label VARCHAR(160),
  token_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
