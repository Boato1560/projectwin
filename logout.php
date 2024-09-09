<?php
session_start();

// ตรวจสอบว่าผู้ใช้ล็อกอินอยู่หรือไม่
if (isset($_SESSION['user_id'])) {
    // เชื่อมต่อกับฐานข้อมูล
    $servername = "sql206.infinityfree.com";
    $db_username = "if0_37184789";
    $db_password = "cZq75jlVz3U";
    $dbname = "if0_37184789_loguser";

    $conn = new mysqli($servername, $db_username, $db_password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // ดึง ID ของผู้ใช้จากเซสชัน
    $user_id = $_SESSION['user_id'];

    // ลบหรือรีเซ็ตค่า session ID ในฐานข้อมูล
    $update_sql = "UPDATE users SET login_session = NULL WHERE id = ?";
    if ($stmt = $conn->prepare($update_sql)) {
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            // การอัปเดตสำเร็จ
            $stmt->close();
        } else {
            echo "Error executing update: " . $stmt->error;
        }
    } else {
        echo "Error preparing statement: " . $conn->error;
    }

    $conn->close();

    // ลบเซสชันทั้งหมด
    session_unset();
    session_destroy();
}

// รีไดเร็กต์ไปที่หน้าแรกหรือหน้าเข้าสู่ระบบ
header("Location: login.php");
exit();
?>
