<?php
session_start();

// ตรวจสอบข้อมูลการล็อกอิน
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    // เชื่อมต่อกับฐานข้อมูล
    $servername = "localhost";
    $db_username = "root";
    $db_password = "";
    $dbname = "testtimnow";

    $conn = new mysqli($servername, $db_username, $db_password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // ตรวจสอบข้อมูลผู้ใช้
    $phone = $conn->real_escape_string($phone); // ป้องกัน SQL Injection
    $sql = "SELECT id, name, phone, password, role, login_session FROM users WHERE phone = '$phone'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $stored_hash = $row['password'];
        $role = $row['role']; // ดึงข้อมูล role ของผู้ใช้
        $login_session = $row['login_session']; // ดึงสถานะการล็อกอินของผู้ใช้
        $user_id = $row['id']; // ดึง ID ของผู้ใช้

        // ตรวจสอบรหัสผ่าน
        if (password_verify($password, $stored_hash)) {
            // ตรวจสอบว่าใช้งานจากที่อื่นอยู่หรือไม่
            if (!empty($login_session) && $login_session !== session_id()) {
                echo "มีการใช้งานบัญชีนี้อยู่ในอุปกรณ์อื่น!";
                exit();
            }

            // บันทึก session ID ปัจจุบันลงในฐานข้อมูล
            $new_session = session_id();
            $update_sql = "UPDATE users SET login_session = ? WHERE id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("si", $new_session, $user_id);
            $stmt->execute();
            $stmt->close();

            $_SESSION['user_id'] = $user_id;
            $_SESSION['phone'] = $row['phone'];
            $_SESSION['role'] = $role; // เก็บ role ลงในเซสชัน

            // Redirect ตาม role ของผู้ใช้
            if ($role === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            echo "เบอร์โทรศัพท์หรือรหัสผ่านไม่ถูกต้อง!";
        }
    } else {
        echo "เบอร์โทรศัพท์หรือรหัสผ่านไม่ถูกต้อง!";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="img/ddlakwin.jpg" type="image/x-icon">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            box-sizing: border-box;
        }
        h2 {
            text-align: center;
            color: #666;
            margin-bottom: 20px;
        }
        .input-group {
            position: relative;
            margin-bottom: 15px;
        }
        .input-group input {
            width: 100%;
            padding: 10px;
            padding-left: 40px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .input-group .icon {
            position: absolute;
            top: 50%;
            left: 10px;
            transform: translateY(-50%);
            color: #999;
        }
        button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-bottom: 10px;
        }
        .login-btn {
            background-color: #3498db;
            color: #fff;
        }
        .alert-btn {
            background-color: #f39c12;
            color: #fff;
        }
        .alert-btn:hover, .login-btn:hover {
            opacity: 0.9;
        }
        .links {
            text-align: center;
        }
        .links a {
            color: #3498db;
            text-decoration: none;
            margin: 0 10px;
        }
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>เข้าสู่ระบบ</h2>
        <form action="login.php" method="POST">
            <div class="input-group">
                <input type="text" name="phone" placeholder="เบอร์มือถือ" required>
                <span class="icon"><i class="fa fa-phone"></i></span>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="รหัสผ่าน" required>
                <span class="icon"><i class="fa fa-lock"></i></span>
            </div>
            <a href="contact_admin.html"><button type="button" class="alert-btn">ถ้าสมัครไม่ได้ ติดต่อแอดมินได้ครับ คลิกที่นี่ครับ!!</button></a>
            <button type="submit" class="login-btn">เข้าสู่ระบบ</button>
        </form>
        <div class="links">
            <a href="register.php">สมัครสมาชิก</a>
            <a href="index.php">กลับไปหน้าแรก</a>
        </div>
    </div>
</body>
</html>
