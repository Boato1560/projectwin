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

// ดึงข้อมูล session_id ที่บันทึกไว้ในฐานข้อมูล
$user_id = $_SESSION['user_id'];
$sql = "SELECT login_session FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $row = $result->fetch_assoc();
    $login_session = $row['login_session'];

    // ตรวจสอบว่า session_id ปัจจุบันตรงกันหรือไม่
    if ($login_session !== session_id()) {
        echo "บัญชีนี้มีการล็อกอินจากอุปกรณ์อื่น!";
        session_destroy(); // ทำการล็อกเอ้าท์ผู้ใช้
        header("Location: login.php");
        exit();
    }
} else {
    echo "ไม่พบข้อมูลของผู้ใช้!";
    exit();
}

// ดึงข้อมูลของผู้ใช้รวมถึงแพ็คเกจที่ซื้อ
$sql = "SELECT u.phone, u.points, u.role, u.name, up.expiration_date FROM users u
        LEFT JOIN user_packages up ON u.id = up.user_id
        WHERE u.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $phone = $row['phone'];
    $points = $row['points'];
    $role = $row['role'];
    $name = $row['name'];
    $expiration_date = $row['expiration_date']; // วันหมดอายุของแพ็คเกจ
    $remaining_days = $expiration_date ? floor((strtotime($expiration_date) - time()) / (60 * 60 * 24)) : 0;
} else {
    echo "ไม่พบข้อมูลของผู้ใช้!";
    exit();
}

// ดึงข้อมูลแพ็คเกจที่ผู้ใช้ได้ซื้อ
$sql_packages = "SELECT package_id FROM user_packages WHERE user_id = ?";
$stmt_packages = $conn->prepare($sql_packages);
$stmt_packages->bind_param("i", $user_id);
$stmt_packages->execute();
$result_packages = $stmt_packages->get_result();

$purchased_packages = [];
while ($row_package = $result_packages->fetch_assoc()) {
    $purchased_packages[] = $row_package['package_id'];
}

$stmt->close();
$stmt_packages->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เลือกแพ็คเกจเติมเงิน</title>
    <link rel="icon" href="img/ddlakwin.jpg" type="image/x-icon">
    <style>
    body {
    font-family: Arial, sans-serif;
    display: flex;
    margin: 0;
    padding: 0;
    }

    .sidebar {
        width: 250px;
        background-color: #333;
        color: white;
        padding: 20px;
        height: 100vh;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        text-align: center;
    }
    .sidebar h1 {
        color: #fff;
        text-align: center;
    }
    .sidebar a {
        display: block;
        color: #ddd;
        text-decoration: none;
        padding: 10px 0;
    }
    .sidebar a:hover {
        background-color: #575757;
        color: #fff;
    }
    .point-info {
        background-color: #007bff;
        color: white;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0px 0px 5px rgba(0,0,0,0.3);
        font-size: 16px;
        text-align: center;
        margin-top: 20px;
    }
    .logout-btn {
        background-color: #dc3545;
        color: white;
        padding: 20px;
        text-decoration: none;
        border-radius: 5px;
        text-align: center;
        display: block;
        margin-top: auto;
        margin-bottom: 100px;
    }
    .logout-btn:hover {
        background-color: #c82333;
    }
    .content {
        flex-grow: 1;
        padding: 20px;
        margin-left: 250px;
        box-sizing: border-box;
        text-align: center;
    }
    .content h2 {
        font-size: 36px;
        margin-bottom: 20px;
    }
    .package-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        justify-content: center;
    }
    .package {
        background-color: #f0f0f0;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        width: calc(33.333% - 20px);
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
    }
    .package h3 {
        margin-bottom: 10px;
    }
    .package p {
        margin-bottom: 20px;
    }
    .package .price {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 10px;
    }
    .package .btn {
        background-color: #28a745;
        color: white;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 5px;
        display: inline-block;
    }
    .package .btn:hover {
        background-color: #218838;
    }
</style>
</head>
<body>
    <div class="sidebar">
        <h1><?php echo htmlspecialchars($name); ?></h1>
        <div class="point-info">
            Point ที่คุณมีอยู่: <?php echo htmlspecialchars($points); ?>
        </div><br>
        <div>
            วันหมดอายุแพ็คเกจ: <?php echo $expiration_date ? htmlspecialchars($remaining_days) . " วัน" : "ยังไม่มีแพ็คเกจ"; ?>
        </div><br>
        <a href="index.php">หน้าหลัก</a>
        <a href="change_password.php">เปลี่ยนรหัสผ่าน</a>
        <a href="buy_package.php">ซื้อแพ็คเกจ</a>
        <a href="contact_admin.html">ติดต่อแอดมิน</a>
        <a href="logout.php" class="logout-btn">ล็อกเอ้า</a>
    </div>
    <div class="content">
        <h2>เลือกสิ่งที่คุณต้องการได้เลย!!</h2><br>
        <div class="package-container">
            <div class="package" style="background-color: #007bff; color: white;">
                <h3>วินเลข</h3>
                <p>สำหรับผู้ใช้ที่ต้องการวินเลข</p>
                <?php if (in_array(1, $purchased_packages)) { ?>
                    <a href="index.html" class="btn">เข้าใช้งาน!</a>
                <?php } else { ?>
                    <p>คุณไม่มีแพ็คเกจนี้</p>
                <?php } ?>
            </div>
            <div class="package" style="background-color: #9966FF; color: white;">
                <h3>ลากเลข</h3>
                <p>สำหรับผู้ใช้ที่ต้องการลากเลข</p>
                <?php if (in_array(2, $purchased_packages)) { ?>
                    <a href="lak.html" class="btn">เข้าใช้งาน!</a>
                <?php } else { ?>
                    <p>คุณไม่มีแพ็คเกจนี้</p>
                <?php } ?>
            </div>
            <div class="package" style="background-color: #17a2b8; color: white;">
                <h3>วินเลข & ลากเลข</h3>
                <p>สำหรับผู้ใช้ที่ต้องการวินเลขและลากเลข</p>
                <?php if (in_array(3, $purchased_packages)) { ?>
                    <a href="winandlak.html" class="btn">เข้าใช้งาน!</a>
                <?php } else { ?>
                    <p>คุณไม่มีแพ็คเกจนี้</p>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>
