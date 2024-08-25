<?php
session_start();

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// เชื่อมต่อกับฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "testtimnow";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ดึงข้อมูล user_id จาก session
$user_id = $_SESSION['user_id'];
$success_message = "";
$error_message = "";

// เมื่อผู้ใช้ส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_new_password = $_POST['confirm_new_password'];

        // ตรวจสอบว่ารหัสผ่านใหม่และการยืนยันตรงกัน
        if ($new_password !== $confirm_new_password) {
            $error_message = "รหัสผ่านใหม่และการยืนยันไม่ตรงกัน!";
        } else {
            // ดึงรหัสผ่านปัจจุบันจากฐานข้อมูล
            $sql = "SELECT password FROM users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $stored_password = $row['password'];

                // ตรวจสอบว่ารหัสผ่านปัจจุบันตรงกัน
                if (password_verify($current_password, $stored_password)) {
                    // เข้ารหัสรหัสผ่านใหม่
                    $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

                    // อัปเดตรหัสผ่านในฐานข้อมูล
                    $update_sql = "UPDATE users SET password = ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("si", $hashed_new_password, $user_id);

                    if ($update_stmt->execute()) {
                        $success_message = "เปลี่ยนรหัสผ่านสำเร็จ!";
                    } else {
                        $error_message = "เกิดข้อผิดพลาดในการเปลี่ยนรหัสผ่าน!";
                    }

                    $update_stmt->close();
                } else {
                    $error_message = "รหัสผ่านปัจจุบันไม่ถูกต้อง!";
                }
            } else {
                $error_message = "ไม่พบข้อมูลของผู้ใช้!";
            }

            $stmt->close();
        }
    } elseif (isset($_POST['back_to_home'])) {
        // ดึงข้อมูล role ของผู้ใช้
        $role_sql = "SELECT role FROM users WHERE id = ?";
        $role_stmt = $conn->prepare($role_sql);
        $role_stmt->bind_param("i", $user_id);
        $role_stmt->execute();
        $role_result = $role_stmt->get_result();

        if ($role_result->num_rows > 0) {
            $role_row = $role_result->fetch_assoc();
            $role = $role_row['role'];

            // เปลี่ยนเส้นทางตาม role
            if ($role === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: index.php");
            }
            exit();
        }

        $role_stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เปลี่ยนรหัสผ่าน</title>
    <link rel="icon" href="img/ddlakwin.jpg" type="image/x-icon">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            box-sizing: border-box;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            margin-bottom: 20px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
            box-sizing: border-box;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            box-sizing: border-box;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .btn {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-b {
            width: 100%;
            padding: 10px;
            background-color: #f39c12;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover, .btn-b:hover {
            background-color: #0056b3;
        }
        .success-message {
            color: green;
            margin-bottom: 15px;
            text-align: center;
        }
        .error-message {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>เปลี่ยนรหัสผ่าน</h2>
        <?php if (!empty($success_message)) { ?>
            <p class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
        <?php } ?>
        <?php if (!empty($error_message)) { ?>
            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <?php } ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="current_password">รหัสผ่านปัจจุบัน</label>
                <input type="password" name="current_password" id="current_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">รหัสผ่านใหม่</label>
                <input type="password" name="new_password" id="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_new_password">ยืนยันรหัสผ่านใหม่</label>
                <input type="password" name="confirm_new_password" id="confirm_new_password" required>
            </div>
            <button type="submit" name="change_password" class="btn">เปลี่ยนรหัสผ่าน</button><br><br>
        </form>
        <form method="POST" action="">
            <button type="submit" name="back_to_home" class="btn-b">กลับไปที่หน้าแรก</button>
        </form>
    </div>
</body>
</html>
