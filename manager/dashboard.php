<?php
// manager/dashboard.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_role('manager');

$name = $_SESSION['full_name'];

// Stats
$students  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM users WHERE role='student'"))['c'];
$lecturers = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM users WHERE role='lecturer'"))['c'];
$batches   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM batches"))['c'];
$revenue   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount) as t FROM payments WHERE status='approved'"))['t'] ?? 0;

$all_students = mysqli_query($conn,"SELECT u.full_name,u.email,s.grade FROM users u LEFT JOIN students s ON u.id=s.user_id WHERE u.role='student' ORDER BY u.full_name");
$all_lecturers= mysqli_query($conn,"SELECT full_name,email,phone FROM users WHERE role='lecturer'");
$all_batches  = mysqli_query($conn,"SELECT b.name,b.schedule,b.type,s.name as subject,u.full_name as lecturer FROM batches b JOIN subjects s ON b.subject_id=s.id JOIN users u ON b.lecturer_id=u.id");
$att_today    = mysqli_query($conn,"SELECT u.full_name,b.name as batch,a.status FROM attendance a JOIN users u ON a.student_id=u.id JOIN batches b ON a.batch_id=b.id WHERE a.date=CURDATE()");
$recent_pays  = mysqli_query($conn,"SELECT p.month,p.amount,p.status,u.full_name as student FROM payments p JOIN users u ON p.student_id=u.id ORDER BY p.paid_at DESC LIMIT 15");
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"/><title>Manager — Activate Academy</title>
<link rel="stylesheet" href="../css/style.css"/></head><body>
<div class="wrapper">
<div class="sidebar">
    <div class="sidebar-brand"><small>Manager</small>📊 Activate Academy</div>
    <nav>
        <a href="#overview"   class="active"><span class="icon">📊</span><span>Overview</span></a>
        <a href="#students">              <span class="icon">👨‍🎓</span><span>Students</span></a>
        <a href="#lecturers">             <span class="icon">👩‍🏫</span><span>Lecturers</span></a>
        <a href="#batches">               <span class="icon">📚</span><span>Batches</span></a>
        <a href="#attendance">            <span class="icon">📅</span><span>Today's Attendance</span></a>
        <a href="#payments">              <span class="icon">💳</span><span>Payments</span></a>
    </nav>
    <div class="sidebar-footer">Logged in as <strong><?= htmlspecialchars($name) ?></strong><br><a href="../logout.php">Logout</a></div>
</div>
<div class="main">
    <div class="topbar"><h1>Manager Dashboard</h1>
        <div class="topbar-user"><div class="avatar">M</div><?= htmlspecialchars($name) ?></div>
    </div>
    <div class="content">

        <div class="cards-row" id="overview">
            <div class="card"><div class="card-icon">👨‍🎓</div><div class="card-value"><?= $students ?></div><div class="card-label">Total Students</div></div>
            <div class="card"><div class="card-icon">👩‍🏫</div><div class="card-value"><?= $lecturers ?></div><div class="card-label">Lecturers</div></div>
            <div class="card"><div class="card-icon">📚</div><div class="card-value"><?= $batches ?></div><div class="card-label">Active Batches</div></div>
            <div class="card"><div class="card-icon">💰</div><div class="card-value">LKR <?= number_format($revenue) ?></div><div class="card-label">Total Revenue</div></div>
        </div>

        <div class="box" id="students">
            <div class="box-header"><h3>👨‍🎓 All Students</h3></div>
            <div class="box-body">
                <table><thead><tr><th>Name</th><th>Email</th><th>Grade</th></tr></thead><tbody>
                <?php while($s=mysqli_fetch_assoc($all_students)): ?>
                <tr><td><?= htmlspecialchars($s['full_name']) ?></td><td><?= htmlspecialchars($s['email']) ?></td><td><?= htmlspecialchars($s['grade']) ?></td></tr>
                <?php endwhile; ?>
                </tbody></table>
            </div>
        </div>

        <div class="box" id="lecturers">
            <div class="box-header"><h3>👩‍🏫 Lecturers</h3></div>
            <div class="box-body">
                <table><thead><tr><th>Name</th><th>Email</th><th>Phone</th></tr></thead><tbody>
                <?php while($l=mysqli_fetch_assoc($all_lecturers)): ?>
                <tr><td><?= htmlspecialchars($l['full_name']) ?></td><td><?= htmlspecialchars($l['email']) ?></td><td><?= htmlspecialchars($l['phone']) ?></td></tr>
                <?php endwhile; ?>
                </tbody></table>
            </div>
        </div>

        <div class="box" id="batches">
            <div class="box-header"><h3>📚 All Batches</h3></div>
            <div class="box-body">
                <table><thead><tr><th>Batch</th><th>Subject</th><th>Lecturer</th><th>Schedule</th><th>Type</th></tr></thead><tbody>
                <?php while($b=mysqli_fetch_assoc($all_batches)): ?>
                <tr><td><?= htmlspecialchars($b['name']) ?></td><td><?= htmlspecialchars($b['subject']) ?></td>
                    <td><?= htmlspecialchars($b['lecturer']) ?></td><td><?= htmlspecialchars($b['schedule']) ?></td>
                    <td><span class="badge badge-blue"><?= $b['type'] ?></span></td></tr>
                <?php endwhile; ?>
                </tbody></table>
            </div>
        </div>

        <div class="box" id="attendance">
            <div class="box-header"><h3>📅 Today's Attendance (<?= date('Y-m-d') ?>)</h3></div>
            <div class="box-body">
                <table><thead><tr><th>Student</th><th>Batch</th><th>Status</th></tr></thead><tbody>
                <?php while($a=mysqli_fetch_assoc($att_today)): ?>
                <tr><td><?= htmlspecialchars($a['full_name']) ?></td><td><?= htmlspecialchars($a['batch']) ?></td>
                    <td><span class="badge <?= $a['status']==='present'?'badge-green':($a['status']==='late'?'badge-yellow':'badge-red') ?>"><?= $a['status'] ?></span></td></tr>
                <?php endwhile; ?>
                </tbody></table>
            </div>
        </div>

        <div class="box" id="payments">
            <div class="box-header"><h3>💳 Recent Payments</h3></div>
            <div class="box-body">
                <table><thead><tr><th>Student</th><th>Month</th><th>Amount</th><th>Status</th></tr></thead><tbody>
                <?php while($p=mysqli_fetch_assoc($recent_pays)): ?>
                <tr><td><?= htmlspecialchars($p['student']) ?></td><td><?= htmlspecialchars($p['month']) ?></td>
                    <td>LKR <?= number_format($p['amount'],2) ?></td>
                    <td><span class="badge <?= $p['status']==='approved'?'badge-green':($p['status']==='rejected'?'badge-red':'badge-yellow') ?>"><?= $p['status'] ?></span></td></tr>
                <?php endwhile; ?>
                </tbody></table>
            </div>
        </div>

    </div>
</div>
</div>
</body></html>
