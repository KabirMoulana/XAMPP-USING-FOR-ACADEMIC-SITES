<?php
// director/dashboard.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_role('director');

$name = $_SESSION['full_name'];

$students  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM users WHERE role='student'"))['c'];
$lecturers = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM users WHERE role='lecturer'"))['c'];
$batches   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM batches"))['c'];
$revenue   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount) as t FROM payments WHERE status='approved'"))['t'] ?? 0;
$pending   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount) as t FROM payments WHERE status='pending'"))['t'] ?? 0;
$exams     = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM results"))['c'];

// Top performing students (by points)
$top_students = mysqli_query($conn,"SELECT u.full_name,SUM(p.points) as total FROM points p JOIN users u ON p.student_id=u.id GROUP BY p.student_id ORDER BY total DESC LIMIT 5");
// Recent financial summary by month
$financial = mysqli_query($conn,"SELECT month,SUM(amount) as total,COUNT(*) as count FROM payments WHERE status='approved' GROUP BY month ORDER BY paid_at DESC LIMIT 6");
// Batch summary
$batch_summary = mysqli_query($conn,"SELECT b.name,s.name as subject,u.full_name as lecturer,(SELECT COUNT(*) FROM enrollments WHERE batch_id=b.id) as students FROM batches b JOIN subjects s ON b.subject_id=s.id JOIN users u ON b.lecturer_id=u.id");
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"/><title>Director — Activate Academy</title>
<link rel="stylesheet" href="../css/style.css"/></head><body>
<div class="wrapper">
<div class="sidebar">
    <div class="sidebar-brand"><small>Director View</small>👔 Activate Academy</div>
    <nav>
        <a href="#overview"   class="active"><span class="icon">📊</span><span>Overview</span></a>
        <a href="#financial">             <span class="icon">💰</span><span>Financial Summary</span></a>
        <a href="#performance">           <span class="icon">🏆</span><span>Performance</span></a>
        <a href="#batches">               <span class="icon">📚</span><span>Batch Report</span></a>
    </nav>
    <div class="sidebar-footer">Logged in as <strong><?= htmlspecialchars($name) ?></strong><br><a href="../logout.php">Logout</a></div>
</div>
<div class="main">
    <div class="topbar"><h1>Director Dashboard</h1>
        <div class="topbar-user"><div class="avatar">D</div><?= htmlspecialchars($name) ?></div>
    </div>
    <div class="content">

        <!-- Key metrics -->
        <div class="cards-row" id="overview">
            <div class="card"><div class="card-icon">👨‍🎓</div><div class="card-value"><?= $students ?></div><div class="card-label">Total Students</div></div>
            <div class="card"><div class="card-icon">👩‍🏫</div><div class="card-value"><?= $lecturers ?></div><div class="card-label">Lecturers</div></div>
            <div class="card"><div class="card-icon">📚</div><div class="card-value"><?= $batches ?></div><div class="card-label">Batches</div></div>
            <div class="card"><div class="card-icon">💚</div><div class="card-value">LKR <?= number_format($revenue) ?></div><div class="card-label">Revenue Collected</div></div>
            <div class="card"><div class="card-icon">⏳</div><div class="card-value">LKR <?= number_format($pending) ?></div><div class="card-label">Pending Payments</div></div>
            <div class="card"><div class="card-icon">📝</div><div class="card-value"><?= $exams ?></div><div class="card-label">Exams Recorded</div></div>
        </div>

        <!-- Financial Summary -->
        <div class="box" id="financial">
            <div class="box-header"><h3>💰 Financial Summary by Month</h3></div>
            <div class="box-body">
                <table><thead><tr><th>Month</th><th>Payments Count</th><th>Total Collected</th></tr></thead><tbody>
                <?php while($f=mysqli_fetch_assoc($financial)): ?>
                <tr><td><?= htmlspecialchars($f['month']) ?></td><td><?= $f['count'] ?></td>
                    <td><strong>LKR <?= number_format($f['total'],2) ?></strong></td></tr>
                <?php endwhile; ?>
                </tbody></table>
            </div>
        </div>

        <!-- Top Students -->
        <div class="box" id="performance">
            <div class="box-header"><h3>🏆 Top Performing Students (by Points)</h3></div>
            <div class="box-body">
                <table><thead><tr><th>Rank</th><th>Student</th><th>Total Points</th></tr></thead><tbody>
                <?php $rank=1; while($s=mysqli_fetch_assoc($top_students)): ?>
                <tr><td><?= $rank++ ?></td><td><?= htmlspecialchars($s['full_name']) ?></td>
                    <td><strong style="color:#f5a623;"><?= $s['total'] ?> pts</strong></td></tr>
                <?php endwhile; ?>
                </tbody></table>
            </div>
        </div>

        <!-- Batch Report -->
        <div class="box" id="batches">
            <div class="box-header"><h3>📚 Batch Report</h3></div>
            <div class="box-body">
                <table><thead><tr><th>Batch</th><th>Subject</th><th>Lecturer</th><th>Enrolled Students</th></tr></thead><tbody>
                <?php while($b=mysqli_fetch_assoc($batch_summary)): ?>
                <tr><td><?= htmlspecialchars($b['name']) ?></td><td><?= htmlspecialchars($b['subject']) ?></td>
                    <td><?= htmlspecialchars($b['lecturer']) ?></td><td><span class="badge badge-blue"><?= $b['students'] ?></span></td></tr>
                <?php endwhile; ?>
                </tbody></table>
            </div>
        </div>

    </div>
</div>
</div>
</body></html>
