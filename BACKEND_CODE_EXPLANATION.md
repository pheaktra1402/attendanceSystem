# 📚 Attendance System — Complete Backend Code Learning Guide

This document is designed to teach beginners how the backend code in this **PHP & MySQL Attendance System** works. You can read it directly, or copy and paste it into **Microsoft Word** for studying.

---

## 📑 Table of Contents
1. [Backend Overview & How PHP Works with MySQL](#1-backend-overview)
2. [Database Connection (`init/db.php`)](#2-database-connection)
3. [Session & Global Initialization (`init/init.php`)](#3-session--global-initialization)
4. [Helper Functions & Security (`init/functions.php`)](#4-helper-functions--security)
5. [User Authentication Backend (`login.php`, `register.php`, `logout.php`)](#5-user-authentication)
6. [Understanding CRUD Operations](#6-understanding-crud-operations)
   - [Create: Adding a Student (`add_student.php`)](#create-adding-a-student)
   - [Read: Fetching & Searching Records (`students.php`)](#read-fetching--searching-records)
   - [Update: Modifying Records (`edit_student.php`)](#update-modifying-records)
   - [Delete: Removing Records (`delete_student.php`)](#delete-removing-records)
7. [Advanced Logic: Batch Attendance & MySQL Joins (`attendance.php`)](#7-advanced-logic-batch-attendance)
8. [Summary Cheat Sheet for PHP Backend Developers](#8-summary-cheat-sheet)

---

<a name="1-backend-overview"></a>
## 1. 🌐 Backend Overview & How PHP Works with MySQL

When a user opens a web page (e.g., `students.php`):
1. **The Browser Sends a Request** to the XAMPP Apache Web Server.
2. **PHP Executes on the Server**: PHP reads code line-by-line, talks to the **MySQL Database**, fetches or saves data.
3. **HTML Output Sent to Browser**: PHP generates clean HTML and sends it back to the user's screen.

```
[ User Browser ]  <--->  [ XAMPP Apache / PHP Engine ]  <--->  [ MySQL Database ]
```

---

<a name="2-database-connection"></a>
## 2. 🔌 Database Connection (`init/db.php`)

To communicate with MySQL, PHP uses an extension called **MySQLi** (*MySQL Improved*).

### Code Breakdown:
```php
<?php
// 1. Define Connection Variables
$host     = 'localhost';        // Server hostname
$dbname   = 'attendanceSystem'; // Name of MySQL database
$user     = 'root';             // Default XAMPP username
$password = '';                 // Default XAMPP password (blank)

// 2. Create the Database Object
$db = new mysqli($host, $user, $password, $dbname);

// 3. Check for Connection Errors
if ($db->connect_error) {
    die("Database Connection Failed: " . $db->connect_error);
}

// 4. Set Character Encoding to UTF-8
$db->set_charset("utf8mb4");
?>
```

### 💡 Key Concepts:
- **`new mysqli(...)`**: Creates a connection object (`$db`).
- **`$db->connect_error`**: Checks if XAMPP MySQL is turned off or if database credentials are wrong.
- **`die(...)`**: Immediately stops running PHP and prints an error message.

---

<a name="3-session--global-initialization"></a>
## 3. ⚙️ Session & Global Initialization (`init/init.php`)

HTTP is "stateless", meaning it forgets who you are every time you refresh a page. **Sessions** store user info (like `user_id` after login) on the server across multiple page visits.

### Code Breakdown:
```php
<?php
// 1. Start Session if it hasn't been started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Enable Output Buffering
ob_start();

// 3. Include Database & Functions using __DIR__ (absolute directory path)
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
?>
```

### 💡 Key Concepts:
- **`session_start()`**: Starts or resumes a session. Allows us to use `$_SESSION['user_id']`.
- **`ob_start()`**: Buffers page output in memory so PHP headers (`header("Location: ...")`) don't fail if HTML was printed earlier.
- **`__DIR__`**: Returns the exact folder path of `init.php`. Using `require_once __DIR__ . '/db.php';` guarantees PHP finds `db.php` from anywhere.

---

<a name="4-helper-functions--security"></a>
## 4. 🛡️ Helper Functions & Security (`init/functions.php`)

Security is crucial in web development. Two major attacks are:
1. **XSS (Cross-Site Scripting)**: Users submitting malicious `<script>` code in form fields.
2. **SQL Injection**: Users injecting SQL code into inputs to steal data.

### Code Breakdown:
```php
<?php
// Function 1: Input Sanitization
function sanitize($data) {
    global $db;
    $data = trim($data);                            // Remove extra spaces
    $data = stripslashes($data);                    // Remove backslashes \
    $data = htmlspecialchars($data, ENT_QUOTES);    // Convert < and > into &lt; and &gt;
    return $db->real_escape_string($data);          // Escape special SQL characters
}

// Function 2: Redirect helper
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Function 3: Authentication Access Guard
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        set_flash_message('danger', 'Please log in to access this page.');
        redirect('login.php');
    }
}
?>
```

---

<a name="5-user-authentication"></a>
## 5. 🔑 User Authentication Backend (`login.php`, `register.php`, `logout.php`)

### A. How Password Hashing & Registration Work (`register.php`)
We NEVER store plain-text passwords in databases. We use `password_hash()`:

```php
// 1. Hash the user's password securely
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 2. Use a Prepared Statement to insert the new user
$stmt = $db->prepare("INSERT INTO users (full_name, username, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $full_name, $username, $hashed_password);
$stmt->execute();
```

### B. How Login Verification Works (`login.php`)
```php
// 1. Fetch user by username using Prepared Statement
$stmt = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // 2. Verify hashed password
    if (password_verify($password, $user['password'])) {
        // Save session variables
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];

        redirect('index.php');
    }
}
```

### 💡 What is a Prepared Statement?
Instead of building raw SQL strings like `"SELECT * FROM users WHERE username = '$username'"`, we use **`?` placeholders**. The database engine compiles the SQL logic separately from user inputs, preventing SQL injection completely!

---

<a name="6-understanding-crud-operations"></a>
## 6. 🔄 Understanding CRUD Operations

**CRUD** stands for **C**reate, **R**ead, **U**pdate, **D**elete.

---

### <a name="create-adding-a-student"></a>➕ Create: Adding a Student (`add_student.php`)

```php
// 1. Read POST data from form
$student_code = sanitize($_POST['student_code']);
$full_name    = sanitize($_POST['full_name']);
$email        = sanitize($_POST['email']);
$gender       = sanitize($_POST['gender']);
$class_name   = sanitize($_POST['class_name']);

// 2. Prepare SQL INSERT statement
$stmt = $db->prepare("INSERT INTO students (student_code, full_name, email, gender, class_name) VALUES (?, ?, ?, ?, ?)");

// 3. Bind parameters ("sssss" = 5 strings)
$stmt->bind_param("sssss", $student_code, $full_name, $email, $gender, $class_name);

// 4. Execute query
if ($stmt->execute()) {
    set_flash_message('success', 'Student added successfully!');
    redirect('students.php');
}
```

---

### <a name="read-fetching--searching-records"></a>📖 Read: Fetching & Searching Records (`students.php`)

```php
// 1. Check if user entered a search keyword
$search = sanitize($_GET['search'] ?? '');

if (!empty($search)) {
    // Filtered query
    $sql = "SELECT * FROM students 
            WHERE student_code LIKE '%$search%' 
               OR full_name LIKE '%$search%' 
            ORDER BY id DESC";
} else {
    // Fetch all students query
    $sql = "SELECT * FROM students ORDER BY id DESC";
}

// 2. Execute query
$students_result = $db->query($sql);

// 3. Loop through result set in HTML table
while ($student = $students_result->fetch_assoc()) {
    echo $student['full_name'];
    echo $student['student_code'];
}
```

---

### <a name="update-modifying-records"></a>✏️ Update: Modifying Records (`edit_student.php`)

```php
// 1. Read student ID from URL parameter (e.g. edit_student.php?id=5)
$student_id = (int)$_GET['id'];

// 2. Handle form submit (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name  = sanitize($_POST['full_name']);
    $class_name = sanitize($_POST['class_name']);

    // 3. Prepare UPDATE statement
    $stmt = $db->prepare("UPDATE students SET full_name = ?, class_name = ? WHERE id = ?");
    $stmt->bind_param("ssi", $full_name, $class_name, $student_id);
    $stmt->execute();

    redirect('students.php');
}
```

---

### <a name="delete-removing-records"></a>🗑️ Delete: Removing Records (`delete_student.php`)

```php
// 1. Read student ID from URL parameter (delete_student.php?id=5)
$student_id = (int)$_GET['id'];

// 2. Prepare DELETE statement
$stmt = $db->prepare("DELETE FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();

set_flash_message('success', 'Student record deleted.');
redirect('students.php');
```

---

<a name="7-advanced-logic-batch-attendance"></a>
## 7. 🚀 Advanced Logic: Batch Attendance & MySQL Joins (`attendance.php`)

Taking attendance for an entire class in a single button click requires **Batch Input Arrays** and **MySQL JOINs**.

### A. Form Radio Button Naming Trick
In HTML, form fields are named as array keys:
```html
<!-- Radio button for Student ID 10 -->
<input type="radio" name="attendance[10]" value="Present">

<!-- Radio button for Student ID 11 -->
<input type="radio" name="attendance[11]" value="Absent">
```

When submitted, PHP receives `$_POST['attendance']` as an associative array:
```php
$_POST['attendance'] = [
    10 => 'Present',
    11 => 'Absent'
];
```

### B. Smart MySQL `ON DUPLICATE KEY UPDATE`
Instead of checking if attendance already exists for that day, MySQL can insert new rows or update existing rows in one query!

```php
$stmt = $db->prepare("INSERT INTO attendance (student_id, attendance_date, status, remarks) 
                      VALUES (?, ?, ?, ?) 
                      ON DUPLICATE KEY UPDATE status = VALUES(status), remarks = VALUES(remarks)");

foreach ($_POST['attendance'] as $student_id => $status) {
    $remark = $_POST['remarks'][$student_id] ?? '';
    
    $stmt->bind_param("isss", $student_id, $attendance_date, $status, $remark);
    $stmt->execute();
}
```

### C. Fetching Attendance with Table JOINs (`attendance_report.php`)
Attendance records store `student_id = 10`. To display the student's **Name** and **Class**, we use a `JOIN`:

```sql
SELECT 
    attendance.id,
    attendance.attendance_date,
    attendance.status,
    students.student_code,
    students.full_name,
    students.class_name
FROM attendance
JOIN students ON attendance.student_id = students.id
ORDER BY attendance.attendance_date DESC;
```

---

<a name="8-summary-cheat-sheet"></a>
## 8. 📋 Summary Cheat Sheet for PHP Backend Developers

| Concept | Code Syntax | Description |
|---|---|---|
| **Database Connection** | `$db = new mysqli($host, $user, $pass, $name)` | Connects PHP to MySQL server |
| **Prepared Statement** | `$stmt = $db->prepare("SELECT * FROM table WHERE id = ?")` | Protects against SQL Injection |
| **Bind Parameters** | `$stmt->bind_param("i", $id)` | `"i"` = Integer, `"s"` = String, `"d"` = Double |
| **Fetch Row** | `$row = $result->fetch_assoc()` | Converts MySQL row into PHP array |
| **Hash Password** | `password_hash($pass, PASSWORD_DEFAULT)` | Encrypts password for database |
| **Verify Password** | `password_verify($input_pass, $db_hash)` | Validates user login password |
| **Redirect** | `header("Location: target.php"); exit();` | Sends browser to a new page |
| **Read GET Query** | `$_GET['search']` | Reads parameters from URL (`page.php?search=alex`) |
| **Read POST Form** | `$_POST['username']` | Reads data submitted by HTML forms |

---

### 💡 Tips for Copying into Word:
1. Open **Microsoft Word**.
2. Copy the contents of this document.
3. Paste into Word using **Keep Source Formatting** or **Merge Formatting**.
4. You can print or save it as a PDF for offline learning!
