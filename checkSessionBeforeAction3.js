function checkSessionBeforeAction(callback) {
    fetch('check_session_and_package3.php')
            .then(response => response.text()) // Assuming response is plain text
            .then(data => {
                console.log('Server response for package 3:', data); // Debugging line to check server response
    
                const showNotification = (message, url) => {
                    const notification = document.getElementById('notification');
                    notification.textContent = message;
                    notification.style.display = 'block';
    
                    setTimeout(() => {
                        notification.style.display = 'none';
                        window.location.href = url; // Redirect to the specified URL after 3 seconds
                    }, 3000); // 3000 milliseconds = 3 seconds
                };
    
                // Handle responses for package 3
                switch (data.trim()) {
                    case 'session_expired':
                        showNotification("มีการล็อคอินจากที่อื่น!!!", 'login.php');
                        break;
                    case 'package_3_expired':
                        showNotification("แพ็คเกจนี้หมดอายุแล้ว กรุณาซื้อแพ็คเกจใหม่", 'index.php');
                        break;
                    case 'no_package_3':
                        showNotification("ไม่มีแพ็คเกจนี้ กรุณาซื้อแพ็คเกจใหม่", 'index.php');
                        break;
                    case 'valid':
                        if (callback && typeof callback === 'function') {
                            callback(); // Call the callback if the status is valid
                        }
                        break;
                    default:
                        showNotification("โปรดรอ 3 วิ เพื่อให้ระบบรีหน้าเว็บใหม่", 'winandlak.php');
                        console.error('Unexpected server response:', data);
                        break;
                }
            })
            .catch(error => {
                console.error('Error checking session for package 3:', error);
                showNotification("เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์ โปรดลองใหม่อีกครั้ง", 'winandlak.php');
            });
    }