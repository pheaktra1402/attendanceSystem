<?php
require_once __DIR__ . '/init/init.php';

// Ensure user is logged in
check_login();

$student_id = (int)($_GET['id'] ?? 0);

if ($student_id > 0) {
    // Fetch name for flash message
    $stmt = $db->prepare("SELECT full_name FROM students WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows > 0) {
        $student_name = $res->fetch_assoc()['full_name'];

        // Perform Delete query
        $delete_stmt = $db->prepare("DELETE FROM students WHERE id = ?");
        $delete_stmt->bind_param("i", $student_id);
        
        if ($delete_stmt->execute()) {
            set_flash_message('success', "Student '$student_name' deleted successfully.");
        } else {
            set_flash_message('danger', "Error deleting student record.");
        }
        $delete_stmt->close();
    } else {
        set_flash_message('danger', "Student record not found.");
    }
    $stmt->close();
} else {
    set_flash_message('danger', "Invalid student ID.");
}

redirect('students.php');
?>
