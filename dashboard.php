<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['uid'])) {
  header("Location: index.php");
  exit();
}

$uid = $_SESSION['uid'];
$sid = null;

// Get sid from tblstudent for this user.
// If none is found, you should ideally have created one during registration.
// Here we fallback by setting $sid = 0, though in production, you should ensure a valid record exists.
$sidQuery = $connection->prepare("SELECT sid FROM tblstudent WHERE uid = ?");
$sidQuery->bind_param("i", $uid);
$sidQuery->execute();
$sidResult = $sidQuery->get_result();
if ($sidResult && $row = $sidResult->fetch_assoc()) {
    $sid = $row['sid'];
} else {
    // Fallback: assign a dummy SID so that the query doesn't error out.
    // NOTE: In a real-world scenario, please ensure every user gets a tblstudent record.
    $sid = 0;
}

// Fetch savings goals (available for all users)
$goalsStmt = $connection->prepare("SELECT goal_name, target_amount, amount_saved, target_date FROM savings_goals WHERE uid = ?");
$goalsStmt->bind_param("i", $uid);
$goalsStmt->execute();
$goalsResult = $goalsStmt->get_result();

// Fetch budgets (for all users)
$budgetsStmt = $connection->prepare("SELECT month, year, total_budget, amount_spent FROM budgets WHERE uid = ?");
$budgetsStmt->bind_param("i", $uid);
$budgetsStmt->execute();
$budgetsResult = $budgetsStmt->get_result();

// Fetch expenses (now for all users, expecting that tblstudent record exists)
$expensesResult = null;
$totalExpenses = 0;
if ($sid != 0) {
    $expensesStmt = $connection->prepare("SELECT expense_name, category, amount, expense_date FROM expenses WHERE sid = ?");
    $expensesStmt->bind_param("i", $sid);
    $expensesStmt->execute();
    $expensesResult = $expensesStmt->get_result();

    // Calculate total expenses
    $expQuery = $connection->prepare("SELECT SUM(amount) AS total_expenses FROM expenses WHERE sid = ?");
    $expQuery->bind_param("i", $sid);
    $expQuery->execute();
    $expRes = $expQuery->get_result();
    if ($expRes && $expRow = $expRes->fetch_assoc()) {
        $totalExpenses = $expRow['total_expenses'] ?? 0;
    }
} else {
    // If $sid is 0 then we set expenses result to an empty result
    $totalExpenses = 0;
}

// Total savings (for all users)
$totalSavings = 0;
$savingsStmt = $connection->prepare("SELECT SUM(amount_saved) AS total_saved FROM savings_goals WHERE uid = ?");
$savingsStmt->bind_param("i", $uid);
$savingsStmt->execute();
$savResult = $savingsStmt->get_result();
if ($savResult && $savRow = $savResult->fetch_assoc()) {
    $totalSavings = $savRow['total_saved'] ?? 0;
}

// Latest budget (if available)
$totalBudget = 0;
$budgetStmt = $connection->prepare("SELECT total_budget FROM budgets WHERE uid = ? ORDER BY year DESC, month DESC LIMIT 1");
$budgetStmt->bind_param("i", $uid);
$budgetStmt->execute();
$budResult = $budgetStmt->get_result();
if ($budResult && $budRow = $budResult->fetch_assoc()) {
    $totalBudget = $budRow['total_budget'] ?? 0;
}

