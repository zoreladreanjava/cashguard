<?php
session_start();
include 'connect.php';

$errors = [];
$success = '';

// Process registration if the form has been submitted
if (isset($_POST['btnRegister'])) {
    // Get and sanitize inputs
    $fname      = htmlspecialchars(trim($_POST['txtfirstname']));
    $lname      = htmlspecialchars(trim($_POST['txtlastname']));
    $gender     = $_POST['txtgender'] ?? '';
    $utype      = $_POST['txtusertype'] ?? '';
    $uname      = htmlspecialchars(trim($_POST['txtusername']));
    $pword      = $_POST['txtpassword'];
    $pword2     = $_POST['txtpassword2'];
    $prog       = isset($_POST['txtprogram']) ? $_POST['txtprogram'] : null;
    $yearlevel  = isset($_POST['txtyearlevel']) ? $_POST['txtyearlevel'] : null;

    // Basic validation
    if (empty($fname)) $errors[] = "First name is required.";
    if (empty($lname)) $errors[] = "Last name is required.";
    if (empty($gender)) $errors[] = "Gender is required.";
    if (empty($utype)) $errors[] = "User type is required.";
    if (empty($uname)) $errors[] = "Username is required.";
    if (empty($pword)) $errors[] = "Password is required.";
    if ($pword !== $pword2) $errors[] = "Passwords do not match.";
    if (strlen($pword) < 6) $errors[] = "Password must be at least 6 characters.";

    // Extra validation for students
    if (strtolower($utype) === 'student') {
        if (empty($prog)) $errors[] = "Program is required for students.";
        if (empty($yearlevel)) $errors[] = "Year level is required for students.";
    }

    // Check if username is already taken
    if (empty($errors)) {
        $stmt = $connection->prepare("SELECT username FROM tbluser WHERE username = ?");
        $stmt->bind_param("s", $uname);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "Username already taken.";
        }
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        // Hash the password
        $hashed_pword = password_hash($pword, PASSWORD_DEFAULT);

        try {
            $connection->begin_transaction();

            // Insert user into tbluser
            $stmt = $connection->prepare("INSERT INTO tbluser (firstname, lastname, gender, usertype, username, password) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $fname, $lname, $gender, $utype, $uname, $hashed_pword);
            $stmt->execute();
            $uid = $connection->insert_id;

            // For students, use provided program/yearlevel; for others, use defaults
            if (strtolower($utype) === 'student') {
                $studentProgram = $prog;
                $studentYear = $yearlevel;
            } else {
                $studentProgram = "N/A";
                $studentYear = 0;
            }

            // Insert record in tblstudent (ensures every user gets a valid sid)
            $stmt2 = $connection->prepare("INSERT INTO tblstudent (program, yearlevel, uid) VALUES (?, ?, ?)");
            $stmt2->bind_param("sii", $studentProgram, $studentYear, $uid);
            $stmt2->execute();

            $connection->commit();
            $_SESSION['success'] = 'Registration successful! You can now login.';
            header("Location: index.php");
            exit();

        } catch (Exception $e) {
            $connection->rollback();
            $errors[] = "Registration failed: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,300italic,400italic,600' rel='stylesheet'>
    <link href="//netdna.bootstrapcdn.com/font-awesome/3.1.1/css/font-awesome.css" rel="stylesheet">
    <style>
        /* Global Reset and Base Styles */
        * { box-sizing: border-box; }
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
            box-shadow: 1px 2px 5px rgba(0,0,0,0.31);
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
        hr {
            border: 0;
            border-top: 1px solid #a9a9a9;
            opacity: 0.3;
            margin: 15px 0;
        }
        .accounttype, .gender {
            display: flex;
            gap: 20px;
            margin: 15px 0;
        }
        input[type=radio] {
            visibility: hidden;
        }
        label.radio {
            cursor: pointer;
            text-indent: 35px;
            position: relative;
            display: inline-block;
            color: #4c4c4c;
        }
        label.radio:before {
            content: '';
            position: absolute;
            left: 0;
            width: 20px;
            height: 20px;
            border-radius: 100%;
            background: #03a5ba;
        }
        label.radio:after {
            content: '';
            opacity: 0;
            position: absolute;
            width: 0.5em;
            height: 0.25em;
            border: 3px solid #fff;
            border-top: none;
            border-right: none;
            transform: rotate(-45deg);
            left: 4.5px;
            top: 7.5px;
        }
        input[type=radio]:checked + label:after {
            opacity: 1;
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
            box-shadow: 1px 2px 5px rgba(0,0,0,0.09);
        }
        input[type=text], 
        input[type=password],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #cbc9c9;
            border-radius: 0 4px 4px 0;
            background-color: #fff;
            box-shadow: 1px 2px 5px rgba(0,0,0,0.09);
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
            box-shadow: 0 3px rgba(58,87,175,0.75);
            transition: all 0.1s linear;
            text-decoration: none;
            font-size: 14px;
        }
        .button:hover {
            background-color: #018394;
            box-shadow: none;
            transform: translateY(3px);
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
        .terms {
            font-size: 12px;
            color: #4c4c4c;
            margin: 15px 0;
            text-align: center;
        }
        .terms a {
            color: #03a5ba;
            text-decoration: none;
        }
        /* Hide student-only fields by default if user type is not student */
        .student-only { display: none; }
    </style>
    <script>
      window.addEventListener('DOMContentLoaded', (event) => {
        // When the DOM is loaded, check which radio button for user type is selected.
        const studentRadio = document.getElementById('student');
        const employeeRadio = document.getElementById('employee');
        const studentFields = document.querySelectorAll('.student-only');

        function toggleStudentFields() {
          if (studentRadio.checked) {
            studentFields.forEach(el => el.style.display = 'block');
          } else {
            studentFields.forEach(el => el.style.display = 'none');
          }
        }

        // Run on load and when radio buttons change.
        toggleStudentFields();
        studentRadio.addEventListener('change', toggleStudentFields);
        employeeRadio.addEventListener('change', toggleStudentFields);
      });
    </script>
</head>
<body>
    <div class="testbox">
      <h1>Registration</h1>
      <hr>
        <?php if(!empty($errors)): ?>
            <div class="error">
                <?php foreach($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

      <form method="post">
          <div class="accounttype">
              <input type="radio" id="student" name="txtusertype" value="student" <?php echo (isset($utype) && $utype == 'student') ? 'checked' : ''; ?>>
              <label for="student" class="radio">Student</label>
              <input type="radio" id="employee" name="txtusertype" value="employee" <?php echo (isset($utype) && $utype == 'employee') ? 'checked' : ''; ?>>
              <label for="employee" class="radio">Employee</label>
          </div>
          <hr>

          <div class="input-group">
              <div id="icon"><i class="icon-user"></i></div>
              <input type="text" name="txtfirstname" placeholder="First Name" required value="<?php echo isset($fname) ? $fname : ''; ?>">
          </div>

          <div class="input-group">
              <div id="icon"><i class="icon-user"></i></div>
              <input type="text" name="txtlastname" placeholder="Last Name" required value="<?php echo isset($lname) ? $lname : ''; ?>">
          </div>

          <div class="gender">
              <input type="radio" id="male" name="txtgender" value="Male" <?php echo (isset($gender) && $gender == 'Male') ? 'checked' : ''; ?>>
              <label for="male" class="radio">Male</label>
              <input type="radio" id="female" name="txtgender" value="Female" <?php echo (isset($gender) && $gender == 'Female') ? 'checked' : ''; ?>>
              <label for="female" class="radio">Female</label>
          </div>

          <div class="input-group">
              <div id="icon"><i class="icon-envelope"></i></div>
              <input type="text" name="txtusername" placeholder="Username" required value="<?php echo isset($uname) ? $uname : ''; ?>">
          </div>

          <div class="input-group">
              <div id="icon"><i class="icon-shield"></i></div>
              <input type="password" name="txtpassword" placeholder="Password" required>
          </div>

          <div class="input-group">
              <div id="icon"><i class="icon-shield"></i></div>
              <input type="password" name="txtpassword2" placeholder="Confirm Password" required>
          </div>

          <!-- Student-only Fields -->
          <div class="input-group student-only">
              <div id="icon"><i class="icon-book"></i></div>
              <select name="txtprogram">
                  <option value="">Select Program</option>
                  <option value="bsit" <?php echo (isset($prog) && $prog == 'bsit') ? 'selected' : ''; ?>>BSIT</option>
                  <option value="bscs" <?php echo (isset($prog) && $prog == 'bscs') ? 'selected' : ''; ?>>BSCS</option>
              </select>
          </div>

          <div class="input-group student-only">
              <div id="icon"><i class="icon-calendar"></i></div>
              <select name="txtyearlevel">
                  <option value="">Select Year Level</option>
                  <option value="1" <?php echo (isset($yearlevel) && $yearlevel == '1') ? 'selected' : ''; ?>>1</option>
                  <option value="2" <?php echo (isset($yearlevel) && $yearlevel == '2') ? 'selected' : ''; ?>>2</option>
                  <option value="3" <?php echo (isset($yearlevel) && $yearlevel == '3') ? 'selected' : ''; ?>>3</option>
                  <option value="4" <?php echo (isset($yearlevel) && $yearlevel == '4') ? 'selected' : ''; ?>>4</option>
              </select>
          </div>

          <div class="terms">
              <p>By clicking Register, you agree to our <a href="#">terms and conditions</a>.</p>
          </div>

          <div class="form-buttons">
              <a href="index.php" class="button">Cancel</a>
              <button type="submit" name="btnRegister" class="button">Register</button>
          </div>
      </form>
    </div>
    <script>
      // The script below is for a cancel button, if needed.
      document.querySelector('.cancel-btn')?.addEventListener('click', function(e) {
        if (confirm('Are you sure you want to cancel registration?')) {
            return true;
        }
        e.preventDefault();
      });
    </script>
</body>
</html>
