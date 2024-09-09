<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แจ้งเตือน</title>
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
        .warning-container {
    background-color: #fff;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    width: auto; /* ปรับขนาดกล่องตามขนาดเนื้อหา */
    max-width: 90%; /* จำกัดขนาดกล่องไม่ให้กว้างเกินไปบนหน้าจอเล็ก */
    box-sizing: border-box;
    text-align: center;
}

        h2 {
            color: #e74c3c;
            margin-bottom: 20px;
        }
        button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-bottom: 10px;
            background-color: #3498db;
            color: #fff;
        }
        button:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="warning-container">
        <h2>มีการใช้งานบัญชีนี้อยู่ในอุปกรณ์อื่น!</h2>
        <p>โปรดกลับไปหน้าแรกหรือทำการล็อกอินใหม่</p>
        <button onclick="window.location.href='index.php'">กลับไปหน้าแรก</button>
    </div>
</body>
</html>
