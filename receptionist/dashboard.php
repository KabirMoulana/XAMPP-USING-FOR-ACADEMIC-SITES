<?php
// receptionist/dashboard.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_role('receptionist');

$uid  = $_SESSION['user_id'];
$name = $_SESSION['full_name'];
$msg  = "";

// Handle actions
if($_SERVER['REQUEST_METHOD']==='POST'){

    if($_POST['action']==='register_student'){
        $un  = mysqli_real_escape_string($conn,$_POST['username']);
        $pw  = md5($_POST['password']);
        $fn  = mysqli_real_escape_string($conn,$_POST['full_name']);
        $em  = mysqli_real_escape_string($conn,$_POST['email']);
        $ph  = mysqli_real_escape_string($conn,$_POST['phone']);
        $gr  = mysqli_real_escape_string($conn,$_POST['grade']);
        $gu  = mysqli_real_escape_string($conn,$_POST['guardian']);
        mysqli_query($conn,"INSERT INTO users (username,password,role,full_name,email,phone) VALUES ('$un','$pw','student','$fn','$em','$ph')");
        $new_id = mysqli_insert_id($conn);
        mysqli_query($conn,"INSERT INTO students (user_id,grade,guardian,joined_date) VALUES ($new_id,'$gr','$gu','".date('Y-m-d')."')");
        $msg="✅ Student registered. Username: $un / Password: ".$_POST['password'];
    }

    if($_POST['action']==='assign_batch'){
        $sid = intval($_POST['student_id']);
        $bid = intval($_POST['batch_id']);
        // Check if already enrolled
        $chk = mysqli_query($conn,"SELECT id FROM enrollments WHERE student_id=$sid AND batch_id=$bid");
        if(mysqli_num_rows($chk)===0){
            mysqli_query($conn,"INSERT INTO enrollments (student_id,batch_id) VALUES ($sid,$bid)");
            $msg="✅ Student assigned to batch.";
        } else {
            $msg="⚠️ Student is already in this batch.";
        }
    }

    if($_POST['action']==='record_payment'){
        $sid    = intval($_POST['student_id']);
        $bid    = intval($_POST['batch_id']);
        $amt    = floatval($_POST['amount']);
        $month  = mysqli_real_escape_string($conn,$_POST['month']);
        $filepath='';
        if(isset($_FILES['receipt']) && $_FILES['receipt']['size']>0){
            $ext      = pathinfo($_FILES['receipt']['name'],PATHINFO_EXTENSION);
            $filepath = "rcpt_".$sid."_".time().".".$ext;
            move_uploaded_file($_FILES['receipt']['tmp_name'],"../uploads/receipts/$filepath");
        }
        mysqli_query($conn,"INSERT INTO payments (student_id,batch_id,amount,month,receipt_image,status,recorded_by) VALUES ($sid,$bid,$amt,'$month','$filepath','approved',$uid)");
        $msg="✅ Payment recorded and approved.";
    }
}

// Load data
$students = mysqli_query($conn,"SELECT u.id,u.full_name,u.email,s.grade FROM users u LEFT JOIN students s ON u.id=s.user_id WHERE u.role='student' ORDER BY u.full_name");
$batches  = mysqli_query($conn,"SELECT b.id,b.name,su.name as subject FROM batches b JOIN subjects su ON b.subject_id=su.id");
$payments = mysqli_query($conn,"SELECT p.*,u.full_name as student,b.name as batch FROM payments p JOIN users u ON p.student_id=u.id JOIN batches b ON p.batch_id=b.id ORDER BY p.paid_at DESC LIMIT 30");

// Store in arrays for dropdowns (need to reuse)
$students_arr=[];$s_res=mysqli_query($conn,"SELECT u.id,u.full_name FROM users u WHERE u.role='student'");
while($r=mysqli_fetch_assoc($s_res)) $students_arr[]=$r;
$batches_arr=[];$b_res=mysqli_query($conn,"SELECT b.id,b.name FROM batches b");
while($r=mysqli_fetch_assoc($b_res)) $batches_arr[]=$r;
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"/><title>Receptionist — Activate Academy</title>
<link rel="stylesheet" href="../css/style.css"/></head><body>
<div class="wrapper">
<div class="sidebar">
    <div class="sidebar-brand"><small>Receptionist</small>🧾 Activate Academy</div>
    <nav>
        <a href="#register" class="active"><span class="icon">📋</span><span>Register Student</span></a>
        <a href="#assign">             <span class="icon">🔗</span><span>Assign Batch</span></a>
        <a href="#payment">            <span class="icon">💳</span><span>Record Payment</span></a>
        <a href="#history">            <span class="icon">📜</span><span>Payment History</span></a>
        <a href="#students">           <span class="icon">👥</span><span>All Students</span></a>
    </nav>
    <div class="sidebar-footer">Logged in as <strong><?= htmlspecialchars($name) ?></strong><br><a href="../logout.php">Logout</a></div>
