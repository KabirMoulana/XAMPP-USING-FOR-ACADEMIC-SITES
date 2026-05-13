<?php
// admin/dashboard.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_role('admin');

$name = $_SESSION['full_name'];
$msg  = "";

// Handle all admin actions
if($_SERVER['REQUEST_METHOD']==='POST'){

    // Add new user (student/lecturer/etc)
    if($_POST['action']==='add_user'){
        $un   = mysqli_real_escape_string($conn,$_POST['username']);
        $pw   = md5($_POST['password']);
        $role = mysqli_real_escape_string($conn,$_POST['role']);
        $fn   = mysqli_real_escape_string($conn,$_POST['full_name']);
        $em   = mysqli_real_escape_string($conn,$_POST['email']);
        $ph   = mysqli_real_escape_string($conn,$_POST['phone']);
        mysqli_query($conn,"INSERT INTO users (username,password,role,full_name,email,phone) VALUES ('$un','$pw','$role','$fn','$em','$ph')");
        // If student, add to students table
        if($role==='student'){
            $new_id = mysqli_insert_id($conn);
            mysqli_query($conn,"INSERT INTO students (user_id,grade,joined_date) VALUES ($new_id,'','".date('Y-m-d')."')");
        }
        $msg="✅ User added successfully.";
    }

    if($_POST['action']==='delete_user'){
        $del_id = intval($_POST['user_id']);
        mysqli_query($conn,"DELETE FROM users WHERE id=$del_id");
        $msg="✅ User deleted.";
    }

    if($_POST['action']==='add_subject'){
        $n  = mysqli_real_escape_string($conn,$_POST['sub_name']);
        $d  = mysqli_real_escape_string($conn,$_POST['sub_desc']);
        $lv = mysqli_real_escape_string($conn,$_POST['sub_level']);
        mysqli_query($conn,"INSERT INTO subjects (name,description,level) VALUES ('$n','$d','$lv')");
        $msg="✅ Subject added.";
    }

    if($_POST['action']==='add_batch'){
        $n   = mysqli_real_escape_string($conn,$_POST['batch_name']);
        $sid = intval($_POST['subject_id']);
        $lid = intval($_POST['lecturer_id']);
        $sch = mysqli_real_escape_string($conn,$_POST['schedule']);
        $typ = mysqli_real_escape_string($conn,$_POST['batch_type']);
        mysqli_query($conn,"INSERT INTO batches (name,subject_id,lecturer_id,schedule,type) VALUES ('$n',$sid,$lid,'$sch','$typ')");
        $msg="✅ Batch created.";
    }

    if($_POST['action']==='approve_payment'){
        $pid    = intval($_POST['payment_id']);
        $status = mysqli_real_escape_string($conn,$_POST['pay_status']);
        mysqli_query($conn,"UPDATE payments SET status='$status' WHERE id=$pid");
        $msg="✅ Payment status updated.";
    }

    if($_POST['action']==='add_announcement'){
        $t = mysqli_real_escape_string($conn,$_POST['ann_title']);
        $c = mysqli_real_escape_string($conn,$_POST['ann_content']);
        $uid = $_SESSION['user_id'];
        mysqli_query($conn,"INSERT INTO announcements (title,content,posted_by) VALUES ('$t','$c',$uid)");
        $msg="✅ Announcement posted.";
    }
}

// Load data for display
$all_users    = mysqli_query($conn,"SELECT * FROM users ORDER BY role,full_name");
$all_subjects = mysqli_query($conn,"SELECT * FROM subjects");
$all_batches  = mysqli_query($conn,"SELECT b.*,s.name as subject,u.full_name as lecturer FROM batches b JOIN subjects s ON b.subject_id=s.id JOIN users u ON b.lecturer_id=u.id");
$all_payments = mysqli_query($conn,"SELECT p.*,u.full_name as student,b.name as batch FROM payments p JOIN users u ON p.student_id=u.id JOIN batches b ON p.batch_id=b.id ORDER BY p.paid_at DESC");
$lecturers    = mysqli_query($conn,"SELECT id,full_name FROM users WHERE role='lecturer'");
$subjects_dd  = mysqli_query($conn,"SELECT id,name FROM subjects");
$enquiries    = mysqli_query($conn,"SELECT * FROM enquiries ORDER BY submitted_at DESC");
$announcements= mysqli_query($conn,"SELECT a.*,u.full_name as poster FROM announcements a JOIN users u ON a.posted_by=u.id ORDER BY a.posted_at DESC LIMIT 10");

