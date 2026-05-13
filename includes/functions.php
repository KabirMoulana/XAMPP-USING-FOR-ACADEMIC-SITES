<?php
// ============================================================
//  includes/functions.php
//  Helper functions used across the whole system
// ============================================================

// Start session (called at top of every page)
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in, redirect to login if not
function requireLogin() {
    startSession();
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../index.php");
        exit();
    }
}

// Check user has the correct role, redirect if not
function requireRole($allowed_role) {
    requireLogin();
    if ($_SESSION['role'] !== $allowed_role) {
        header("Location: ../index.php?error=unauthorized");
        exit();
    }
}

// Safely clean user input to prevent SQL injection (basic approach)
function clean($conn, $data) {
    return mysqli_real_escape_string($conn, trim($data));
}

// Show a styled alert box
function alert($message, $type = 'success') {
    $color = ($type === 'success') ? '#d1fae5' : '#fee2e2';
    $border = ($type === 'success') ? '#6ee7b7' : '#fca5a5';
    $text   = ($type === 'success') ? '#065f46' : '#991b1b';
    echo "<div style='background:$color;border:1px solid $border;color:$text;
               padding:12px 18px;border-radius:8px;margin:12px 0;font-family:sans-serif;font-size:14px;'>
               $message
          </div>";
}

// Format a date nicely
function niceDate($date) {
    return date('d M Y', strtotime($date));
}

// Get student's total points
function getTotalPoints($conn, $student_id) {
    $r = mysqli_query($conn, "SELECT SUM(points) as total FROM performance_points WHERE student_id=$student_id");
    $row = mysqli_fetch_assoc($r);
    return $row['total'] ?? 0;
}
?>
