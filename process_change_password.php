<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['loggedin'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate inputs
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['password_msg'] = "All fields are required!";
        header("Location: change_password.php");
        exit();
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['password_msg'] = "New passwords do not match!";
        header("Location: change_password.php");
        exit();
    }

    // Fetch current password from the database
    $username = $_SESSION['username'];
    $sql = "SELECT password FROM tbluser WHERE username = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if (password_verify($current_password, $user['password'])) {
            // Update password in the database
            $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE tbluser SET password = ? WHERE username = ?";
            $update_stmt = $connection->prepare($update_sql);
            $update_stmt->bind_param("ss", $new_password_hashed, $username);
            $update_stmt->execute();
            
            $_SESSION['password_msg'] = "Congratulations!";
            header("Location: change_password.php?success=true");
            exit();
        } else {
            $_SESSION['password_msg'] = "Current password is incorrect!";
            header("Location: change_password.php");
            exit();
        }
    }
}
?>
