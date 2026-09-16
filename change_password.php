<?php
session_start();
if(!isset($_SESSION['user_id'])){ 
    header("Location: index.php"); 
    exit(); 
}
include 'config.php';

$msg = "";
$msg_type = "";

if(isset($_POST['change'])){
    $old = trim($_POST['old_pass']);
    $new = trim($_POST['new_pass']);
    $confirm = trim($_POST['confirm_pass']);
    $user_id = $_SESSION['user_id'];
    
    if($new !== $confirm){
        $msg = "New Password and Confirm Password do not match!";
        $msg_type = "error";
    } 
    else if(strlen($new) < 6){
        $msg = "Password must be at least 6 characters!";
        $msg_type = "error";
    }
    else {
        // FETCH CURRENT PASSWORD HASH
        $sql = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows === 1){
            $row = $result->fetch_assoc();
            $hash = $row['password'];
            
            // VERIFY OLD PASSWORD
            if(password_verify($old, $hash)){
                
                // CREATE NEW HASH
                $new_hash = password_hash($new, PASSWORD_DEFAULT);
                
                // UPDATE IN DATABASE
                $update_sql = "UPDATE users SET password = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("si", $new_hash, $user_id);
                
                if($update_stmt->execute()){
                    // CLEAR SESSION & LOGOUT
                    $_SESSION = array();
                    if (ini_get("session.use_cookies")) {
                        $params = session_get_cookie_params();
                        setcookie(session_name(), '', time() - 42000,
                            $params["path"], $params["domain"],
                            $params["secure"], $params["httponly"]
                        );
                    }
                    session_destroy();

                    // REDIRECT TO LOGIN
                    echo "<script>
                        alert('Password Changed Successfully! Please log in again.');
                        window.location.href = 'index.php';
                    </script>";
                    exit();
                } else {
                    $msg = "Error: Could not update password.";
                    $msg_type = "error";
                }
                $update_stmt->close();
            } else {
                $msg = "Old Password is Incorrect!";
                $msg_type = "error";
            }
        } else {
            $msg = "User not found!";
            $msg_type = "error";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <style>
        body { font-family: 'Segoe UI'; background: #f0f2f5; display: flex; margin: 0; }
        .sidebar { width: 260px; background: linear-gradient(180deg, #4030D0, #008080); height: 100vh; position: fixed; }
        .sidebar h2 { color:white; text-align:center; padding:20px 0; }
        .sidebar a { display: block; color: white; padding: 15px 25px; text-decoration: none; }
        .sidebar a:hover { background: rgba(255,255,255,0.1); }
        .main { margin-left: 260px; width: 100%; padding: 30px; }
        .card { background: white; padding: 30px; border-radius: 15px; max-width: 500px; margin: auto; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        label { font-weight: bold; color: #333; }
        input, .btn { width: 100%; padding: 12px; margin: 10px 0 20px 0; border: 2px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .btn { background: linear-gradient(90deg, #4030D0, #008080); color: white; border: none; font-weight: bold; cursor: pointer; }
        .btn:hover { opacity: 0.9; }
        .success { background: #d4edda; color: #155724; padding: 12px; border-radius: 5px; margin-bottom: 15px; }
        .error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>NC3 MP&HG stimulation</h2>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="settings.php">⚙️ Settings</a>
        <a href="edit_profile.php">✏️ Edit Profile</a>
    </div>
    <div class="main">
        <div class="card">
            <h2 style="color:#4030D0;">Change Password</h2>
            <?php if(!empty($msg)) echo "<div class='$msg_type'>$msg</div>"; ?>
            <form method="POST">
                <label>Old Password</label>
                <input type="password" name="old_pass" required>
                
                <label>New Password</label>
                <input type="password" name="new_pass" required>
                
                <label>Confirm New Password</label>
                <input type="password" name="confirm_pass" required>
                
                <button type="submit" name="change" class="btn">Change Password</button>
            </form>
        </div>
    </div>
</body>
</html>