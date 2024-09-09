<?php
$error = ''; // ใช้สำหรับเก็บข้อความผิดพลาด

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = $_POST['phone'];

    // เชื่อมต่อกับฐานข้อมูล
    $servername = "sql206.infinityfree.com";
    $db_username = "if0_37184789";
    $db_password = "cZq75jlVz3U";
    $dbname = "if0_37184789_loguser";

    $conn = new mysqli($servername, $db_username, $db_password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // ตรวจสอบว่ารหัสผ่านและการยืนยันรหัสผ่านตรงกันหรือไม่
    if ($password !== $confirm_password) {
        $error = "รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน!";
    } else {
        // ตรวจสอบว่ามีชื่อผู้ใช้ที่ลงทะเบียนแล้วหรือไม่
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $error = "ชื่อผู้ใช้นี้ถูกใช้แล้ว!";
        } else {
            // ตรวจสอบว่ามีเบอร์โทรศัพท์ที่ลงทะเบียนแล้วหรือไม่
            $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE phone = ?");
            $stmt->bind_param("s", $phone);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if ($count > 0) {
                $error = "เบอร์โทรศัพท์นี้ถูกใช้แล้ว!";
            } else {
                // แฮชรหัสผ่าน
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // เก็บข้อมูลผู้ใช้ใหม่ลงในฐานข้อมูล
                $stmt = $conn->prepare("INSERT INTO users (name, password, phone) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $name, $hashed_password, $phone);

                if ($stmt->execute()) {
                    // รีไดเรกต์ไปยัง success.php พร้อมส่งข้อมูล
                    header("Location: success.php?username=" . urlencode($name) . "&phone=" . urlencode($phone));
                    exit();
                } else {
                    $error = "เกิดข้อผิดพลาด: " . $stmt->error;
                }

                $stmt->close();
            }
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก</title>
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
        .register-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 500px;
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
        .register-btn {
            background-color: #3498db;
            color: #fff;
        }
        .alert-btn {
            background-color: #f39c12;
            color: #fff;
        }
        .alert-btn:hover, .register-btn:hover {
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
        .error-message {
            color: #e74c3c;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>สมัครสมาชิก</h2>
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form action="register.php" method="POST">
            <div class="input-group">
                <input type="text" name="name" placeholder="ชื่อ-นามสกุล" required>
                <span class="icon"><i class="fa fa-user"></i></span>
            </div>
            <div class="input-group">
                <input type="text" name="phone" placeholder="เบอร์มือถือ (ใส่เฉพาะตัวเลขไม่ต้องมีขีด)" required>
                <span class="icon"><i class="fa fa-phone"></i></span>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="รหัสผ่าน" required>
                <span class="icon"><i class="fa fa-lock"></i></span>
            </div>
            <div class="input-group">
                <input type="password" name="confirm_password" placeholder="ยืนยันรหัสผ่าน" required>
                <span class="icon"><i class="fa fa-lock"></i></span>
            </div>
            <a href="call_admin.html"><button type="button" class="alert-btn">ถ้าสมัครไม่ได้ ติดต่อแอดมินได้ครับ คลิกที่นี่ครับ!!</button></a>
            <button type="submit" class="register-btn">สมัครสมาชิก</button>
        </form>
        <div class="links">
            <a href="login.php">ไปหน้าเข้าสู่ระบบ</a>
            <a href="index.php">กลับไปหน้าแรก</a>
        </div>
    </div>
</body>
</html>
