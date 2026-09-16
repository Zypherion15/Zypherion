<?php
session_start();
include 'config.php';

$uid = isset($_GET['uid'])? intval($_GET['uid']) : 0;
if($uid == 0){ die("Error: No User ID. <a href='index.php'>Login</a>"); }

$msg = "";
$result = mysqli_query($conn, "SELECT * FROM users WHERE ID=$uid");
$user = mysqli_fetch_assoc($result);

// UPDATE PROFILE + PROFILE PIC
if(isset($_POST['update'])){
    $name = $_POST['fullname'];
    $email = $_POST['email'];
    $profile_pic = $user['profile_pic'];
    
    // UPLOAD PIC
    if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['name'] != ""){
        $target_dir = "assets/img/";
        $file_name = time() . "_" . basename($_FILES["profile_pic"]["name"]);
        $target_file = $target_dir . $file_name;
        if(move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)){
            $profile_pic = $target_file;
        }
    }
    
    mysqli_query($conn, "UPDATE users SET fullname='$name', email='$email', profile_pic='$profile_pic' WHERE id=$uid");
    $msg = "✅ Profile Updated Successfully!";
    $user['fullname'] = $name;
    $user['email'] = $email;
    $user['profile_pic'] = $profile_pic;
}

// UPDATE PASSWORD WITH OLD PASS CHECK
if(isset($_POST['update_pass'])){
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    
    if(password_verify($old_pass, $user['password'])){
        $hashed_new = password_hash($new_pass, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET password='$hashed_new' WHERE ID=$uid");
        $msg = "✅ Password Updated Successfully!";
    } else {
        $msg = "❌ Old Password is Incorrect!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Settings - NC3 MP&HG Simulation</title>
<link rel="icon" type="image/jpeg" href="assets/img/logo.jpg">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Poppins',sans-serif;background:linear-gradient(rgba(102, 126, 234, 0.9), rgba(118, 75, 162, 0.9)),url('https://images.unsplash.com/photo-1519389950473-47ba0277781c?q=80&w=1170') center/cover fixed;min-height:100vh}
.topbar{background:rgba(0,0,0,0.3);backdrop-filter:blur(10px);color:white;padding:15px 50px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid rgba(255,255,255,0.2);position:sticky;top:0;z-index:1000}
.topbar .logo{display:flex;align-items:center;gap:12px;font-size:18px;font-weight:800}
.topbar .logo img{height:32px; border-radius:8px;}
.user-info{background:rgba(255,255,255,0.2);padding:10px 20px;border-radius:50px;display:flex;gap:15px;align-items:center}
.user-info a{color:white;text-decoration:none;font-weight:600;border-left:1px solid rgba(255,255,255,0.3);padding-left:15px}
.container{padding:50px;max-width:800px;margin:auto}
.page-title{color:white;margin-bottom:30px}
.page-title h1{font-size:36px;font-weight:700;text-shadow:0 5px 15px rgba(0,0,0,0.3)}
.settings-card{background:rgba(255,255,255,0.95);padding:40px;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,0.3);margin-bottom:25px}
.settings-card h3{color:#667eea;margin-bottom:20px;font-size:22px;border-bottom:2px solid #f0f0f0;padding-bottom:15px}
.form-group{margin-bottom:20px}
.form-group label{display:block;color:#555;font-weight:600;margin-bottom:8px}
.form-group input{width:100%;padding:14px;border:2px solid #e0e0e0;border-radius:12px;font-size:15px;font-family:'Poppins'}
.profile-preview{display:flex;align-items:center;gap:20px;margin-bottom:20px}
.profile-preview img{width:100px;height:100px;border-radius:50%;object-fit:cover;border:4px solid #667eea}
.btn-save{padding:14px 35px;background:linear-gradient(90deg,#667eea,#764ba2);color:white;border:none;border-radius:12px;font-size:16px;font-weight:700;cursor:pointer}
.msg{text-align:center;padding:12px;border-radius:10px;margin-bottom:20px;font-weight:600}
.success{background:#e0ffe0;color:#388e3c}
.error{background:#ffe0e0;color:#d32f2f}
</style>
</head>
<body>

<div class="topbar">
    <div class="logo">
        <img src="assets/img/logo.jpg" alt="NC3 Logo">
        NC3 MP&HG <span style="color:#f093fb">Simulation</span>
    </div>
    <div class="user-info">
        <img src="<?php echo $user['profile_pic'];?>" style="width:35px;height:35px;border-radius:50%;object-fit:cover;">
        <b><?php echo $user['fullname'];?></b>
        <a href="dashboard.php?uid=<?php echo $uid;?>">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <div class="page-title">
        <a href="dashboard.php?uid=<?php echo $uid;?>" style="color:white">← Back to Dashboard</a>
        <h1>⚙️ Account Settings</h1>
    </div>

    <?php if($msg) echo "<div class='msg ".(strpos($msg,'✅')!==false?'success':'error')."'>$msg</div>"; ?>

    <!-- PROFILE SETTINGS -->
    <div class="settings-card">
        <h3>👤 Profile Information</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="profile-preview">
                <img src="<?php echo $user['profile_pic'];?>" alt="Profile">
                <div>
                    <label>Change Profile Picture</label>
                    <input type="file" name="profile_pic" accept="image/*">
                </div>
            </div>
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="fullname" value="<?php echo $user['fullname'];?>" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo $user['email'];?>" required>
            </div>
            <button type="submit" name="update" class="btn-save">Save Changes</button>
        </form>
    </div>

    <!-- PASSWORD SETTINGS -->
    <div class="settings-card">
        <h3>🔒 Change Password</h3>
        <form method="POST">
            <div class="form-group">
                <label>Old Password</label>
                <input type="password" name="old_password" required>
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" required>
            </div>
            <button type="submit" name="update_pass" class="btn-save">Update Password</button>
        </form>
    </div>

</div>
</body></html>