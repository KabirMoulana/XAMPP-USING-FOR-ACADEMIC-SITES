-- ============================================================
--  ACTIVATE ACADEMY - DATABASE SETUP
--  1. Open phpMyAdmin  (http://localhost/phpmyadmin)
--  2. Click "New" to create database named: activate_academy
--  3. Click "Import" tab and upload this file
-- ============================================================

CREATE DATABASE IF NOT EXISTS activate_academy;
USE activate_academy;

CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)  UNIQUE NOT NULL,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('admin','student','lecturer','receptionist','parent','manager','director') NOT NULL,
    full_name   VARCHAR(100) NOT NULL,
    email       VARCHAR(100),
    phone       VARCHAR(20),
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE subjects (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    description TEXT,
    level       VARCHAR(50)
);

CREATE TABLE batches (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    subject_id  INT,
    lecturer_id INT,
    schedule    VARCHAR(100),
    type        ENUM('online','physical','both') DEFAULT 'both',
    FOREIGN KEY (subject_id)  REFERENCES subjects(id),
    FOREIGN KEY (lecturer_id) REFERENCES users(id)
);

CREATE TABLE students (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNIQUE,
    grade       VARCHAR(20),
    guardian    VARCHAR(100),
    joined_date DATE,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE enrollments (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    student_id  INT,
    batch_id    INT,
    enrolled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (batch_id)   REFERENCES batches(id)
);

CREATE TABLE attendance (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    student_id  INT,
    batch_id    INT,
    date        DATE,
    status      ENUM('present','absent','late') DEFAULT 'present',
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (batch_id)   REFERENCES batches(id)
);

CREATE TABLE payments (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    student_id      INT,
    batch_id        INT,
    amount          DECIMAL(10,2),
    month           VARCHAR(20),
    receipt_image   VARCHAR(255),
    status          ENUM('pending','approved','rejected') DEFAULT 'pending',
    recorded_by     INT,
    paid_at         DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id)  REFERENCES users(id),
    FOREIGN KEY (batch_id)    REFERENCES batches(id),
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);

CREATE TABLE materials (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    batch_id    INT,
    lecturer_id INT,
    title       VARCHAR(200),
    file_path   VARCHAR(255),
    type        ENUM('notes','recording','link') DEFAULT 'notes',
    class_link  VARCHAR(500),
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (batch_id)    REFERENCES batches(id),
    FOREIGN KEY (lecturer_id) REFERENCES users(id)
);

CREATE TABLE results (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    student_id  INT,
    batch_id    INT,
    exam_name   VARCHAR(100),
    marks       DECIMAL(5,2),
    total_marks DECIMAL(5,2),
    feedback    TEXT,
    uploaded_by INT,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id)  REFERENCES users(id),
    FOREIGN KEY (batch_id)    REFERENCES batches(id),
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

CREATE TABLE points (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    student_id  INT,
    points      INT DEFAULT 0,
    reason      VARCHAR(200),
    given_by    INT,
    given_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (given_by)   REFERENCES users(id)
);

CREATE TABLE announcements (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200),
    content     TEXT,
    posted_by   INT,
    batch_id    INT NULL,
    posted_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (posted_by) REFERENCES users(id),
    FOREIGN KEY (batch_id)  REFERENCES batches(id)
);

CREATE TABLE enquiries (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100),
    email        VARCHAR(100),
    phone        VARCHAR(20),
    subject      VARCHAR(100),
    message      TEXT,
    status       ENUM('new','read','replied') DEFAULT 'new',
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ---- SAMPLE DATA ----
INSERT INTO subjects (name, description, level) VALUES
('Mathematics','Algebra, Calculus, Statistics','O/L - A/L'),
('Science','Physics, Chemistry, Biology','Grade 8 - O/L'),
('English','Language and Literature','Grade 8 - A/L'),
('ICT / Computing','Computer Science and ICT','O/L - A/L'),
('Accounting','Financial accounts and bookkeeping','A/L');

-- All demo passwords = "password123"
INSERT INTO users (username,password,role,full_name,email,phone) VALUES
('admin',       MD5('password123'),'admin',       'System Admin',     'admin@activate.lk',     '0771000001'),
('manager',     MD5('password123'),'manager',     'Academy Manager',  'manager@activate.lk',   '0771000002'),
('director',    MD5('password123'),'director',    'Mr. Director',     'director@activate.lk',  '0771000003'),
('lec_math',    MD5('password123'),'lecturer',    'Mr. Karunarathne', 'math@activate.lk',      '0771000004'),
('lec_eng',     MD5('password123'),'lecturer',    'Ms. Perera',       'english@activate.lk',   '0771000005'),
('receptionist',MD5('password123'),'receptionist','Reception Staff',  'reception@activate.lk', '0771000006'),
('student1',    MD5('password123'),'student',     'Aisha Farhan',     'aisha@gmail.com',       '0771000007'),
('student2',    MD5('password123'),'student',     'Rithik Sharma',    'rithik@gmail.com',      '0771000008'),
('parent1',     MD5('password123'),'parent',      'Mr. Farhan',       'farhan@gmail.com',      '0771000009');

INSERT INTO students (user_id,grade,guardian,joined_date) VALUES
(7,'O/L','Mr. Farhan','2025-01-10'),
(8,'A/L','Mrs. Sharma','2025-01-15');

INSERT INTO batches (name,subject_id,lecturer_id,schedule,type) VALUES
('Math O/L Batch A',   1,4,'Monday & Wednesday 4PM','both'),
('English A/L Batch A',3,5,'Tuesday & Friday 5PM',  'online');

INSERT INTO enrollments (student_id,batch_id) VALUES (7,1),(8,1),(7,2);

INSERT INTO attendance (student_id,batch_id,date,status) VALUES
(7,1,CURDATE(),'present'),(8,1,CURDATE(),'absent');

INSERT INTO results (student_id,batch_id,exam_name,marks,total_marks,feedback,uploaded_by) VALUES
(7,1,'April Monthly Exam',88,100,'Excellent work! Keep it up.',4),
(8,1,'April Monthly Exam',72,100,'Good effort. Focus on algebra.',4);

INSERT INTO points (student_id,points,reason,given_by) VALUES
(7,100,'Top scorer in April exam',4),(7,50,'Perfect attendance this week',4),
(8,80,'Consistent attendance',4),(8,30,'Good homework submission',4);

INSERT INTO announcements (title,content,posted_by,batch_id) VALUES
('New Batch Starting June 2025','O/L Maths batch now open for enrolment. Contact reception.',1,NULL),
('Monthly Exam – May 2025','The monthly exam will be held on May 25th. Prepare well!',4,1);

INSERT INTO enquiries (name,email,phone,subject,message) VALUES
('Test User','test@gmail.com','0771234567','Mathematics','I want to enrol in the Maths batch.');

INSERT INTO payments (student_id,batch_id,amount,month,status,recorded_by) VALUES
(7,1,3500,'May 2025','approved',6),
(8,1,3500,'May 2025','pending',6);
