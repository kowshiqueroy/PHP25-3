--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `knowledge_base`
--

CREATE TABLE IF NOT EXISTS `knowledge_base` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `entity` VARCHAR(255) NOT NULL UNIQUE,
  `definition` TEXT NOT NULL,
  `source_user_id` INT(11) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`source_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `relationships`
--

CREATE TABLE IF NOT EXISTS `relationships` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `entity1_id` INT(11) NOT NULL,
  `relationship_type` VARCHAR(255) NOT NULL,
  `entity2_id` INT(11) NOT NULL,
  `source_user_id` INT(11) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`entity1_id`) REFERENCES `knowledge_base`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`entity2_id`) REFERENCES `knowledge_base`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`source_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `deferred_questions`
--

CREATE TABLE IF NOT EXISTS `deferred_questions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `question_text` TEXT NOT NULL,
  `related_entity` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('pending', 'resolved') DEFAULT 'pending',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `resolved_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
