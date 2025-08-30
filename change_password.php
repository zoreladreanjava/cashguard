<?php
session_start();
include 'connect.php';

$errors = [];
$success = '';

// Process password change when form is submitted
if (isset($_POST['btnChangePassword'])) {
    // Sanitize and validate inputs
    $current_password = $_POST['txtcurrentpassword'];
    $new_password = $_POST['txtnewpassword'];
    $new_password2 = $_POST['txtnewpassword2'];

    // Validation
    if (empty($current_password)) $errors[] = "Current password is required";
    if (empty($new_password)) $errors[] = "New password is required";
    if ($new_password !== $new_password2) $errors[] = "New passwords don't match";
    if (strlen($new_password) < 6) $errors[] = "Password must be at least 6 characters";

    // If no errors, proceed with password update
    if (empty($errors)) {
        $uname = $_SESSION['username']; // Assuming username is stored in session
        
        // Check current password
        $check = $connection->prepare("SELECT password FROM tbluser WHERE username = ?");
        $check->bind_param("s", $uname);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (!password_verify($current_password, $user['password'])) {
                $errors[] = "Current password is incorrect";
            } else {
                $hashed_pword = password_hash($new_password, PASSWORD_DEFAULT);
                $update = $connection->prepare("UPDATE tbluser SET password = ? WHERE username = ?");
                $update->bind_param("ss", $hashed_pword, $uname);
                $update->execute();
                
                $success = "Password updated successfully!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,300italic,400italic,600' rel='stylesheet'>
    <link href="//netdna.bootstrapcdn.com/font-awesome/3.1.1/css/font-awesome.css" rel="stylesheet">
    <style>
        /* Matching register.php styles */
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }

        .testbox {
            margin: 20px auto;
            width: 400px;
            background-color: #ebebeb;
            border-radius: 8px;
            box-shadow: 1px 2px 5px rgba(0,0,0,.31);
            border: solid 1px #cbc9c9;
            padding: 20px;
        }

        h1 {
            font-size: 32px;
            font-weight: 300;
            color: #4c4c4c;
            text-align: center;
            padding: 10px 0;
            margin-bottom: 10px;
        }

        form {
            margin: 0 20px;
        }

        .input-group {
            display: flex;
            margin: 15px 0;
        }

        #icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            background-color: #03a5ba;
            color: white;
            border-radius: 4px 0 0 4px;
            box-shadow: 1px 2px 5px rgba(0,0,0,.09);
        }

        input[type=password] {
            width: 100%;
            padding: 10px;
            border: 1px solid #cbc9c9;
            border-radius: 0 4px 4px 0;
            background-color: #fff;
            box-shadow: 1px 2px 5px rgba(0,0,0,.09);
        }

        .form-buttons {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .button {
            background-color: #03a5ba;
            color: white;
            padding: 8px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            box-shadow: 0 3px rgba(58,87,175,.75);
            transition: all 0.1s linear;
            text-decoration: none;
            font-size: 14px;
        }

        .button:hover {
            background-color: #018394;
            box-shadow: none;
            transform: translateY(3px);
        }

        .button:active {
            background-color: #018394;
            box-shadow: none;
        }

        .error {
            color: #e74c3c;
            background-color: #fdeded;
            padding: 10px;
            margin: 15px 0;
            border-left: 4px solid #e74c3c;
            border-radius: 4px;
            font-size: 14px;
        }

        .success {
            color: #27ae60;
            background-color: #e8f6e8;
            padding: 10px;
            margin: 15px 0;
            border-left: 4px solid #27ae60;
            border-radius: 4px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="testbox">
        <h1>Change Password</h1>
        <hr>

        <?php if(!empty($errors)): ?>
            <div class="error">
                <?php foreach($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="success">
                <p><?php echo $success; ?></p>
            </div>
            <a href="index.php" class="button">Back to Home</a>
        <?php endif; ?>

        <form method="post">
            <div class="input-group">
                <div id="icon"><i class="icon-shield"></i></div>
                <input type="password" name="txtcurrentpassword" placeholder="Current Password" required>
            </div>

            <div class="input-group">
                <div id="icon"><i class="icon-shield"></i></div>
                <input type="password" name="txtnewpassword" placeholder="New Password" required>
            </div>

            <div class="input-group">
                <div id="icon"><i class="icon-shield"></i></div>
                <input type="password" name="txtnewpassword2" placeholder="Confirm New Password" required>
            </div>

            <div class="form-buttons">
                <a href="index.php" class="button">Cancel</a>
                <button type="submit" name="btnChangePassword" class="button">Change Password</button>
            </div>
        </form>
    </div>
</body>
</html>
