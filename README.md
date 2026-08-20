# Beginner-Friendly Attendance Management System (PHP & MySQL)

A modern, easy-to-understand **Student Attendance System** with full **CRUD** (Create, Read, Update, Delete) functionality built using PHP, MySQL (MySQLi), and Bootstrap 5.

---

## Features Overview

1. **Dashboard (`index.php`)**:
   - Total Students count
   - Present, Absent, and Late statistics for today
   - Quick action shortcuts and recent activity log

2. **Student Management (`students.php`, `add_student.php`, `edit_student.php`, `delete_student.php`)**:
   - **Create**: Add new students with Code, Full Name, Class, Gender, Email
   - **Read**: View student list with live search by Name, Code, or Class
   - **Update**: Edit existing student records
   - **Delete**: Delete a student (automatically cleans up their attendance history)

3. **Attendance Management (`attendance.php`, `attendance_report.php`)**:
   - **Take Daily Attendance**: Batch-mark students as Present, Absent, or Late for any date with quick "Mark All" helpers
   - **Reports & Filter**: View attendance history filtered by date, student name, or class name
   - **Update / Delete**: Change individual student status or delete individual attendance logs

4. **Authentication & User System (`login.php`, `register.php`, `logout.php`)**:
   - Secure login & registration with hashed passwords (`password_hash`) and session protection

---

## XAMPP Setup & How to Run

### Step 1: Place Files in XAMPP `htdocs`
Ensure this project folder is located inside your XAMPP `htdocs` directory:
`C:\xampp\htdocs\attendanceSystem\` or `D:\xampp\htdocs\attendanceSystem\`

### Step 2: Start Apache and MySQL in XAMPP
1. Open **XAMPP Control Panel**.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.

### Step 3: Create & Import Database
1. Open your browser and go to `http://localhost/phpmyadmin/`.
2. Click on **Import** in the top menu.
3. Select the `schema.sql` file located in `htdocs/attendanceSystem/schema.sql`.
4. Click **Go** / **Import** to create the `attendanceSystem` database and tables automatically.

### Step 4: Open the Application
Navigate to the following URL in your web browser:
`http://localhost/attendanceSystem/`

---

## Default Login Credentials

- **Username**: `admin`
- **Password**: `admin123`

*(You can also register a new account on `http://localhost/attendanceSystem/register.php`)*

---

## Code Structure (Beginner Friendly)

- `schema.sql` — MySQL database creation script with sample data
- `init/db.php` — Database connection setup using `mysqli`
- `init/init.php` — Session initialization & global includes
- `init/functions.php` — Helper functions (`sanitize`, `redirect`, `check_login`, `set_flash_message`)
- `includes/header.php` — Page header, Bootstrap 5 CSS, FontAwesome icons, custom styles
- `includes/navbar.php` — Responsive top navigation menu
- `includes/footer.php` — Footer layout & JavaScript files
- `index.php` — Main Dashboard with statistics counters
- `students.php` — Read & Search student roster
- `add_student.php` — Create student form
- `edit_student.php` — Update student form
- `delete_student.php` — Delete student handler
- `attendance.php` — Take daily attendance form
- `attendance_report.php` — View and edit attendance reports
