<?php 
session_start(); 
include 'config.php'; 

$msg = ""; 

// Check if redirected with a success message
if (isset($_SESSION['success_msg'])) {
    $msg = "✅ " . $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}

// LOGIN LOGIC
if (isset($_POST['login'])) {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT ID, password FROM users WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            
            if (password_verify($password, $row['password'])) {
                session_regenerate_id(true);
                $_SESSION['uid'] = $row['ID'];
                header("Location: dashboard.php?uid=" . $row['ID']); 
                exit();
            } else { 
                $msg = "❌ Wrong Password!"; 
            }
        } else { 
            $msg = "❌ Email not found!"; 
        }
        $stmt->close();
    } else {
        $msg = "❌ SQL Error: " . $conn->error;
    }
}

// REGISTER LOGIC
if (isset($_POST['register_btn'])) {
    $fullname = trim($_POST['fullname']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $role = "student"; 

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "❌ Invalid email format!";
    } else if (strlen($password) < 6) {
        $msg = "❌ Password must be at least 6 characters!";
    } else {
        // Check Existing User
        $stmt_check = $conn->prepare("SELECT ID FROM users WHERE email = ?");
        if ($stmt_check) {
            $stmt_check->bind_param("s", $email);
            $stmt_check->execute();
            $check_result = $stmt_check->get_result();

            if ($check_result->num_rows > 0) {
                $msg = "❌ Email already exists! Please login.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert into standard columns only
                $stmt_insert = $conn->prepare("INSERT INTO users (fullname, email, password, role, profile_pic) VALUES (?, ?, ?, ?, 'default.png')");
                
                if ($stmt_insert) {
                    $stmt_insert->bind_param("ssss", $fullname, $email, $hashed_password, $role);

                    if ($stmt_insert->execute()) {
                        $msg = "✅ Account created successfully! You can now log in.";
                    } else {
                        $msg = "❌ Registration error: " . $stmt_insert->error;
                    }
                    $stmt_insert->close();
                } else {
                    $msg = "❌ Database Insert Error: " . $conn->error;
                }
            }
            $stmt_check->close();
        } else {
            $msg = "❌ Database Check Error: " . $conn->error;
        }
    }
} 
?>
<!DOCTYPE html> 
<html lang="en"> 
<head> 
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login & Register - NC3 MP&HG Simulation</title>
<link rel="icon" type="image/jpeg" href="assets/img/logo.jpg">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<style> 
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(rgba(10, 10, 26, 0.85), rgba(118, 75, 162, 0.85)), 
                url('https://images.unsplash.com/photo-1519389950473-47ba0277781c?q=80&w=1170') center/cover fixed;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 20px;
}

.container {
    width: 100%;
    max-width: 420px;
    margin: auto;
}

.top-logo {
    text-align: center;
    margin-bottom: 20px;
}

.top-logo img {
    height: 60px;
    width: 60px;
    border-radius: 14px;
    object-fit: cover;
    margin-bottom: 8px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
}

.top-logo h2 {
    color: #ffffff;
    font-size: 22px;
    font-weight: 700;
    text-shadow: 0 2px 10px rgba(0,0,0,0.4);
}

.login-box {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(15px);
    padding: 40px 30px;
    border-radius: 24px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
    border: 1px solid rgba(255, 255, 255, 0.4);
}

h2.form-title {
    text-align: center;
    color: #4a5568;
    margin-bottom: 6px;
    font-size: 26px;
    font-weight: 700;
}

.subtitle {
    text-align: center;
    color: #718096;
    margin-bottom: 25px;
    font-size: 14px;
}

.form-group {
    margin-bottom: 18px;
    position: relative;
}

.form-group label {
    display: block;
    color: #4a5568;
    font-weight: 600;
    margin-bottom: 6px;
    font-size: 13px;
}

