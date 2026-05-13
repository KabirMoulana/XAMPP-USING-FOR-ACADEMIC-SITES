<?php
// student/dashboard.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_role('student');

$uid  = $_SESSION['user_id'];
$name = $_SESSION['full_name'];

// Count enrolled batches
$r1 = mysqli_query($conn,"SELECT COUNT(*) as c FROM enrollments WHERE student_id=$uid");
$enrolled = mysqli_fetch_assoc($r1)['c'];

// Count present days this month
$r2 = mysqli_query($conn,"SELECT COUNT(*) as c FROM attendance WHERE student_id=$uid AND status='present' AND MONTH(date)=MONTH(CURDATE())");
$present = mysqli_fetch_assoc($r2)['c'];

// Total points
$r3 = mysqli_query($conn,"SELECT SUM(points) as total FROM points WHERE student_id=$uid");
$total_points = mysqli_fetch_assoc($r3)['total'] ?? 0;

// My enrolled batches with subject info
$batches = mysqli_query($conn,"
    SELECT b.name, b.schedule, b.type, s.name as subject, u.full_name as lecturer
    FROM enrollments e
    JOIN batches b ON e.batch_id=b.id
    JOIN subjects s ON b.subject_id=s.id
    JOIN users u ON b.lecturer_id=u.id
    WHERE e.student_id=$uid
");

// My latest results
$results = mysqli_query($conn,"
    SELECT r.exam_name, r.marks, r.total_marks, r.feedback, r.uploaded_at, b.name as batch
    FROM results r JOIN batches b ON r.batch_id=b.id
    WHERE r.student_id=$uid ORDER BY r.uploaded_at DESC LIMIT 5
");

// Attendance list
$att = mysqli_query($conn,"
    SELECT a.date, a.status, b.name as batch
    FROM attendance a JOIN batches b ON a.batch_id=b.id
    WHERE a.student_id=$uid ORDER BY a.date DESC LIMIT 10
");

// Payment history
$pays = mysqli_query($conn,"
    SELECT p.month, p.amount, p.status, b.name as batch
    FROM payments p JOIN batches b ON p.batch_id=b.id
    WHERE p.student_id=$uid ORDER BY p.paid_at DESC
");

// Materials
$mats = mysqli_query($conn,"
    SELECT m.title, m.type, m.file_path, m.class_link, m.uploaded_at, b.name as batch
    FROM materials m
    JOIN batches b ON m.batch_id=b.id
    JOIN enrollments e ON e.batch_id=b.id
    WHERE e.student_id=$uid ORDER BY m.uploaded_at DESC LIMIT 10
");

// Announcements
$ann = mysqli_query($conn,"SELECT title,content,posted_at FROM announcements ORDER BY posted_at DESC LIMIT 5");

// Leaderboard
$board = mysqli_query($conn,"
    SELECT u.full_name, SUM(p.points) as total
    FROM points p JOIN users u ON p.student_id=u.id
    GROUP BY p.student_id ORDER BY total DESC LIMIT 10
");

// Handle payment receipt upload
$msg = "";
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_FILES['receipt'])) {
    $batch_id = intval($_POST['batch_id']);
    $month    = mysqli_real_escape_string($conn,$_POST['month']);
    $amount   = floatval($_POST['amount']);
    $file     = $_FILES['receipt'];
    $ext      = pathinfo($file['name'],PATHINFO_EXTENSION);
    $filename = "receipt_".$uid."_".time().".".$ext;
    $dest     = "../uploads/receipts/".$filename;
    if(move_uploaded_file($file['tmp_name'],$dest)){
        mysqli_query($conn,"INSERT INTO payments (student_id,batch_id,amount,month,receipt_image,status,recorded_by) VALUES ($uid,$batch_id,$amount,'$month','$filename','pending',$uid)");
        $msg = "Receipt uploaded! Waiting for admin approval.";
    } else {
        $msg = "Upload failed. Try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"/><title>Student Portal — Activate Academy</title>
<link rel="stylesheet" href="../css/style.css"/>
</head><body>
<div class="wrapper">

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-brand"><small>Student Portal</small>🎓 Activate Academy</div>
    <nav>
        <a href="#batches"     class="active"><span class="icon">📚</span> <span>My Batches</span></a>
        <a href="#materials">          <span class="icon">📁</span> <span>Materials</span></a>
        <a href="#results">            <span class="icon">📝</span> <span>Exam Results</span></a>
        <a href="#attendance">         <span class="icon">📅</span> <span>Attendance</span></a>
        <a href="#payments">           <span class="icon">💳</span> <span>Payments</span></a>
        <a href="#leaderboard">        <span class="icon">🏆</span> <span>Leaderboard</span></a>
        <a href="#announcements">      <span class="icon">📢</span> <span>Announcements</span></a>
    </nav>
    <div class="sidebar-footer">Logged in as <strong><?= htmlspecialchars($name) ?></strong><br><a href="../logout.php">Logout</a></div>
</div>

<!-- MAIN -->
<div class="main">
    <div class="topbar">
        <h1>Student Dashboard</h1>
        <div class="topbar-user">
            <div class="avatar"><?= strtoupper($name[0]) ?></div>
            <?= htmlspecialchars($name) ?>
        </div>
    </div>
    <div class="content">

        <?php if($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

        <!-- Summary cards -->
        <div class="cards-row">
            <div class="card"><div class="card-icon">📚</div><div class="card-value"><?= $enrolled ?></div><div class="card-label">Enrolled Batches</div></div>
            <div class="card"><div class="card-icon">✅</div><div class="card-value"><?= $present ?></div><div class="card-label">Days Present (This Month)</div></div>
            <div class="card"><div class="card-icon">⭐</div><div class="card-value"><?= $total_points ?></div><div class="card-label">Total Points</div></div>
        </div>

        <!-- My Batches -->
        <div class="box" id="batches">
            <div class="box-header"><h3>📚 My Enrolled Batches</h3></div>
            <div class="box-body">
                <table><thead><tr><th>Batch</th><th>Subject</th><th>Lecturer</th><th>Schedule</th><th>Type</th></tr></thead><tbody>
                <?php while($b=mysqli_fetch_assoc($batches)): ?>
                <tr>
                    <td><?= htmlspecialchars($b['name']) ?></td>
                    <td><?= htmlspecialchars($b['subject']) ?></td>
                    <td><?= htmlspecialchars($b['lecturer']) ?></td>
                    <td><?= htmlspecialchars($b['schedule']) ?></td>
                    <td><span class="badge badge-blue"><?= $b['type'] ?></span></td>
                </tr>
                <?php endwhile; ?>
                </tbody></table>
            </div>
        </div>

        <!-- Materials -->
        <div class="box" id="materials">
            <div class="box-header"><h3>📁 Study Materials & Class Links</h3></div>
            <div class="box-body">
                <table><thead><tr><th>Title</th><th>Batch</th><th>Type</th><th>Action</th></tr></thead><tbody>
                <?php while($m=mysqli_fetch_assoc($mats)): ?>
                <tr>
                    <td><?= htmlspecialchars($m['title']) ?></td>
                    <td><?= htmlspecialchars($m['batch']) ?></td>
                    <td><span class="badge badge-blue"><?= $m['type'] ?></span></td>
                    <td>
                        <?php if($m['type']==='link' && $m['class_link']): ?>
                            <a href="<?= htmlspecialchars($m['class_link']) ?>" target="_blank" class="btn btn-blue btn-sm">Join Class</a>
                        <?php elseif($m['file_path']): ?>
                            <a href="../uploads/materials/<?= $m['file_path'] ?>" class="btn btn-primary btn-sm" download>Download</a>
                        <?php else: ?>
                            <span style="color:#94a3b8;font-size:12px;">No file</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody></table>
            </div>
        </div>

        <!-- Exam Results -->
        <div class="box" id="results">
            <div class="box-header"><h3>📝 Exam Results & Feedback</h3></div>
            <div class="box-body">
                <table><thead><tr><th>Exam</th><th>Batch</th><th>Marks</th><th>Percentage</th><th>Feedback</th></tr></thead><tbody>
                <?php while($r=mysqli_fetch_assoc($results)): 
                    $pct = $r['total_marks']>0 ? round(($r['marks']/$r['total_marks'])*100) : 0;
                ?>
                <tr>
                    <td><?= htmlspecialchars($r['exam_name']) ?></td>
                    <td><?= htmlspecialchars($r['batch']) ?></td>
                    <td><?= $r['marks'] ?> / <?= $r['total_marks'] ?></td>
                    <td><span class="badge <?= $pct>=75?'badge-green':($pct>=50?'badge-yellow':'badge-red') ?>"><?= $pct ?>%</span></td>
                    <td style="font-size:13px;color:#6b7280;"><?= htmlspecialchars($r['feedback']) ?></td>
                </tr>
                <?php endwhile; ?>
                </tbody></table>
            </div>
        </div>

        <!-- Attendance -->
        <div class="box" id="attendance">
            <div class="box-header"><h3>📅 My Attendance</h3></div>
            <div class="box-body">
                <table><thead><tr><th>Date</th><th>Batch</th><th>Status</th></tr></thead><tbody>
                <?php while($a=mysqli_fetch_assoc($att)): ?>
                <tr>
                    <td><?= $a['date'] ?></td>
                    <td><?= htmlspecialchars($a['batch']) ?></td>
                    <td><span class="badge <?= $a['status']==='present'?'badge-green':($a['status']==='late'?'badge-yellow':'badge-red') ?>"><?= $a['status'] ?></span></td>
                </tr>
                <?php endwhile; ?>
                </tbody></table>
            </div>
        </div>

        <!-- Payments -->
        <div class="box" id="payments">
            <div class="box-header"><h3>💳 Payments</h3></div>
            <div class="box-body">

                <!-- Upload payment receipt form -->
                <form method="POST" enctype="multipart/form-data" style="background:#f8fafc;padding:18px;border-radius:8px;margin-bottom:20px;">
                    <h4 style="margin-bottom:14px;font-size:14px;color:#0d1b3e;">Upload Payment Receipt</h4>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:12px;align-items:end;">
                        <div class="form-group" style="margin:0;">
                            <label>Batch</label>
                            <select name="batch_id" required>
                                <?php
                                // Reload batch list for dropdown
                                $bl = mysqli_query($conn,"SELECT b.id,b.name FROM enrollments e JOIN batches b ON e.batch_id=b.id WHERE e.student_id=$uid");
                                while($bl_row=mysqli_fetch_assoc($bl)):
                                ?>
                                <option value="<?= $bl_row['id'] ?>"><?= htmlspecialchars($bl_row['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label>Month (e.g. May 2025)</label>
                            <input type="text" name="month" placeholder="May 2025" required/>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label>Amount (LKR)</label>
                            <input type="number" name="amount" placeholder="3500" required/>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label>Receipt Image</label>
                            <input type="file" name="receipt" accept="image/*,.pdf" required/>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top:12px;">Upload Receipt</button>
                </form>

                <!-- Payment history table -->
                <table><thead><tr><th>Month</th><th>Batch</th><th>Amount</th><th>Status</th></tr></thead><tbody>
                <?php while($p=mysqli_fetch_assoc($pays)): ?>
                <tr>
                    <td><?= htmlspecialchars($p['month']) ?></td>
                    <td><?= htmlspecialchars($p['batch']) ?></td>
                    <td>LKR <?= number_format($p['amount'],2) ?></td>
                    <td><span class="badge <?= $p['status']==='approved'?'badge-green':($p['status']==='rejected'?'badge-red':'badge-yellow') ?>"><?= $p['status'] ?></span></td>
                </tr>
                <?php endwhile; ?>
                </tbody></table>
            </div>
        </div>

        <!-- Leaderboard -->
        <div class="box" id="leaderboard">
            <div class="box-header"><h3>🏆 Points Leaderboard</h3></div>
            <div class="box-body">
                <table><thead><tr><th>#</th><th>Student</th><th>Total Points</th></tr></thead><tbody>
                <?php $rank=1; while($lb=mysqli_fetch_assoc($board)): ?>
                <tr <?= $lb['total']==$total_points?'style="background:#fef9c3;"':'' ?>>
                    <td><?= $rank++ ?></td>
                    <td><?= htmlspecialchars($lb['full_name']) ?></td>
                    <td><strong><?= $lb['total'] ?> pts</strong></td>
                </tr>
                <?php endwhile; ?>
                </tbody></table>
            </div>
        </div>

        <!-- Announcements -->
        <div class="box" id="announcements">
            <div class="box-header"><h3>📢 Announcements</h3></div>
            <div class="box-body">
                <?php while($an=mysqli_fetch_assoc($ann)): ?>
                <div style="padding:14px 0;border-bottom:1px solid #f1f5f9;">
                    <strong style="color:#0d1b3e;font-size:14px;"><?= htmlspecialchars($an['title']) ?></strong>
                    <p style="color:#6b7280;font-size:13px;margin-top:4px;"><?= htmlspecialchars($an['content']) ?></p>
                    <small style="color:#94a3b8;"><?= $an['posted_at'] ?></small>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

    </div><!-- /content -->
</div><!-- /main -->
</div><!-- /wrapper -->
</body></html>
