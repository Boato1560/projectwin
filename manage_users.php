<?php
session_start();

// ตรวจสอบสิทธิ์การเข้าถึง (เฉพาะ admin)
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

// เชื่อมต่อกับฐานข้อมูล
$servername = "sql206.infinityfree.com";
$db_username = "if0_37184789";
$db_password = "cZq75jlVz3U";
$dbname = "if0_37184789_loguser";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ตั้งค่าการแบ่งหน้า
$items_per_page = 25; // จำนวนข้อมูลต่อหน้า
$page = isset($_GET['page']) ? intval($_GET['page']) : 1; // หน้าปัจจุบัน
$offset = ($page - 1) * $items_per_page; // คำนวณ offset

// ดึงข้อมูลผู้ใช้ทั้งหมดด้วย LIMIT และ OFFSET
$sql = "SELECT id, name, phone, role, last_login, login_session FROM users LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $items_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();

// นับจำนวนข้อมูลทั้งหมด
$total_sql = "SELECT COUNT(*) as total FROM users";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_items = $total_row['total'];
$total_pages = ceil($total_items / $items_per_page); // จำนวนหน้าทั้งหมด

// ตรวจสอบผลลัพธ์จากการดึงข้อมูล
if ($result === false) {
    echo "Error: " . $conn->error; // แสดงข้อผิดพลาด SQL
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการข้อมูลผู้ใช้</title>
    <link rel="icon" href="img/icon3.png" type="image/x-icon">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        table {
            width: 80%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-left: auto;
            margin-right: auto;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #3498db;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .action-btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .action-btn:hover {
            opacity: 0.9;
        }
        .checkbox {
            margin-right: 10px;
        }
        .button-container {
            margin-top: 50px;
            text-align: center;
        }
        #delete-btn {
            display: none; /* ซ่อนปุ่มลบข้อมูลทั้งหมด */
        }
        .nav-btn {
            position: absolute;
            top: 20px;
            left: 20px;
        }
        .login-session {
            width: 100px; /* ลดขนาดความกว้างของช่อง Login Session */
        }
        .pagination {
            text-align: center;
            margin-top: 20px;
        }
        .pagination a {
            color: #3498db;
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #ddd;
            margin: 0 2px;
            border-radius: 5px;
        }
        .pagination a.active {
            background-color: #3498db;
            color: white;
            border: 1px solid #3498db;
        }
        .pagination a:hover {
            background-color: #ddd;
        }
    </style>
    <script>
        function toggleDeleteButton() {
            var checkboxes = document.querySelectorAll('input[name="user_ids[]"]');
            var deleteBtn = document.getElementById('delete-btn');
            var anyChecked = Array.from(checkboxes).some(cb => cb.checked);
            deleteBtn.style.display = anyChecked ? 'inline-block' : 'none';
        }
        function autoRefresh() {
            setTimeout(function() {
                window.location.reload();
            }, 30000);
        }
    </script>
</head>
<body onload="autoRefresh();">
    <a href="admin.php" class="nav-btn action-btn">กลับไปหน้าหลัก</a>
    <br><h2>จัดการข้อมูลผู้ใช้</h2><br><br>
    <form method="post" action="manage_users.php">
        <table>
            <tr>
                <th></th>
                <th>ID</th>
                <th>ชื่อ</th>
                <th>เบอร์</th>
                <th>Role</th>
                <th>วันที่ล็อคอิน</th>
                <th>เวลาล็อคอิน</th>
                <th class="login-session">Login Session</th>
                <th>Actions</th>
            </tr>

            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td><input type='checkbox' name='user_ids[]' value='" . $row['id'] . "' class='checkbox' onchange='toggleDeleteButton()'></td>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . $row['name'] . "</td>";
                    echo "<td>" . $row['phone'] . "</td>";
                    echo "<td>" . $row['role'] . "</td>";

                    if (!empty($row['last_login'])) {
                        $datetime = new DateTime($row['last_login']);
                        $datetime->modify('+14 hours');
                        echo "<td>" . $datetime->format('d-m-Y') . "</td>";
                        echo "<td>" . $datetime->format('H:i') . "</td>";
                    } else {
                        echo "<td>ไม่เคยล็อกอิน</td>";
                        echo "<td>-</td>";
                    }

                    echo "<td class='login-session'>" . (!empty($row['login_session']) ? $row['login_session'] : 'ไม่มี') . "</td>";
                    echo "<td>";
                    if (!empty($row['login_session'])) {
                        echo "<a href='manage_users.php?action=clear_session&id=" . $row['id'] . "' class='btn'>ลบ Session</a>";
                    }
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='9'>ไม่มีข้อมูลผู้ใช้</td></tr>";
            }
            ?>
        </table>

        <div class="button-container">
            <button type="submit" name="action" value="delete_all" id="delete-btn" class="action-btn">ลบข้อมูล</button>
        </div>
    </form>

    <!-- Pagination -->
    <div class="pagination">
        <?php
        if ($page > 1) {
            echo "<a href='manage_users.php?page=" . ($page - 1) . "'>&laquo; ก่อนหน้า</a>";
        }
        for ($i = 1; $i <= $total_pages; $i++) {
            echo "<a href='manage_users.php?page=" . $i . "' class='" . ($i == $page ? "active" : "") . "'>" . $i . "</a>";
        }
        if ($page < $total_pages) {
            echo "<a href='manage_users.php?page=" . ($page + 1) . "'>ถัดไป &raquo;</a>";
        }
        ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>
