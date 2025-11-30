<?php
// utils/log_action.php

/**
 * Logs a specific action to the system_logs table.
 *
 * @param PDO $pdo The database connection object.
 * @param int|null $user_id The ID of the user performing the action.
 * @param string $action_type A string identifying the type of action (e.g., 'LOGIN', 'ADD_ITEM').
 * @param string $details A description of the action.
 * @return void
 */
function log_action(PDO $pdo, ?int $user_id, string $action_type, string $details): void {
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO system_logs (user_id, action_type, details) VALUES (?, ?, ?)"
        );
        $stmt->execute([$user_id, $action_type, $details]);
    } catch (PDOException $e) {
        // In a real-world application, you would have a more robust error handling
        // mechanism here, like writing to a fallback log file.
        error_log("Failed to log action: " . $e->getMessage());
    }
}
