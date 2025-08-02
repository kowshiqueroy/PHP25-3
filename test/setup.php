<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "company_db";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select the database
$conn->select_db($dbname);

// SQL to create tables
$sql = ""

// Create departments table
. "CREATE TABLE IF NOT EXISTS `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `departments`

INSERT INTO `departments` (`id`, `name`) VALUES
(1, 'HR'),
(2, 'IT'),
(3, 'Sales');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`) VALUES
(1, 'Superadmin'),
(2, 'Admin'),
(3, 'Manager'),
(4, 'Staff'),
(5, 'Auditor');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `name`, `value`) VALUES
(1, 'branding', 'My Company'),
(2, 'system_preference', 'default'),
(3, 'company_name', 'My Company');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `department_id`, `role_id`) VALUES
(1, 'superadmin', '<?php echo password_hash('superadmin', PASSWORD_DEFAULT); ?>', NULL, 1),
(2, 'hr_admin', '<?php echo password_hash('password', PASSWORD_DEFAULT); ?>', 1, 2),
(3, 'it_admin', '<?php echo password_hash('password', PASSWORD_DEFAULT); ?>', 2, 2),
(4, 'sales_admin', '<?php echo password_hash('password', PASSWORD_DEFAULT); ?>', 3, 2),
(5, 'hr_manager', '<?php echo password_hash('password', PASSWORD_DEFAULT); ?>', 1, 3),
(6, 'it_manager', '<?php echo password_hash('password', PASSWORD_DEFAULT); ?>', 2, 3),
(7, 'sales_manager', '<?php echo password_hash('password', PASSWORD_DEFAULT); ?>', 3, 3),
(8, 'hr_staff', '<?php echo password_hash('password', PASSWORD_DEFAULT); ?>', 1, 4),
(9, 'it_staff', '<?php echo password_hash('password', PASSWORD_DEFAULT); ?>', 2, 4),
(10, 'sales_staff', '<?php echo password_hash('password', PASSWORD_DEFAULT); ?>', 3, 4),
(11, 'hr_auditor', '<?php echo password_hash('password', PASSWORD_DEFAULT); ?>', 1, 5),
(12, 'it_auditor', '<?php echo password_hash('password', PASSWORD_DEFAULT); ?>', 2, 5),
(13, 'sales_auditor', '<?php echo password_hash('password', PASSWORD_DEFAULT); ?>', 3, 5);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE IF NOT EXISTS `role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `module` varchar(255) NOT NULL,
  `can_view` tinyint(1) NOT NULL DEFAULT 0,
  `can_edit` tinyint(1) NOT NULL DEFAULT 0,
  `can_create` tinyint(1) NOT NULL DEFAULT 0,
  `can_delete` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `module`, `can_view`, `can_edit`, `can_create`, `can_delete`) VALUES
(1, 'departments', 1, 1, 1, 1),
(1, 'roles', 1, 1, 1, 1),
(1, 'users', 1, 1, 1, 1),
(2, 'users', 1, 1, 1, 0),
(3, 'users', 1, 0, 0, 0);

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

";

if ($conn->multi_query($sql)) {
    echo "Tables created and data inserted successfully";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>