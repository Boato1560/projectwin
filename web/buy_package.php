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

// ดึงข้อมูลบทบาทของผู้ใช้
$sql = "SELECT phone, points, role, name FROM users WHERE id = ?";
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
} else {
    echo "ไม่พบข้อมูลของผู้ใช้!";
    exit();
}

// ตรวจสอบข้อมูลผู้ใช้
$phone = $conn->real_escape_string($phone); // ป้องกัน SQL Injection
$sql = "SELECT id, phone, password, role FROM users WHERE phone = '$phone'";
$result = $conn->query($sql);

if ($result->num_rows == 1) {
    $row = $result->fetch_assoc();
    $stored_hash = $row['password'];
    $role = $row['role']; // ดึงข้อมูล role ของผู้ใช้

    // ตรวจสอบรหัสผ่าน
    if (password_verify($password, $stored_hash)) {
        $user_id = $row['id'];
        $_SESSION['user_id'] = $user_id;
        $_SESSION['phone'] = $row['phone'];

        // ตรวจสอบว่าผู้ใช้มีแพ็คเกจอยู่หรือไม่
        $sql_package = "SELECT * FROM user_packages WHERE user_id = ?";
        $stmt = $conn->prepare($sql_package);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result_package = $stmt->get_result();

        if ($result_package->num_rows > 0) {
            // หากมีแพ็คเกจอยู่ เปลี่ยนเส้นทางไปที่ winandlak.php
            header("Location: winandlak.php");
            exit();
        } else {
            // ตรวจสอบบทบาทของผู้ใช้
            if ($role === 'admin') {
                // หากเป็นแอดมิน เปลี่ยนเส้นทางไปที่หน้า admin.php
                header("Location: admin.php");
            } else {
                // หากไม่ใช่แอดมิน เปลี่ยนเส้นทางไปที่หน้า index.php
                header("Location: index.php");
            }
            exit();
        }
    }
    $stmt->close();
}

// ตรวจสอบว่า package_id มีอยู่หรือไม่
if (isset($_GET['package_id'])) {
    $package_id = intval($_GET['package_id']);

    // รับข้อมูลแพ็คเกจ
    $sql = "SELECT * FROM packages WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $package_id);
    $stmt->execute();
    $package_result = $stmt->get_result();

    if ($package_result->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid package']);
        exit();
    }

    $package = $package_result->fetch_assoc();
    $package_cost = $package['price']; // สมมติว่ามีคอลัมน์ price ในตาราง packages

    // ตรวจสอบสถานะของแพ็คเกจที่ผู้ใช้มีอยู่
    $sql = "SELECT expiration_date FROM user_packages WHERE user_id = ? AND package_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $package_id);
    $stmt->execute();
    $package_result = $stmt->get_result();

    if ($package_result->num_rows > 0) {
        $existing_package = $package_result->fetch_assoc();
        $expiration_date = $existing_package['expiration_date'];

        // ตรวจสอบว่าแพ็คเกจหมดอายุหรือไม่
        if (strtotime($expiration_date) > time()) {
            echo json_encode(['success' => false, 'message' => 'คุณมีแพ็คเกจนี้อยู่แล้ว']);
            exit();
        }
    }

    // ตรวจสอบคะแนนของผู้ใช้
    if ($points < $package_cost) {
        echo json_encode(['success' => false, 'message' => 'จำนวน Point ไม่เพียงพอ']);
        exit();
    }

    // คำนวณวันหมดอายุของแพ็คเกจ
    $new_expiration_date = date('Y-m-d', strtotime('+1 month')); // สมมติว่าแพ็คเกจมีอายุ 1 เดือน

    // อัปเดตข้อมูลแพ็คเกจของผู้ใช้
    $sql = "REPLACE INTO user_packages (user_id, package_id, expiration_date) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $user_id, $package_id, $new_expiration_date);
    $stmt->execute();

    // อัปเดตคะแนนผู้ใช้
    $new_points = $points - $package_cost;
    $sql = "UPDATE users SET points = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $new_points, $user_id);
    $stmt->execute();

    $stmt->close();
    $conn->close();

    echo json_encode(['success' => true, 'new_points' => $new_points]);
    exit();
}

// ดึงข้อมูลแพ็คเกจที่ผู้ใช้ได้ซื้อ
$sql_packages = "SELECT package_id, expiration_date FROM user_packages WHERE user_id = ?";
$stmt_packages = $conn->prepare($sql_packages);
$stmt_packages->bind_param("i", $user_id);
$stmt_packages->execute();
$result_packages = $stmt_packages->get_result();

$purchased_packages = [];
while ($row_package = $result_packages->fetch_assoc()) {
    $purchased_packages[$row_package['package_id']] = $row_package['expiration_date'];
}

