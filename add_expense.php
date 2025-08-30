<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['uid'])) {
    header("Location: index.php");
    exit();
}

$uid = $_SESSION['uid'];

// Get student SID using UID
$sidQuery = $connection->prepare("SELECT sid FROM tblstudent WHERE uid = ?");
$sidQuery->bind_param("i", $uid);
$sidQuery->execute();
$sidResult = $sidQuery->get_result();
$sid = $sidResult->fetch_assoc()['sid'];

$expense_name = $_POST['expense_name'];
$category = $_POST['category'];
$amount = $_POST['amount'];
$expense_date = $_POST['expense_date'];

$stmt = $connection->prepare("INSERT INTO expenses (sid, expense_name, category, amount, expense_date) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issds", $sid, $expense_name, $category, $amount, $expense_date);

if ($stmt->execute()) {
    header("Location: dashboard.php");
} else {
    echo "Failed to add expense: " . $stmt->error;
}
?>
