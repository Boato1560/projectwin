<?php
session_start();

// ตรวจสอบสิทธิ์การเข้าถึง (เฉพาะแอดมินเท่านั้น)
if ($_SESSION['role'] !== 'admin') {
    echo "<!DOCTYPE html>
    <html lang='th'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>คุณไม่มีสิทธิ์เข้าถึง</title>
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
            .container {
                text-align: center;
                padding: 20px;
                background-color: #fff;
                border: 1px solid #ddd;
                border-radius: 5px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }
            .btn {
                background-color: #3498db;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 5px;
                cursor: pointer;
                text-decoration: none;
            }
            .btn:hover {
                opacity: 0.9;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>คุณไม่มีสิทธิ์เข้าถึงหน้านี้!</h1>
            <a href='admin.php' class='btn'>กลับไปหน้า Admin</a>
        </div>
    </body>
    </html>";
    exit();
}

// การตั้งค่าการเชื่อมต่อฐานข้อมูล
$servername = "sql206.infinityfree.com";
$db_username = "if0_37184789";
$db_password = "cZq75jlVz3U";
$dbname = "if0_37184789_loguser";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}

// การจัดการการส่งข้อมูลฟอร์มสำหรับการอัปเดตวันหมดอายุ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);
    $new_expiration_date = $_POST['new_expiration_date'];

    // ตรวจสอบว่าผู้ใช้มี package_id 3 หรือไม่
    $check_package_sql = "SELECT package_id FROM user_packages WHERE user_id = ? AND package_id = 3";
    $stmt = $conn->prepare($check_package_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    // อัปเดตเฉพาะข้อมูลของ package_id = 3
    if ($row && $row['package_id'] == 3) {
        if (isset($_POST['update_expiration'])) {
            // อัปเดตวันหมดอายุ (อัปเดตเฉพาะ package_id = 3)
            $update_expiration_sql = "UPDATE user_packages SET expiration_date = ? WHERE user_id = ? AND package_id = 3";
            $stmt = $conn->prepare($update_expiration_sql);
            $stmt->bind_param("si", $new_expiration_date, $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// การจัดการการลบข้อมูลแพ็คเกจผู้ใช้
if (isset($_GET['remove_date'])) {
    $user_id = intval($_GET['remove_date']);
    // ตรวจสอบว่าผู้ใช้มี package_id 3 ก่อนการลบ
    $check_package_sql = "SELECT package_id FROM user_packages WHERE user_id = ? AND package_id = 3";
    $stmt = $conn->prepare($check_package_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row && $row['package_id'] == 3) {
        // ลบข้อมูลแพ็คเกจของผู้ใช้เฉพาะ package_id = 3
        $remove_record_sql = "DELETE FROM user_packages WHERE user_id = ? AND package_id = 3";
        $stmt = $conn->prepare($remove_record_sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        header("Location: PackageC.php");
        exit();
    }
}

// ตั้งค่าการแบ่งหน้า
$items_per_page = 15;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $items_per_page;

// ดึงข้อมูลผู้ใช้ที่มี package_id = 3
$sql = "SELECT up.user_id, u.name, up.package_id, up.expiration_date, up.purchase_time, p.name as package_name
        FROM user_packages up
        JOIN users u ON up.user_id = u.id
        JOIN packages p ON up.package_id = p.id
        WHERE up.package_id = 3
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $items_per_page, $offset);
$stmt->execute();
$user_packages_result = $stmt->get_result();

// นับจำนวนข้อมูลทั้งหมดที่มี package_id = 3
$total_sql = "SELECT COUNT(*) as total FROM user_packages WHERE package_id = 3";
$total_stmt = $conn->prepare($total_sql);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_items = $total_row['total'];
$total_pages = ceil($total_items / $items_per_page);

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการแพ็คเกจ</title>
    <link rel="icon" href="img/icon3.jpg" type="image/x-icon">
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 30px;
            background-color: #f4f4f4;
            position: relative;
        }
        h1 {
            color: #333;
            font-size: 26pt;
        }
        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            border: 1px solid #ccc;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        input[type="text"], select {
            padding: 5px;
            margin: 5px 0;
            border: 1px solid #ccc;
            text-align: center;
        }
        button {
            background-color: green;
            color: white;
            border: none;
            padding: 8px 16px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            margin: 5px 0;
        }
        button:hover {
            background-color: #45a049;
        }
        .remove-date-btn {
            background-color: red;
        }
        .remove-date-btn:hover {
            background-color: #c82333;
        }
        .back-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            position: absolute;
            top: 10px;
            left: 10px;
        }
        .back-btn:hover {
            background-color: #0056b3;
        }
        .pagination {
            margin-top: 20px;
            text-align: center;
        }
        .pagination a {
            color: #007bff;
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin: 0 2px;
        }
        .pagination a.active {
            background-color: #007bff;
            color: white;
            border: 1px solid #007bff;
        }
        .pagination a:hover {
            background-color: #0056b3;
            color: white;
        }
    </style>
</head>
<body>
    <button class="back-btn" onclick="window.location.href='PackageB.php'">กลับไปที่หน้าPackageB</button>
    <br><h1>แพ็คเกจวินเลขและเลขลาก</h1><br><br><br>
    <table>
        <tr>
            <th>ID</th>
            <th>ชื่อผู้ใช้</th>
            <th>ชื่อแพ็คเกจ</th>
            <th>วันที่เหลือ</th>
            <th>เวลาหมดอายุ</th>
            <th>วันหมดอายุ</th>
            <th>ลบแพ็คเกจ</th>
        </tr>
        <?php
        if ($user_packages_result->num_rows > 0) {
            while ($row = $user_packages_result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row["user_id"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["package_name"]) . "</td>";

                                         // คำนวณจำนวนวันที่เหลือ
            $current_date = new DateTime();
            $exp_date = new DateTime($row["expiration_date"]);
            $interval = $current_date->diff($exp_date);
            $days_remaining = $interval->format('%r%a'); // %r แสดงเครื่องหมายลบถ้าผ่านวันหมดอายุแล้ว

            // แสดงจำนวนวันที่เหลือแบบที่ต้องการ
            if ($days_remaining >= 0) {
                echo "<td>" . $days_remaining . " วัน</td>";
            } else {
                echo "<td>หมดอายุแล้ว</td>";
            }

                // คำนวณเวลาใหม่โดยการบวก 14 ชั่วโมง
                 $purchase_time = new DateTime($row["purchase_time"]);
                $purchase_time->modify('+11 hours');

                echo "<td>" . $purchase_time->format('H:i') . "</td>";

                // ฟอร์มสำหรับอัปเดตวันหมดอายุ
                echo "<td>
                        <form method='post'>
                            <input type='hidden' name='user_id' value='" . htmlspecialchars($row["user_id"]) . "'>
                            <input type='text' name='new_expiration_date' placeholder='YYYY-MM-DD' value='" . htmlspecialchars($row["expiration_date"]) . "'>
                            <button type='submit' name='update_expiration'>ตกลง</button>
                        </form>
                      </td>";

                // ปุ่มสำหรับลบแพ็คเกจ
                echo "<td>
                        <a href='PackageC.php?remove_date=" . urlencode($row["user_id"]) . "'><button class='remove-date-btn'>ลบแพ็คเกจ</button></a>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>ไม่มีข้อมูลผู้ใช้</td></tr>";
        }
        ?>
    </table>
    <div class="pagination">
        <?php
        if ($page > 1) {
            echo "<a href='PackageC.php?page=" . ($page - 1) . "'>&laquo; ก่อนหน้า</a>";
        }
        for ($i = 1; $i <= $total_pages; $i++) {
            echo "<a href='PackageC.php?page=" . $i . "' class='" . ($i == $page ? "active" : "") . "'>" . $i . "</a>";
        }
        if ($page < $total_pages) {
            echo "<a href='PackageC.php?page=" . ($page + 1) . "'>ถัดไป &raquo;</a>";
        }
        ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>
