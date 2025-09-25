-- Consolidated schema (idempotent). No data inserts.

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255),
  email VARCHAR(255) UNIQUE,
  password_hash VARCHAR(255),
  role VARCHAR(50) DEFAULT 'staff',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notifications
CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type VARCHAR(50) NOT NULL,
  title VARCHAR(255) NOT NULL,
  message TEXT,
  link_url VARCHAR(255),
  icon VARCHAR(50) DEFAULT 'bell',
  target_roles JSON,
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  expires_at TIMESTAMP NULL,
  is_active TINYINT(1) DEFAULT 1,
  INDEX idx_active_notifications (is_active, created_at),
  INDEX idx_notification_type (type),
  INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  notification_id INT NOT NULL,
  is_read TINYINT(1) DEFAULT 0,
  read_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_user_notification (user_id, notification_id),
  INDEX idx_user_unread (user_id, is_read, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- KB articles minimal (guards for new columns)
CREATE TABLE IF NOT EXISTS kb_articles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(255) UNIQUE,
  title VARCHAR(255),
  category VARCHAR(100),
  content MEDIUMTEXT,
  status VARCHAR(50) DEFAULT 'published',
  allow_print TINYINT(1) DEFAULT 1,
  enable_sections TINYINT(1) DEFAULT 1,
  created_by INT NULL,
  updated_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Forms tables referenced in this repo (minimal)
CREATE TABLE IF NOT EXISTS bi_weekly_reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(255) NOT NULL,
  last_name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  work_location VARCHAR(100) NOT NULL,
  role VARCHAR(150) NOT NULL,
  period_start_date DATE NOT NULL,
  period_end_date DATE NOT NULL,
  date_range VARCHAR(255),
  educator_name VARCHAR(255),
  apprentice_name VARCHAR(255),
  understands_4rs TINYINT(1) DEFAULT 0,
  four_rs_notes TEXT,
  on_time_prepared_engaged TINYINT(1) DEFAULT 0,
  on_time_prepared_engaged_notes TEXT,
  needs_focus_on TEXT,
  units_completed_or_help VARCHAR(50),
  units_completed_or_help_notes TEXT,
  practicing_and_asking TINYINT(1) DEFAULT 0,
  practicing_and_asking_notes TEXT,
  would_work_at_location TINYINT(1) DEFAULT 0,
  would_work_at_location_notes TEXT,
  stage_success_rating TINYINT DEFAULT 0,
  retail_conversation_followup TINYINT(1) DEFAULT 0,
  retail_conversation_followup_notes TEXT,
  guest_feedback TEXT,
  finishing_rating TINYINT DEFAULT 0,
  finishing_helping_notes TEXT,
  loyalty_score TINYINT DEFAULT 0,
  long_term_commitment_score TINYINT DEFAULT 0,
  adaptability_score TINYINT DEFAULT 0,
  interpersonal_skills_score TINYINT DEFAULT 0,
  professional_growth_score TINYINT DEFAULT 0,
  remarks TEXT,
  additional_info TEXT,
  status VARCHAR(50) DEFAULT 'submitted',
  submitted_by INT NULL,
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
