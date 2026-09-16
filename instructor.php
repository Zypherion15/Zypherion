<?php
session_start();
include 'config.php';

$uid = isset($_GET['uid'])? intval($_GET['uid']) : 0;
if($uid == 0){ die("Error: No User ID. <a href='index.php'>Login</a>"); }

// KUNIN USER DATA
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE ID=$uid");
$user = mysqli_fetch_assoc($user_query);
$role = $user['role'];

// CHECK KUNG INSTRUCTOR BA
if($role != 'Instructor' && $role != 'Admin'){
    die("Access Denied. Instructor only. <a href='dashboard.php?uid=$uid'>Back to Dashboard</a>");
}

// UPLOAD TUTORIAL
if(isset($_POST['upload_tutorial'])){
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $video = mysqli_real_escape_string($conn, $_POST['video_url']);
    
    $sql = "INSERT INTO tutorials (title, video_url, uploaded_by) VALUES ('$title','$video',$uid)";
    
    if(mysqli_query($conn, $sql)){
        // INSERT ACTIVITY
        $activity_title = "New Tutorial Uploaded: ".$title;
        $activity_details = "by Instructor ".$user['fullname'];
        $activity_link = "tutorials.php?uid=".$uid;
        $act_sql = "INSERT INTO activities (user_id, type, title, details, link) VALUES ($uid, 'tutorial', '$activity_title', '$activity_details', '$activity_link')";
        mysqli_query($conn, $act_sql);
        
        header("Location: instructor.php?uid=$uid&success=1");
        exit();
    } else {
        $error = "Upload Failed: ".mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Instructor Panel - NC3</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif}
body{background:linear-gradient(135deg,#667eea,#764ba2);padding:40px;color:white}
.container{max-width:800px;margin:auto;background:rgba(255,255,255,0.1);backdrop-filter:blur(20px);padding:40px;border-radius:20px}
h1{margin-bottom:30px}
input{width:100%;padding:15px;margin-bottom:20px;border-radius:10px;border:none;font-size:16px}
button{background:linear-gradient(90deg,#f093fb,#f5576c);color:white;padding:15px 30px;border:none;border-radius:10px;font-weight:700;cursor:pointer;font-size:16px}
.success{background:#00ff88;color:#000;padding:15px;border-radius:10px;margin-bottom:20px}
a{color:white}
</style>
</head>
<body>
<div class="container">
    <h1>📢 Instructor Panel</h1>
    <a href="dashboard.php?uid=<?php echo $uid;?>">← Back to Dashboard</a>
    
    <?php if(isset($_GET['success'])): ?>
        <div class="success">Tutorial Uploaded Successfully!</div>
    <?php endif; ?>
    <?php if(isset($error)): ?>
        <div class="success" style="background:red"><?php echo $error;?></div>
    <?php endif; ?>

    <form method="POST">
        <h3>Upload New Tutorial</h3>
        <input type="text" name="title" placeholder="Tutorial Title - Ex: NC3 Module 1" required>
        <input type="text" name="video_url" placeholder="Youtube Link - Ex: https://youtube.com/..." required>
        <button type="submit" name="upload_tutorial">Upload Tutorial</button>
    </form>
</div>
</body>
</html>