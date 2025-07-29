<?php

// Common utility functions will be added here.

function get_pending_qc_count($store_id) {
    $conn = connect_db();
    $stmt = $conn->prepare("SELECT COUNT(*) FROM transactions WHERE store_id = ? AND qc_status = 'Pending'");
    $stmt->bind_param("i", $store_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    $conn->close();
    return $count;
}

function get_low_stock_count($store_id) {
    $conn = connect_db();
    // This is a simplified example. A real low stock alert would need a defined reorder point per product.
    // For now, let's consider products with quantity < 10 as low stock.
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT product_id) FROM product_batches WHERE store_id = ? AND quantity < 10");
    $stmt->bind_param("i", $store_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    $conn->close();
    return $count;
}

function get_expired_batches_count($store_id) {
    $conn = connect_db();
    $current_date = date('Y-m-d');
    $stmt = $conn->prepare("SELECT COUNT(*) FROM product_batches WHERE store_id = ? AND expiry_date IS NOT NULL AND expiry_date <= ?");
    $stmt->bind_param("is", $store_id, $current_date);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    $conn->close();
    return $count;
}

function get_store_config($store_id) {
    $conn = connect_db();
    $stmt = $conn->prepare("SELECT config_json FROM stores WHERE id = ?");
    $stmt->bind_param("i", $store_id);
    $stmt->execute();
    $stmt->bind_result($config_json);
    $stmt->fetch();
    $stmt->close();
    $conn->close();

    if ($config_json) {
        return json_decode($config_json, true);
    } else {
        return []; // Return empty array if no config found
    }
}

function update_store_config($store_id, $config_array) {
    $conn = connect_db();
    $config_json = json_encode($config_array);
    $stmt = $conn->prepare("UPDATE stores SET config_json = ? WHERE id = ?");
    $stmt->bind_param("si", $config_json, $store_id);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

function log_audit_trail($user_id, $action, $entity_type = NULL, $entity_id = NULL, $old_value = NULL, $new_value = NULL) {
    $conn = connect_db();
    $stmt = $conn->prepare("INSERT INTO audit_trail (user_id, action, entity_type, entity_id, old_value, new_value) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $user_id, $action, $entity_type, $entity_id, $old_value, $new_value);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

?>