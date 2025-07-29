CREATE DATABASE IF NOT EXISTS simple_ai;

USE simple_ai;

-- Table for storing NLU data
CREATE TABLE IF NOT EXISTS nlu_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    input_text TEXT,
    intent VARCHAR(255),
    entities TEXT,
    sentiment VARCHAR(50),
    confidence FLOAT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for storing user-specific memory
DROP TABLE IF EXISTS `user_memory`;
CREATE TABLE IF NOT EXISTS user_memory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    memory_key VARCHAR(255),
    memory_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `user_id_memory_key` (`user_id`,`memory_key`)
);

-- Table for storing decision paths or rules
CREATE TABLE IF NOT EXISTS decision_paths (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rule_name VARCHAR(255),
    conditions TEXT,
    action TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for storing training data from user feedback
CREATE TABLE IF NOT EXISTS training_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    input_text TEXT,
    corrected_intent VARCHAR(255),
    feedback TEXT,
    rating INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for story blocks and templates
CREATE TABLE IF NOT EXISTS story_blocks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    block_name VARCHAR(255),
    template TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table to store conversation history
CREATE TABLE IF NOT EXISTS conversation_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    user_input TEXT,
    bot_response TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);