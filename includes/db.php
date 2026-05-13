<?php
// ============================================================
//  includes/db.php  — Database Connection
//  This file connects to MySQL. Include it in every page.
// ============================================================

$host     = "localhost";       // XAMPP default
$dbname   = "activate_academy";
$username = "root";            // XAMPP default username
$password = "";                // XAMPP default password (empty)

// Connect using mysqli
$conn = mysqli_connect($host, $username, $password, $dbname);

// Check if connection worked
if (!$conn) {
    die("<h3 style='color:red;font-family:sans-serif;padding:20px;'>
        ❌ Database connection failed: " . mysqli_connect_error() . "
        <br><br>Make sure XAMPP MySQL is running and the database 'activate_academy' exists.
    </h3>");
}

// Set character encoding to UTF-8
mysqli_set_charset($conn, "utf8");
?>
