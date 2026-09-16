<?php 
session_start(); 
include 'config.php'; 

$msg = ""; 
$msg_type = ""; 

if (isset($_POST['register_btn'])) {
    $fullname = trim($_POST['fullname']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // 1. Validate Email Format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "Invalid email address format!";
        $msg_type = "error";
    } 
    // 2. Validate Password Length
    else if (strlen($password) < 6) {
        $msg = "Password must be at least 6 characters!";
        $msg_type = "error";
    } 
    else {
        // 3. Secure Prepared Statement to Check Existing Account
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        if ($result->num_rows > 0) {
            $msg = "Email is already registered! Please log in.";
            $msg_type = "error";
        }
        $stmt_check->close();

        if (empty($msg)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert User as Verified (is_verified = 1)
            $stmt_insert = $conn->prepare("INSERT INTO users (fullname, email, password, role, profile_pic, is_verified) VALUES (?, ?, ?, 'student', 'default.png', 1)");
            $stmt_insert->bind_param("sss", $fullname, $email, $hashed_password);

            if ($stmt_insert->execute()) {
                $_SESSION['success_msg'] = "Registration successful! You can now log in.";
                header("Location: index.php");
                exit();
            } else {
                $msg = "Database Error: Registration failed!";
                $msg_type = "error";
            }
            $stmt_insert->close();
        }
    }
} 
?>
<!DOCTYPE html> 
<html lang="en"> 
<head> 
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - NC3 MP&HG</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap" rel="stylesheet">
<style> 
body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #667eea, #764ba2); display: flex; justify-content: center; align-items: center; height: 100vh; margin:0; } 
.box { background: white; padding: 40px; border-radius: 20px; width: 400px; box-shadow:0 20px 60px rgba(0,0,0,0.3); } 
.box h2 { color: #667eea; text-align: center; margin-bottom: 20px; } 
.box input { width: 100%; padding: 14px; margin: 10px 0; border: 2px solid #ddd; border-radius: 10px; box-sizing:border-box; font-family: inherit; } 
.btn { width: 100%; padding: 14px; background: linear-gradient(90deg, #f093fb, #f5576c); color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; transition: opacity 0.3s; } 
.btn:hover { opacity: 0.9; }
.error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 15px; font-size: 14px; } 
.success { background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 15px; font-size: 14px; }
</style> 
</head> 
<body> 
<div class="box"> 
<h2>Create Account ⚡</h2> 
<?php if($msg) echo "<div class='$msg_type'>$msg</div>"; ?> 
<form method="POST"> 
<input type="text" name="fullname" placeholder="Full Name" required> 
<input type="email" name="email" placeholder="Email Address" required> 
<input type="password" name="password" placeholder="Password (Min. 6 characters)" required> 
<input type="submit" name="register_btn" class="btn" value="Register"> 
</form> 
<p style="text-align:center; margin-top:15px; font-size: 14px;">May account na? <a href="index.php" style="color: #764ba2; font-weight:600; text-decoration:none;">Login dito</a></p> 
</div> 
</body> 
</html>