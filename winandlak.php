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

// ฟังก์ชันตรวจสอบว่า session_id ในฐานข้อมูลตรงกับเซสชันปัจจุบันหรือไม่
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

// ดึงข้อมูลวันหมดอายุจากฐานข้อมูล
$user_id = $_SESSION['user_id'];
$sql = "SELECT expiration_date FROM user_packages WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $expiration_date = $row['expiration_date'];
    $current_date = date("Y-m-d");

    // ตรวจสอบว่าถึงวันหมดอายุหรือยัง
    if ($current_date >= $expiration_date) {
        echo '<script>window.location.href = "index.php";</script>';
        exit();
    }
} else {
    echo "ไม่พบข้อมูลแพ็คเกจ";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>โปรแกรมวินเลขจากหน้าไปหลัง</title>
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


table {
    margin-left: auto;
    margin-right: auto;
}

img {
    max-width: 100%;
}

.textshadow {
    text-shadow: 5px 3px 7px #333;
}

.bigtext {
    font-weight: bold;
    font-size: 26pt;
}

.txtshadow {
    color: #000;
    text-shadow: 2px 2px 0px #ffffff, 5px 4px 0px rgba(0,0,0,0.15);
}

.blink_me {
    animation: blinker 1s linear infinite;
}

@keyframes blinker {
    50% {
        opacity: 0;
    }
}

.formbdr {
    border: 2px solid #ddd;
    padding: 15px;
    border-radius: 5px;
    background-color: #fff;
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

.greenbtn, .bluebtn, .redbtn {
    display: inline-block;
    padding: 10px 20px;
    margin: 5px;
    color: #fff;
    text-decoration: none;
    border-radius: 5px;
}

.greenbtn {
    background-color: #0400ff;
}

.bluebtn {
    background-color: #007BFF;
}

.redbtn {
    background-color: #FF0000;
}

.text-result {
    font-family: "Tahoma", "Verdana", sans-serif;
    font-size: 15px;
    font-weight: bold;
    color: rgb(0, 0, 0);
}

.number-result2 {
    font-family: "Tahoma", "Verdana", sans-serif;
    font-size: 17px;
    font-weight: bold;
    color: #0051ff;
    white-space: normal;
    word-wrap: break-word;
    padding: 10px;
    max-width: 60%;
    display: inline-block;
}

.number-result3 {
    font-family: "Tahoma", "Verdana", sans-serif;
    font-size: 17px;
    font-weight: bold;
    color: red;
    white-space: normal;
    word-wrap: break-word;
    padding: 10px;
    max-width: 60%;
    display: inline-block;
}

#result {
    border: 2px solid #ddd;
    padding: 0px;
    border-radius: 5px;
    background: linear-gradient(to right, #ffffff, #ffffff);
    margin-top: 20px;
    display: inline-block;
    max-width: 70%;
    overflow-y: auto;
    
}

#rootresult {
    border: 2px solid #ddd;
    padding: 0px;
    border-radius: 5px;
    background: linear-gradient(to right, #ffffff, #ffffff);
    margin-top: 20px;
    display: inline-block;
    max-width: 70%;
    overflow-y: auto;
}

input[type="text"] {
    width: 300px;
    height: 50px;
    box-sizing: border-box;
    text-align: center;
    margin: 0 auto;
    padding: 15px;
    border: 2px solid #ddd;
    border-radius: 5px;
    display: block;
}

td {
    width: 100%;
    text-align: center;
    vertical-align: top;
    padding: 10px;
    box-sizing: border-box;
}

.copy-btn {
    background-color: #007BFF;
    color: white;
    border: none;
    padding: 10px 20px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    cursor: pointer;
    border-radius: 5px;
    margin-left: 10px;
}

.copy-btn:hover {
    background-color: #0056b3;
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
        /* ปุ่มออกจากระบบที่อยู่ตรงข้ามกับปุ่ม "กลับไปหน้าแรก" */
        .logout-btn {
    position: fixed;
    top: 10px; /* ตำแหน่งเดียวกับปุ่ม "กลับไปหน้าแรก" */
    right: 10px; /* วางไว้ด้านขวา */
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
    top: 20px;
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
<body bgcolor="#eeeeee">
<button class="back-btn" onclick="goBack()">กลับไปหน้าแรก</button>
    <table border="0" cellpadding="0" cellspacing="0" width="100%" align="center">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" align="center">
    </table><br>
    <table border="0" align="center">
        <tbody>
            <tr>
                <td align="center" style="padding:10px;">
                <form name="form1" method="post" action="?act=process" onsubmit="return submitHandler()">
                        <table border="0" cellspacing="0" cellpadding="0" align="center">
                            <tbody>
                                <tr>
                                    <td colspan="2"><br><br>
                                        <strong style="color: back; margin-left:0px; font-size: 24px;">โปรแกรมวินเลขจากหน้าไปหลัง</strong><br>
                                        <span style=" color: red; margin-left:0px; font-size: 18px;">ป้อนเลขที่ต้องการวิน อย่างน้อย 3 ตัว</span><br><br><br>                                    
                                        <input type="text" name="txtdg1" id="txtdg1" size="30" maxlength="30" value="" style="text-align: center; margin: left 100px; margin-top:auto; padding:5px;" oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');"><br>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" align="center">
                                    <label><input type="checkbox" id="excludeDoubles"> <span style="color:red; font-weight:bold; font-size: 20px">ไม่รวมเบิ้ล</span></label><br>
                                    </td>
                                </tr>
                                <tr>
                                   <td colspan="2" align="center">
                                        <input name="btnwin" type="button" id="btnwin" value="วินเลข" class="mbtn">
                                        <input name="btnGenerate" type="button" id="btnGenerate" value="ชุดลาก" class="mbtn">
                                        <input name="btnClear" type="button" id="btnClear" value="เริ่มใหม่" onclick="clearForm();" class="mbtn">
                                         <br>
                                         <div style="display: flex; justify-content: center; margin-top: 20px;">
                                            <div id="result"></div>
                                            <div id="rootresult"></div>
                                         </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </form>                    
                    <br>
                </td>
            </tr>
        </tbody>
    </table>
    <div id="notification"></div>
    <script src="checkSessionBeforeAction3.js"></script>
</body>

<script>
function chkvaliddata(form) {
    if (form.txtdg1.value === "") {
        alert('กรุณาป้อนตัวเลขด้วย');
        form.txtdg1.focus();
        return false;
    }
    return true;
}

function generateCombinations2(numbers, includeDoubles = false) {
    const results = [];
    for (let i = 0; i < numbers.length; i++) {
        for (let j = i + (includeDoubles ? 0 : 1); j < numbers.length; j++) {
            results.push(numbers[i] + '' + numbers[j]);
        }
    }
    return results;
}

function generateCombinations3(numbers) {
    const results = [];
    // เลข 3 ตัวที่ไม่กลับเลข
    for (let i = 0; i < numbers.length - 2; i++) {
        for (let j = i + 1; j < numbers.length - 1; j++) {
            for (let k = j + 1; k < numbers.length; k++) {
                results.push(numbers[i] + '' + numbers[j] + '' + numbers[k]);
            }
        }
    }
    return results;
}

function generateCombinations3WithDoubles(numbers) {
    const results = [];
    // เลข 3 ตัวรวมเลขเบิ้ลและเลขตอง โดยไม่กลับเลข
    for (let i = 0; i < numbers.length; i++) {
        for (let j = i; j < numbers.length; j++) {
            for (let k = j; k < numbers.length; k++) {
                results.push(numbers[i] + '' + numbers[j] + '' + numbers[k]);
            }
        }
    }
    return results;
}

function copyToClipboard(text) {
    const tempInput = document.createElement("textarea");
    tempInput.value = text;
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

    // ปิดแจ้งเตือนหลังจาก 2 วินาที
    setTimeout(() => {
        document.body.removeChild(alertBox);
    }, 2000);
}

function generateAllCombinations() {
    const numbers = document.getElementById('txtdg1').value.split('').filter((v, i, self) => self.indexOf(v) === i);
    const excludeDoubles = document.getElementById('excludeDoubles').checked;
    const combinations2NoDoubles = generateCombinations2(numbers);
    const combinations2WithDoubles = generateCombinations2(numbers, true);
    const combinations3 = generateCombinations3(numbers);
    const combinations3WithDoubles = generateCombinations3WithDoubles(numbers);

    let resultHtml = 
        `<br><br><span class="text-result">เลข 3 ตัว ไม่รวมเบิ้ล</span><br>
        <span class="number-result3">${combinations3.join('-')}</span><br>
        <span class="text-result">(รวม ${combinations3.length} ชุด)</span><br><br>
        <button class="mbtn" onclick="copyToClipboard('${combinations3.join('-')}')">คัดลอก</button><br><br>

        <br><br><span class="text-result">เลข 2 ตัว ไม่รวมเบิ้ล</span> <br>
        <span class="number-result2">${combinations2NoDoubles.join('-')}</span><br>
        <span class="text-result">(รวม ${combinations2NoDoubles.length} ชุด)</span><br><br>
        <button class="mbtn" onclick="copyToClipboard('${combinations2NoDoubles.join('-')}')">คัดลอก</button><br><br>

        <br><br><span class="text-result">เลข 3 ตัว รวมเบิ้ล</span><br>
        <span class="number-result3">${combinations3WithDoubles.join('-')}</span><br>
        <span class="text-result">(รวม ${combinations3WithDoubles.length} ชุด)</span><br><br>
        <button class="mbtn" onclick="copyToClipboard('${combinations3WithDoubles.join('-')}')">คัดลอก</button><br><br>

        <br><br><span class="text-result">เลข 2 ตัว รวมเบิ้ล</span><br>
        <span class="number-result2">${combinations2WithDoubles.join('-')}</span><br>
        <span class="text-result">(รวม ${combinations2WithDoubles.length} ชุด)</span><br><br>
        <button class="mbtn" onclick="copyToClipboard('${combinations2WithDoubles.join('-')}')">คัดลอก</button><br><br>
    `;

    if (excludeDoubles) {
        resultHtml = 
            `<br><br><span class="text-result">เลข 3 ตัว ไม่รวมเบิ้ล</span> <br>
            <span class="number-result3">${combinations3.join('-')}</span><br>
            <span class="text-result">(รวม ${combinations3.length} ชุด)</span><br><br>
            <button class="mbtn" onclick="copyToClipboard('${combinations3.join('-')}')">คัดลอก</button><br><br>
            
            <br><br><span class="text-result">เลข 2 ตัว ไม่รวมเบิ้ล</span> <br>
            <span class="number-result2">${combinations2NoDoubles.join('-')}</span><br>
            <span class="text-result">(รวม ${combinations2NoDoubles.length} ชุด)</span><br><br>
            <button class="mbtn" onclick="copyToClipboard('${combinations2NoDoubles.join('-')}')">คัดลอก</button><br><br>
        `;
    }

    document.getElementById('result').innerHTML = resultHtml;
    document.getElementById('rootresult').innerHTML = '';
}

function generateNumbers() {
    let prefix = document.getElementById("txtdg1").value;

    if (prefix.length !== 2 || isNaN(prefix)) {
        alert("กรุณากรอกเลขหน้า 2 ตัวที่ถูกต้อง");
        return;
    }

    let result = '';
    for (let i = 0; i <= 9; i++) {
        result += prefix + i + '\n';
    }

    document.getElementById("rootresult").innerHTML = 
        `<div class="result-section">
            <strong>เลขชุดจาก ${prefix}:</strong><br>
            ${result.replace(/\n/g, '<br>')}
            <button class="copy-btn" onclick="copyToClipboard('${result.replace(/'/g, "\\'").replace(/\n/g, '\\n')}')">คัดลอก</button>
        </div>`;
    document.getElementById("result").innerHTML = '';
}

function clearForm() {
    document.getElementById('txtdg1').value = '';
    document.getElementById('excludeDoubles').checked = false;
    document.getElementById('result').innerHTML = '';
    document.getElementById('rootresult').innerHTML = '';
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('txtdg1').addEventListener('keypress', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault(); // ป้องกันการ submit แบบปกติของฟอร์ม
            checkSessionBeforeAction(function() {
                const input = document.getElementById('txtdg1').value;
                if (input.length >= 3) {
                    generateAllCombinations(); // สำหรับการวินเลขถ้ากรอกเลขมากกว่า 3 ตัว
                } else if (input.length === 2) {
                    generateNumbers(); // สำหรับการสร้างชุดลากถ้ากรอกเลข 2 ตัว
                } else {
                    alert('กรุณาป้อนตัวเลข 2 หรือมากกว่า 3 ตัว');
                }
            });
        }
    });

    document.getElementById('btnwin').addEventListener('click', function() {
        checkSessionBeforeAction(generateAllCombinations); // ตรวจสอบสถานะก่อนทำการวินเลข
    });

    document.getElementById('btnGenerate').addEventListener('click', function() {
        checkSessionBeforeAction(generateNumbers); // ตรวจสอบสถานะก่อนทำการสร้างชุดลาก
    });
});

// ฟังก์ชันนี้จะตรวจสอบว่า role เป็นอะไร และเปลี่ยนเส้นทางตาม role นั้น
function goBack() {
    const role = "<?php echo $_SESSION['role']; ?>";
    if (role === 'admin') {
        window.location.href = 'admin.php';
    } else {
        window.location.href = 'index.php';
    }
}

const expirationDate = "<?php echo $expiration_date; ?>"; // รับค่าวันหมดอายุจาก PHP
const currentDate = new Date().toISOString().split('T')[0]; // วันที่ปัจจุบันในรูปแบบ YYYY-MM-DD

// ถ้าวันหมดอายุเท่ากับวันที่ปัจจุบัน ให้รีเฟรชหน้าเว็บ
if (currentDate === expirationDate) {
    setTimeout(() => {
        location.reload();
    }, 5000); // ตั้งเวลาให้รีเฟรชใน 5 วินาที (5000 มิลลิวินาที)
}

function submitHandler() {
    const input = document.getElementById('txtdg1').value;
    if (input.length >= 3) {
        generateAllCombinations(); // สำหรับการวินเลข
        return false; // ป้องกันการ submit ฟอร์ม
    } else if (input.length === 2) {
        generateNumbers(); // สำหรับการสร้างเลขลาก
        return false; // ป้องกันการ submit ฟอร์ม
    } else {
        alert('กรุณาป้อนตัวเลข 2 หรือมากกว่า 3 ตัว');
        return false; // ป้องกันการ submit ฟอร์ม
    }
}
</script>
</html>
