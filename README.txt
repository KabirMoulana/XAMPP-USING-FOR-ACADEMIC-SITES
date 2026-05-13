# Activate Academy — XAMPP Setup Guide
# BSD Project | CODSE252F-034 & CODSE252F-029 | NIBM

==============================================================
  STEP-BY-STEP SETUP INSTRUCTIONS
==============================================================

STEP 1 — Copy the project folder
---------------------------------
Copy the entire "activate_academy" folder into:
  C:\xampp\htdocs\activate_academy

Your folder structure should look like this:
  C:\xampp\htdocs\activate_academy\
      index.php
      login.php
      logout.php
      database.sql
      css\
          style.css
      includes\
          db.php
          auth.php
      student\
          dashboard.php
      lecturer\
          dashboard.php
      admin\
          dashboard.php
      receptionist\
          dashboard.php
      parent\
          dashboard.php
      manager\
          dashboard.php
      director\
          dashboard.php
      uploads\
          receipts\
          materials\


STEP 2 — Start XAMPP
---------------------------------
1. Open XAMPP Control Panel
2. Click START next to "Apache"
3. Click START next to "MySQL"
Both should turn green.


STEP 3 — Create the Database
---------------------------------
1. Open your browser and go to:
   http://localhost/phpmyadmin

2. On the left side, click "New"

3. Type the database name:  activate_academy
   Then click "Create"

4. Click the "Import" tab at the top

5. Click "Choose File" and select:
   C:\xampp\htdocs\activate_academy\database.sql

6. Scroll down and click "Go"

You should see a success message with green checkmarks.


STEP 4 — Open the Website
---------------------------------
Go to:  http://localhost/activate_academy

You will see the Activate Academy public website.

Click "Login" to go to the login page.


STEP 5 — Login with Demo Accounts
---------------------------------
All demo accounts use the password:  password123

  USERNAME          ROLE
  --------          ----
  admin             Admin Panel
  manager           Manager Dashboard
  director          Director Dashboard
  lec_math          Lecturer Portal (Mathematics)
  lec_eng           Lecturer Portal (English)
  receptionist      Receptionist Dashboard
  student1          Student Portal (Aisha Farhan)
  student2          Student Portal (Rithik Sharma)
  parent1           Parent Portal (Mr. Farhan)


==============================================================
  WHAT EACH ACTOR CAN DO (matches the proposal)
==============================================================

STUDENT (student1 / student2)
  - View enrolled batches and schedules
  - Download study materials and join class links
  - View exam results and feedback with percentage
  - View attendance record
  - Upload monthly payment receipt
  - View payment history and approval status
  - View the points leaderboard
  - Read announcements

LECTURER (lec_math / lec_eng)
  - View assigned batches
  - Upload notes, recordings, or class links (Zoom/Meet)
  - Mark student attendance (present/absent/late)
  - Upload exam results with feedback per student
  - Give performance points to students with reason
  - Post announcements for a batch or all students

ADMIN (admin)
  - Add/delete users (any role)
  - Add subjects
  - Create batches and assign lecturers
  - Approve or reject student payment receipts
  - Post announcements
  - View all enquiries from the public contact form

RECEPTIONIST (receptionist)
  - Register new students (creates login account)
  - Assign students to batches
  - Record monthly payments (marks as approved)
  - View full payment history

PARENT (parent1)
  - View children's attendance
  - View children's exam results and feedback
  - View performance points earned
  - View payment status for each child
  - Read announcements

MANAGER (manager)
  - View all students and lecturers
  - Monitor all batches
  - View today's attendance
  - View recent payment records
  - See total revenue figure

DIRECTOR (director)
  - View key academy stats (students, lecturers, revenue)
  - Financial summary by month
  - Top performing students leaderboard
  - Batch report with student counts


==============================================================
  FILE EXPLANATIONS (for your project presentation)
==============================================================

database.sql      — Creates all tables and sample data
includes/db.php   — Connects to MySQL (used by every page)
includes/auth.php — Checks if user is logged in (session)
login.php         — Login form, checks username+password
logout.php        — Destroys session and redirects to login
index.php         — Public website homepage
css/style.css     — All styling shared by dashboard pages
student/          — Student portal dashboard
lecturer/         — Lecturer portal dashboard
admin/            — Admin control panel
receptionist/     — Receptionist operations panel
parent/           — Parent monitoring portal
manager/          — Manager operations view
director/         — Director reports view
uploads/          — Stores uploaded receipts and materials


==============================================================
  TECHNOLOGIES USED
==============================================================
  Frontend  : HTML, CSS, JavaScript
  Backend   : PHP (server-side scripting)
  Database  : MySQL (via phpMyAdmin)
  Server    : Apache (XAMPP localhost)


==============================================================
  COMMON ISSUES & FIXES
==============================================================

Problem: "Database connection failed"
Fix: Make sure MySQL is running in XAMPP Control Panel.
     Make sure you created the database named "activate_academy".

Problem: Page shows PHP code instead of website
Fix: Make sure Apache is running in XAMPP Control Panel.
     Make sure you placed the folder in htdocs, not Desktop.

Problem: File upload not working
Fix: Make sure the uploads/receipts and uploads/materials
     folders exist inside the project folder.

Problem: Parent portal shows "No children linked"
Fix: The parent account is matched by guardian name.
     parent1's name is "Mr. Farhan" — student1's guardian
     is also set to "Mr. Farhan" in the database.
     This matches them automatically.


==============================================================
  PROJECT INFO
==============================================================
  Project     : Academy Management System
  Module      : Business Solution Development (BSD)
  Institute   : National Institute of Business Management (NIBM)
  Programme   : Diploma in Software Engineering (DSE25.2F)
  Members     : CODSE252F-034 – Moulana S A K.
                CODSE252F-029 – M I M Ishfaq.
  Organization: Activate Academy, Nugegoda
==============================================================
