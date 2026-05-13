<?php
// lecturer/dashboard.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_role('lecturer');

$uid  = $_SESSION['user_id'];
$name = $_SESSION['full_name'];

// My batches
$batches = mysqli_query($conn,"SELECT b.*,s.name as subject FROM batches b JOIN subjects s ON b.subject_id=s.id WHERE b.lecturer_id=$uid");
$batch_list = [];
while($row=mysqli_fetch_assoc($batches)) $batch_list[]=$row;

// Count students in my batches
$bid_list = implode(',',array_column($batch_list,'id') ?: [0]);
$r1 = mysqli_query($conn,"SELECT COUNT(DISTINCT student_id) as c FROM enrollments WHERE batch_id IN ($bid_list)");
$student_count = mysqli_fetch_assoc($r1)['c'];

// Handle: Upload material
$msg="";
if($_SERVER['REQUEST_METHOD']==='POST'){

    if(isset($_POST['action']) && $_POST['action']==='upload_material'){
        $batch_id = intval($_POST['batch_id']);
        $title    = mysqli_real_escape_string($conn,$_POST['title']);
        $type     = $_POST['mat_type'];
        $link     = mysqli_real_escape_string($conn,$_POST['class_link']??'');
        $filepath = '';
        if($type!=='link' && isset($_FILES['material_file']) && $_FILES['material_file']['size']>0){
            $ext      = pathinfo($_FILES['material_file']['name'],PATHINFO_EXTENSION);
            $filepath = "mat_".$uid."_".time().".".$ext;
            move_uploaded_file($_FILES['material_file']['tmp_name'],"../uploads/materials/$filepath");
        }
        mysqli_query($conn,"INSERT INTO materials (batch_id,lecturer_id,title,file_path,type,class_link) VALUES ($batch_id,'$title','$filepath','$type','$link',$uid)");
        $msg="✅ Material uploaded successfully.";
    }

    if(isset($_POST['action']) && $_POST['action']==='mark_attendance'){
        $batch_id = intval($_POST['batch_id']);
        $date     = mysqli_real_escape_string($conn,$_POST['att_date']);
        // Delete old records for this batch+date to avoid duplicates
        mysqli_query($conn,"DELETE FROM attendance WHERE batch_id=$batch_id AND date='$date'");
        // Insert new ones
        if(isset($_POST['students'])){
            foreach($_POST['students'] as $sid=>$status){
                $sid    = intval($sid);
                $status = mysqli_real_escape_string($conn,$status);
                mysqli_query($conn,"INSERT INTO attendance (student_id,batch_id,date,status) VALUES ($sid,$batch_id,'$date','$status')");
            }
        }
        $msg="✅ Attendance saved.";
    }

    if(isset($_POST['action']) && $_POST['action']==='upload_result'){
        $batch_id   = intval($_POST['batch_id']);
        $student_id = intval($_POST['student_id']);
        $exam_name  = mysqli_real_escape_string($conn,$_POST['exam_name']);
        $marks      = floatval($_POST['marks']);
        $total      = floatval($_POST['total_marks']);
        $feedback   = mysqli_real_escape_string($conn,$_POST['feedback']);
        mysqli_query($conn,"INSERT INTO results (student_id,batch_id,exam_name,marks,total_marks,feedback,uploaded_by) VALUES ($student_id,$batch_id,'$exam_name',$marks,$total,'$feedback',$uid)");
        $msg="✅ Result uploaded.";
    }

    if(isset($_POST['action']) && $_POST['action']==='add_points'){
        $student_id = intval($_POST['student_id']);
        $pts        = intval($_POST['points']);
        $reason     = mysqli_real_escape_string($conn,$_POST['reason']);
        mysqli_query($conn,"INSERT INTO points (student_id,points,reason,given_by) VALUES ($student_id,$pts,'$reason',$uid)");
        $msg="✅ Points added.";
    }

    if(isset($_POST['action']) && $_POST['action']==='announcement'){
        $title   = mysqli_real_escape_string($conn,$_POST['ann_title']);
        $content = mysqli_real_escape_string($conn,$_POST['ann_content']);
        $bid     = intval($_POST['ann_batch']);
        mysqli_query($conn,"INSERT INTO announcements (title,content,posted_by,batch_id) VALUES ('$title','$content',$uid,".($bid?$bid:'NULL').")");
        $msg="✅ Announcement posted.";
    }
}

