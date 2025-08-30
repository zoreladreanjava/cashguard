<?php
session_start();

// Redirect logic
if (isset($_SESSION['loggedin'])) {
    // If logged in and accessing index.php, redirect to home section
    if (basename($_SERVER['PHP_SELF']) === 'index.php') {
        echo "<script>showSection('home');</script>";
    }
} else {
    // If not logged in and trying to access any section other than login, redirect to login
    if (!isset($_GET['section']) || $_GET['section'] !== 'login') {
        echo "<script>showSection('login');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CashGuard - Smart Budgeting App</title>
  <style>
    /* Global Reset and Base Styles */
    * { box-sizing: border-box; }
    body { 
      font-family: Arial, sans-serif; 
      margin: 0; 
      line-height: 1.5; 
      background: linear-gradient(90deg, rgba(207,237,238,1) 17%, rgba(232,252,255,1) 50%);
    }
    /* Header */
    .header {
      padding: 20px;
      text-align: center;
      background: linear-gradient(90deg, rgba(133,255,166,1) 0%, rgba(8,124,56,0.5) 50%, rgba(207,233,238,1) 100%);
      color: white;
      font-weight: bold;
      text-shadow: 0.5px 0.5px 5px #032311;
      transition: background 1s;
      transition: 0.5s;
    }
    .header:hover {
      background: linear-gradient(90deg, rgba(133,255,166,1) 0%, rgba(8,124,56,1) 50%, rgba(207,233,238,1) 100%);
      color: #064420;
      text-shadow: 0.5px 0.5px 10px #ffffff;
      transform: scale(1.05);
    }
    /* Layout Container */
    .layout-container {
      display: flex;
      min-height: calc(100vh - 160px); /* Adjust based on header/footer height */
    }
    /* Sidebar Navigation */
    .sidebar {
      width: 250px;
      background: radial-gradient(90deg, rgba(133,255,166,1) 0%, rgba(8,124,56,0.5) 50%, rgba(207,233,238,1) 100%);
      padding: 20px;
      position: sticky;
      top: 0;
      height: 100vh;
      overflow-y: auto;
    }
    .sidebar ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    .sidebar li { margin-bottom: 10px; }
    .sidebar a {
      color: #40566c;
      text-decoration: none;
      font-size: 18px;
      display: block;
      padding: 10px;
      border-radius: 5px;
      transition: background 0.3s;
    }
    .sidebar a:hover {
      background: #40566c;
      color: white;
      transition: 0.5s;
    }

    .sidebar a:active {
        transform: scale(1.05);
    }
    /* Main Content Area */
    main.container {
      flex: 1;
      margin: 20px;
      padding: 20px;
      background: white;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    /* Page Sections */
    section.page { display: none; }
    section.page.active { display: block; }
    /* Footer */
    .footer {
      padding: 20px;
      text-align: center;
      background: linear-gradient(90deg, rgba(133,238,255,1) 0%, rgba(2,165,185,1) 50%, rgba(207,237,238,1) 100%);
      color: white;
      text-shadow: 5px 5px 15px #40566c;
      transition: background 1s;
      transition: 0.5s;
    }
    .footer:hover {
      background: linear-gradient(90deg, rgba(133,238,255,0.7) 0%, rgba(2,165,185,0.7) 50%, rgba(207,237,238,0.7) 100%);
      color: #40566c;
      text-shadow: 2px 2px 15px #ffffff;
      transform: scale(1.05);
    }
    /* ----- Home Page Content ----- */
    .content-img {
      background-color: rgba(197,248,255,0.55);
      max-width: 683px;
      padding: 20px;
      margin: 20px auto;
      text-align: center;
      box-shadow: 10px 10px 10px rgba(133,238,255,0.4);
      border-radius: 38px;
      transition: transform 0.5s;
    }
    .content-img img {
      max-width: 100%;
      height: auto;
      border-radius: 48px;
      transition: transform 0.5s, box-shadow 0.5s;
    }
    .content-img img:hover {
      transform: scale(1.02);
      box-shadow: 7px 7px 7px rgba(226,226,226,1);
    }
    /* ----- Features Page Styles ----- */
    section.features section { margin-bottom: 40px; }
    section.features h2 { margin-bottom: 10px; }
    /* ----- About Us Page Styles ----- */
    .about-section {
      padding: 20px;
      text-align: center;
      background: linear-gradient(90deg, rgba(207,237,238,0.7) 0%, rgba(2,165,185,0.7) 50%, rgba(207,237,238,0.7) 100%);
      color: white;
      text-shadow: 5px 5px 15px #40566c;
      transition: background 1s;
      margin-bottom: 40px;
    }
    .row2 { overflow: hidden; }
    .column { float: left; width: 33.3%; padding: 0 8px; margin-bottom: 16px; }
    .card { box-shadow: 0 4px 8px rgba(0,0,0,0.2); margin: 8px; }
    .container-aboutUs { padding: 0 16px; text-align: center; }
    .button { border: none; padding: 8px; background-color: #03a5ba; color: white; cursor: pointer; width: 100%; border-radius: 15px; transition: transform 1s; }
    .button:hover { background-color: #4ecfe2; transform: scale(1.02); }
    .button:active {transform: scale(1.05);}
    /* Ensure About Us images fit their boxes */
    .card img {
      width: 100%;
      height: 300px;
      object-fit: cover;
    }
    /* ----- Contact Us Page Styles ----- */
    .container-contactUs {
      border-radius: 5px;
      background: linear-gradient(90deg, rgba(207,237,238,0.3) 0%, rgba(2,165,185,0.2) 50%, rgba(207,237,238,0.3) 100%);
      padding: 10px;
      color: #40566c;
      margin-bottom: 40px;
    }
    .row:after { content: ""; display: table; clear: both; }
    .column-contactUs { float: left; width: 50%; padding: 20px; }
    input[type=text], select, textarea {
      width: 100%; padding: 12px; border: 1px solid #ccc; margin: 6px 0 16px; resize: vertical;
    }
    input[type=submit] { background-color: #03a5ba; color: white; padding: 12px 20px; border: none; cursor: pointer; }
    input[type=submit]:hover { background-color: #4ecfe2; }
    /* ----- Login Page Styles ----- */
    .login-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      width: 80%;
      max-width: 400px;
      background-color: #fff;
      padding: 20px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      border-radius: 8px;
      margin: 20px auto;
    }
    .logo { text-align: center; margin-bottom: 20px; }
    .logo img { max-width: 100px; border-radius: 19px; transition: transform 0.5s, box-shadow 0.5s; }
    .logo img:hover { transform: scale(1.02); box-shadow: 7px 7px 7px rgba(133,238,255,0.8); }
    .login-form { width: 100%; text-align: center; }
    .login-form input {
      width: 100%;
      padding: 10px;
      margin: 5px 0 15px;
      border: 1px solid #ccc;
      border-radius: 4px;
      background-color: #e4eaf0;
      transition: background 1s;
    }
    .login-form input:hover { background-color: white; }
    .buttons { display: flex; justify-content: center; gap: 10px; margin-top: 10px; }
    .buttons .button { flex: 1; max-width: 120px; }
    /* Clear floats */
    .clearfix::after { content: ""; clear: both; display: table; }
    /* Responsive */
    @media screen and (max-width: 700px) {
      .column, .column-contactUs { width: 100%; }
      .layout-container { flex-direction: column; }
      .sidebar { width: 100%; height: auto; position: relative; }
    }

    .error-message {
      background-color: #ffdddd;
      border: 1px solid #ff5c5c;
      color: #a70000;
      padding: 10px 15px;
      margin: 10px 0;
      border-radius: 5px;
      font-weight: bold;
      text-align: center;
    }

    .error-message {
      animation: fadeIn 0.5s ease-in-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    .login-form.disabled {
      pointer-events: none;
      opacity: 0.4;
    }

    .button.fade-in {
      opacity: 0;
      transition: opacity 1s ease-in-out;
    }

    .button.enabled {
      opacity: 1;
    }

    #login-error.success {
      background-color: #27ae60; /* Green background */
      color: #fff; /* White text */
    }


  </style>

  <script>
    function showSection(sectionId) {
      // Only allow login section if not logged in
      <?php if (!isset($_SESSION['loggedin'])): ?>
        if (sectionId !== 'login') {
            sectionId = 'login';
        }
      <?php endif; ?>

      var pages = document.querySelectorAll("section.page");
      pages.forEach(function(page) {
        page.classList.remove("active");
      });

      var target = document.getElementById(sectionId);
      if (target) {
        target.classList.add("active");
      }

      window.scrollTo(0, 0);

      // Update URL without reloading the page
      history.pushState(null, null, '?section=' + sectionId);
    }

    window.onload = function() {
      // Check session status from PHP
      <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
        showSection('home');
      <?php else: ?>
        showSection('login');
      <?php endif; ?>
    };

    // Handle back/forward browser navigation
    window.addEventListener('popstate', function() {
      const urlParams = new URLSearchParams(window.location.search);
      const section = urlParams.get('section') || 
        (<?php echo isset($_SESSION['loggedin']) ? "'home'" : "'login'"; ?>);
      showSection(section);
    });
  </script>

</head>
<body>
  <!-- Header -->
  <header class="header">
    <h1>CashGuard</h1>
    <p>Your Smart Financial Guardian</p>
  </header>
  
  <!-- Layout Container: Sidebar & Main Content -->
  <div class="layout-container">
    <!-- Sidebar Navigation -->
    <?php if (isset($_SESSION['loggedin'])): ?>
    <nav class="sidebar">
      <ul>
        <li><a href="#" onclick="showSection('home');">Home</a></li>
        <li><a href="#" onclick="showSection('features');">Features</a></li>
        <li><a href="#" onclick="showSection('about');">About Us</a></li>
        <li><a href="#" onclick="showSection('contact');">Contact Us</a></li>
        <li><a href="dashboard.php">Dashboard</a></li>
        <a href="change_password.php">Change Password</a>
        <li><a href="logout.php">Log Out</a></li>
      </ul>
    </nav>
    <?php endif; ?>
    
    <!-- Main Content Area -->
    <main class="container">
      <!-- Home Section (from Act5.html) -->
      <section id="home" class="page">
        <article>
          <h2>Welcome to CashGuard</h2>
          <h5>Manage Your Finances Smarter, Every Day</h5>
          <div class="content-img">
            <img src="image/cashguard_logo.gif" alt="CashGuard App" loading="lazy">
          </div>
          <p>
            CashGuard empowers you to take control of your financial future. Whether you’re a student or a young professional, our app is tailored to help you track expenses, set savings goals, and avoid overspending.
          </p>
          <div class="content-img">
            <img src="image/cashguard_white.gif" alt="CashGuard App" loading="lazy">
          </div>
        </article>
      </section>
      
      <!-- Features Section (from Features_Page-Act5.html) -->
      <section id="features" class="page features">
        <article>
          <h2>Features of CashGuard</h2>
          <h5>Enhance your financial management with our smart features.</h5>
          <section id="expense-tracking">
            <h2>Expense Tracking</h2>
            <div class="content-img">
              <img src="image/Expense-Tracking.gif" alt="Expense Tracking" loading="lazy">
            </div>
            <p>
              Track every expense effortlessly. CashGuard allows you to record transactions automatically, categorize them into custom groups, and monitor spending trends to help you stay within budget.
            </p>
          </section>
          <section id="offline-functionality">
            <h2>Offline Functionality</h2>
            <div class="content-img">
              <img src="image/Offline-Functionality.gif" alt="Offline Functionality" loading="lazy">
            </div>
            <p>
              Access your financial data anytime, anywhere. With offline capabilities, you can update your transactions and review past records even without an internet connection.
            </p>
          </section>
          <section id="data-security">
            <h2>Data Security</h2>
            <div class="content-img">
              <img src="image/Data-Security.gif" alt="Data Security" loading="lazy">
            </div>
            <p>
              Your data is our top priority. CashGuard uses robust encryption and security protocols to ensure that your personal and financial information remains safe and private.
            </p>
          </section>
          <section id="savings-goals">
            <h2>Savings Goals</h2>
            <div class="content-img">
              <img src="image/Savings-Goals.gif" alt="Savings Goals" loading="lazy">
            </div>
            <p>
              Set clear savings goals and track your progress. Whether you're saving for a large purchase or building an emergency fund, our app helps you set targets and monitor your achievements over time.
            </p>
          </section>
          <section id="financial-tips">
            <h2>Financial Tips & Education</h2>
            <div class="content-img">
              <img src="image/Financial-Tips.gif" alt="Financial Tips" loading="lazy">
            </div>
            <p>
              Improve your financial literacy with tailored advice and actionable tips. CashGuard offers personalized suggestions based on your spending habits, helping you make smarter decisions.
            </p>
          </section>
          <section id="user-engagement">
            <h2>User Engagement & Notifications</h2>
            <div class="content-img">
              <img src="image/User-Engagement.gif" alt="User Engagement" loading="lazy">
            </div>
            <p>
              Stay informed with timely notifications and alerts. Receive real-time updates on your spending, budget limits, and financial milestones, ensuring you always have a handle on your money.
            </p>
          </section>
          <p>
            CashGuard integrates these features into a cohesive, intuitive platform that empowers you to manage your finances with confidence and ease.
          </p>
        </article>
      </section>
      
      <!-- About Us Section (from AboutUs_Page-Act5.html) -->
      <section id="about" class="page">
        <div class="about-section">
          <h1>About Us</h1>
          <p>Some text about who we are and what we do.</p>
          <p>
            Lorem ipsum dolor, sit amet consectetur adipisicing elit. Molestiae id fugiat, illo doloribus repellat minima cum libero! Inventore voluptate, amet doloribus pariatur consectetur fuga exercitationem, perspiciatis laborum, ducimus sapiente iste.
          </p>
        </div>
        <div class="row2 clearfix">
          <h2 style="text-align:center">Our Team</h2>
          <div class="column">
            <div class="card">
              <img src="image/myphoto.png" alt="John Doe">
              <div class="container-aboutUs">
                <h2>Zorel Adrean R. Java</h2>
                <p class="title">CEO &amp; Founder</p>
                <p>Some text that describes me lorem ipsum ipsum lorem.</p>
                <p>petrovamario@gmail.com</p>
                <p><button class="button"><a href="https://www.facebook.com/ZorelAdrean/" target="_blank">Contact</a></button></p>
              </div>
            </div>
          </div>
          <div class="column">
            <div class="card">
              <img src="image/mike.png" alt="Jane Doe">
              <div class="container-aboutUs">
                <h2>Mike Lebron S. Monterona</h2>
                <p class="title">Art Director</p>
                <p>Some text that describes me lorem ipsum ipsum lorem.</p>
                <p>jane@example.com</p>
                <p><button class="button"><a href="https://www.facebook.com/39Starmiya" target="_blank">Contact</a></button></p>
              </div>
            </div>
          </div>
          <div class="column">
            <div class="card">
              <img src="image/sebyer.png" alt="Jane Doe">
              <div class="container-aboutUs">
                <h2>Xavier A. Fernandez</h2>
                <p class="title">Designer</p>
                <p>Some text that describes me lorem ipsum ipsum lorem.</p>
                <p>jane@example.com</p>
                <p><button class="button"><a href="https://www.facebook.com/sebyer.xaf" target="_blank">Contact</a></button></p>
              </div>
            </div>
          </div>
          <!-- <div class="column">
            <div class="card">
              <img src="image/anime4.gif" alt="Jane Doe">
              <div class="container-aboutUs">
                <h2>Jane Doe</h2>
                <p class="title">Art Director</p>
                <p>Some text that describes me lorem ipsum ipsum lorem.</p>
                <p>jane@example.com</p>
                <p><button class="button">Contact</button></p>
              </div>
            </div>
          </div> -->
          <!-- <div class="column">
            <div class="card">
              <img src="image/anime5.gif" alt="John Doe">
              <div class="container-aboutUs">
                <h2>John Doe</h2>
                <p class="title">Designer</p>
                <p>Some text that describes me lorem ipsum ipsum lorem.</p>
                <p>john@example.com</p>
                <p><button class="button">Contact</button></p>
              </div>
            </div>
          </div> -->
        </div>
      </section>
      
      <!-- Contact Us Section (from ContactUs_Page-Act5.html) -->
      <section id="contact" class="page">
        <div class="container-contactUs">
          <h2>Contact Us</h2>
          <p>Swing by for a cup of coffee, or leave us a message:</p>
          <div class="row clearfix">
            <div class="column-contactUs">
              <img src="image/contact_us.jpg" style="width:100%">
            </div>
            <div class="column-contactUs">
              <form action="/action_page.php">
                <label for="fname">First Name</label>
                <input type="text" id="fname" name="firstname" placeholder="Your name..">
                <label for="lname">Last Name</label>
                <input type="text" id="lname" name="lastname" placeholder="Your last name..">
                <label for="country">Country</label>
                <select id="country" name="country">
                  <option value="australia">Australia</option>
                  <option value="canada">Canada</option>
                  <option value="usa">USA</option>
                  <option value="ph">Philippines</option>
                </select>
                <label for="subject">Subject</label>
                <textarea id="subject" name="subject" placeholder="Write something.." style="height:170px"></textarea>
                <input type="submit" value="Submit">
              </form>
            </div>
          </div>
        </div>
      </section>
      
      <!-- Login Section (from Act6.html) -->
      <section id="login" class="page <?php echo !isset($_SESSION         ['loggedin']) ? 'active' : ''; ?>">
        <div class="login-container">
          <?php if (isset($_SESSION['login_error'])): ?>
            <?php
                $countdownSeconds = 0;
                if (preg_match('/Try again in (\d+) seconds/', $_SESSION['login_error'], $matches)) {
                    $countdownSeconds = $matches[1];
                }
            ?>
            <div id="login-error" class="error-message" data-countdown="<?php   echo $countdownSeconds; ?>">
                <?php echo $_SESSION['login_error']; ?>
            </div>
                <?php unset($_SESSION['login_error']); ?>
          <?php endif; ?>

            
            <div class="logo">
                <img src="image/cashguard_logoDraft2.png" alt="Logo">
            </div>
            <form class="login-form" action="login_process.php" method="post">
                <label for="uname">Username:</label>
                <input type="text" id="uname" name="username" placeholder="Enter Username" required>
                <label for="pname">Password:</label>
                <input type="password" id="pname" name="password" placeholder="Enter Password" required>
                <div class="buttons">
                    <button class="button" type="submit">Login</button>
                    <button class="button" type="button" onclick="window.location.href='register.php'">SignUp</button>
                </div>
            </form>
            <p>Forgot Password?</p>
        </div>
      </section>
    </main>
  </div>
  
  <!-- Footer -->
  <footer class="footer">
    <h2>&copy; Zorel Adrean R. Java | BSIT - 2</h2>

  </footer>

  <script>
    const errorDiv = document.getElementById('login-error');
    if (errorDiv && errorDiv.textContent.includes("Try again in")) {
      const match = errorDiv.textContent.match(/Try again in (\d+) seconds/);
      if (match) {
        let secondsLeft = parseInt(match[1]);

        const countdown = setInterval(() => {
          secondsLeft--;
          if (secondsLeft > 0) {
            errorDiv.textContent = `Too many failed attempts. Try again in ${secondsLeft} seconds.`;
          } else {
            clearInterval(countdown);
            errorDiv.textContent = "You can now try logging in again.";
          }
        }, 1000);
      }
    }

    document.addEventListener('DOMContentLoaded', function () {
      const errorDiv = document.getElementById('login-error');
      const form = document.querySelector('.login-form');
      const loginBtn = form.querySelector('button[type="submit"]');

      // Check if the error message contains the countdown
      if (errorDiv && errorDiv.textContent.includes("Try again in")) {
        const match = errorDiv.textContent.match(/Try again in (\d+) seconds/);
        if (match) {
          let secondsLeft = parseInt(match[1]);

          // Disable the form and button while countdown is active
          form.classList.add('disabled');
          loginBtn.disabled = true;
          loginBtn.classList.add('fade-in');

          // Start countdown interval
          const countdown = setInterval(() => {
            secondsLeft--;

            // Update the message with the remaining seconds
            if (secondsLeft > 0) {
              errorDiv.textContent = `Too many failed attempts. Try again in ${secondsLeft} seconds.`;
            } else {
              // When the countdown reaches 0
              clearInterval(countdown);
              errorDiv.textContent = "You can now try logging in again."; // Show success message
              
              // Add success class for green styling (if using CSS class)
              errorDiv.classList.add('success');

              // Enable the form and the button
              form.classList.remove('disabled'); // Enable the form
              loginBtn.disabled = false; // Enable the submit button
              loginBtn.classList.add('enabled'); // Optional: style to indicate button is active
            }
          }, 1000);
        }
      }
    });
  </script>

</body>
</html>


