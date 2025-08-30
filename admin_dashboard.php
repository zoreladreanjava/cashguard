<?php
session_start();
include 'connect.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Query to get total users
$totalUsersSql = "SELECT COUNT(*) as total_users FROM tbluser";
$totalUsersResult = $connection->query($totalUsersSql);
$totalUsers = $totalUsersResult->fetch_assoc()['total_users'];

// Query to count student users
$studentCountSql = "SELECT COUNT(*) as student_count FROM tbluser WHERE usertype = 'student'";
$studentCountResult = $connection->query($studentCountSql);
$studentCount = $studentCountResult->fetch_assoc()['student_count'] ?? 0;

// Query to count employee users
$employeeCountSql = "SELECT COUNT(*) as employee_count FROM tbluser WHERE usertype = 'employee'";
$employeeCountResult = $connection->query($employeeCountSql);
$employeeCount = $employeeCountResult->fetch_assoc()['employee_count'] ?? 0;

// Query to get the average budget
$avgBudgetSql = "SELECT AVG(total_budget) as avg_budget FROM budgets";
$avgBudgetResult = $connection->query($avgBudgetSql);
$avgBudget = $avgBudgetResult->fetch_assoc()['avg_budget'] ?? 0;

// Query to get savings goals for all users
$savingsGoalsSql = "
    SELECT u.firstname, u.lastname, sg.goal_name, sg.target_amount, sg.amount_saved, sg.target_date 
    FROM tbluser u
    JOIN savings_goals sg ON u.uid = sg.uid
";
$savingsGoalsResult = $connection->query($savingsGoalsSql);

// Query to get expenses for all users
$expensesSql = "
    SELECT u.firstname, u.lastname, ex.expense_name, ex.category, ex.amount, ex.expense_date
    FROM expenses ex
    JOIN tblstudent s ON ex.sid = s.sid
    JOIN tbluser u ON s.uid = u.uid
";
$expensesResult = $connection->query($expensesSql);

// Query for total spending per month
$spendingSql = "SELECT MONTH(expense_date) as month, SUM(amount) as total_spent FROM expenses GROUP BY MONTH(expense_date)";
$spendingResult = $connection->query($spendingSql);

// Query for user count by gender
$genderSql = "SELECT gender, COUNT(*) as gender_count FROM tbluser GROUP BY gender";
$genderResult = $connection->query($genderSql);

