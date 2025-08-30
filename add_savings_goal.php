<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['uid'])) {
    header("Location: index.php");
    exit();
}

$uid = $_SESSION['uid'];
$goal_name = $_POST['goal_name'];
$target_amount = $_POST['target_amount'];
$target_date = $_POST['target_date'];

$stmt = $connection->prepare("INSERT INTO savings_goals (uid, goal_name, target_amount, amount_saved, target_date) VALUES (?, ?, ?, 0, ?)");
$stmt->bind_param("isds", $uid, $goal_name, $target_amount, $target_date);

if ($stmt->execute()) {
    header("Location: dashboard.php");
} else {
    echo "Failed to add savings goal: " . $stmt->error;
}
?>
