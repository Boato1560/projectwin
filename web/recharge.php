<!DOCTYPE html>
<html lang="en">
<link rel="icon" href="img/ddlakwin.jpg" type="image/x-icon">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="img/dd lakwin.jpg" type="image/x-icon">
    <title>เติมเงิน</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-qrcode/1.0/jquery.qrcode.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#recharge-form').on('submit', function(event) {
                event.preventDefault(); // ป้องกันการส่งฟอร์มแบบปกติ

                var points = $('#points').val();

                // สร้างคิวอาร์โค้ด
                $('#qr-code').empty();
                $('#qr-code').qrcode({
                    text: 'https://example.com/pay?amount=' + points // เปลี่ยน URL ตามต้องการ
                });

                $('#message').text('กรุณาทำการโอนเงินตามจำนวนที่ระบุในคิวอาร์โค้ด');
            });
        });
    </script>
</head>
<body>
    <h2>เติมเงิน</h2>
    <form id="recharge-form">
        <label for="points">จำนวนเงินที่ต้องการเติม (ในหน่วย point):</label>
        <input type="number" id="points" name="points" required>
        <button type="submit">สร้างคิวอาร์โค้ด</button>
    </form>
    <div id="qr-code"></div>
    <p id="message"></p>
</body>
</html>
