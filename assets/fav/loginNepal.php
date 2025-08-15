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
            $error = "Invalid credentials. Please try again.";
        }
    } else {
        $error = "Account not found. Please check your username.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Secure Login | Nepal SBI Bank Limited</title>
  <link rel="icon" href="favicon.ico" type="image/x-icon">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/bootstrap-icons/bootstrap-icons.css">
  <style>
    :root {
      --sbi-red: #E41937;
      --sbi-blue: #0033A0;
      --sbi-gold: #FFD700;
      --nepal-red: #DC143C;
      --nepal-blue: #003893;
      --nepal-white: #FFFFFF;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      overflow: hidden;
      height: 100vh;
    }
    
    .flag-background {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(
        135deg,
        rgba(220, 20, 60, 0.03) 0%,
        rgba(220, 20, 60, 0.05) 50%,
        rgba(0, 56, 147, 0.03) 50%,
        rgba(0, 56, 147, 0.05) 100%
      );
      z-index: -1;
      opacity: 0.15;
      background-size: 200% 200%;
      animation: flagWave 20s ease infinite;
    }
    
    @keyframes flagWave {
      0% { background-position: 0% 0%; }
      50% { background-position: 100% 100%; }
      100% { background-position: 0% 0%; }
    }
    
    .login-container {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 16px;
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
      backdrop-filter: blur(8px);
      width: 100%;
      max-width: 480px;
      padding: 2.5rem;
      border: 1px solid rgba(255, 255, 255, 0.3);
      position: relative;
      overflow: hidden;
    }
    
    .login-container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 6px;
      background: linear-gradient(90deg, var(--sbi-red), var(--sbi-blue));
    }
    
    .bank-logo {
      height: 50px;
      margin-bottom: 1.5rem;
    }
    
    .system-title {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--sbi-blue);
      margin-bottom: 0.5rem;
      text-align: center;
      letter-spacing: -0.5px;
    }
    
    .system-subtitle {
      color: #555;
      text-align: center;
      margin-bottom: 2rem;
      font-weight: 400;
      font-size: 1rem;
    }
    
    .form-control {
      padding: 14px 16px;
      border-radius: 8px;
      border: 1px solid #ddd;
      transition: all 0.3s;
      font-size: 0.95rem;
    }
    
    .form-control:focus {
      border-color: var(--sbi-blue);
      box-shadow: 0 0 0 0.25rem rgba(0, 51, 160, 0.15);
    }
    
    .input-group-text {
      background-color: #f8f9fa;
      border-right: none;
    }
    
    .password-container {
      position: relative;
    }
    
    .toggle-password {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #6c757d;
      background: none;
      border: none;
      z-index: 5;
    }
    
    .btn-login {
      background: linear-gradient(135deg, var(--sbi-red), var(--sbi-blue));
      border: none;
      padding: 14px;
      font-weight: 600;
      letter-spacing: 0.5px;
      border-radius: 8px;
      transition: all 0.3s;
      font-size: 1rem;
    }
    
    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(228, 25, 55, 0.3);
    }
    
    .footer {
      font-size: 0.8rem;
      color: #6c757d;
      text-align: center;
      margin-top: 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .version-badge {
      background-color: rgba(0, 51, 160, 0.1);
      color: var(--sbi-blue);
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
    }
    
    .security-badge {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 0.75rem;
      color: var(--sbi-blue);
    }
    
    .error-message {
      background-color: #f8d7da;
      color: #721c24;
      padding: 12px 16px;
      border-radius: 8px;
      margin-top: 1.5rem;
      display: none;
      align-items: center;
      animation: fadeIn 0.3s ease-out;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .loader-container {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(255,255,255,0.9);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 9999;
      flex-direction: column;
    }
    
    .loader {
      width: 50px;
      height: 50px;
      border: 4px solid var(--sbi-blue);
      border-top-color: var(--sbi-red);
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin-bottom: 1rem;
    }
    
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    
    .loading-text {
      color: var(--sbi-blue);
      font-weight: 500;
      margin-top: 1rem;
    }
    
    .language-switcher {
      position: absolute;
      top: 20px;
      right: 20px;
    }
    
    .language-btn {
      background: none;
      border: 1px solid #ddd;
      border-radius: 20px;
      padding: 4px 12px;
      font-size: 0.8rem;
      color: #555;
      cursor: pointer;
      transition: all 0.2s;
    }
    
    .language-btn:hover {
      background-color: #f8f9fa;
    }
    
    .forgot-password {
      color: var(--sbi-blue);
      text-decoration: none;
      font-size: 0.9rem;
      transition: all 0.2s;
      display: inline-block;
      margin-top: 1rem;
    }
    
    .forgot-password:hover {
      color: var(--sbi-red);
      text-decoration: underline;
    }
    
    .virtual-keyboard {
      margin-top: 1rem;
      display: none;
    }
    
    .keyboard-btn {
      background-color: #f8f9fa;
      border: 1px solid #ddd;
      border-radius: 6px;
      padding: 8px 12px;
      margin: 2px;
      cursor: pointer;
      transition: all 0.2s;
    }
    
    .keyboard-btn:hover {
      background-color: #e9ecef;
    }
    
    @media (max-width: 576px) {
      .login-container {
        padding: 1.5rem;
        margin: 0 15px;
      }
      
      .system-title {
        font-size: 1.5rem;
      }
    }
  </style>
</head>
<body>
  <!-- Nepal Flag Inspired Background -->
  <div class="flag-background"></div>
  
  <div class="d-flex justify-content-center align-items-center min-vh-100 p-3">
    <div class="login-container">
      <!-- Language Switcher -->
      <div class="language-switcher">
        <button class="language-btn">
          <i class="bi bi-translate me-1"></i>English / नेपाली
        </button>
      </div>
      
      <!-- Bank Logo -->
      <div class="text-center">
        <img src="https://www.sbi.co.in/documents/16012/0/SBI-logo.png" alt="Nepal SBI Bank Logo" class="bank-logo">
      </div>
      
      <h1 class="system-title">DOCUMENT AUTOMATION SYSTEM</h1>
      <p class="system-subtitle">Secure Access Portal for Authorized Personnel</p>
      
      <form id="loginForm" method="POST" action="">
        <div class="mb-3">
          <label for="username" class="form-label">User ID</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
            <input type="text" class="form-control" id="username" name="username" placeholder="Enter your User ID" required>
          </div>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <div class="password-container">
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
              <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="button" class="toggle-password" id="togglePassword">
              <i class="bi bi-eye-fill"></i>
            </button>
          </div>
        </div>
        
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" id="rememberMe">
          <label class="form-check-label" for="rememberMe">Remember my User ID</label>
        </div>

        <div class="d-grid mb-3">
          <button type="submit" class="btn btn-login btn-lg text-white" id="loginBtn">
            <span id="loginText">SIGN IN</span>
            <span id="loginSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
          </button>
        </div>
        
        <div class="text-center">
          <a href="#" class="forgot-password">
            <i class="bi bi-key me-1"></i>Forgot Password?
          </a>
        </div>
        
        <!-- Virtual Keyboard (Optional) -->
        <div class="virtual-keyboard" id="virtualKeyboard">
          <div class="d-flex justify-content-center flex-wrap">
            <button type="button" class="keyboard-btn">1</button>
            <button type="button" class="keyboard-btn">2</button>
            <button type="button" class="keyboard-btn">3</button>
            <button type="button" class="keyboard-btn">4</button>
            <button type="button" class="keyboard-btn">5</button>
            <button type="button" class="keyboard-btn">6</button>
            <button type="button" class="keyboard-btn">7</button>
            <button type="button" class="keyboard-btn">8</button>
            <button type="button" class="keyboard-btn">9</button>
            <button type="button" class="keyboard-btn">0</button>
            <button type="button" class="keyboard-btn">@</button>
            <button type="button" class="keyboard-btn">#</button>
          </div>
        </div>
        
        <?php if (isset($error)): ?>
          <div class="error-message" id="errorMessage">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?php echo htmlspecialchars($error); ?>
          </div>
        <?php endif; ?>
      </form>
      
      <div class="footer">
        <div class="version-badge">
          <i class="bi bi-patch-check-fill me-1"></i>Version V0.01
        </div>
        <div class="security-badge">
          <i class="bi bi-shield-check"></i>
          <span>Secure Banking</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Loader Container -->
  <div class="loader-container" id="loader">
    <div class="loader"></div>
    <div class="loading-text">Authenticating with Nepal SBI Bank...</div>
    <div class="text-muted mt-2" style="font-size: 0.8rem;">Please wait while we verify your credentials</div>
  </div>

  <!-- Bootstrap JS -->
  <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- Custom JS -->
  <script>
    // Password visibility toggle
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    
    togglePassword.addEventListener('click', function() {
      const icon = this.querySelector('i');
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
      loginText.textContent = 'VERIFYING...';
      loginSpinner.classList.remove('d-none');
      loginBtn.disabled = true;
      loader.style.display = 'flex';
      
      // Hide error message if exists
      const errorMessage = document.getElementById('errorMessage');
      if (errorMessage) {
        errorMessage.style.display = 'none';
      }
    });

    // Virtual keyboard toggle (optional)
    passwordInput.addEventListener('focus', function() {
      document.getElementById('virtualKeyboard').style.display = 'block';
    });
    
    document.addEventListener('click', function(e) {
      if (!passwordInput.contains(e.target) && !document.getElementById('virtualKeyboard').contains(e.target)) {
        document.getElementById('virtualKeyboard').style.display = 'none';
      }
    });
    
    // Keyboard button clicks
    document.querySelectorAll('.keyboard-btn').forEach(button => {
      button.addEventListener('click', function() {
        const currentPos = passwordInput.selectionStart;
        const currentValue = passwordInput.value;
        passwordInput.value = currentValue.substring(0, currentPos) + this.textContent + currentValue.substring(currentPos);
        passwordInput.focus();
        passwordInput.selectionStart = currentPos + 1;
        passwordInput.selectionEnd = currentPos + 1;
      });
    });

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