// Calculate remaining budget
$remaining = $totalBudget - $totalExpenses;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <style>
        /* General Reset and Base Styles */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: #f4f7f6;
            color: #333;
            padding: 20px;
        }
        header {
            background-color: #00796b;
            padding: 15px 0;
            color: white;
            text-align: center;
        }
        .header-container h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .nav-links a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
            font-size: 1rem;
        }
        .nav-links a:hover {
            text-decoration: underline;
        }
        .container { margin-top: 20px; }
        .summary {
            display: flex;
            justify-content: space-around;
            margin-bottom: 30px;
        }
        .stat {
            background-color: #fff;
            padding: 15px;
            border-radius: 5px;
            width: 22%;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .stat h3 {
            font-size: 1.2rem;
            color: #00796b;
        }
        .stat p {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .section {
            margin-bottom: 30px;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h2 {
            color: #00796b;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
        .form input {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 2px solid #00796b;
            border-radius: 5px;
            font-size: 1rem;
        }
        .form button {
            padding: 10px 15px;
            background-color: #00796b;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
        }
        .form button:hover {
            background-color: #004d40;
        }
        ul {
            list-style-type: none;
            margin-top: 15px;
        }
        ul li {
            font-size: 1rem;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['firstname']); ?>!</h1>
            <div class="nav-links">
              <a href="index.php">Home</a> | 
              <a href="logout.php">Logout</a> | 
              <a href="change_password.php">Change Password</a>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Summary Stats -->
        <div class="summary">
            <div class="stat">
                <h3>Total Savings</h3>
                <p>₱<?php echo number_format($totalSavings, 2); ?></p>
            </div>
            <div class="stat">
                <h3>Total Expenses</h3>
                <p>₱<?php echo number_format($totalExpenses, 2); ?></p>
            </div>
            <div class="stat">
                <h3>Current Budget</h3>
                <p>₱<?php echo number_format($totalBudget, 2); ?></p>
            </div>
            <div class="stat">
                <h3>Remaining Budget</h3>
                <p>₱<?php echo number_format($remaining, 2); ?></p>
            </div>
        </div>

        <!-- Savings Goals Section -->
        <div class="section">
            <h2>Savings Goals</h2>
            <form action="add_savings_goal.php" method="post" class="form">
                <input type="text" name="goal_name" placeholder="Goal Name" required>
                <input type="number" name="target_amount" placeholder="Target Amount" required>
                <input type="date" name="target_date" required>
                <button type="submit">Add Goal</button>
            </form>
            <ul>
                <?php if ($goalsResult && $goalsResult->num_rows > 0): ?>
                    <?php while ($row = $goalsResult->fetch_assoc()): ?>
                        <li><?php echo "{$row['goal_name']} - ₱" . number_format($row['amount_saved'], 2) . "/₱" . number_format($row['target_amount'], 2) . " (Target: {$row['target_date']})"; ?></li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li>No savings goals to display.</li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Expenses Section -->
        <div class="section">
            <h2>Expenses</h2>
            <form action="add_expense.php" method="post" class="form">
                <input type="text" name="expense_name" placeholder="Expense Name" required>
                <input type="text" name="category" placeholder="Category" required>
                <input type="number" name="amount" placeholder="Amount" required>
                <input type="date" name="expense_date" required>
                <button type="submit">Add Expense</button>
            </form>
            <ul>
                <?php if ($expensesResult !== null && $expensesResult->num_rows > 0): ?>
                    <?php while ($row = $expensesResult->fetch_assoc()): ?>
                        <li><?php echo "{$row['expense_name']} ({$row['category']}) - ₱" . number_format($row['amount'], 2) . " on {$row['expense_date']}"; ?></li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li>No expenses to display.</li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Budgets Section -->
        <div class="section">
            <h2>Budgets</h2>
            <form action="add_budget.php" method="post" class="form">
                <input type="number" name="month" placeholder="Month (1-12)" required>
                <input type="number" name="year" placeholder="Year" required>
                <input type="number" name="total_budget" placeholder="Total Budget" required>
                <button type="submit">Add Budget</button>
            </form>
            <ul>
                <?php if ($budgetsResult && $budgetsResult->num_rows > 0): ?>
                    <?php while ($row = $budgetsResult->fetch_assoc()): ?>
                        <li><?php echo "{$row['month']}/{$row['year']} - ₱{$row['amount_spent']} / ₱{$row['total_budget']}"; ?></li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li>No budgets to display.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</body>
</html>
