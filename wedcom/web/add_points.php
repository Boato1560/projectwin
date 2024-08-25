<?php
session_start();

// เชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "testtimnow";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$selected_user = null;
$current_points = null;
$alert_message = null;
$alert_class = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['search'])) {
        $search_name = $conn->real_escape_string($_POST['search_name']);
        $sql = "SELECT id, name, points FROM users WHERE name LIKE '%$search_name%'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $selected_user = $result->fetch_assoc();
            $current_points = $selected_user['points'];
        } else {
            $alert_message = "ไม่พบชื่อผู้ใช้ที่ค้นหา!";
            $alert_class = 'alert-error';
        }
    } elseif (isset($_POST['points_to_add']) && isset($_POST['user_id'])) {
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
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ค้นหาและเติม Point</title>
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
        .points-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 500px;
            box-sizing: border-box;
            text-align: center;
        }
        h2 {
            color: #666;
            margin-bottom: 20px;
        }
        label {
            font-size: 16px;
            color: #666;
        }
        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .search-btn, .add-points-btn, .back-btn {
            background-color: #3498db;
            color: #fff;
            margin-bottom: 10px;
        }
        .back-btn {
            background-color: #f39c12;
            color: #fff;
        }
        .search-btn:hover, .add-points-btn:hover, .back-btn:hover {
            opacity: 0.9;
        }
        .alert {
            background-color: #dff0d8;
            border: 1px solid #d6e9c6;
            border-radius: 5px;
            padding: 10px;
            color: #3c763d;
            margin-bottom: 20px;
            text-align: center;
        }
        .alert-error {
            background-color: #f2dede;
            border: 1px solid #ebccd1;
            color: #a94442;
        }
    </style>
</head>
<body>
    <div class="points-container">
        <!-- กล่องข้อความแจ้งเตือน -->
        <?php if (isset($alert_message)): ?>
            <div class="alert <?php echo $alert_class; ?>">
                <?php echo htmlspecialchars($alert_message); ?>
            </div>
        <?php endif; ?>

        <h2>ค้นหาและเติม Point</h2>
        <form method="POST" action="">
            <label for="search_name">ค้นหาชื่อผู้ใช้:</label>
            <input type="text" id="search_name" name="search_name" required>
            <button type="submit" name="search" class="search-btn">ค้นหา</button>
        </form>

        <?php if ($selected_user): ?>
            <h3>ชื่อผู้ใช้: <?php echo htmlspecialchars($selected_user['name']); ?></h3>
            <p>Point ปัจจุบัน: <?php echo htmlspecialchars($current_points); ?></p>
            <form method="POST" action="">
                <input type="hidden" name="user_id" value="<?php echo $selected_user['id']; ?>">
                <label for="points_to_add">จำนวน Point ที่ต้องการเติม:</label>
                <input type="number" id="points_to_add" name="points_to_add" required>
                <button type="submit" class="add-points-btn">เติม Point</button>
            </form>
        <?php endif; ?>

        <a href="admin.php"><button class="back-btn">กลับไปที่หน้าแรก</button></a>
    </div>
</body>
</html>

<?php
// ปิดการเชื่อมต่อฐานข้อมูลหลังจากทำงานทั้งหมดเสร็จสิ้น
$conn->close();
?>
