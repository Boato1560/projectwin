<?php
// ตรวจสอบว่ามีข้อมูลที่ส่งมาจากหน้า register.php หรือไม่
if (!isset($_GET['username']) || !isset($_GET['phone'])) {
    header("Location: register.php");
    exit();
}

// รับค่าที่ส่งมาจากหน้า register.php
$username = htmlspecialchars($_GET['username']);
$phone = htmlspecialchars($_GET['phone']);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงทะเบียนสำเร็จ</title>
    <link rel="icon" href="img/ddlakwin.jpg" type="image/x-icon">
    <link rel="stylesheet" href="styles.css"> 
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
        .success-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 500px;
            text-align: center;
        }
        h2 {
            color: #3498db;
            margin-bottom: 20px;
        }
        p {
            color: #666;
            margin-bottom: 20px;
        }
        button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            background-color: #3498db;
            color: #fff;
        }
        button:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <h2>ลงทะเบียนสำเร็จ!</h2>
        <p>ชื่อผู้ใช้: <?php echo $username; ?></p>
        <p>เบอร์โทรศัพท์: <?php echo $phone; ?></p>
        <a href="login.php"><button>กลับไปหน้าเข้าสู่ระบบ</button></a>
    </div>
</body>
</html>
