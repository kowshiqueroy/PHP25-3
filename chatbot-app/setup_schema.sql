-- Run in setup.php or directly in your MySQL client
-- Users
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  is_admin TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- Intents
CREATE TABLE intents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  tone_tag VARCHAR(20) NOT NULL DEFAULT 'default',
  default_response TEXT NOT NULL
) ENGINE=InnoDB;

-- Conversations
CREATE TABLE conversations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  thread_id VARCHAR(36) NOT NULL,
  user_id INT NOT NULL,
  message TEXT,
  response TEXT,
  intent_id INT,
  confidence_score FLOAT,
  emotion_tag VARCHAR(20),
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (intent_id) REFERENCES intents(id) ON DELETE SET NULL,
  INDEX(thread_id),
  INDEX(user_id),
  INDEX(created_at)
) ENGINE=InnoDB;

-- Feedbacks
CREATE TABLE feedbacks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  conversation_id INT NOT NULL,
  helpful TINYINT(1) NOT NULL,
  suggestion TEXT,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- Intent Patterns
CREATE TABLE intent_patterns (
  id INT AUTO_INCREMENT PRIMARY KEY,
  intent_id INT NOT NULL,
  pattern VARCHAR(255) NOT NULL,
  FOREIGN KEY (intent_id) REFERENCES intents(id) ON DELETE CASCADE,
  INDEX(intent_id)
) ENGINE=InnoDB;

-- Word Statistics (global)
CREATE TABLE word_stats (
  word VARCHAR(50) PRIMARY KEY,
  global_count INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- Intent-Word Statistics
CREATE TABLE intent_word_stats (
  intent_id INT NOT NULL,
  word VARCHAR(50) NOT NULL,
  count INT NOT NULL DEFAULT 0,
  PRIMARY KEY (intent_id, word),
  FOREIGN KEY (intent_id) REFERENCES intents(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Model Configuration (priors, last_trained)
CREATE TABLE model_config (
  `key` VARCHAR(50) PRIMARY KEY,
  `value` TEXT
) ENGINE=InnoDB;

-- Intent Responses (A/B testing)
CREATE TABLE intent_responses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  intent_id INT NOT NULL,
  template TEXT NOT NULL,
  usage_count INT NOT NULL DEFAULT 0,
  success_count INT NOT NULL DEFAULT 0,
  FOREIGN KEY (intent_id) REFERENCES intents(id) ON DELETE CASCADE
) ENGINE=InnoDB;