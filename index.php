<?php
// index.php — Public website homepage
// Saves enquiry form submissions to the database
require_once 'includes/db.php';

$enquiry_msg = "";

if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['enquiry'])){
    $n  = mysqli_real_escape_string($conn,$_POST['full_name']);
    $e  = mysqli_real_escape_string($conn,$_POST['email']);
    $ph = mysqli_real_escape_string($conn,$_POST['phone']);
    $su = mysqli_real_escape_string($conn,$_POST['subject']);
    $ms = mysqli_real_escape_string($conn,$_POST['message']);
    mysqli_query($conn,"INSERT INTO enquiries (name,email,phone,subject,message) VALUES ('$n','$e','$ph','$su','$ms')");
    $enquiry_msg = "✅ Thank you! Your enquiry has been submitted. We will contact you soon.";
}

// Load announcements for homepage
$announcements = mysqli_query($conn,"SELECT title,content,posted_at FROM announcements WHERE batch_id IS NULL ORDER BY posted_at DESC LIMIT 4");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Activate Academy</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet"/>
  <style>
    :root{--navy:#0d1b3e;--blue:#1a56db;--gold:#f5a623;--light:#f4f7ff;--white:#fff;--gray:#6b7280;--text:#1f2937;--radius:12px;}
    *{margin:0;padding:0;box-sizing:border-box;}
    body{font-family:'DM Sans',sans-serif;background:var(--light);color:var(--text);line-height:1.6;}
    h1,h2,h3{font-family:'Playfair Display',serif;}
    a{text-decoration:none;color:inherit;}
    nav{background:var(--navy);color:#fff;display:flex;align-items:center;justify-content:space-between;padding:16px 60px;position:sticky;top:0;z-index:100;box-shadow:0 2px 12px rgba(0,0,0,0.3);}
    .nav-logo{display:flex;align-items:center;gap:10px;}
    .nav-logo .logo-icon{width:42px;height:42px;background:var(--gold);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px;}
    .nav-logo span{font-family:'Playfair Display',serif;font-size:22px;font-weight:700;}
    .nav-links{display:flex;gap:30px;list-style:none;}
    .nav-links a{font-size:15px;font-weight:500;color:#cbd5e1;transition:color 0.2s;}
    .nav-links a:hover{color:var(--gold);}
    .nav-login{background:var(--blue);color:#fff;padding:10px 22px;border-radius:8px;font-weight:600;font-size:14px;transition:background 0.2s;}
    .nav-login:hover{background:var(--gold);color:var(--navy);}
    .hero{background:var(--navy);color:#fff;padding:90px 60px;display:flex;align-items:center;justify-content:space-between;gap:40px;min-height:520px;}
    .hero-text{max-width:580px;}
    .hero-tag{display:inline-block;background:var(--gold);color:var(--navy);font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:1px;padding:5px 14px;border-radius:20px;margin-bottom:18px;}
    .hero h1{font-size:52px;line-height:1.15;margin-bottom:20px;}
    .hero h1 span{color:var(--gold);}
    .hero p{font-size:17px;color:#94a3b8;margin-bottom:34px;max-width:480px;}
    .hero-btns{display:flex;gap:16px;flex-wrap:wrap;}
    .btn-primary{background:var(--blue);color:#fff;padding:14px 30px;border-radius:var(--radius);font-weight:600;font-size:15px;border:none;cursor:pointer;transition:background 0.2s,transform 0.15s;display:inline-block;}
    .btn-primary:hover{background:var(--gold);color:var(--navy);transform:translateY(-2px);}
    .btn-outline{background:transparent;color:#fff;padding:14px 30px;border-radius:var(--radius);font-weight:600;font-size:15px;border:2px solid #475569;cursor:pointer;transition:border-color 0.2s,color 0.2s;display:inline-block;}
    .btn-outline:hover{border-color:var(--gold);color:var(--gold);}
    .hero-stats{display:flex;flex-direction:column;gap:18px;min-width:240px;}
    .stat-card{background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.12);border-radius:var(--radius);padding:22px 28px;text-align:center;}
    .stat-card .number{font-family:'Playfair Display',serif;font-size:36px;color:var(--gold);}
    .stat-card .label{font-size:13px;color:#94a3b8;margin-top:4px;}
    section{padding:80px 60px;}
    .section-header{text-align:center;margin-bottom:50px;}
    .section-header h2{font-size:38px;color:var(--navy);margin-bottom:10px;}
    .section-header p{color:var(--gray);font-size:16px;max-width:500px;margin:0 auto;}
    .underline-gold{display:inline-block;border-bottom:3px solid var(--gold);padding-bottom:4px;}
    #about{background:var(--white);}
    .about-grid{display:grid;grid-template-columns:1fr 1fr;gap:50px;align-items:center;}
    .about-text h2{font-size:36px;color:var(--navy);margin-bottom:16px;}
    .about-text p{color:var(--gray);margin-bottom:14px;font-size:15.5px;}
    .badges{display:flex;flex-wrap:wrap;gap:10px;margin-top:20px;}
    .badge{background:var(--light);border:1px solid #dde3f0;padding:7px 16px;border-radius:20px;font-size:13px;font-weight:500;color:var(--navy);}
    .about-image{background:linear-gradient(135deg,var(--navy) 0%,#1a3a6e 100%);border-radius:16px;height:340px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:80px;}
    #courses{background:var(--light);}
    .courses-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:24px;}
    .course-card{background:var(--white);border-radius:var(--radius);padding:28px 24px;border:1px solid #e2e8f0;transition:box-shadow 0.2s,transform 0.2s;}
    .course-card:hover{box-shadow:0 8px 30px rgba(13,27,62,0.12);transform:translateY(-4px);}
    .course-icon{width:52px;height:52px;background:var(--navy);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:16px;}
    .course-card h3{font-family:'DM Sans',sans-serif;font-size:17px;font-weight:600;color:var(--navy);margin-bottom:8px;}
    .course-card p{font-size:14px;color:var(--gray);margin-bottom:16px;}
    .course-tag{display:inline-block;background:#eff6ff;color:var(--blue);font-size:12px;font-weight:600;padding:4px 12px;border-radius:20px;}
    #lecturers{background:var(--white);}
    .lecturers-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:24px;}
    .lecturer-card{border-radius:var(--radius);border:1px solid #e2e8f0;overflow:hidden;text-align:center;background:var(--white);transition:box-shadow 0.2s;}
    .lecturer-card:hover{box-shadow:0 6px 24px rgba(0,0,0,0.1);}
    .lecturer-avatar{background:linear-gradient(160deg,var(--navy),#274fa0);height:130px;display:flex;align-items:center;justify-content:center;font-size:52px;color:var(--gold);}
    .lecturer-info{padding:18px 16px 22px;}
    .lecturer-info h3{font-family:'DM Sans',sans-serif;font-weight:600;font-size:16px;color:var(--navy);margin-bottom:4px;}
    .lecturer-info .subject{font-size:13px;color:var(--blue);font-weight:500;}
    #testimonials{background:var(--navy);color:#fff;}
    #testimonials .section-header h2{color:#fff;}
    #testimonials .section-header p{color:#94a3b8;}
    .testimonials-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px;}
    .testimonial-card{background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.12);border-radius:var(--radius);padding:28px 24px;}
    .stars{color:var(--gold);font-size:18px;margin-bottom:12px;}
    .testimonial-card p{font-size:14.5px;color:#cbd5e1;margin-bottom:18px;font-style:italic;}
    .testimonial-author{display:flex;align-items:center;gap:12px;}
    .author-avatar{width:42px;height:42px;background:var(--gold);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:16px;color:var(--navy);}
    .author-info .name{font-weight:600;font-size:15px;}
    .author-info .grade{font-size:12px;color:#94a3b8;}
    #announcements{background:var(--light);}
    .announcements-list{display:flex;flex-direction:column;gap:16px;max-width:750px;margin:0 auto;}
    .announcement-item{background:var(--white);border-radius:var(--radius);padding:20px 24px;border-left:4px solid var(--blue);display:flex;justify-content:space-between;align-items:flex-start;gap:16px;}
    .announcement-item h4{font-family:'DM Sans',sans-serif;font-weight:600;font-size:15.5px;color:var(--navy);margin-bottom:4px;}
    .announcement-item p{font-size:13.5px;color:var(--gray);}
    .ann-date{font-size:12px;color:var(--blue);font-weight:600;white-space:nowrap;}
    #contact{background:var(--white);}
    .contact-wrapper{display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:start;}
    .contact-info h2{font-size:34px;color:var(--navy);margin-bottom:16px;}
    .contact-info p{color:var(--gray);font-size:15px;margin-bottom:28px;}
    .info-row{display:flex;align-items:flex-start;gap:14px;margin-bottom:18px;}
    .info-icon{width:40px;height:40px;background:var(--light);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;}
    .info-row strong{display:block;font-size:14px;color:var(--navy);}
    .info-row span{font-size:13.5px;color:var(--gray);}
    .contact-form{display:flex;flex-direction:column;gap:16px;}
    .contact-form input,.contact-form textarea,.contact-form select{padding:12px 16px;border:1.5px solid #e2e8f0;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:14.5px;color:var(--text);background:var(--light);transition:border-color 0.2s;width:100%;}
    .contact-form input:focus,.contact-form textarea:focus,.contact-form select:focus{outline:none;border-color:var(--blue);}
    .contact-form textarea{resize:vertical;min-height:130px;}
    .contact-form .submit-btn{background:var(--navy);color:#fff;padding:14px;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer;transition:background 0.2s;font-family:'DM Sans',sans-serif;}
    .contact-form .submit-btn:hover{background:var(--blue);}
    .alert-success{background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:12px 16px;border-radius:8px;font-size:14px;}
    footer{background:var(--navy);color:#94a3b8;padding:40px 60px 24px;}
    .footer-top{display:flex;justify-content:space-between;align-items:flex-start;gap:40px;padding-bottom:30px;border-bottom:1px solid rgba(255,255,255,0.08);flex-wrap:wrap;}
    .footer-brand p{font-size:13.5px;max-width:280px;margin-top:10px;line-height:1.7;}
    .footer-col h4{color:#fff;font-family:'DM Sans',sans-serif;font-weight:600;margin-bottom:14px;font-size:14px;}
    .footer-col ul{list-style:none;}
    .footer-col ul li{margin-bottom:8px;font-size:13.5px;}
    .footer-col ul li a{color:#94a3b8;transition:color 0.2s;}
    .footer-col ul li a:hover{color:var(--gold);}
    .footer-bottom{text-align:center;padding-top:22px;font-size:13px;}
    @media(max-width:900px){nav{padding:14px 24px;}.nav-links{display:none;}.hero{flex-direction:column;padding:60px 24px;text-align:center;}.hero h1{font-size:36px;}.hero-btns{justify-content:center;}.hero-stats{flex-direction:row;flex-wrap:wrap;justify-content:center;}section{padding:60px 24px;}.about-grid,.contact-wrapper{grid-template-columns:1fr;}footer{padding:40px 24px 24px;}.footer-top{flex-direction:column;gap:24px;}}
  </style>
</head>
<body>

<nav>
  <div class="nav-logo">
    <div class="logo-icon">🎓</div>
    <span>Activate Academy</span>
  </div>
  <ul class="nav-links">
    <li><a href="#about">About</a></li>
    <li><a href="#courses">Courses</a></li>
    <li><a href="#lecturers">Lecturers</a></li>
    <li><a href="#testimonials">Testimonials</a></li>
    <li><a href="#announcements">News</a></li>
    <li><a href="#contact">Contact</a></li>
  </ul>
  <a href="login.php" class="nav-login">Login →</a>
</nav>

<section class="hero" id="home">
  <div class="hero-text">
    <span class="hero-tag">Beyond Grades Towards Greatness</span>
    <h1>Learn Smarter,<br>Achieve <span>More</span>.</h1>
    <p>Activate Academy offers quality education for students from Grade 8 to Advanced Level in both Edexcel &amp; London syllabi — online and physical classes.</p>
    <div class="hero-btns">
      <a href="#courses" class="btn-primary">Explore Courses</a>
      <a href="#contact" class="btn-outline">Contact Us</a>
    </div>
  </div>
  <div class="hero-stats">
    <div class="stat-card"><div class="number">500+</div><div class="label">Students Enrolled</div></div>
    <div class="stat-card"><div class="number">15+</div><div class="label">Qualified Lecturers</div></div>
    <div class="stat-card"><div class="number">20+</div><div class="label">Subjects Offered</div></div>
  </div>
</section>

<section id="about">
  <div class="about-grid">
    <div class="about-text">
      <h2><span class="underline-gold">About</span> Activate Academy</h2>
      <p>Activate Academy is a private educational institute located in Nugegoda, Sri Lanka. We provide academic support for students from Grade 8 through Advanced Level (A/L).</p>
      <p>We offer both <strong>Edexcel</strong> and <strong>London syllabus</strong> classes conducted by highly qualified lecturers, with both physical and online options.</p>
      <p>Beyond academics, we also organize extracurricular activities like sports events and student engagement programs.</p>
      <div class="badges">
        <span class="badge">📚 Grade 8 – A/L</span>
        <span class="badge">🌐 Online &amp; Physical</span>
        <span class="badge">🏆 Edexcel &amp; London</span>
        <span class="badge">⭐ Point Reward System</span>
      </div>
    </div>
    <div class="about-image">🏫</div>
  </div>
</section>

<section id="courses">
  <div class="section-header">
    <h2><span class="underline-gold">Our</span> Courses</h2>
    <p>Wide range of subjects for all levels</p>
  </div>
  <div class="courses-grid">
    <div class="course-card"><div class="course-icon">➕</div><h3>Mathematics</h3><p>Algebra, calculus, and statistics for every level.</p><span class="course-tag">Grade 8 – A/L</span></div>
    <div class="course-card"><div class="course-icon">🔬</div><h3>Science</h3><p>Physics, Chemistry, and Biology with real-world examples.</p><span class="course-tag">Grade 8 – O/L</span></div>
    <div class="course-card"><div class="course-icon">🌍</div><h3>English Language</h3><p>Reading, writing, speaking and grammar for Edexcel and London.</p><span class="course-tag">Grade 8 – A/L</span></div>
    <div class="course-card"><div class="course-icon">💻</div><h3>ICT / Computing</h3><p>Computer Science and ICT aligned with the latest syllabus.</p><span class="course-tag">O/L – A/L</span></div>
    <div class="course-card"><div class="course-icon">📊</div><h3>Accounting</h3><p>Financial accounts, statements, and bookkeeping.</p><span class="course-tag">A/L</span></div>
    <div class="course-card"><div class="course-icon">🗺️</div><h3>Geography</h3><p>Physical and human geography with map skills.</p><span class="course-tag">Grade 8 – O/L</span></div>
  </div>
</section>

<section id="lecturers">
  <div class="section-header">
    <h2><span class="underline-gold">Meet Our</span> Lecturers</h2>
    <p>Qualified and experienced teachers dedicated to your success</p>
  </div>
  <div class="lecturers-grid">
    <div class="lecturer-card"><div class="lecturer-avatar">👨‍🏫</div><div class="lecturer-info"><h3>Mr. Karunarathne</h3><span class="subject">Mathematics</span></div></div>
    <div class="lecturer-card"><div class="lecturer-avatar">👩‍🏫</div><div class="lecturer-info"><h3>Ms. Perera</h3><span class="subject">English Language</span></div></div>
    <div class="lecturer-card"><div class="lecturer-avatar">👨‍🔬</div><div class="lecturer-info"><h3>Mr. Fernando</h3><span class="subject">Science &amp; Physics</span></div></div>
    <div class="lecturer-card"><div class="lecturer-avatar">👩‍💻</div><div class="lecturer-info"><h3>Ms. Silva</h3><span class="subject">ICT / Computing</span></div></div>
  </div>
</section>

<section id="testimonials">
  <div class="section-header">
    <h2>What Our <span class="underline-gold">Students</span> Say</h2>
    <p>Real feedback from students who achieved great results</p>
  </div>
  <div class="testimonials-grid">
    <div class="testimonial-card"><div class="stars">★★★★★</div><p>"Activate Academy helped me score A's in my O/L exams. The lecturers are very supportive and the online materials are very useful."</p><div class="testimonial-author"><div class="author-avatar">A</div><div class="author-info"><div class="name">Aisha Farhan</div><div class="grade">O/L Student</div></div></div></div>
    <div class="testimonial-card"><div class="stars">★★★★★</div><p>"The point-based leaderboard really motivated me to study harder every week. I love the competitive but friendly environment."</p><div class="testimonial-author"><div class="author-avatar">R</div><div class="author-info"><div class="name">Rithik Sharma</div><div class="grade">A/L Student</div></div></div></div>
    <div class="testimonial-card"><div class="stars">★★★★☆</div><p>"Online classes are very smooth. I can access notes and recordings anytime, which is great for revision before exams."</p><div class="testimonial-author"><div class="author-avatar">N</div><div class="author-info"><div class="name">Nethmi Jayasena</div><div class="grade">Grade 10 Student</div></div></div></div>
  </div>
</section>

<section id="announcements">
  <div class="section-header">
    <h2>Latest <span class="underline-gold">Announcements</span></h2>
    <p>Stay updated with academy activities</p>
  </div>
  <div class="announcements-list">
    <?php while($a=mysqli_fetch_assoc($announcements)): ?>
    <div class="announcement-item">
      <div>
        <h4>📢 <?= htmlspecialchars($a['title']) ?></h4>
        <p><?= htmlspecialchars($a['content']) ?></p>
      </div>
      <span class="ann-date"><?= date('M d, Y', strtotime($a['posted_at'])) ?></span>
    </div>
    <?php endwhile; ?>
  </div>
</section>

<section id="contact">
  <div class="contact-wrapper">
    <div class="contact-info">
      <h2><span class="underline-gold">Get In</span> Touch</h2>
      <p>Have a question about enrolling or our courses? Send us a message and we'll get back to you soon.</p>
      <div class="info-row"><div class="info-icon">📍</div><div><strong>Address</strong><span>144 A 1/1, S DE Jayasinghe Mawatha, Nugegoda</span></div></div>
      <div class="info-row"><div class="info-icon">📞</div><div><strong>Phone</strong><span>+94 774 216 009</span></div></div>
      <div class="info-row"><div class="info-icon">✉️</div><div><strong>Email</strong><span>activateacademylk@gmail.com</span></div></div>
      <div class="info-row"><div class="info-icon">🕒</div><div><strong>Office Hours</strong><span>Mon – Sat: 8:00 AM – 6:00 PM</span></div></div>
    </div>
    <form class="contact-form" method="POST">
      <input type="hidden" name="enquiry" value="1"/>
      <?php if($enquiry_msg): ?><div class="alert-success"><?= $enquiry_msg ?></div><?php endif; ?>
      <input type="text"  name="full_name" placeholder="Your Full Name"     required/>
      <input type="email" name="email"     placeholder="Your Email Address" required/>
      <input type="tel"   name="phone"     placeholder="Phone Number"/>
      <select name="subject">
        <option value="">-- Subject of Enquiry --</option>
        <option>Mathematics</option><option>Science</option><option>English Language</option>
        <option>ICT / Computing</option><option>Accounting</option><option>Other</option>
      </select>
      <textarea name="message" placeholder="Your message or question..."></textarea>
      <button type="submit" class="submit-btn">Send Enquiry</button>
    </form>
  </div>
</section>

<footer>
  <div class="footer-top">
    <div class="footer-brand">
      <div class="nav-logo" style="color:white"><div class="logo-icon">🎓</div><span style="font-family:'Playfair Display',serif;font-size:20px;color:white;">Activate Academy</span></div>
      <p>Beyond Grades Towards Greatness. Quality education for Grade 8 to A/L students.</p>
    </div>
    <div class="footer-col"><h4>Quick Links</h4><ul>
      <li><a href="#about">About Us</a></li><li><a href="#courses">Courses</a></li>
      <li><a href="#lecturers">Lecturers</a></li><li><a href="#contact">Contact</a></li>
    </ul></div>
    <div class="footer-col"><h4>Portals</h4><ul>
      <li><a href="login.php">Student Login</a></li><li><a href="login.php">Lecturer Login</a></li>
      <li><a href="login.php">Parent Login</a></li><li><a href="login.php">Admin Panel</a></li>
    </ul></div>
  </div>
  <div class="footer-bottom">
    <p>© 2025 Activate Academy | BSD Project — CODSE252F-034 &amp; CODSE252F-029 | NIBM</p>
  </div>
</footer>

<script>
// Smooth scroll for nav links
document.querySelectorAll('a[href^="#"]').forEach(function(link){
  link.addEventListener('click',function(e){
    var target=document.querySelector(this.getAttribute('href'));
    if(target){e.preventDefault();target.scrollIntoView({behavior:'smooth'});}
  });
});
</script>
</body>
</html>
