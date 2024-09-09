<?php
session_start();

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// เชื่อมต่อกับฐานข้อมูล
$servername = "sql206.infinityfree.com";
$db_username = "if0_37184789";
$db_password = "cZq75jlVz3U";
$dbname = "if0_37184789_loguser";


$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$user_id = $_SESSION['user_id'];

// Update last login time
$sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);

if (!$stmt->execute()) {
    echo "Error updating last login: " . $stmt->error;
}




// ลบแพ็คเกจที่หมดอายุ
$sql = "DELETE FROM user_packages WHERE expiration_date < NOW()";
$conn->query($sql);

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
        session_unset(); // ลบข้อมูลเซสชันทั้งหมด
        session_destroy(); // ทำลายเซสชัน
        echo '<script>window.location.href = "login.php";</script>'; // ใช้ JavaScript เพื่อรีไดเรกต์ไปที่หน้าล็อกอิน
        exit();
    }
} else {
    echo "ไม่พบข้อมูลของผู้ใช้!";
    exit();
}

// ดึงข้อมูลของผู้ใช้รวมถึงแพ็คเกจที่ซื้อ พร้อมกับแยกวันที่หมดอายุของแต่ละแพ็คเกจ
$sql = "SELECT u.phone, u.points, u.role, u.name, 
        up1.expiration_date AS expiration_date1, 
        up2.expiration_date AS expiration_date2, 
        up3.expiration_date AS expiration_date3 
        FROM users u
        LEFT JOIN user_packages up1 ON u.id = up1.user_id AND up1.package_id = 1
        LEFT JOIN user_packages up2 ON u.id = up2.user_id AND up2.package_id = 2
        LEFT JOIN user_packages up3 ON u.id = up3.user_id AND up3.package_id = 3
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
    $expiration_date1 = $row['expiration_date1']; // วันหมดอายุของแพ็คเกจ 1
    $expiration_date2 = $row['expiration_date2']; // วันหมดอายุของแพ็คเกจ 2
    $expiration_date3 = $row['expiration_date3']; // วันหมดอายุของแพ็คเกจ 3
    $remaining_days1 = $expiration_date1 ? floor((strtotime($expiration_date1) - time()) / (60 * 60 * 24)) : 0;
    $remaining_days2 = $expiration_date2 ? floor((strtotime($expiration_date2) - time()) / (60 * 60 * 24)) : 0;
    $remaining_days3 = $expiration_date3 ? floor((strtotime($expiration_date3) - time()) / (60 * 60 * 24)) : 0;
} else {
    echo "ไม่พบข้อมูลของผู้ใช้!";
    exit();
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลัก</title>
    <link rel="icon" href="img/icon3.png" type="image/x-icon">
    <style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
}

.sidebar {
    width: 250px;
    background-color: #333;
    color: white;
    padding: 20px;
    height: calc(98vh - 0px); /* ปรับให้ครอบคลุมความสูงทั้งหมดของหน้าจอ */
    box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    text-align: center;
    position: fixed;
    left: 0;
    top: 0;
    transition: transform 0.3s ease;
}

    .sidebar.collapsed {
        transform: translateX(-100%);
    }
    .sidebar h1 {
        color: #fff;
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
        margin-top: auto; /* เพิ่มระยะห่างจากด้านบน */
        margin-bottom: 0px; /* เพิ่มระยะห่างจากขอบล่าง */
    }
    .logout-btn:hover {
        background-color: #c82333;
    }

    .version {
        text-align: center;
        display: block;
        margin-top: 20px; /* เพิ่มระยะห่างจากด้านบน */
        margin-bottom: 20px;
    }

    .content {
    flex-grow: 1;
    padding: 20px;
    margin-left: 250px;
    box-sizing: border-box;
    text-align: center;
    transition: margin-left 0.3s ease;
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

    .expiration-box {
        border: 2px solid white; /* กำหนดกรอบสีขาว */
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 10px;
        background-color: #fff;
        color:black;
    }

   /* เมื่อหน้าจอเล็กกว่า 768px */
        @media (max-width: 768px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: static;
        transform: translateX(0);
        padding: 10px;
    }
    .content {
        margin-left: 0;
        padding: 10px;
    }
    .package {
        width: 100%;
        margin-bottom: 20px;
    }
    }

    /* เมื่อหน้าจอเล็กกว่า 480px */
    @media (max-width: 480px) {
    .sidebar {
        padding: 10px;
        }
    .content {
        padding: 10px;
        }
    .package {
        width: 100%;
        margin-bottom: 10px;
        }
    }
    </style>