</div>
<div class="main">
    <div class="topbar"><h1>Receptionist Dashboard</h1>
        <div class="topbar-user"><div class="avatar">R</div><?= htmlspecialchars($name) ?></div>
    </div>
    <div class="content">
        <?php if($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

        <!-- Register Student -->
        <div class="box" id="register">
            <div class="box-header"><h3>📋 Register New Student</h3></div>
            <div class="box-body">
                <form method="POST">
                    <input type="hidden" name="action" value="register_student"/>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div class="form-group"><label>Full Name</label><input type="text" name="full_name" required/></div>
                        <div class="form-group"><label>Username (for login)</label><input type="text" name="username" required/></div>
                        <div class="form-group"><label>Password</label><input type="text" name="password" value="student123" required/></div>
                        <div class="form-group"><label>Email</label><input type="email" name="email"/></div>
                        <div class="form-group"><label>Phone</label><input type="text" name="phone"/></div>
                        <div class="form-group"><label>Grade (e.g. O/L)</label><input type="text" name="grade"/></div>
                        <div class="form-group"><label>Guardian Name</label><input type="text" name="guardian"/></div>
                    </div>
                    <button type="submit" class="btn btn-primary">Register Student</button>
                </form>
            </div>
        </div>

        <!-- Assign to Batch -->
        <div class="box" id="assign">
            <div class="box-header"><h3>🔗 Assign Student to Batch</h3></div>
            <div class="box-body">
                <form method="POST">
                    <input type="hidden" name="action" value="assign_batch"/>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div class="form-group"><label>Student</label><select name="student_id" required>
                            <?php foreach($students_arr as $s): ?><option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['full_name']) ?></option><?php endforeach; ?>
                        </select></div>
                        <div class="form-group"><label>Batch</label><select name="batch_id" required>
                            <?php foreach($batches_arr as $b): ?><option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option><?php endforeach; ?>
                        </select></div>
                    </div>
                    <button type="submit" class="btn btn-blue">Assign</button>
                </form>
            </div>
        </div>

        <!-- Record Payment -->
        <div class="box" id="payment">
            <div class="box-header"><h3>💳 Record Monthly Payment</h3></div>
            <div class="box-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="record_payment"/>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div class="form-group"><label>Student</label><select name="student_id" required>
                            <?php foreach($students_arr as $s): ?><option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['full_name']) ?></option><?php endforeach; ?>
                        </select></div>
                        <div class="form-group"><label>Batch</label><select name="batch_id" required>
                            <?php foreach($batches_arr as $b): ?><option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option><?php endforeach; ?>
                        </select></div>
                        <div class="form-group"><label>Month</label><input type="text" name="month" placeholder="May 2025" required/></div>
                        <div class="form-group"><label>Amount (LKR)</label><input type="number" name="amount" placeholder="3500" required/></div>
                        <div class="form-group"><label>Receipt (optional)</label><input type="file" name="receipt" accept="image/*,.pdf"/></div>
                    </div>
                    <button type="submit" class="btn btn-green">Record Payment</button>
                </form>
            </div>
        </div>

        <!-- Payment History -->
        <div class="box" id="history">
            <div class="box-header"><h3>📜 Payment History</h3></div>
            <div class="box-body">
                <table><thead><tr><th>Student</th><th>Batch</th><th>Month</th><th>Amount</th><th>Status</th><th>Receipt</th><th>Date</th></tr></thead><tbody>
                <?php while($p=mysqli_fetch_assoc($payments)): ?>
                <tr>
                    <td><?= htmlspecialchars($p['student']) ?></td>
                    <td><?= htmlspecialchars($p['batch']) ?></td>
                    <td><?= htmlspecialchars($p['month']) ?></td>
                    <td>LKR <?= number_format($p['amount'],2) ?></td>
                    <td><span class="badge <?= $p['status']==='approved'?'badge-green':($p['status']==='rejected'?'badge-red':'badge-yellow') ?>"><?= $p['status'] ?></span></td>
                    <td><?php if($p['receipt_image']): ?><a href="../uploads/receipts/<?= $p['receipt_image'] ?>" target="_blank" class="btn btn-blue btn-sm">View</a><?php else: ?>—<?php endif; ?></td>
                    <td style="font-size:12px;"><?= $p['paid_at'] ?></td>
                </tr>
                <?php endwhile; ?>
                </tbody></table>
            </div>
        </div>

        <!-- All Students -->
        <div class="box" id="students">
            <div class="box-header"><h3>👥 All Registered Students</h3></div>
            <div class="box-body">
                <table><thead><tr><th>Name</th><th>Email</th><th>Grade</th></tr></thead><tbody>
                <?php while($s=mysqli_fetch_assoc($students)): ?>
                <tr><td><?= htmlspecialchars($s['full_name']) ?></td><td><?= htmlspecialchars($s['email']) ?></td><td><?= htmlspecialchars($s['grade']) ?></td></tr>
                <?php endwhile; ?>
                </tbody></table>
            </div>
        </div>

    </div>
</div>
</div>
</body></html>
