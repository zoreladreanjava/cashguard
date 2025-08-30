<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['uid'])) {
    header("Location: index.php");
    exit();
}

$uid = $_SESSION['uid'];
$month = $_POST['month'];
$year = $_POST['year'];
$total_budget = $_POST['total_budget'];

$stmt = $connection->prepare("INSERT INTO budgets (uid, month, year, total_budget, amount_spent) VALUES (?, ?, ?, ?, 0)");
$stmt->bind_param("iiid", $uid, $month, $year, $total_budget);

if ($stmt->execute()) {
    header("Location: dashboard.php");
} else {
    echo "Failed to add budget: " . $stmt->error;
}
?>
