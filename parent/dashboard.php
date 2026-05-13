<?php
// parent/dashboard.php
// A parent's account is linked to a student via guardian name matching
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_role('parent');

$uid  = $_SESSION['user_id'];
$name = $_SESSION['full_name'];

// Find children: students whose guardian name matches this parent's full_name
$children = mysqli_query($conn,"
    SELECT u.id,u.full_name,u.email,s.grade,s.joined_date
    FROM students s JOIN users u ON s.user_id=u.id
    WHERE s.guardian='$name'
");
$children_arr=[];
while($c=mysqli_fetch_assoc($children)) $children_arr[]=$c;

// Get IDs of all children
$child_ids = implode(',', array_column($children_arr,'id') ?: [0]);
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"/><title>Parent Portal — Activate Academy</title>
<link rel="stylesheet" href="../css/style.css"/></head><body>
<div class="wrapper">
<div class="sidebar">
    <div class="sidebar-brand"><small>Parent Portal</small>👨‍👩‍👧 Activate Academy</div>
    <nav>
        <a href="#children"    class="active"><span class="icon">👧</span><span>My Children</span></a>
        <a href="#attendance">             <span class="icon">📅</span><span>Attendance</span></a>
        <a href="#results">                <span class="icon">📝</span><span>Exam Results</span></a>
        <a href="#points">                 <span class="icon">⭐</span><span>Performance Points</span></a>
        <a href="#payments">               <span class="icon">💳</span><span>Payments</span></a>
        <a href="#announcements">          <span class="icon">📢</span><span>Announcements</span></a>
    </nav>
    <div class="sidebar-footer">Logged in as <strong><?= htmlspecialchars($name) ?></strong><br><a href="../logout.php">Logout</a></div>
</div>
<div class="main">
    <div class="topbar"><h1>Parent Dashboard</h1>
        <div class="topbar-user"><div class="avatar"><?= strtoupper($name[0]) ?></div><?= htmlspecialchars($name) ?></div>
    </div>
    <div class="content">

        <?php if(empty($children_arr)): ?>
        <div class="alert alert-info">No children linked to your account yet. Please contact the reception to link your child's account.</div>
        <?php else: ?>

        <!-- My Children -->
        <div class="box" id="children">
            <div class="box-header"><h3>👧 My Children</h3></div>
            <div class="box-body">
                <table><thead><tr><th>Name</th><th>Grade</th><th>Email</th><th>Joined</th></tr></thead><tbody>
                <?php foreach($children_arr as $c): ?>
                <tr><td><?= htmlspecialchars($c['full_name']) ?></td><td><?= htmlspecialchars($c['grade']) ?></td>
                    <td><?= htmlspecialchars($c['email']) ?></td><td><?= $c['joined_date'] ?></td></tr>
                <?php endforeach; ?>
                </tbody></table>
            </div>
        </div>

        <!-- Attendance -->
        <div class="box" id="attendance">
            <div class="box-header"><h3>📅 Attendance</h3></div>
            <div class="box-body">
                <table><thead><tr><th>Child</th><th>Batch</th><th>Date</th><th>Status</th></tr></thead><tbody>
                <?php
                $att = mysqli_query($conn,"SELECT a.date,a.status,b.name as batch,u.full_name as child
                    FROM attendance a JOIN batches b ON a.batch_id=b.id JOIN users u ON a.student_id=u.id
                    WHERE a.student_id IN ($child_ids) ORDER BY a.date DESC LIMIT 20");
                while($a=mysqli_fetch_assoc($att)):
                ?>
                <tr><td><?= htmlspecialchars($a['child']) ?></td><td><?= htmlspecialchars($a['batch']) ?></td>
                    <td><?= $a['date'] ?></td>
                    <td><span class="badge <?= $a['status']==='present'?'badge-green':($a['status']==='late'?'badge-yellow':'badge-red') ?>"><?= $a['status'] ?></span></td></tr>
                <?php endwhile; ?>
                </tbody></table>
            </div>
        </div>

        <!-- Results -->
        <div class="box" id="results">
            <div class="box-header"><h3>📝 Exam Results</h3></div>
            <div class="box-body">
                <table><thead><tr><th>Child</th><th>Exam</th><th>Batch</th><th>Marks</th><th>%</th><th>Feedback</th></tr></thead><tbody>
                <?php
                $res = mysqli_query($conn,"SELECT r.*,u.full_name as child,b.name as batch
                    FROM results r JOIN users u ON r.student_id=u.id JOIN batches b ON r.batch_id=b.id
                    WHERE r.student_id IN ($child_ids) ORDER BY r.uploaded_at DESC");
                while($r=mysqli_fetch_assoc($res)):
                    $pct = $r['total_marks']>0?round(($r['marks']/$r['total_marks'])*100):0;
                ?>
                <tr><td><?= htmlspecialchars($r['child']) ?></td><td><?= htmlspecialchars($r['exam_name']) ?></td>
                    <td><?= htmlspecialchars($r['batch']) ?></td><td><?= $r['marks']."/".$r['total_marks'] ?></td>
                    <td><span class="badge <?= $pct>=75?'badge-green':($pct>=50?'badge-yellow':'badge-red') ?>"><?= $pct ?>%</span></td>
                    <td style="font-size:13px;"><?= htmlspecialchars($r['feedback']) ?></td></tr>
                <?php endwhile; ?>
                </tbody></table>
            </div>
        </div>

        <!-- Performance Points -->
        <div class="box" id="points">
            <div class="box-header"><h3>⭐ Performance Points</h3></div>
            <div class="box-body">
                <table><thead><tr><th>Child</th><th>Points</th><th>Reason</th><th>Date</th></tr></thead><tbody>
                <?php
                $pts = mysqli_query($conn,"SELECT p.*,u.full_name as child FROM points p JOIN users u ON p.student_id=u.id WHERE p.student_id IN ($child_ids) ORDER BY p.given_at DESC");
                while($p=mysqli_fetch_assoc($pts)):
                ?>
                <tr><td><?= htmlspecialchars($p['child']) ?></td><td><strong style="color:#f5a623;">+<?= $p['points'] ?></strong></td>
                    <td><?= htmlspecialchars($p['reason']) ?></td><td style="font-size:12px;"><?= $p['given_at'] ?></td></tr>
                <?php endwhile; ?>
                </tbody></table>
            </div>
        </div>

        <!-- Payments -->
        <div class="box" id="payments">
            <div class="box-header"><h3>💳 Payment Status</h3></div>
            <div class="box-body">
                <table><thead><tr><th>Child</th><th>Batch</th><th>Month</th><th>Amount</th><th>Status</th></tr></thead><tbody>
                <?php
                $pays = mysqli_query($conn,"SELECT p.*,u.full_name as child,b.name as batch
                    FROM payments p JOIN users u ON p.student_id=u.id JOIN batches b ON p.batch_id=b.id
                    WHERE p.student_id IN ($child_ids) ORDER BY p.paid_at DESC");
                while($p=mysqli_fetch_assoc($pays)):
                ?>
                <tr><td><?= htmlspecialchars($p['child']) ?></td><td><?= htmlspecialchars($p['batch']) ?></td>
                    <td><?= htmlspecialchars($p['month']) ?></td><td>LKR <?= number_format($p['amount'],2) ?></td>
                    <td><span class="badge <?= $p['status']==='approved'?'badge-green':($p['status']==='rejected'?'badge-red':'badge-yellow') ?>"><?= $p['status'] ?></span></td></tr>
                <?php endwhile; ?>
                </tbody></table>
            </div>
        </div>

        <!-- Announcements -->
        <div class="box" id="announcements">
            <div class="box-header"><h3>📢 Announcements</h3></div>
            <div class="box-body">
                <?php
                $ann = mysqli_query($conn,"SELECT title,content,posted_at FROM announcements ORDER BY posted_at DESC LIMIT 10");
                while($a=mysqli_fetch_assoc($ann)):
                ?>
                <div style="padding:12px 0;border-bottom:1px solid #f1f5f9;">
                    <strong style="color:#0d1b3e;font-size:14px;"><?= htmlspecialchars($a['title']) ?></strong>
                    <p style="color:#6b7280;font-size:13px;margin-top:4px;"><?= htmlspecialchars($a['content']) ?></p>
                    <small style="color:#94a3b8;"><?= $a['posted_at'] ?></small>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <?php endif; ?>
    </div>
</div>
</div>
</body></html>
