<?php
session_start();
include 'config.php';

if(!isset($_SESSION['uid'])){ 
    header("Location: index.php"); 
    exit(); 
} 

$uid = $_SESSION['uid'];

// UPDATE PROFILE INFO
if(isset($_POST['update'])){
    $fullname = trim($_POST['fullname']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    
    $stmt = $conn->prepare("UPDATE users SET fullname=?, email=? WHERE ID=?");
    if(!$stmt) {
        $stmt = $conn->prepare("UPDATE users SET fullname=?, email=? WHERE id=?");
    }
    $stmt->bind_param("ssi", $fullname, $email, $uid);
    
    if($stmt->execute()){
        $act_stmt = $conn->prepare("INSERT INTO activities (user_id, type, title, details, link, created_at) VALUES (?, 'profile', 'Updated Profile', 'User updated profile information', 'profile.php', NOW())");
        if($act_stmt){
            $act_stmt->bind_param("i", $uid);
            $act_stmt->execute();
            $act_stmt->close();
        }
        echo "<script>alert('Profile Updated Successfully!');window.location='profile.php';</script>";
    } else {
        echo "<script>alert('Error updating profile!');</script>";
    }
    $stmt->close();
}

// UPLOAD PROFILE PIC
if(isset($_POST['upload_pic'])){
    $target_dir = "assets/uploads/";
    if(!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    
    $file_name = time().'_'.basename($_FILES["profile_pic"]["name"]);
    $target_file = $target_dir . $file_name;
    
    if(move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)){
        $stmt = $conn->prepare("UPDATE users SET profile_pic=? WHERE ID=?");
        if(!$stmt) {
            $stmt = $conn->prepare("UPDATE users SET profile_pic=? WHERE id=?");
        }
        $stmt->bind_param("si", $target_file, $uid);
        $stmt->execute();
        $stmt->close();

        $act_stmt = $conn->prepare("INSERT INTO activities (user_id, type, title, details, link, created_at) VALUES (?, 'profile', 'Changed Profile Picture', 'User updated profile picture', 'profile.php', NOW())");
        if($act_stmt){
            $act_stmt->bind_param("i", $uid);
            $act_stmt->execute();
            $act_stmt->close();
        }
        echo "<script>alert('Profile Picture Updated!');window.location='profile.php';</script>";
    } else {
        echo "<script>alert('Error uploading file');</script>";
    }
}

// CHANGE PASSWORD LOGIC WITH AUTO-LOGOUT
if(isset($_POST['change_pass'])){
    $old_pass = $_POST['old_pass'];
    $new_pass = $_POST['new_pass'];
    $confirm_pass = $_POST['confirm_pass'];
    
    if($new_pass !== $confirm_pass){
        echo "<script>alert('New Password and Confirm Password do not match!');</script>";
    } else if(strlen($new_pass) < 6){
        echo "<script>alert('New Password must be at least 6 characters!');</script>";
    } else {
        // Fetch current password hash from database
        $stmt = $conn->prepare("SELECT password FROM users WHERE ID=?");
        if(!$stmt) {
            $stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
        }
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($row = $result->fetch_assoc()){
            // Verify old password using password_verify()
            if(password_verify($old_pass, $row['password'])){
                $new_hashed = password_hash($new_pass, PASSWORD_DEFAULT);
                
                $update_pass = $conn->prepare("UPDATE users SET password=? WHERE ID=?");
                if(!$update_pass) {
                    $update_pass = $conn->prepare("UPDATE users SET password=? WHERE id=?");
                }
                $update_pass->bind_param("si", $new_hashed, $uid);
                
                if($update_pass->execute()){
                    // Log activity prior to clearing session
                    $act_stmt = $conn->prepare("INSERT INTO activities (user_id, type, title, details, link, created_at) VALUES (?, 'security', 'Changed Password', 'User changed account password', 'profile.php', NOW())");
                    if($act_stmt){
                        $act_stmt->bind_param("i", $uid);
                        $act_stmt->execute();
                        $act_stmt->close();
                    }
                    
                    // CLEAR SESSION & COOKIES
                    $_SESSION = array();
                    if (ini_get("session.use_cookies")) {
                        $params = session_get_cookie_params();
                        setcookie(session_name(), '', time() - 42000,
                            $params["path"], $params["domain"],
                            $params["secure"], $params["httponly"]
                        );
                    }
                    session_destroy();

                    // NOTIFY & REDIRECT TO LOGIN PAGE
                    echo "<script>alert('Password Changed Successfully! Please log in again.');window.location='index.php';</script>";
                    exit();
                } else {
                    echo "<script>alert('Error updating password!');</script>";
                }
                $update_pass->close();
            } else {
                echo "<script>alert('Old Password is Incorrect!');</script>";
            }
        }
        $stmt->close();
    }
}

// FETCH USER DATA
$stmt_user = $conn->prepare("SELECT * FROM users WHERE ID=?");
if(!$stmt_user) {
    $stmt_user = $conn->prepare("SELECT * FROM users WHERE id=?");
}
$stmt_user->bind_param("i", $uid);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();
$stmt_user->close();
?>
<!DOCTYPE html>
<html>
<head>
<title>Profile Settings - NC3</title>
<link rel="icon" type="image/jpeg" href="assets/img/logo.jpg">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{
    font-family:'Poppins';
    background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
    min-height:100vh;
    padding:40px 20px;
    color:#e0e0e0;
    animation:fadeIn 0.8s ease;
}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
@keyframes slideUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}