// ปิดการเชื่อมต่อฐานข้อมูล
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
            margin-bottom: 40px;
        }
        .logout-btn:hover {
            background-color: #c82333;
        }
        .content {
            flex-grow: 1;
            padding: 20px;
            margin-left: 250px;
            box-sizing: border-box;
        }
        .content h2 {
            font-size: 36px;
            margin-bottom: 20px;
            text-align: center;
        }
        .package-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .package {
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            width: calc(33.333% - 20px);
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .package-a {
            background-color: #007bff; /* Blue */
            color: white;
        }
        .package-b {
            background-color: #9966FF; /* Green */
            color: white;
        }
        .package-c {
            background-color: #17a2b8; /* Teal */
            color: white;
        }
        .package h3 {
            margin-bottom: 10px;
            font-size: 24px;
        }
        .package p {
            margin-bottom: 20px;
        }
        .package .price {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .package .btn {
            display: block;
            width: 100%;
            background-color: #28a745;
            color: white;
            padding: 15px 0;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .package .btn:hover {
            background-color: #218838;
        }
        .btn-disabled {
            display: block;
            width: 100%;
            background-color: #6c757d;
            color: white;
            padding: 15px 0;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            cursor: not-allowed;
        }
        .btn-disabled:hover {
            background-color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h1><?php echo htmlspecialchars($name); ?></h1>
        <div class="point-info">
            Point ที่คุณมีอยู่: <?php echo htmlspecialchars($points); ?>
        </div><br>
        <a href="#" id="main-page-link">หน้าหลัก</a>
        <a href="change_password.php">เปลี่ยนรหัสผ่าน</a>
        <a href="contact_admin.php">ติดต่อผู้ดูแล</a>
        <a href="logout.php" class="logout-btn">ออกจากระบบ</a>
    </div>

    <div class="content">
        <h2>เลือกแพ็คเกจเติมเงิน</h2>
        <div class="package-container">
            <div class="package package-a">
                <h3>แพ็คเกจ A</h3>
                <span class="price">50 บาท/เดือน</span>
                <p>เหมาะสำหรับผู้ใช้ที่ต้องการฟีเจอร์ A</p>
                <?php if (isset($purchased_packages[1])): ?>
                    <a href="#" class="btn-disabled">คุณมีแพ็คเกจนี้แล้ว</a>
                <?php else: ?>
                    <a href="#" class="btn" data-package-id="1">สมัครแพ็คเกจนี้!</a>
                <?php endif; ?>
            </div>
            <div class="package package-b">
                <h3>แพ็คเกจ B</h3>
                <span class="price">100 บาท/เดือน</span>
                <p>เหมาะสำหรับผู้ใช้ที่ต้องการฟีเจอร์ B</p>
                <?php if (isset($purchased_packages[2])): ?>
                    <a href="#" class="btn-disabled">คุณมีแพ็คเกจนี้แล้ว</a>
                <?php else: ?>
                    <a href="#" class="btn" data-package-id="2">สมัครแพ็คเกจนี้!</a>
                <?php endif; ?>
            </div>
            <div class="package package-c">
                <h3>แพ็คเกจ C</h3>
                <span class="price">200 บาท/เดือน</span>
                <p>เหมาะสำหรับผู้ใช้ที่ต้องการฟีเจอร์ C</p>
                <?php if (isset($purchased_packages[3])): ?>
                    <a href="#" class="btn-disabled">คุณมีแพ็คเกจนี้แล้ว</a>
                <?php else: ?>
                    <a href="#" class="btn" data-package-id="3">สมัครแพ็คเกจนี้!</a>
                <?php endif; ?>
            </div>
            <!-- เพิ่มแพ็คเกจที่ 4 และ 5 ที่นี่ -->
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('.btn').on('click', function(e) {
                e.preventDefault();

                var packageId = $(this).data('package-id');

                $.ajax({
                    url: 'buy_package.php',
                    type: 'GET',
                    data: { package_id: packageId },
                    success: function(response) {
                        var data = JSON.parse(response);
                        if (data.success) {
                            alert('ซื้อแพ็คเกจสำเร็จ! คะแนนปัจจุบัน: ' + data.new_points);
                            location.reload(); // โหลดหน้าใหม่เพื่อแสดงการเปลี่ยนแปลง
                        } else {
                            alert(data.message);
                        }
                    }
                });
            });

            $('#main-page-link').on('click', function(e) {
                e.preventDefault();

                $.ajax({
                    url: 'check_role.php',
                    type: 'GET',
                    success: function(response) {
                        var data = JSON.parse(response);
                        if (data.role === 'admin') {
                            window.location.href = 'admin.php';
                        } else {
                            window.location.href = 'index.php';
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
