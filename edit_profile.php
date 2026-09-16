<?php
session_start();
if(!isset($_SESSION['user_id'])){ header("Location: login.php"); exit(); }
include 'config.php';

$user_id = $_SESSION['user_id'];
$msg = "";

// KUNIN DATA
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// PAG NAG SUBMIT
if(isset($_POST['update'])){
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    
    // UPLOAD PIC
    if($_FILES['profile_pic']['name'] != ""){
        $target = "uploads/" . basename($_FILES['profile_pic']['name']);
        move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target);
        $pic = $_FILES['profile_pic']['name'];
        $sql = "UPDATE users SET fullname=?, email=?, profile_pic=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $fullname, $email, $pic, $user_id);
    } else {
        $sql = "UPDATE users SET fullname=?, email=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $fullname, $email, $user_id);
    }
    
    if($stmt->execute()){
        $_SESSION['fullname'] = $fullname;
        $msg = "Profile Updated Successfully!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <style>
        body { font-family: 'Segoe UI'; background: #f0f2f5; display: flex; }
        .sidebar { width: 260px; background: linear-gradient(180deg, #4030D0, #008080); height: 100vh; position: fixed; }
        .sidebar a { display: block; color: white; padding: 15px 25px; text-decoration: none; }
        .main { margin-left: 260px; width: 100%; padding: 30px; }
        .card { background: white; padding: 30px; border-radius: 15px; max-width: 500px; margin: auto; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        input, .btn { width: 100%; padding: 12px; margin: 10px 0; border: 2px solid #ddd; border-radius: 8px; }
        .btn { background: linear-gradient(90deg, #4030D0, #008080); color: white; border: none; font-weight: bold; cursor: pointer; }
        .btn:hover { opacity: 0.9; }
        .avatar { width: 120px; height: 120px; border-radius: 50%; margin: 0 auto 20px; display: block; object-fit: cover; }
        .msg { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2 style="color:white; text-align:center;">NC3 MP&HG stimualtion</h2>
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="settings.php">⚙️ Settings</a>
    </div>
    
    <div class="main">
        <div class="card">
            <h2 style="color:#4030D0;">Edit Profile</h2>
            <?php if($msg) echo "<div class='msg'>$msg</div>"; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <img src="uploads/<?php echo $user['profile_pic']; ?>" class="avatar">
                <label>Change Profile Picture</label>
                <input type="file" name="profile_pic" accept="image/*">
                
                <label>Full Name</label>
                <input type="text" name="fullname" value="<?php echo $user['fullname']; ?>" required>
                
                <label>Email</label>
                <input type="email" name="email" value="<?php echo $user['email']; ?>" required>
                
                <button type="submit" name="update" class="btn">Save Changes</button>
                <a href="change_password.php" class="btn" style="background:#008080; text-align:center; display:block; text-decoration:none;">Change Password</a>
            </form>
        </div>
    </div>
</body>
</html>