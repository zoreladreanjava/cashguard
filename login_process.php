<?php
session_start();
include 'connect.php';

// Brute force protection - 2-minute timeout after 5 failed attempts
$timeoutDuration = 120; // seconds

// Initialize login attempts if not already
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

// Check if user is locked out
if ($_SESSION['login_attempts'] >= 3) {
    if (!isset($_SESSION['lockout_time'])) {
        $_SESSION['lockout_time'] = time();
    }

    $elapsed = time() - $_SESSION['lockout_time'];

    if ($elapsed < $timeoutDuration) {
        $_SESSION['login_error'] = 'Too many failed attempts. Try again in ' . ($timeoutDuration - $elapsed) . ' seconds.';
        header("Location: index.php");
        exit();
    } else {
        // Lockout expired
        $_SESSION['login_attempts'] = 0;
        unset($_SESSION['lockout_time']);
    }
}

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validate required fields
    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = 'Username and password are required';
        $_SESSION['login_attempts']++;
        header("Location: index.php");
        exit();
    }

    // Query user by username
    $sql = "SELECT * FROM tbluser WHERE username = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Successful login
            $_SESSION['login_attempts'] = 0;
            unset($_SESSION['lockout_time']);
            session_regenerate_id(true);

            $_SESSION['loggedin'] = true;
            $_SESSION['uid'] = $user['uid'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['usertype'] = $user['usertype'];
            $_SESSION['firstname'] = $user['firstname'];

            // Redirect based on usertype
            if ($user['usertype'] === 'admin') {
                header("Location: admin_dashboard.php");
            } elseif ($user['usertype'] === 'student' || $user['usertype'] === 'employee') {
                header("Location: dashboard.php");
            } else {
                $_SESSION['login_error'] = 'Unknown user type.';
                header("Location: index.php");
            }

            exit();
        } else {
            $_SESSION['login_attempts']++;
            $_SESSION['login_error'] = 'Incorrect password.';
            header("Location: index.php");
            exit();
        }
    } else {
        $_SESSION['login_attempts']++;
        $_SESSION['login_error'] = 'User not found.';
        header("Location: index.php");
        exit();
    }
}
?>
