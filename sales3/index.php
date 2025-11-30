<?php
// index.php - Main application entry point and router

require_once 'config.php';
require_once 'functions.php';

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $currentUser = get_current_user();
    if ($currentUser) {
        log_action($currentUser['user_id'], $currentUser['company_id'], 'LOGOUT', 'user', $currentUser['user_id'], null, null, 'User logged out.');
    }
    logout_user();
    redirect('index.php?page=login');
}

// Handle Login POST request
if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = login_user($username, $password);
    if ($user) {
        log_action($user['user_id'], $user['company_id'], 'LOGIN_SUCCESS', 'user', $user['user_id'], null, ['username' => $username], 'User logged in successfully.');
        redirect('index.php?page=dashboard');
    } else {
        log_action(null, null, 'LOGIN_FAILED', 'user', null, ['username' => $username], null, 'Failed login attempt for username: ' . $username, $_SERVER['REMOTE_ADDR']);
        $login_error = "Invalid username or password.";
    }
}

// Redirect to login page if not authenticated
if (!is_logged_in() && (!isset($_GET['page']) || $_GET['page'] !== 'login')) {
    redirect('index.php?page=login');
}

$current_user = get_current_user();
$page = $_GET['page'] ?? 'dashboard';

// --- Routing and Page Rendering ---
switch ($page) {
    case 'login':
        // If already logged in, redirect to dashboard
        if (is_logged_in()) {
            redirect('index.php?page=dashboard');
        }
        render_view('login', ['login_error' => $login_error ?? null]);
        break;

    case 'dashboard':
        // Access control for dashboard is implied by is_logged_in()
        render_view('dashboard', ['current_user' => $current_user]);
        break;

    // --- Admin-specific pages ---
    case 'users':
        if (!has_role(ROLE_ADMIN)) {
            redirect('index.php?page=dashboard&error=access_denied');
        }
        $users = get_all_users($current_user['company_id']);
        render_view('users', ['current_user' => $current_user, 'users' => $users]);
        break;
    
    case 'company_profile':
        if (!has_role(ROLE_ADMIN)) {
            redirect('index.php?page=dashboard&error=access_denied');
        }
        $companyData = get_company_by_id($current_user['company_id']);
        // Handle POST for company update
        if (isset($_POST['update_company'])) {
            $update_data = [
                'name' => $_POST['name'] ?? '',
                'address' => $_POST['address'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'email' => $_POST['email'] ?? ''
            ];
            $oldCompanyData = get_company_by_id($current_user['company_id']);
            db_update('companies', $update_data, 'company_id', $current_user['company_id']);
            log_action($current_user['user_id'], $current_user['company_id'], 'COMPANY_UPDATED', 'company', $current_user['company_id'], $oldCompanyData, $update_data, 'Company data updated.');
            redirect('index.php?page=company_profile&message=updated');
        }
        render_view('company_profile', ['current_user' => $current_user, 'company_data' => $companyData]);
        break;

    // --- Placeholder for other pages ---
    case 'routes':
    case 'shops':
    case 'items':
    case 'invoices':
    case 'cash_collections':
    case 'approvals':
    case 'reports':
    case 'logs': // Admin-only logs page
        // Basic access control, more granular checks needed within views
        if ($page === 'logs' && !has_role(ROLE_ADMIN)) {
             redirect('index.php?page=dashboard&error=access_denied');
        }
        if ($page === 'approvals' && !has_role(ROLE_MANAGER)) {
             redirect('index.php?page=dashboard&error=access_denied');
        }
        render_view($page, ['current_user' => $current_user]);
        break;


    default:
        render_view('404');
        break;
}
