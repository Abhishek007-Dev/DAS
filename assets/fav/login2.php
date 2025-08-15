<?php
session_start();
require_once "../backend/db_connect.php"; // Ensure this path is correct
$_SESSION['LAST_ACTIVITY'] = time(); // initialize last activity time

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if username and password are set
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Query to get the user
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            switch ($user['role']) {
                case 'admin':
                    header("Location: ../frontend/Admin_1/dashboard.php");
                    break;
                case 'maker':
                    header("Location: ../frontend/maker_a/dashboard.php");
                    break;
                case 'checker':
                    header("Location: ../frontend/Checker_A/dashboard.php");
                    break;
                case 'superadmin':
                    header("Location: ../frontend/Super_Admin/dashboard.php");
                    break;
            }
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No user found with that username.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login | Document Automation System</title>
  <link rel="icon" href="favicon.ico" type="image/x-icon">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/bootstrap-icons/bootstrap-icons.css">
  <!-- frontend/login.html -->
  <link rel="stylesheet" href="../assets/css/login.css">
  <style>
    /* Loader Styles */
    .loader-container {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.7);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }
    
    .loader {
      width: 48px;
      height: 48px;
      border: 5px solid #fff;
      border-bottom-color: transparent;
      border-radius: 50%;
      display: inline-block;
      box-sizing: border-box;
      animation: rotation 3s linear infinite;
    }
    
    @keyframes rotation {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    .login-container {
      position: relative;
    }
    
    /* Error message styling */
    .error-message {
      color: #dc3545;
      background-color: #f8d7da;
      border-color: #f5c6cb;
      padding: 8px 15px;
      border-radius: 4px;
      margin-top: 15px;
      display: none;
    }
  </style>
</head>
<body>

  <div class="center-wrapper">
    <div class="title">Document Automation System</div>

    <div class="login-container">
      <form id="loginForm" method="POST" action="">
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input type="text" class="form-control" id="username" name="username" required>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <div class="d-grid">
          <button type="submit" class="btn btn-gradient btn-lg" id="loginBtn">Login</button>
        </div>

        <div class="mt-3 text-center form-text">
          Forgot password? <a href="#" class="text-decoration-none">Click here</a>
        </div>
        
        <!-- Error message container -->
        <?php if (isset($error)): ?>
          <div class="error-message mt-3" id="errorMessage">
            <i class="bi bi-exclamation-circle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
          </div>
        <?php endif; ?>
      </form>
    </div>
    <div class="copyright-text">© Abhishek</div>
  </div>

  <!-- Loader Container -->
  <div class="loader-container" id="loader">
    <div class="loader"></div>
  </div>

  <!-- Bootstrap JS -->
  <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- Custom JS -->
  <script>
    document.getElementById('loginForm').addEventListener('submit', function(e) {
      // Show loader
      document.getElementById('loader').style.display = 'flex';
      
      // Disable login button to prevent multiple submissions
      document.getElementById('loginBtn').disabled = true;
      
      // If there was a previous error, hide it
      const errorMessage = document.getElementById('errorMessage');
      if (errorMessage) {
        errorMessage.style.display = 'none';
      }
      
      // The form will continue to submit normally
    });

    // Show error message if it exists
    <?php if (isset($error)): ?>
      document.addEventListener('DOMContentLoaded', function() {
        const errorMessage = document.getElementById('errorMessage');
        if (errorMessage) {
          errorMessage.style.display = 'block';
        }
      });
    <?php endif; ?>
  </script>
</body>
</html>