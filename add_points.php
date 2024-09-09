<?php
session_start();

// เชื่อมต่อฐานข้อมูล
$servername = "sql206.infinityfree.com";
$db_username = "if0_37184789";
$db_password = "cZq75jlVz3U";
$dbname = "if0_37184789_loguser";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// กำหนดค่าการแบ่งหน้า
$items_per_page = 20;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $items_per_page;

// ค้นหาผู้ใช้
$search_query = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $search_name = $conn->real_escape_string($_POST['search_name']);
    $search_query = "WHERE name LIKE '%$search_name%'";
}

// ดึงข้อมูลผู้ใช้ทั้งหมด
$sql = "SELECT id, name, phone, points FROM users $search_query LIMIT $items_per_page OFFSET $offset";
$result = $conn->query($sql);

// นับจำนวนข้อมูลทั้งหมด
$total_sql = "SELECT COUNT(*) as total FROM users $search_query";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_items = $total_row['total'];
$total_pages = ceil($total_items / $items_per_page);

// จัดการการเติมพอยต์
$alert_message = null;
$alert_class = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['points_to_add']) && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    $points_to_add = intval($_POST['points_to_add']);

    // เพิ่ม point
    $sql = "UPDATE users SET points = points + $points_to_add WHERE id = $user_id";
    if ($conn->query($sql) === TRUE) {
        $alert_message = "เติม Point สำเร็จ!";
        $alert_class = 'alert';
    } else {
        $alert_message = "Error updating points: " . $conn->error;
        $alert_class = 'alert-error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เติมPOINT</title>
    <link rel="icon" href="img/icon3.png" type="image/x-icon">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0 10px;
            box-sizing: border-box;
        }
        h1 {
            color: #333;
            font-size: 26pt;
            text-align: center;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
        }
        .header .search-container {
            display: flex;
            align-items: center;
        }
        .search-container input[type="text"] {
            padding: 6px;
            margin-right: 5px;
            border: 1px solid #ccc;
            border-radius: 3px;
            box-sizing: border-box;
            font-size: 14px;
        }
        .search-container button {
            padding: 6px 10px;
            border: none;
            border-radius: 3px;
            background-color: #3498db;
            color: white;
            cursor: pointer;
            font-size: 17px;
        }
        .search-container button:hover {
            opacity: 0.9;
        }
        .back-btn {
            background-color: #f39c12;
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
            opacity: 0.9;
        }
        .alert {
            background-color: #dff0d8;
            border: 1px solid #d6e9c6;
            border-radius: 3px;
            padding: 10px;
            color: #3c763d;
            margin-bottom: 10px;
            text-align: center;
            font-size: 14px;
        }
        .alert-error {
            background-color: #f2dede;
            border: 1px solid #ebccd1;
            color: #a94442;
            font-size: 14px;
        }
        table {
            width: 70%;
            border-collapse: collapse;
            margin-top: 5px;
            font-size: 20px;
            margin: 0 auto;
        }
        table, th, td {
            border: 1px solid #ddd;
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
        .pagination {
            text-align: center;
            margin-top: 10px;
        }
        .pagination a {
            color: #3498db;
            padding: 6px 10px;
            text-decoration: none;
            border: 1px solid #ddd;
            margin: 0 1px;
            border-radius: 3px;
            font-size: 14px;
        }
        .pagination a.active {
            background-color: #3498db;
            color: white;
            border: 1px solid #3498db;
        }
        .pagination a:hover {
            background-color: #ddd;
        }
        .add-points-input {
            width: 60px;
            padding: 4px;
            text-align: center;
            font-size: 14px;
        }
        .add-points-btn {
            background-color: #2ecc71;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 17px;
        }
        .add-points-btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <br><h1>เติม POINT</h1>
    <div class="header">
        <a href="admin.php" class="back-btn">กลับไปที่หน้าแรก</a>
        <div class="search-container">
            <form method="POST" action="">
                <input type="text" name="search_name" placeholder="ค้นหาชื่อผู้ใช้" required>
                <button type="submit" name="search">ค้นหา</button><br><br><br><br>
            </form>
        </div>
    </div>

    <!-- กล่องข้อความแจ้งเตือน -->
    <?php if (isset($alert_message)): ?>
        <div class="alert <?php echo $alert_class; ?>">
            <?php echo htmlspecialchars($alert_message); ?>
        </div>
    <?php endif; ?>

    <table>
        <tr>
            <th>ID</th>
            <th>ชื่อ</th>
            <th>เบอร์</th>
            <th>จำนวน Point</th>
            <th>เติม Point</th>
        </tr>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td><?php echo htmlspecialchars($row['points']); ?></td>
                    <td>
                        <form method="POST" action="" style="display: flex; justify-content: center; align-items: center;">
                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                            <input type="number" name="points_to_add" class="add-points-input" required>
                            <button type="submit" class="add-points-btn">ตกลง</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">ไม่มีข้อมูลผู้ใช้</td>
            </tr>
        <?php endif; ?>
    </table>

    <!-- Pagination -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="add_points.php?page=<?php echo $page - 1; ?>">&laquo; ก่อนหน้า</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="add_points.php?page=<?php echo $i; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        <?php if ($page < $total_pages): ?>
            <a href="add_points.php?page=<?php echo $page + 1; ?>">ถัดไป &raquo;</a>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
// ปิดการเชื่อมต่อฐานข้อมูลหลังจากทำงานทั้งหมดเสร็จสิ้น
$conn->close();
?>