.container{max-width:800px;margin:auto}
.box{
    background:rgba(255,255,255,0.95);
    backdrop-filter:blur(20px);
    padding:40px;
    border-radius:25px;
    box-shadow:0 25px 70px rgba(0,0,0,0.3);
    margin-bottom:20px;
    animation:slideUp 0.8s ease;
    transition:0.3s;
}
.box:hover{transform:translateY(-5px)}

h1{color:#667eea;margin-bottom:30px;text-align:center;font-weight:900}
h2{color:#667eea;margin-bottom:20px;font-weight:700}

.profile-pic{text-align:center;margin-bottom:20px}
.profile-pic img{
    width:120px;
    height:120px;
    border-radius:50%;
    border:4px solid #f093fb;
    object-fit:cover;
    margin-bottom:15px;
    transition:0.3s;
}
.profile-pic img:hover{transform:scale(1.1) rotate(5deg)}

.form-group{margin-bottom:20px}
.form-group label{display:block;color:#333;font-weight:600;margin-bottom:8px}
.form-group input{
    width:100%;
    padding:14px;
    border:2px solid #e0e0e0;
    border-radius:10px;
    font-size:15px;
    transition:0.3s;
    font-family:'Poppins';
    color:#333;
}
.form-group input:focus{outline:none;border-color:#667eea;box-shadow:0 0 15px rgba(102,126,234,0.3)}

.btn{
    background:linear-gradient(90deg,#667eea,#764ba2);
    color:white;
    border:none;
    padding:14px;
    border-radius:12px;
    font-weight:700;
    cursor:pointer;
    width:100%;
    font-size:16px;
    transition:0.3s;
    font-family:'Poppins';
}
.btn:hover{transform:translateY(-3px);box-shadow:0 10px 30px rgba(102,126,234,0.4)}
.btn-gray{background:linear-gradient(90deg,#6c757d,#495057)}

.grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}

.back{text-align:center;margin-top:20px}
.back a{
    color:white;
    text-decoration:none;
    font-weight:600;
    transition:0.3s;
    display:inline-flex;
    align-items:center;
    gap:8px;
}
.back a:hover{transform:translateX(-5px);color:#f093fb}

hr{border:none;border-top:1px solid #ddd;margin:30px 0}

@media(max-width:768px){
    body{padding:20px 15px}
    .box{padding:25px 20px}
    .grid{grid-template-columns:1fr}
    h1{font-size:26px}
    h2{font-size:20px}
    .profile-pic img{width:100px;height:100px}
    .form-group input{padding:12px;font-size:14px}
    .btn{padding:12px;font-size:15px}
}
</style>
</head>
<body>
<div class="container">

<!-- PROFILE PIC -->
<div class="box">
<h1>⚙️ Profile Settings</h1>
<div class="profile-pic">
    <img src="<?php echo (!empty($user['profile_pic'])) ? htmlspecialchars($user['profile_pic']) : 'https://ui-avatars.com/api/?name='.urlencode($user['fullname']).'&size=128&background=667eea&color=fff';?>">
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="profile_pic" accept="image/*" required style="margin-bottom:10px; color:#333;">
        <button type="submit" name="upload_pic" class="btn btn-gray">Upload New Picture</button>
    </form>
</div>
</div>

<!-- UPDATE INFO -->
<div class="box">
<form method="POST">
    <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
    </div>
    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
    </div>
    <div class="form-group">
        <label>Role</label>
        <input type="text" value="<?php echo htmlspecialchars($user['role']); ?>" readonly style="background:#f0f0f0">
    </div>
    <button type="submit" name="update" class="btn">Update Profile</button>
</form>
</div>

<!-- CHANGE PASSWORD -->
<div class="box">
<h2>🔒 Change Password</h2>
<form method="POST">
    <div class="form-group">
        <label>Old Password</label>
        <input type="password" name="old_pass" required>
    </div>
    <div class="grid">
        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_pass" required>
        </div>
        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="confirm_pass" required>
        </div>
    </div>
    <button type="submit" name="change_pass" class="btn">Change Password</button>
</form>
</div>

<div class="back">
    <a href="dashboard.php">← Back to Dashboard</a>
</div>

</div>
</body>
</html>