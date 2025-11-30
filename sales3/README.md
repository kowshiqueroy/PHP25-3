# Simple Sales Operations Web Application

This is a simplified, procedural PHP web application for managing core sales operations. It provides basic functionalities for authentication, user management, and placeholders for other sales-related tasks, all rendered directly from PHP scripts with a mobile-first, print-friendly UI.

## Features

*   **User Authentication:** Login/Logout with role-based access.
*   **Role-Based Access Control (RBAC):** Admin, Sales Representative, Manager, Viewer roles.
*   **User Management (Admin):** Add, Edit, Delete users within the Admin's company.
*   **Company Profile (Admin):** View and update basic company information.
*   **Logging:** Logs critical actions like login attempts, user CRUD, and company updates.
*   **Mobile-First UI:** Basic responsive design using CSS.
*   **Print-Friendly:** Basic print styles for content areas.
*   **Placeholders:** Views for Routes, Shops, Items, Invoices, Cash Collections, Approvals, Reports, and Logs are included as placeholders for future expansion.

## Requirements

*   **Web Server:** Apache or Nginx with PHP support.
*   **PHP:** Version 7.4 or higher, with the `pdo_mysql` extension enabled.
*   **MySQL Database Server:** Version 5.7 or higher.

## Setup Instructions

1.  **Place Application Files:**
    Copy all the files and folders from this project (e.g., `index.php`, `config.php`, `functions.php`, `style.css`, `script.js`, `views/`, `setup_db.php`) into your web server's document root (e.g., `htdocs` for XAMPP/WAMP, or a virtual host directory).

2.  **Configure Database Connection:**
    Open `config.php` and update the database credentials if they differ from the defaults.
    ```php
    define('DB_HOST', 'localhost');
    define('DB_PORT', '3306');
    define('DB_NAME', 'sales_app_db_simple'); // Make sure this database exists or will be created by you
    define('DB_USER', 'root');
    define('DB_PASS', ''); // Your MySQL root password, or a user with CREATE DATABASE/TABLE privileges
    ```

3.  **Create MySQL Database:**
    Manually create an empty MySQL database with the name specified in `DB_NAME` (e.g., `sales_app_db_simple`) using phpMyAdmin or your MySQL client.

4.  **Initialize Database Schema and Data:**
    Navigate to `setup_db.php` in your web browser or run it from the command line:
    ```bash
    # If running from command line (navigate to project root first)
    php setup_db.php
    ```
    ```
    # Or, in your browser:
    http://localhost/your_project_folder/setup_db.php
    ```
    This script will create all necessary tables, insert default roles, statuses, a sample company, and an initial Admin user. **After running, it is recommended to delete or rename `setup_db.php` from your web server for security reasons.**

5.  **Access the Application:**
    Open your web browser and navigate to the application's URL:
    ```
    http://localhost/your_project_folder/index.php
    ```
    You should be redirected to the login page.

## Default Admin Credentials

After running `setup_db.php`, you can log in with the following credentials:

*   **Username:** `admin`
*   **Password:** `password123`

## Usage

*   **Login:** Use the default admin credentials to access the dashboard.
*   **Navigation:** Use the header navigation to switch between different sections. Access to certain sections is restricted by user role.
*   **User Management:** As an Admin, you can navigate to the "Users" page to add, edit, or delete user accounts.
*   **Company Profile:** As an Admin, manage your company's basic information.
*   **Logging:** Critical actions will be logged to the `logs` table, viewable by Admin.

## Future Development

This application provides a basic framework. Here are some areas for future development:

*   **Complete CRUD for all entities:** Implement full create, read, update, and delete functionality for Routes, Shops, Items, Invoices, Cash Collections, etc.
*   **Implement detailed Invoice/Cash Collection workflows:** Add forms for creating/editing invoices with items, and managing approvals.
*   **Reports and Summaries:** Develop detailed reports for various sales metrics.
*   **Frontend Enhancements:** Add more dynamic client-side validation, better form handling, and potentially interactive data tables using JavaScript.
*   **Security Hardening:** Implement CSRF protection for all POST requests, improve error handling, and consider using prepared statements more consistently for dynamic updates (though `db_insert` and `db_update` already use prepared statements).
*   **Password Reset:** Add functionality for users to reset forgotten passwords.
*   **User Interface Refinements:** Enhance the UI/UX with more interactive elements and a more polished design.
*   **Error Logging:** Improve server-side error logging for debugging.

This README provides a comprehensive guide to set up and start using the simple PHP sales operations application.