// Students in my batches (for dropdowns)
$my_students = mysqli_query($conn,"SELECT DISTINCT u.id,u.full_name FROM enrollments e JOIN users u ON e.student_id=u.id WHERE e.batch_id IN ($bid_list)");
$students_arr=[];
while($s=mysqli_fetch_assoc($my_students)) $students_arr[]=$s;
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"/><title>Lecturer Portal — Activate Academy</title>
<link rel="stylesheet" href="../css/style.css"/>
</head><body>
<div class="wrapper">
<div class="sidebar">
    <div class="sidebar-brand"><small>Lecturer Portal</small>👩‍🏫 Activate Academy</div>
    <nav>
        <a href="#batches"      class="active"><span class="icon">📚</span><span>My Batches</span></a>
        <a href="#materials">               <span class="icon">📁</span><span>Upload Material</span></a>
        <a href="#attendance">              <span class="icon">📅</span><span>Mark Attendance</span></a>
        <a href="#results">                 <span class="icon">📝</span><span>Upload Results</span></a>
        <a href="#points">                  <span class="icon">⭐</span><span>Give Points</span></a>
        <a href="#announcements">           <span class="icon">📢</span><span>Announcements</span></a>
    </nav>
    <div class="sidebar-footer">Logged in as <strong><?= htmlspecialchars($name) ?></strong><br><a href="../logout.php">Logout</a></div>
