<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("User ID not set in session.");
}

$user_id = $_SESSION['user_id'];
error_log("User ID: $user_id");

// Database connection details
$servername = "sql206.infinityfree.com";
$db_username = "if0_37184789";
$db_password = "cZq75jlVz3U";
$dbname = "if0_37184789_loguser";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check database connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

// Check if the session ID in the database matches the current session ID
$sql = "SELECT login_session FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $login_session = $row['login_session'];
    error_log("Current session ID: " . session_id());
    error_log("Database session ID: " . $login_session);

    if ($login_session !== session_id()) {
        echo "session_expired";
        exit();
    }

    // Check the expiration status of package 3
    $sql = "SELECT expiration_date FROM user_packages WHERE user_id = ? AND package_id = 3";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $package3_status = 'not_found';
    $current_date = date("Y-m-d");

    if ($row = $result->fetch_assoc()) {
        $expiration_date = $row['expiration_date'];
        error_log("Package ID: 3, Expiration Date: $expiration_date");

        if ($current_date < $expiration_date) {
            $package3_status = 'active';
        } else {
            $package3_status = 'expired';
        }
    }

    if ($package3_status === 'expired') {
        echo "package_3_expired";
    } elseif ($package3_status === 'not_found') {
        echo "no_package_3";
    } else {
        echo "valid";
    }
} else {
    error_log("Error: User ID not found or multiple entries detected.");
    echo "error";
}

$conn->close();
?>
