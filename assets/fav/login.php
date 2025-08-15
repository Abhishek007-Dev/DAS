<?php
session_start();
require_once "../backend/db_connect.php";
$_SESSION['LAST_ACTIVITY'] = time();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            switch ($user['role']) {
                case 'admin': header("Location: ../frontend/Admin_1/dashboard.php"); break;
                case 'maker': header("Location: ../frontend/maker_a/dashboard.php"); break;
                case 'checker': header("Location: ../frontend/Checker_A/dashboard.php"); break;
                case 'superadmin': header("Location: ../frontend/Super_Admin/dashboard.php"); break;
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
  <style>
    :root {
      --primary-color: #4a36cf;
      --gradient-start: #6f42c1;
      --gradient-end: #4a00e0;
    }
    
    body {
      background-color: #f8f9fa;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .login-wrapper {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      background: linear-gradient(135deg, rgba(106, 27, 154, 0.1), rgba(49, 27, 146, 0.1));
    }
    
    .login-container {
      background: white;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 420px;
      padding: 2.5rem;
      position: relative;
      overflow: hidden;
      border: 1px solid rgba(0,0,0,0.05);
    }
    
    .login-container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 5px;
      background: linear-gradient(90deg, var(--gradient-start), var(--gradient-end));
    }
    
    .system-title {
      font-size: 1.8rem;
      font-weight: 700;
      background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
      margin-bottom: 1.5rem;
      text-align: center;
      letter-spacing: -0.5px;
    }
    
    .form-control {
      padding: 12px 15px;
      border-radius: 8px;
      border: 1px solid #ddd;
      transition: all 0.3s;
    }
    
    .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.25rem rgba(74, 54, 207, 0.25);
    }
    
    .password-container {
      position: relative;
    }
    
    .toggle-password {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #6c757d;
    }
    
    .btn-login {
      background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
      border: none;
      padding: 12px;
      font-weight: 600;
      letter-spacing: 0.5px;
      border-radius: 8px;
      transition: all 0.3s;
    }
    
    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(106, 27, 154, 0.3);
    }
    
    .copyright {
      font-size: 0.8rem;
      color: #6c757d;
      text-align: center;
      margin-top: 1.5rem;
    }
    
    .version-badge {
      background-color: rgba(74, 54, 207, 0.1);
      color: var(--primary-color);
      padding: 3px 8px;
      border-radius: 4px;
      font-size: 0.7rem;
      font-weight: 600;
      display: inline-block;
      margin-top: 0.5rem;
    }
    
    .error-message {
      background-color: #f8d7da;
      color: #721c24;
      padding: 10px 15px;
      border-radius: 8px;
      margin-top: 1rem;
      display: none;
      align-items: center;
    }
    
    .error-message i {
      margin-right: 8px;
    }
    
    .loader-container {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(255,255,255,0.8);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }
    
    .loader {
      width: 48px;
      height: 48px;
      border: 5px solid var(--primary-color);
      border-bottom-color: transparent;
      border-radius: 50%;
      display: inline-block;
      box-sizing: border-box;
      animation: rotation 1s linear infinite;
    }
    
    @keyframes rotation {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    .forgot-password {
      color: var(--primary-color);
      text-decoration: none;
      font-size: 0.9rem;
      transition: all 0.2s;
    }
    
    .forgot-password:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="login-wrapper">
    <div class="login-container">
      <div class="system-title">Document Automation System</div>
      
      <form id="loginForm" method="POST" action="">
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
            <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
          </div>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <div class="password-container">
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
              <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <i class="bi bi-eye-fill toggle-password" id="togglePassword"></i>
          </div>
        </div>

        <div class="d-grid mb-3">
          <button type="submit" class="btn btn-login btn-lg text-white" id="loginBtn">
            <span id="loginText">Login</span>
            <span id="loginSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
          </button>
        </div>

        <div class="text-center">
          <a href="#" class="forgot-password">Forgot password?</a>
        </div>
        
        <?php if (isset($error)): ?>
          <div class="error-message" id="errorMessage">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <?php echo htmlspecialchars($error); ?>
          </div>
        <?php endif; ?>
      </form>
      
      <div class="copyright">
        © <span id="currentYear"></span> Abhishek Paswan
        <div class="version-badge">Version V0.01</div>
      </div>
    </div>
  </div>

  <!-- Loader Container -->
  <div class="loader-container" id="loader">
    <div class="loader"></div>
  </div>

  <!-- Bootstrap JS -->
  <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- Custom JS -->
  <script>
    // Password visibility toggle
    document.getElementById('togglePassword').addEventListener('click', function() {
      const passwordInput = document.getElementById('password');
      const icon = this;
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('bi-eye-fill');
        icon.classList.add('bi-eye-slash-fill');
      } else {
        passwordInput.type = 'password';
        icon.classList.remove('bi-eye-slash-fill');
        icon.classList.add('bi-eye-fill');
      }
    });

    // Form submission with loader
    document.getElementById('loginForm').addEventListener('submit', function(e) {
      const loginBtn = document.getElementById('loginBtn');
      const loginText = document.getElementById('loginText');
      const loginSpinner = document.getElementById('loginSpinner');
      const loader = document.getElementById('loader');
      
      // Show loading state
      loginText.textContent = 'Authenticating...';
      loginSpinner.classList.remove('d-none');
      loginBtn.disabled = true;
      loader.style.display = 'flex';
      
      // Hide error message if exists
      const errorMessage = document.getElementById('errorMessage');
      if (errorMessage) {
        errorMessage.style.display = 'none';
      }
    });

    // Set current year in copyright
    document.getElementById('currentYear').textContent = new Date().getFullYear();

    // Show error message if exists
    <?php if (isset($error)): ?>
      document.addEventListener('DOMContentLoaded', function() {
        const errorMessage = document.getElementById('errorMessage');
        if (errorMessage) {
          errorMessage.style.display = 'flex';
        }
      });
    <?php endif; ?>
  </script>
</body>
</html>