.form-group input {
    width: 100%;
    padding: 13px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 14px;
    transition: 0.3s ease;
    font-family: 'Poppins', sans-serif;
    background: #f8fafc;
}

.form-group input:focus {
    border-color: #667eea;
    background: #ffffff;
    outline: none;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
}

.toggle-pwd {
    position: absolute;
    right: 15px;
    top: 38px;
    cursor: pointer;
    font-size: 12px;
    color: #667eea;
    font-weight: 600;
    user-select: none;
}

.btn-submit {
    width: 100%;
    padding: 14px;
    background: linear-gradient(90deg, #667eea, #764ba2);
    color: #ffffff;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.3s ease;
    margin-top: 10px;
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 25px rgba(102, 126, 234, 0.4);
}

.switch-mode {
    text-align: center;
    margin-top: 20px;
    color: #718096;
    font-size: 14px;
}

.switch-mode a {
    color: #667eea;
    text-decoration: none;
    font-weight: 700;
}

.switch-mode a:hover {
    text-decoration: underline;
}

.msg {
    text-align: center;
    padding: 12px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-weight: 600;
    font-size: 13px;
    line-height: 1.4;
}

.error {
    background: #fed7d7;
    color: #9b2c2c;
    border: 1px solid #feb2b2;
}

.success {
    background: #c6f6d5;
    color: #22543d;
    border: 1px solid #9ae6b4;
}

.back-home {
    text-align: center;
    margin-top: 25px;
}

.back-home a {
    color: #ffffff;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    text-shadow: 0 2px 5px rgba(0,0,0,0.4);
    transition: 0.3s;
}

.back-home a:hover {
    color: #f093fb;
}

@media (max-width: 480px) {
    .login-box {
        padding: 30px 20px;
        border-radius: 20px;
    }
    
    h2.form-title {
        font-size: 22px;
    }
    
    .top-logo h2 {
        font-size: 19px;
    }
}
</style> 
</head> 
<body> 

<div class="container">
    <div class="top-logo">
        <img src="assets/img/logo.jpg" alt="NC3 Logo">
        <h2>NC3 MP&HG Simulation</h2>
    </div>

    <div class="login-box">
        <h2 class="form-title"><?php echo (isset($_GET['mode']) && $_GET['mode']=='register') ? 'Create Account' : 'Welcome Back'; ?></h2>
        <p class="subtitle"><?php echo (isset($_GET['mode']) && $_GET['mode']=='register') ? 'Join and start learning today' : 'Login to continue your training'; ?></p>

        <?php if($msg): ?>
            <div class="msg <?php echo (strpos($msg, '✅') !== false) ? 'success' : 'error'; ?>">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_GET['mode']) && $_GET['mode'] == 'register'): ?>
            <!-- REGISTER FORM -->
            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="fullname" placeholder="John Doe" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="student@example.com" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" id="regPassword" placeholder="Minimum 6 characters" required>
                    <span class="toggle-pwd" onclick="togglePassword('regPassword')">Show</span>
                </div>
                <button type="submit" name="register_btn" class="btn-submit">Register Now</button>
            </form>
            <div class="switch-mode">
                Already have an account? <a href="index.php">Login Here</a>
            </div>

        <?php else: ?>
            <!-- LOGIN FORM -->
            <form method="POST">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" id="loginPassword" placeholder="Enter your password" required>
                    <span class="toggle-pwd" onclick="togglePassword('loginPassword')">Show</span>
                </div>
                <button type="submit" name="login" class="btn-submit">Login</button>
            </form>
            <div class="switch-mode">
                Don't have an account? <a href="index.php?mode=register">Register Here</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="back-home">
        <a href="index.html">← Back to Home</a>
    </div>
</div> 

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const toggleBtn = field.nextElementSibling;
    if (field.type === "password") {
        field.type = "text";
        toggleBtn.textContent = "Hide";
    } else {
        field.type = "password";
        toggleBtn.textContent = "Show";
    }
}
</script>

</body>
</html>