<?php
session_start();

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$servername = "sql206.infinityfree.com";
$db_username = "if0_37184789";
$db_password = "cZq75jlVz3U";
$dbname = "if0_37184789_loguser";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if ($conn->connect_error) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}

// ฟังก์ชันตรวจสอบ session_id ในฐานข้อมูลว่าตรงกับ session ปัจจุบันหรือไม่
function checkSession($conn, $user_id) {
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
}

// เรียกใช้ฟังก์ชันตรวจสอบเซสชันก่อนแสดงผลหน้าเว็บ
checkSession($conn, $_SESSION['user_id']);

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรแกรมสร้างเลขชุด 3 ตัว</title>
    <link rel="icon" href="img/icon3.png" type="image/x-icon">
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 30px;
            background-color: #f4f4f4;
            background-image: url(img/123.png);
            background-size: 100% 100%;
            background-position: center;
            background-repeat: no-repeat;
            height: 150vh;
            display: flex;
            flex-direction: column;
        }

        h1 {
            color: #333;
            font-size: 26pt;
            text-shadow: 2px 2px 0px #ffffff, 5px 4px 0px rgba(0,0,0,0.15);
        }

        #prefixInput {
            padding: 15px;
            font-size: 16px;
            border: 2px solid #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
            width: 200px;
        }

        .mbtn {
            background-color: green;
            color: white;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 5px;
        }

        .mbtn:hover {
            background-color: #45a049;
        }

        #result {
            border: 2px solid #ddd;
            padding: 0px;
            border-radius: 5px;
            background: linear-gradient(to right, #ffffff, #ffffff);
            margin-top: 20px;
            display: inline-block;
            max-width: 70%;
            display: none; /* ซ่อนส่วนผลลัพธ์เป็นค่าเริ่มต้น */
            overflow-y: auto;
            text-align: left;
            font-family: "Tahoma", "Verdana", sans-serif;
        }

        .number-result {
            font-size: 20px;
            font-weight: bold;
            color: black;
            white-space: normal;
            word-wrap: break-word;
            padding: 10px;
        }

        .copy-button {
            background-color: green;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            display: none; /* ซ่อนปุ่มคัดลอกเป็นค่าเริ่มต้น */
            margin-top: 10px;
        }

        .copy-button:hover {
            background-color: #C3EEFA;
        }

        .back-btn {
            position: fixed;
            top: 10px;
            left: 10px;
            background-color: #33b854;
            color: white;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        .back-btn:hover {
            background-color: #218838;
        }

        .logout-btn {
            position: fixed;
            top: 10px;
            right: 10px;
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        .logout-btn:hover {
            background-color: #c82333;
        }

#notification {
    display: none;
    position: fixed;
    top: 50px;
    left: 50%;
    transform: translateX(-50%);
    padding: 10px 20px;
    background-color: white;
    color: red;
    border-radius: 5px;
    z-index: 1000;
    font-size: 18px; /* ปรับขนาดตัวหนังสือตามที่ต้องการ */
}
    </style>
</head>
<body>
    <button class="back-btn" onclick="goBack()">กลับไปหน้าแรก</button>
    <table border="0" cellpadding="0" cellspacing="0" width="100%" align="center">
        <tr>
            <td align="center">
                <h1>โปรแกรมสร้างเลขชุด 3 ตัว</h1>
                <input type="text" id="prefixInput" placeholder="กรอกเลขหน้า 2 ตัว" maxlength="2">
                <br><br>
                <input name="btnwin" type="button" id="btnwin" value="วินเลข" class="mbtn" onclick="generateNumbers()">
                <button class="mbtn onclick="reset()">เริ่มใหม่</button>
                <br>
                <div id="result">
                    <div id="resultContent" class="number-result"></div>
                    <button class="copy-button" onclick="copyToClipboard()">คัดลอก</button>
                </div>
            </td>
        </tr>
    </table>
    <div id="notification"></div>
<script src="checkSessionBeforeAction2.js"></script>
</body>
    <script>
        function goBack() {
            const role = "<?php echo isset($_SESSION['role']) ? $_SESSION['role'] : 'guest'; ?>";
            if (role === 'admin') {
                window.location.href = 'admin.php';
            } else {
                window.location.href = 'index.php';
            }
        }

        function generateNumbers() {
            // เรียกใช้ฟังก์ชันตรวจสอบเซสชันจาก checkSessionBeforeAction2.js
            checkSessionBeforeAction(function() {
                const prefix = document.getElementById('prefixInput').value;
                if (prefix.length !== 2 || isNaN(prefix)) {
                    alert("กรุณากรอกเลขหน้า 2 ตัวที่ถูกต้อง");
                    return;
                }

                let result = '';
                for (let i = 0; i <= 9; i++) {
                    result += prefix + i + '<br>';
                }

                document.getElementById("resultContent").innerHTML = `
                    <strong>เลขชุดจาก ${prefix}:</strong><br><br>
                    ${result}
                `;

                document.getElementById('result').style.display = 'inline-block';
                document.querySelector('.copy-button').style.display = 'inline-block'; // แสดงปุ่มคัดลอก
            });
        }

        function reset() {
            document.getElementById("prefixInput").value = '';
            document.getElementById("resultContent").innerHTML = '';
            document.getElementById('result').style.display = 'none';
            document.querySelector('.copy-button').style.display = 'none'; // ซ่อนปุ่มคัดลอก
        }

        function copyToClipboard() {
            const resultContent = document.getElementById("resultContent").innerHTML;

            const regex = /\b\d{3}\b/g;
            const matches = resultContent.match(regex);

            if (matches && matches.length > 0) {
                const numbersToCopy = matches.join('\n');
                
                const tempInput = document.createElement("textarea");
                tempInput.style.position = 'absolute';
                tempInput.style.left = '-10000px';
                tempInput.value = numbersToCopy;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand("copy");
                document.body.removeChild(tempInput);

                const alertBox = document.createElement('div');
                alertBox.innerText = "คัดลอกสำเร็จ!";
                alertBox.style.position = 'fixed';
                alertBox.style.top = '20px';
                alertBox.style.right = '20px';
                alertBox.style.padding = '10px';
                alertBox.style.backgroundColor = 'red';
                alertBox.style.color = 'white';
                alertBox.style.borderRadius = '5px';
                document.body.appendChild(alertBox);

                setTimeout(() => {
                    document.body.removeChild(alertBox);
                }, 2000);
            } else {
                alert("ไม่พบเลขที่ต้องการคัดลอก");
            }
        }

        document.getElementById("prefixInput").addEventListener("keydown", function(event) {
            if (event.key === "Enter") {
                event.preventDefault();
                generateNumbers();
            }
        });

        function logout() {
            // ฟังก์ชันการออกจากระบบ (เช่น ลบเซสชัน, เปลี่ยนไปที่หน้าเข้าสู่ระบบ)
            window.location.href = 'logout.php'; // เปลี่ยนเป็น URL หน้าล็อกเอาท์ของคุณ
        }
    </script>
</html>