// Query for total spending per month (again for demonstration)
$totalSpentSql = "SELECT MONTH(expense_date) as month, SUM(amount) as total_spent FROM expenses GROUP BY MONTH(expense_date)";
$totalSpentResult = $connection->query($totalSpentSql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="styles.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f4f7fc;
      margin: 0;
      padding: 0;
    }

    .container {
      width: 90%;
      margin: 20px auto;
    }

    nav ul {
      list-style-type: none;
      padding: 0;
      display: flex;
      justify-content: flex-end;
      background-color: #2d3e50;
      margin: 0;
    }

    nav ul li {
      margin-left: 20px;
    }

    nav ul li a {
      text-decoration: none;
      color: white;
      font-size: 18px;
      padding: 10px;
      display: inline-block;
      transition: background-color 0.3s ease;
    }

    nav ul li a:hover {
      background-color: #03a5ba;
      border-radius: 5px;
    }

    h1 {
      color: #333;
      text-align: center;
      margin-bottom: 30px;
    }

    h2 {
      color: #444;
      margin-bottom: 15px;
    }

    .insights p {
      background-color: #ffffff;
      border-radius: 8px;
      padding: 15px;
      margin: 10px 0;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      font-size: 16px;
      color: #555;
    }

    .tables table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 40px;
      background-color: #fff;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .tables th, .tables td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    .tables th {
      background-color: #03a5ba;
      color: white;
      font-size: 16px;
    }

    .tables tr:hover {
      background-color: #f1f1f1;
      cursor: pointer;
    }

    .tables td {
      font-size: 14px;
      color: #555;
    }

    /* Graphs Section Styling */
    .graphs {
      margin-top: 30px;
      display: block; /* Stacks items vertically */
    }

    .graph-item {
      width: 80%;             /* Adjust width as needed */
      max-width: 800px;       /* Maximum width for each chart */
      margin: 20px auto;      /* Center items with vertical spacing */
      background: #fff;
      padding: 15px;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .graph-item canvas {
      width: 100%;   /* Fill the container width */
      height: 400px; /* Adjust chart height as needed */
      display: block;
      margin: 0 auto;
    }

    section {
      margin-bottom: 50px;
    }

    section h2 {
      font-size: 24px;
      color: #333;
    }

    section p {
      font-size: 18px;
      color: #555;
      margin-bottom: 20px;
    }

    /* Mobile responsiveness */
    @media (max-width: 768px) {
      .container {
        width: 100%;
        margin: 0;
        padding: 10px;
      }

      nav ul {
        flex-direction: column;
        align-items: flex-start;
      }

      nav ul li {
        margin-left: 0;
        margin-bottom: 10px;
      }

      .insights, .tables, .graphs {
        padding: 20px;
        margin-bottom: 30px;
      }

      .tables th, .tables td {
        font-size: 12px;
      }

      .graph-item {
        width: 90%;
        max-width: 90%;
      }
      
      .graph-item canvas {
        height: 300px; /* Slightly lower height for mobile devices */
      }
    }

  </style>
</head>
<body>
  <nav>
    <ul>
      <li><a href="change_password.php">Change Password</a></li>| 
      <li><a href="logout.php">Logout</a></li>
    </ul>
  </nav>
  <div class="container">
    <h1>Admin Dashboard</h1>
    
    <!-- Insights Section -->
    <section class="insights">
      <h2>Insights</h2>
      <p>Total Users: <?php echo $totalUsers; ?></p>
      <p>Average Budget: <?php echo number_format($avgBudget, 2); ?></p>
      <p>Students: <?php echo $studentCount; ?> | Employees: <?php echo $employeeCount; ?></p>
    </section>
    
    <!-- Savings Goals Table -->
    <section class="tables">
      <h2>Savings Goals</h2>
      <table>
        <thead>
          <tr>
            <th>User</th>
            <th>Goal</th>
            <th>Target Amount</th>
            <th>Amount Saved</th>
            <th>Target Date</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $savingsGoalsResult->fetch_assoc()): ?>
            <tr>
              <td><?php echo $row['firstname'] . ' ' . $row['lastname']; ?></td>
              <td><?php echo $row['goal_name']; ?></td>
              <td><?php echo number_format($row['target_amount'], 2); ?></td>
              <td><?php echo number_format($row['amount_saved'], 2); ?></td>
              <td><?php echo $row['target_date']; ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>

      <!-- Expenses Table -->
      <h2>Expenses</h2>
      <table>
        <thead>
          <tr>
            <th>User</th>
            <th>Expense Name</th>
            <th>Category</th>
            <th>Amount</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $expensesResult->fetch_assoc()): ?>
            <tr>
              <td><?php echo $row['firstname'] . ' ' . $row['lastname']; ?></td>
              <td><?php echo $row['expense_name']; ?></td>
              <td><?php echo $row['category']; ?></td>
              <td><?php echo number_format($row['amount'], 2); ?></td>
              <td><?php echo $row['expense_date']; ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </section>
    
    <!-- Graphs Section -->
    <section class="graphs">
      <h2>Graphs</h2>
      <div class="graph-item">
        <canvas id="usersSpendingPerMonth"></canvas>
      </div>
      <div class="graph-item">
        <canvas id="usersByGender"></canvas>
      </div>
      <div class="graph-item">
        <canvas id="totalSpentPerMonth"></canvas>
      </div>
    </section>
  </div>

  <script>
    // Spending per Month Chart (line chart)
    const spendingData = <?php echo json_encode($spendingResult->fetch_all(MYSQLI_ASSOC)); ?>;
    const spendingMonths = spendingData.map(item => item.month);
    const spendingAmounts = spendingData.map(item => item.total_spent);

    const ctx1 = document.getElementById('usersSpendingPerMonth').getContext('2d');
    new Chart(ctx1, {
      type: 'line',
      data: {
        labels: spendingMonths,
        datasets: [{
          label: 'Total Spending per Month',
          data: spendingAmounts,
          borderColor: 'rgba(75, 192, 192, 1)',
          fill: false
        }]
      }
    });

    // Users by Gender Chart (pie chart)
    const genderData = <?php echo json_encode($genderResult->fetch_all(MYSQLI_ASSOC)); ?>;
    const genderLabels = genderData.map(item => item.gender);
    const genderCounts = genderData.map(item => item.gender_count);

    const ctx2 = document.getElementById('usersByGender').getContext('2d');
    new Chart(ctx2, {
      type: 'pie',
      data: {
        labels: genderLabels,
        datasets: [{
          label: 'Users by Gender',
          data: genderCounts,
          backgroundColor: ['#808080','#b0b0b0', '#FF6384', '#36A2EB']
        }]
      }
    });

    // Total Spent per Month Chart (bar chart)
    const totalSpentData = <?php echo json_encode($totalSpentResult->fetch_all(MYSQLI_ASSOC)); ?>;
    const totalSpentMonths = totalSpentData.map(item => item.month);
    const totalSpentAmounts = totalSpentData.map(item => item.total_spent);

    const ctx3 = document.getElementById('totalSpentPerMonth').getContext('2d');
    new Chart(ctx3, {
      type: 'bar',
      data: {
        labels: totalSpentMonths,
        datasets: [{
          label: 'Total Amount Spent per Month',
          data: totalSpentAmounts,
          backgroundColor: 'rgba(255, 159, 64, 0.2)',
          borderColor: 'rgba(255, 159, 64, 1)',
          borderWidth: 1
        }]
      }
    });
  </script>
</body>
</html>
