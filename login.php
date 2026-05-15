<?php
// ============================================================
//  login.php  —  Login page for ALL roles
// ============================================================
session_start();
require_once 'includes/db.php';

$error = "";  // will hold error message if login fails

// ---- Handle the form when submitted ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get the username and password from the form
    $username = trim($_POST['username']);
    $password = md5($_POST['password']);   // hash the password with MD5

    // Search the database for this username + password
    $sql    = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) === 1) {
        // Login SUCCESS — save user info in session
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role']      = $user['role'];

        // Redirect to the correct dashboard based on role
        switch ($user['role']) {
            case 'student':      header("Location: student/dashboard.php");      break;
            case 'lecturer':     header("Location: lecturer/dashboard.php");     break;
            case 'admin':        header("Location: admin/dashboard.php");        break;
            case 'receptionist': header("Location: receptionist/dashboard.php"); break;
            case 'parent':       header("Location: parent/dashboard.php");       break;
            case 'manager':      header("Location: manager/dashboard.php");      break;
            case 'director':     header("Location: director/dashboard.php");     break;
        }
        exit();

    } else {
        $error = "❌ Invalid username or password. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Login — Activate Academy</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet"/>
    <style>
        :root{--navy:#0d1b3e;--blue:#1a56db;--gold:#f5a623;--light:#f4f7ff;}
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'DM Sans',sans-serif;background:var(--navy);min-height:100vh;display:flex;align-items:center;justify-content:center;}
        .login-wrap{display:flex;width:860px;min-height:520px;border-radius:16px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.4);}
        .login-left{background:linear-gradient(145deg,#1a3a6e,var(--navy));color:white;padding:50px 40px;flex:1;display:flex;flex-direction:column;justify-content:center;}
        .login-left h1{font-family:'Playfair Display',serif;font-size:32px;margin-bottom:12px;}
        .login-left p{color:#94a3b8;font-size:15px;line-height:1.7;margin-bottom:30px;}
        .role-list{list-style:none;}
        .role-list li{display:flex;align-items:center;gap:10px;padding:8px 0;color:#cbd5e1;font-size:14px;border-bottom:1px solid rgba(255,255,255,0.06);}
        .role-list li span{font-size:20px;}
        .login-right{background:white;padding:50px 40px;width:380px;display:flex;flex-direction:column;justify-content:center;}
        .login-right h2{font-size:24px;color:var(--navy);margin-bottom:6px;font-family:'Playfair Display',serif;}
        .login-right p{color:#6b7280;font-size:14px;margin-bottom:28px;}
        .form-group{margin-bottom:16px;}
        .form-group label{display:block;font-size:13px;font-weight:600;color:var(--navy);margin-bottom:6px;}
        .form-group input{width:100%;padding:12px 14px;border:1.5px solid #e2e8f0;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:14px;background:var(--light);transition:border-color 0.2s;}
        .form-group input:focus{outline:none;border-color:var(--blue);}
        .btn-login{width:100%;background:var(--navy);color:white;padding:13px;border:none;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:15px;font-weight:600;cursor:pointer;transition:background 0.2s;}
        .btn-login:hover{background:var(--blue);}
        .error{background:#fee2e2;color:#991b1b;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;border:1px solid #fca5a5;}
        .demo-info{background:#f0f9ff;border:1px solid #bae6fd;padding:12px 14px;border-radius:8px;font-size:12px;color:#0369a1;margin-top:16px;line-height:1.7;}
        .back-link{text-align:center;margin-top:20px;font-size:13px;color:#6b7280;}
        .back-link a{color:var(--blue);text-decoration:none;font-weight:600;}
        @media(max-width:700px){.login-wrap{flex-direction:column;width:95%;}.login-left{display:none;}.login-right{width:100%;}}
    </style>
</head>
<body>
<div class="login-wrap">

    <!-- Left side: branding -->
    <div class="login-left">
        <h1>🎓 Activate Academy</h1>
        <p>Your complete academy management system. Login to access your personalized portal.</p>
        <ul class="role-list">
            <li><span>👨‍🎓</span> Students — materials, marks, attendance</li>
            <li><span>👩‍🏫</span> Lecturers — classes, results, points</li>
            <li><span>🏢</span> Admin — full system control</li>
            <li><span>🧾</span> Receptionist — payments & registration</li>
            <li><span>👨‍👩‍👧</span> Parents — child progress & payments</li>
            <li><span>📊</span> Manager — operations & reports</li>
            <li><span>👔</span> Director — reports & performance</li>
        </ul>
    </div>

    <!-- Right side: login form -->
    <div class="login-right">
        <h2>Welcome Back</h2>
        <p>Enter your credentials to login</p>

        <!-- Show error if login failed -->
        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <!-- Login form -->
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Enter your username" required autofocus/>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required/>
            </div>
            <button type="submit" class="btn-login">Login →</button>
        </form>

        <!-- Demo credentials hint -->
        <div class="demo-info">
            <strong>Demo Credentials (all use password: password123)</strong><br>
            admin / manager / director / lec_math / lec_eng<br>
            receptionist / student1 / student2 / parent1
        </div>

        <div class="back-link">
            <a href="index.php">← Back to Academy Website</a>
        </div>
        </div>

</div>
</body>
</html>
