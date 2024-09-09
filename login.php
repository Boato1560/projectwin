<?php
session_start();

// ตรวจสอบข้อมูลการล็อกอิน
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    // เชื่อมต่อกับฐานข้อมูล
    $servername = "sql206.infinityfree.com";
    $db_username = "if0_37184789";
    $db_password = "cZq75jlVz3U";
    $dbname = "if0_37184789_loguser";

    $conn = new mysqli($servername, $db_username, $db_password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // ใช้ Prepared Statements เพื่อป้องกัน SQL Injection
    $sql = "SELECT id, name, phone, password, role, login_session FROM users WHERE phone = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    // เพิ่มการตรวจสอบข้อผิดพลาด SQL
    if (!$result) {
        error_log("Error executing query: " . $conn->error);
        $_SESSION['login_error'] = "เกิดข้อผิดพลาด โปรดลองอีกครั้ง!";
        header("Location: login.php");
        exit();
    }
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $stored_hash = $row['password'];
        $role = $row['role'];
        $login_session = $row['login_session'];
        $user_id = $row['id'];

        // ตรวจสอบรหัสผ่าน
        if (password_verify($password, $stored_hash)) {
            // ตรวจสอบว่าใช้งานจากที่อื่นอยู่หรือไม่
            if (!empty($login_session) && $login_session !== session_id()) {
                // ทำการอัปเดต session_id ในฐานข้อมูลเพื่อให้เข้าสู่ระบบบนอุปกรณ์ใหม่
                session_regenerate_id(true);
                $new_session = session_id();
                $update_sql = "UPDATE users SET login_session = ? WHERE id = ?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("si", $new_session, $user_id);
                $stmt->execute();
                $stmt->close();
            }

            // บันทึก session ID ปัจจุบันลงในฐานข้อมูล
            session_regenerate_id(true);
            $new_session = session_id();
            $update_sql = "UPDATE users SET login_session = ? WHERE id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("si", $new_session, $user_id);
            $stmt->execute();
            $stmt->close();

            // ตั้งค่า session สำหรับผู้ใช้
            $_SESSION['user_id'] = $user_id;
            $_SESSION['phone'] = $row['phone'];
            $_SESSION['role'] = $role; 
            $_SESSION['last_access'] = time(); // ตั้งค่าเวลาเข้าถึงล่าสุด
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];

            // Redirect ตาม role ของผู้ใช้
            if ($role === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $_SESSION['login_error'] = "เบอร์โทรศัพท์หรือรหัสผ่านไม่ถูกต้อง!";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['login_error'] = "ไม่มีข้อมูลเบอร์นี้ในระบบ!";
        header("Location: login.php");
        exit();
    }

    $conn->close();
}

// Session timeout after 15 minutes
$timeout_duration = 900;

if (isset($_SESSION['last_access']) && (time() - $_SESSION['last_access']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=true");
    exit();
}

$_SESSION['last_access'] = time(); // Reset last access time

// ตรวจสอบ user-agent และ IP address เพื่อป้องกันการโจมตีแบบ session hijacking
if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

if (isset($_SESSION['ip_address']) && $_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" href="img/icon3.png" type="image/x-icon">
    <style>
       body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f2f3f8;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            display: flex;
            align-items: center;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 900px;
            width: 100%;
            box-sizing: border-box;
        }

        .image-container {
            flex: 1;
            text-align: center;
        }

        .image-container img {
            width: 100%;
            max-width: 300px; /* ปรับขนาดรูปภาพที่ต้องการ */
            border-radius: 10px;
        }

        .login-container {
            flex: 1;
            padding: 20px;
            text-align: center; /* Center align all text in the container */
        }

        .login-container h2 {
            font-size: 24px;
            margin-bottom: 20px; /* Adjust spacing as needed */
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group input {
            width: 100%;
            padding: 15px;
            padding-right: 40px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            background-color: #f9f9f9;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        .input-group input:focus {
            border-color: #3498db;
            outline: none;
        }

        .input-group .icon {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            color: #e74c3c; /* สีแดงสำหรับไอคอนแจ้งเตือน */
            font-size: 18px;
            display: none;
        }

        .input-group .error-message {
            color: #e74c3c; /* สีแดงสำหรับข้อความแสดงข้อผิดพลาด */
            font-size: 14px;
            margin-top: 5px;
            display: none;
        }

        .show-password {
            display: flex;
            align-items: center;
            margin-top: -10px;
            font-size: 14px;
        }

        .show-password input {
            margin-right: 5px;
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            background-color: #3498db; /* สีฟ้าสำหรับปุ่ม */
            color: #fff;
            transition: background-color 0.3s;
            margin-top: 20px;
        }

        .login-btn:hover {
            background-color: #2980b9; /* สีฟ้าเข้มเมื่อวางเมาส์ */
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

        .error-message-box {
            background-color: #f8d7da; /* Red background */
            color: #721c24; /* Dark red text */
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb; /* Border color */
            text-align: center; /* Center text */
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="image-container">
            <img src="img/ddlakwin.jpg" alt="Login Image">
        </div>
        <div class="login-container">
            <h2>เข้าสู่ระบบ</h2>
            <?php if(isset($_SESSION['login_error'])): ?>
                <div class="error-message-box">
                    <?php 
                        echo $_SESSION['login_error']; 
                        unset($_SESSION['login_error']);
                    ?>
                </div>
            <?php endif; ?>
            <form method="post" action="">
                <div class="input-group">
                    <input type="text" name="phone" placeholder="เบอร์โทรศัพท์" required>
                    <i class="fas fa-exclamation-circle icon"></i>
                    <div class="error-message">กรุณากรอกเบอร์โทรศัพท์</div>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="รหัสผ่าน" required>
                    <i class="fas fa-exclamation-circle icon"></i>
                    <div class="error-message">กรุณากรอกรหัสผ่าน</div>
                </div>
                <div class="show-password">
                    <input type="checkbox" onclick="togglePassword()"> แสดงรหัสผ่าน
                </div>
                <button type="submit" class="login-btn">เข้าสู่ระบบ</button>
            </form>
            <div class="links">
                <a href="#">ลืมรหัสผ่าน?</a>
                <a href="register.php">สมัครสมาชิก</a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            var passwordField = document.querySelector('input[name="password"]');
            var type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
        }
    </script>
</body>
</html>
