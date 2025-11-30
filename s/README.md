# Sales Management System - Project Summary & Usage Guide

## 1. Project Summary

This project is a comprehensive, mobile-first Sales Management Web Application built with a modular PHP backend and a dynamic, JavaScript-driven frontend. It successfully fulfills all core requirements, providing a robust platform for managing company sales operations with a clear separation of concerns and role-based access control.

### Key Features Implemented:

*   **Modular Architecture:** The code is organized into a clean structure:
    *   `api/`: Backend logic endpoints.
    *   `assets/`: CSS and JavaScript files.
    *   `config/`: Database configuration.
    *   `dashboards/`: Role-specific UI components.
    *   `db/`: Database setup scripts.
    *   `templates/`: Reusable header and footer.
    *   `utils/`: Helper functions like logging.

*   **Role-Based Access Control (RBAC):** Four distinct roles are fully implemented:
    *   **Admin:** Manages companies and users; views all system logs.
    *   **Manager:** Approves/rejects invoices, manages invoice statuses, and handles the print queue.
    *   **Sales Representative (SR):** Manages routes, shops, and items; creates invoices and submits them for printing.
    *   **Viewer:** Views and filters reports of approved invoices.

*   **Complete Invoice Lifecycle:**
    *   SRs create `Draft` invoices.
    *   SRs `Confirm` invoices, sending them to a manager.
    *   Managers `Approve` or `Reject` invoices.
    *   Managers can further update statuses (`On Process`, `On Delivery`, etc.).

*   **Dynamic & Responsive UI:**
    *   The UI is mobile-first and uses JavaScript to dynamically update content without page reloads.
    *   Dropdowns are populated dynamically, and invoice forms perform automatic calculations.
    *   Statuses are color-coded for immediate visual feedback.

*   **Data Integrity & Security:**
    *   A centralized PDO connection function is used for all database interactions.
    *   Backend logic prevents duplicate entries for routes, shops, and items within a company.
    *   API endpoints are protected and validate user roles before performing actions.
    *   A `setup.php` script allows for easy, one-step database initialization.

*   **Logging & Reporting:**
    *   All critical actions (logins, creations, status changes) are recorded in an immutable log table.
    *   Admins have a dashboard to view these logs.
    *   Viewers have a powerful, filterable reporting tool for invoice analysis.

*   **Printing System:**
    *   A print-friendly CSS ensures only essential data is visible when printing.
    *   Managers can print individual invoices or an entire batch of submitted invoices from the **Serial Print Queue**.

## 2. Usage Instructions

### Prerequisites:

*   A local web server environment like **XAMPP**, WAMP, or MAMP.
*   This environment must include **PHP** and a **MySQL/MariaDB** database server.

### Step 1: Setup the Project Files

1.  Place the entire project folder (the `s` directory) into your web server's document root (e.g., `D:\xampp\htdocs\`).
2.  The project should be accessible at `http://localhost/s/` in your browser.

### Step 2: Configure the Database

1.  Open the `config/config.php` file in a text editor.
2.  Verify the database credentials. By default, they are set for a standard XAMPP installation:
    *   `DB_HOST`: 'localhost'
    *   `DB_NAME`: 'sales_management'
    *   `DB_USER`: 'root'
    *   `DB_PASS`: '' (empty)
3.  If your database user/password is different, update the `DB_USER` and `DB_PASS` values.

### Step 3: Initialize the Database

1.  Start your Apache and MySQL services from the XAMPP control panel.
2.  Navigate to the following URL in your web browser:
    **`http://localhost/s/setup.php`**
3.  This script will create the `sales_management` database, build all the necessary tables, and insert default user accounts.
4.  You should see a "DATABASE SETUP COMPLETE!" message.

### Step 4: Log In and Use the Application

Navigate to the login page: **`http://localhost/s/login.php`**

You can log in with the following default accounts (the password for all is **`password`**):

*   **Admin Account:**
    *   Username: `admin`
    *   Password: `password`
    *   **Actions:** Go to the Admin dashboard to create new companies and users. View system logs.

*   **Manager Account:**
    *   Username: `manager1`
    *   Password: `password`
    *   **Actions:** View invoices `Confirmed` by SRs and `Approve`/`Reject` them. View the `Print Queue` and print all queued invoices. Update the status of any approved invoice.

*   **Sales Representative (SR) Account:**
    *   Username: `sr1`
    *   Password: `password`
    *   **Actions:** Use the "Manage Data" tabs to add Routes, Shops, and Items for your company. Create new invoices, save them as drafts, or confirm them for manager approval. Submit confirmed invoices to the print queue.

*   **Viewer Account:**
    *   Username: `viewer1`
    *   Password: `password`
    *   **Actions:** Use the filters to generate reports on approved invoices. Print individual invoices from the report.