</div>
<div class="main">
    <div class="topbar"><h1>Lecturer Dashboard</h1>
        <div class="topbar-user"><div class="avatar"><?= strtoupper($name[0]) ?></div><?= htmlspecialchars($name) ?></div>
    </div>
    <div class="content">
        <?php if($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

        <div class="cards-row">
            <div class="card"><div class="card-icon">📚</div><div class="card-value"><?= count($batch_list) ?></div><div class="card-label">My Batches</div></div>
            <div class="card"><div class="card-icon">👨‍🎓</div><div class="card-value"><?= $student_count ?></div><div class="card-label">Students</div></div>
        </div>

        <!-- My Batches -->
        <div class="box" id="batches">
            <div class="box-header"><h3>📚 My Batches</h3></div>
            <div class="box-body">
                <table><thead><tr><th>Batch Name</th><th>Subject</th><th>Schedule</th><th>Type</th></tr></thead><tbody>
                <?php foreach($batch_list as $b): ?>
                <tr><td><?= htmlspecialchars($b['name']) ?></td><td><?= htmlspecialchars($b['subject']) ?></td>
                    <td><?= htmlspecialchars($b['schedule']) ?></td><td><span class="badge badge-blue"><?= $b['type'] ?></span></td></tr>
                <?php endforeach; ?>
                </tbody></table>
            </div>
        </div>

        <!-- Upload Material -->
        <div class="box" id="materials">
            <div class="box-header"><h3>📁 Upload Study Material / Class Link</h3></div>
            <div class="box-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="upload_material"/>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div class="form-group"><label>Batch</label><select name="batch_id" required>
                            <?php foreach($batch_list as $b): ?><option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option><?php endforeach; ?>
                        </select></div>
                        <div class="form-group"><label>Title</label><input type="text" name="title" required placeholder="e.g. Chapter 3 Notes"/></div>
                        <div class="form-group"><label>Type</label><select name="mat_type">
                            <option value="notes">Notes / File</option>
                            <option value="recording">Recording</option>
                            <option value="link">Class Link (Zoom/Meet)</option>
                        </select></div>
                        <div class="form-group"><label>File (if notes/recording)</label><input type="file" name="material_file"/></div>
                    </div>
                    <div class="form-group"><label>Class Link (if online class)</label><input type="text" name="class_link" placeholder="https://meet.google.com/..."/></div>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </form>
            </div>
        </div>

        <!-- Mark Attendance -->
        <div class="box" id="attendance">
            <div class="box-header"><h3>📅 Mark Attendance</h3></div>
            <div class="box-body">
                <form method="POST">
                    <input type="hidden" name="action" value="mark_attendance"/>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                        <div class="form-group"><label>Batch</label><select name="batch_id" required>
                            <?php foreach($batch_list as $b): ?><option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option><?php endforeach; ?>
                        </select></div>
                        <div class="form-group"><label>Date</label><input type="date" name="att_date" value="<?= date('Y-m-d') ?>" required/></div>
                    </div>
                    <table><thead><tr><th>Student</th><th>Status</th></tr></thead><tbody>
                    <?php foreach($students_arr as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['full_name']) ?></td>
                        <td>
                            <select name="students[<?= $s['id'] ?>]">
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="late">Late</option>
                            </select>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody></table>
                    <button type="submit" class="btn btn-green" style="margin-top:14px;">Save Attendance</button>
                </form>
            </div>
        </div>

        <!-- Upload Results -->
        <div class="box" id="results">
            <div class="box-header"><h3>📝 Upload Exam Result</h3></div>
            <div class="box-body">
                <form method="POST">
                    <input type="hidden" name="action" value="upload_result"/>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div class="form-group"><label>Batch</label><select name="batch_id" required>
                            <?php foreach($batch_list as $b): ?><option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option><?php endforeach; ?>
                        </select></div>
                        <div class="form-group"><label>Student</label><select name="student_id" required>
                            <?php foreach($students_arr as $s): ?><option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['full_name']) ?></option><?php endforeach; ?>
                        </select></div>
                        <div class="form-group"><label>Exam Name</label><input type="text" name="exam_name" placeholder="April Monthly Exam" required/></div>
                        <div class="form-group"><label>Marks Obtained</label><input type="number" name="marks" step="0.01" required/></div>
                        <div class="form-group"><label>Total Marks</label><input type="number" name="total_marks" value="100" required/></div>
                    </div>
                    <div class="form-group"><label>Feedback</label><textarea name="feedback" placeholder="Write feedback for this student..."></textarea></div>
                    <button type="submit" class="btn btn-blue">Upload Result</button>
                </form>
            </div>
        </div>

        <!-- Give Points -->
        <div class="box" id="points">
            <div class="box-header"><h3>⭐ Add Performance Points</h3></div>
            <div class="box-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add_points"/>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
                        <div class="form-group"><label>Student</label><select name="student_id" required>
                            <?php foreach($students_arr as $s): ?><option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['full_name']) ?></option><?php endforeach; ?>
                        </select></div>
                        <div class="form-group"><label>Points</label><input type="number" name="points" placeholder="50" required/></div>
                        <div class="form-group"><label>Reason</label><input type="text" name="reason" placeholder="Top scorer in exam" required/></div>
                    </div>
                    <button type="submit" class="btn btn-gold">Add Points</button>
                </form>
            </div>
        </div>

        <!-- Announcements -->
        <div class="box" id="announcements">
            <div class="box-header"><h3>📢 Post Announcement</h3></div>
            <div class="box-body">
                <form method="POST">
                    <input type="hidden" name="action" value="announcement"/>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div class="form-group"><label>Title</label><input type="text" name="ann_title" required placeholder="Announcement title"/></div>
                        <div class="form-group"><label>Batch (optional — 0 = all)</label><select name="ann_batch">
                            <option value="0">All Students</option>
                            <?php foreach($batch_list as $b): ?><option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option><?php endforeach; ?>
                        </select></div>
                    </div>
                    <div class="form-group"><label>Content</label><textarea name="ann_content" required></textarea></div>
                    <button type="submit" class="btn btn-primary">Post</button>
                </form>
            </div>
        </div>

    </div>
</div>
</div>
</body></html>
