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
$search_name = '';

// การลบข้อมูล
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    $delete_sql = "UPDATE users SET points = 0 WHERE id = $user_id";
    
    if ($conn->query($delete_sql) === TRUE) {
        echo "<p>Points reset successfully</p>";
    } else {
        echo "<p>Error resetting points: " . $conn->error . "</p>";
    }
    
    // เปลี่ยนเส้นทางไปยังหน้าเดิมหลังจากลบ
    header("Location: delete_points.php");
    exit();
}

// ค้นหาและเพิ่ม points
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['search'])) {
        $search_name = $conn->real_escape_string($_POST['search_name']);
    } elseif (isset($_POST['points_to_add']) && isset($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);
        $points_to_add = intval($_POST['points_to_add']);

        // เพิ่ม point
        $sql = "UPDATE users SET points = points + $points_to_add WHERE id = $user_id";

        if ($conn->query($sql) === TRUE) {
            echo "<p>Points updated successfully!</p>";
        } else {
            echo "<p>Error updating points: " . $conn->error . "</p>";
        }
    }
}

// ดึงข้อมูล points จากฐานข้อมูล
if ($search_name) {
    $sql = "SELECT id, name, points FROM users WHERE name LIKE '%$search_name%'";
} else {
    $sql = "SELECT id, name, points FROM users";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการ Points</title>
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
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 600px;
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
        .search-btn, .add-points-btn, .delete-btn, .back-btn {
            background-color: #3498db;
            color: #fff;
            margin-bottom: 10px;
        }
        .delete-btn {
            background-color: #e74c3c;
        }
        .back-btn {
            background-color: #f39c12;
        }
        .search-btn:hover, .add-points-btn:hover, .delete-btn:hover, .back-btn:hover {
            opacity: 0.9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>ค้นหาและจัดการ Points</h2>
        <h2>ข้อมูล Points</h2>
        <table>
            <tr>
                <th>User ID</th>
                <th>Name</th>
                <th>Points</th>
                <th>Action</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                // แสดงข้อมูลในตาราง
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["points"]) . "</td>";
                    echo "<td><a href='delete_points.php?delete=" . $row["id"] . "'><button class='delete-btn'>ลบ Points</button></a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No records found</td></tr>";
            }
            ?>
        </table>

        <!-- ปุ่มกลับไปที่หน้าแรก -->
        <a href="admin.php"><button class="back-btn">กลับไปที่หน้าแรก</button></a>
    </div>
</body>
</html>

<?php
// ปิดการเชื่อมต่อฐานข้อมูลหลังจากทำงานทั้งหมดเสร็จสิ้น
$conn->close();
?>