// Stats
$count = function($table,$where='') use ($conn){
    $r = mysqli_query($conn,"SELECT COUNT(*) as c FROM $table".($where?" WHERE $where":''));
    return mysqli_fetch_assoc($r)['c'];
};
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"/><title>Admin Panel — Activate Academy</title>
<link rel="stylesheet" href="../css/style.css"/>
</head><body>
<div class="wrapper">
<div class="sidebar">
    <div class="sidebar-brand"><small>Admin Panel</small>🏢 Activate Academy</div>
    <nav>
        <a href="#users"         class="active"><span class="icon">👥</span><span>Manage Users</span></a>
        <a href="#subjects">                  <span class="icon">📖</span><span>Subjects</span></a>
        <a href="#batches">                   <span class="icon">📚</span><span>Batches</span></a>
        <a href="#payments">                  <span class="icon">💳</span><span>Payments</span></a>
        <a href="#announcements">             <span class="icon">📢</span><span>Announcements</span></a>
        <a href="#enquiries">                 <span class="icon">✉️</span><span>Enquiries</span></a>
    </nav>
    <div class="sidebar-footer">Logged in as <strong><?= htmlspecialchars($name) ?></strong><br><a href="../logout.php">Logout</a></div>
</div>
<div class="main">
    <div class="topbar"><h1>Admin Dashboard</h1>
        <div class="topbar-user"><div class="avatar">A</div><?= htmlspecialchars($name) ?></div>
    </div>
    <div class="content">
        <?php if($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

        <!-- Stats -->
        <div class="cards-row">
            <div class="card"><div class="card-icon">👨‍🎓</div><div class="card-value"><?= $count('users',"role='student'") ?></div><div class="card-label">Students</div></div>
            <div class="card"><div class="card-icon">👩‍🏫</div><div class="card-value"><?= $count('users',"role='lecturer'") ?></div><div class="card-label">Lecturers</div></div>
            <div class="card"><div class="card-icon">📚</div><div class="card-value"><?= $count('batches') ?></div><div class="card-label">Batches</div></div>
            <div class="card"><div class="card-icon">💳</div><div class="card-value"><?= $count('payments',"status='pending'") ?></div><div class="card-label">Pending Payments</div></div>
            <div class="card"><div class="card-icon">✉️</div><div class="card-value"><?= $count('enquiries',"status='new'") ?></div><div class="card-label">New Enquiries</div></div>
        </div>

        <!-- Manage Users -->
        <div class="box" id="users">
            <div class="box-header"><h3>👥 Manage Users</h3></div>
            <div class="box-body">
                <!-- Add user form -->
                <form method="POST" style="background:#f8fafc;padding:16px;border-radius:8px;margin-bottom:20px;">
                    <input type="hidden" name="action" value="add_user"/>
                    <h4 style="margin-bottom:14px;font-size:14px;color:#0d1b3e;">Add New User</h4>
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                        <div class="form-group" style="margin:0;"><label>Full Name</label><input type="text" name="full_name" required/></div>
                        <div class="form-group" style="margin:0;"><label>Username</label><input type="text" name="username" required/></div>
                        <div class="form-group" style="margin:0;"><label>Password</label><input type="password" name="password" required/></div>
                        <div class="form-group" style="margin:0;"><label>Role</label><select name="role">
                            <option>student</option><option>lecturer</option><option>admin</option>
                            <option>receptionist</option><option>parent</option><option>manager</option><option>director</option>
                        </select></div>
                        <div class="form-group" style="margin:0;"><label>Email</label><input type="email" name="email"/></div>
                        <div class="form-group" style="margin:0;"><label>Phone</label><input type="text" name="phone"/></div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top:12px;">Add User</button>
                </form>

                <!-- Users table -->
                <table><thead><tr><th>Name</th><th>Username</th><th>Role</th><th>Email</th><th>Phone</th><th>Action</th></tr></thead><tbody>
                <?php while($u=mysqli_fetch_assoc($all_users)): ?>
                <tr>
                    <td><?= htmlspecialchars($u['full_name']) ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><span class="badge badge-blue"><?= $u['role'] ?></span></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['phone']) ?></td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Delete this user?')">
                            <input type="hidden" name="action" value="delete_user"/>
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>"/>
                            <button type="submit" class="btn btn-red btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody></table>
            </div>
        </div>

        <!-- Subjects -->
        <div class="box" id="subjects">
            <div class="box-header"><h3>📖 Subjects</h3></div>
            <div class="box-body">
                <form method="POST" style="background:#f8fafc;padding:16px;border-radius:8px;margin-bottom:20px;">
                    <input type="hidden" name="action" value="add_subject"/>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
                        <div class="form-group" style="margin:0;"><label>Subject Name</label><input type="text" name="sub_name" required/></div>
                        <div class="form-group" style="margin:0;"><label>Description</label><input type="text" name="sub_desc"/></div>
                        <div class="form-group" style="margin:0;"><label>Level (e.g. O/L)</label><input type="text" name="sub_level"/></div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top:12px;">Add Subject</button>
                </form>
                <table><thead><tr><th>Name</th><th>Description</th><th>Level</th></tr></thead><tbody>
                <?php while($s=mysqli_fetch_assoc($all_subjects)): ?>
                <tr><td><?= htmlspecialchars($s['name']) ?></td><td><?= htmlspecialchars($s['description']) ?></td><td><?= htmlspecialchars($s['level']) ?></td></tr>
                <?php endwhile; ?>
                </tbody></table>
            </div>
        </div>

        <!-- Batches -->
        <div class="box" id="batches">
            <div class="box-header"><h3>📚 Batches</h3></div>
            <div class="box-body">
                <form method="POST" style="background:#f8fafc;padding:16px;border-radius:8px;margin-bottom:20px;">
                    <input type="hidden" name="action" value="add_batch"/>
                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">
                        <div class="form-group" style="margin:0;"><label>Batch Name</label><input type="text" name="batch_name" required/></div>
                        <div class="form-group" style="margin:0;"><label>Subject</label><select name="subject_id">
                            <?php $subjects_dd=mysqli_query($conn,"SELECT id,name FROM subjects"); while($s=mysqli_fetch_assoc($subjects_dd)): ?><option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option><?php endwhile; ?>
                        </select></div>
                        <div class="form-group" style="margin:0;"><label>Lecturer</label><select name="lecturer_id">
                            <?php $lecturers=mysqli_query($conn,"SELECT id,full_name FROM users WHERE role='lecturer'"); while($l=mysqli_fetch_assoc($lecturers)): ?><option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['full_name']) ?></option><?php endwhile; ?>
                        </select></div>
                        <div class="form-group" style="margin:0;"><label>Schedule</label><input type="text" name="schedule" placeholder="Monday 4PM"/></div>
                        <div class="form-group" style="margin:0;"><label>Type</label><select name="batch_type"><option>both</option><option>online</option><option>physical</option></select></div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top:12px;">Create Batch</button>
                </form>
                <table><thead><tr><th>Batch</th><th>Subject</th><th>Lecturer</th><th>Schedule</th><th>Type</th></tr></thead><tbody>
                <?php while($b=mysqli_fetch_assoc($all_batches)): ?>
                <tr><td><?= htmlspecialchars($b['name']) ?></td><td><?= htmlspecialchars($b['subject']) ?></td>
                    <td><?= htmlspecialchars($b['lecturer']) ?></td><td><?= htmlspecialchars($b['schedule']) ?></td>
                    <td><span class="badge badge-blue"><?= $b['type'] ?></span></td></tr>
                <?php endwhile; ?>
                </tbody></table>
            </div>
        </div>

        <!-- Payments -->
        <div class="box" id="payments">
            <div class="box-header"><h3>💳 Payment Approval</h3></div>
            <div class="box-body">
                <table><thead><tr><th>Student</th><th>Batch</th><th>Month</th><th>Amount</th><th>Receipt</th><th>Status</th><th>Action</th></tr></thead><tbody>
                <?php while($p=mysqli_fetch_assoc($all_payments)): ?>
                <tr>
                    <td><?= htmlspecialchars($p['student']) ?></td>
                    <td><?= htmlspecialchars($p['batch']) ?></td>
                    <td><?= htmlspecialchars($p['month']) ?></td>
                    <td>LKR <?= number_format($p['amount'],2) ?></td>
                    <td><?php if($p['receipt_image']): ?><a href="../uploads/receipts/<?= $p['receipt_image'] ?>" target="_blank" class="btn btn-blue btn-sm">View</a><?php else: ?>No receipt<?php endif; ?></td>
                    <td><span class="badge <?= $p['status']==='approved'?'badge-green':($p['status']==='rejected'?'badge-red':'badge-yellow') ?>"><?= $p['status'] ?></span></td>
                    <td>
                        <?php if($p['status']==='pending'): ?>
                        <form method="POST" style="display:flex;gap:6px;">
                            <input type="hidden" name="action" value="approve_payment"/>
                            <input type="hidden" name="payment_id" value="<?= $p['id'] ?>"/>
                            <button name="pay_status" value="approved" class="btn btn-green btn-sm">Approve</button>
                            <button name="pay_status" value="rejected" class="btn btn-red btn-sm">Reject</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody></table>
            </div>
        </div>

        <!-- Announcements -->
        <div class="box" id="announcements">
            <div class="box-header"><h3>📢 Announcements</h3></div>
            <div class="box-body">
                <form method="POST" style="background:#f8fafc;padding:16px;border-radius:8px;margin-bottom:20px;">
                    <input type="hidden" name="action" value="add_announcement"/>
                    <div class="form-group"><label>Title</label><input type="text" name="ann_title" required/></div>
                    <div class="form-group"><label>Content</label><textarea name="ann_content" required></textarea></div>
                    <button type="submit" class="btn btn-primary">Post Announcement</button>
                </form>
                <table><thead><tr><th>Title</th><th>Posted By</th><th>Date</th></tr></thead><tbody>
                <?php while($a=mysqli_fetch_assoc($announcements)): ?>
                <tr><td><?= htmlspecialchars($a['title']) ?></td><td><?= htmlspecialchars($a['poster']) ?></td><td><?= $a['posted_at'] ?></td></tr>
                <?php endwhile; ?>
                </tbody></table>
            </div>
        </div>

        <!-- Enquiries -->
        <div class="box" id="enquiries">
            <div class="box-header"><h3>✉️ Enquiries from Public</h3></div>
            <div class="box-body">
                <table><thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Subject</th><th>Message</th><th>Date</th><th>Status</th></tr></thead><tbody>
                <?php while($e=mysqli_fetch_assoc($enquiries)): ?>
                <tr>
                    <td><?= htmlspecialchars($e['name']) ?></td>
                    <td><?= htmlspecialchars($e['email']) ?></td>
                    <td><?= htmlspecialchars($e['phone']) ?></td>
                    <td><?= htmlspecialchars($e['subject']) ?></td>
                    <td style="max-width:200px;font-size:13px;"><?= htmlspecialchars($e['message']) ?></td>
                    <td><?= $e['submitted_at'] ?></td>
                    <td><span class="badge <?= $e['status']==='new'?'badge-yellow':'badge-green' ?>"><?= $e['status'] ?></span></td>
                </tr>
                <?php endwhile; ?>
                </tbody></table>
            </div>
        </div>

    </div>
</div>
</div>
</body></html>
