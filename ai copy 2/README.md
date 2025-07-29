# Simple AI - A Terminal-Based Learning Chatbot

## Description

Simple AI is a self-hosted, terminal-based learning chatbot built in pure PHP/MySQL. It's designed for educational, creative, and computational tasks.

## Setup

1.  **Database Setup:**
    *   Make sure you have a MySQL server running.
    *   Create a database named `simple_ai`.
    *   Import the `install.sql` file to create the necessary tables. You can do this using a tool like phpMyAdmin or from the command line:
        ```bash
        mysql -u your_username -p simple_ai < install.sql
        ```

2.  **Configuration:**
    *   Open `config.php`.
    *   Update the `DB_USER` and `DB_PASS` constants with your MySQL username and password.

3.  **Running the Chatbot:**
    *   Open your terminal or command prompt.
    *   Navigate to the project directory.
    *   Run the chatbot using the following command:
        ```bash
        php chatbot.php
        ```

## How to Use

*   Simply type your message and press Enter.
*   To end the conversation, type `exit`.