</head>
<body>
    <div class="sidebar">
        <h1><?php echo htmlspecialchars($name); ?></h1>
        <div class="point-info">
           จำนวน Point: <?php echo htmlspecialchars($points); ?>
        </div><br>
        <a href="buy_package.php">เช่าแพ็คเกจ</a>
        <a href="contact_admin.html">เติม Point</a>
        <a href="change_password.php">เปลี่ยนรหัสผ่าน</a>
        <a href="calladmin.html">ติดต่อแอดมิน</a>
        <a href="logout.php" class="logout-btn">ออกจากระบบ</a>
        <div class="version">version: 1.0.0</div> 
    </div>
    <div class="content">
        <h2>เลือกสิ่งที่คุณต้องการได้เลย!!</h2><br>
        <div class="package-container">
            <div class="package" style="background-color: #007bff; color: white;">
                <?php if ($expiration_date1) { ?>
                    <div class="expiration-box">
                        ใช้งานได้อีก: <?php echo htmlspecialchars($remaining_days1) . " วัน"; ?>
                    </div>
                <?php } else { ?>
                    <div class="expiration-box">ยังไม่มีแพ็คเกจ</div>
                <?php } ?>
                <h3>วินเลข</h3>
                <p>สำหรับผู้ใช้ที่ต้องการวินเลข</p>
                <?php if ($expiration_date1) { ?>
                    <a href="win.php" class="btn">เข้าใช้งาน!</a>
                <?php } else { ?>
                    <p>กรุณาเช่าแพ็คเกจนี้ก่อนใช้งาน !</p>
                <?php } ?>
            </div>

            <div class="package" style="background-color: #9966FF; color: white;">
                <?php if ($expiration_date2) { ?>
                    <div class="expiration-box">
                        ใช้งานได้อีก: <?php echo htmlspecialchars($remaining_days2) . " วัน"; ?>
                    </div>
                <?php } else { ?>
                    <div class="expiration-box">ยังไม่มีแพ็คเกจ</div>
                <?php } ?>
                <h3>ลากเลข</h3>
                <p>สำหรับผู้ใช้ที่ต้องการลากเลข</p>
                <?php if ($expiration_date2) { ?>
                    <a href="lak.php" class="btn">เข้าใช้งาน!</a>
                <?php } else { ?>
                    <p>กรุณาเช่าแพ็คเกจนี้ก่อนใช้งาน !</p>
                <?php } ?>
            </div>

            <div class="package" style="background-color: #CD853F; color: white;">
                <?php if ($expiration_date3) { ?>
                    <div class="expiration-box">
                        ใช้งานได้อีก: <?php echo htmlspecialchars($remaining_days3) . " วัน"; ?>
                    </div>
                <?php } else { ?>
                    <div class="expiration-box">ยังไม่มีแพ็คเกจ</div>
                <?php } ?>
                <h3>วินเลข & ลากเลข</h3>
                <p>สำหรับผู้ใช้ที่ต้องการวินเลขและลากเลข</p>
                <?php if ($expiration_date3) { ?>
                    <a href="winandlak.php" class="btn">เข้าใช้งาน!</a>
                <?php } else { ?>
                    <p>กรุณาเช่าแพ็คเกจนี้ก่อนใช้งาน !</p>
                <?php } ?>
            </div>
        </div>
    </div>

</body>

<script>
    // ตรวจสอบว่าแพ็คเกจมีวันหมดอายุหรือไม่
    <?php if ($expiration_date1 || $expiration_date2 || $expiration_date3): ?>
        var currentDate = new Date().toISOString().split('T')[0];
        var expirationDates = [
            "<?php echo $expiration_date1; ?>",
            "<?php echo $expiration_date2; ?>",
            "<?php echo $expiration_date3; ?>"
        ];
        
        expirationDates.forEach(function(date) {
            if (date === currentDate) {
                location.reload();
            }
        });
    <?php endif; ?>
</script>
</html>

