<?php
// ============================================================
//  includes/auth.php  — Session & Authentication Helper
//  Include this at the top of every protected page.
//  Usage:  require_role('student');
// ============================================================

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and has the correct role
// If not, redirect to login page
function require_role($role) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== $role) {
        header("Location: /activate_academy/login.php");
        exit();
    }
}

// Allow multiple roles (e.g. admin OR manager)
function require_any_role($roles) {
    if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], $roles)) {
        header("Location: /activate_academy/login.php");
        exit();
    }
}

// Logout function — destroys the session
function logout() {
    session_destroy();
    header("Location: /activate_academy/login.php");
    exit();
}
?>
