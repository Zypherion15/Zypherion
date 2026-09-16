<?php 
session_start();
include 'config.php';

if(!isset($_SESSION['uid'])){ 
    header("Location: index.php"); 
    exit(); 
}

$uid = $_SESSION['uid'];
$msg = "";

if(isset($_POST['add_tutorial'])){
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $video = trim($_POST['video_link']);
    $category = trim($_POST['category']);
    $instructor_id = $_SESSION['uid'];

    // AUTO EMBED YOUTUBE (Supports standard, shortened, shorts, and embed links)
    if(preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/', $video, $match)){
        $video = "https://www.youtube.com/embed/" . $match[1];
    }

    // Prepared Statement for SQL Injection Security
    $stmt = $conn->prepare("INSERT INTO tutorials (instructor_id, title, description, video_link, category) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $instructor_id, $title, $description, $video, $category);

    if($stmt->execute()){
        $stmt->close();
        header("Location: tutorials.php"); 
        exit();
    } else {
        $msg = "Error: " . $stmt->error;
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Tutorial - NC3</title>
<link rel="icon" type="image/jpeg" href="assets/img/logo.jpg">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{
    font-family:'Poppins', sans-serif;
    background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:30px 20px;
    animation:fadeIn 0.8s ease;
}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
@keyframes slideUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}

.box{
    background:rgba(255,255,255,0.95);
    backdrop-filter:blur(20px);
    padding:40px;
    border-radius:25px;
    width:100%;
    max-width:550px;
    box-shadow:0 25px 70px rgba(0,0,0,0.3);
    animation:slideUp 0.6s ease;
    border:1px solid rgba(255,255,255,0.2);
}

h2{
    text-align:center;
    margin-bottom:25px;
    background:linear-gradient(90deg,#667eea,#764ba2);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
    font-size:28px;
    font-weight:800;
}

.msg.error {
    background: #ffe0e0;
    color: #e74c3c;
    padding: 12px 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: 14px;
    border-left: 4px solid #e74c3c;
    font-weight: 600;
}

label{
    font-weight:600;
    color:#444;
    font-size:14px;
    display:block;
    margin-top:15px;
}

input,textarea,select{
    width:100%;
    padding:14px;
    margin-top:6px;
    border:2px solid #e0e0e0;
    border-radius:12px;
    font-family:'Poppins', sans-serif;
    font-size:14px;
    transition:0.3s;
    background:white;
    color:#333;
}

input:focus,textarea:focus,select:focus{
    border-color:#667eea;
    outline:none;
    box-shadow:0 0 0 4px rgba(102,126,234,0.15);
}

.btn{
    background:linear-gradient(90deg,#667eea,#764ba2);
    color:white;
    border:none;
    padding:16px;
    border-radius:12px;
    width:100%;
    font-weight:700;
    cursor:pointer;
    font-size:16px;
    transition:0.3s;
    margin-top:25px;
    box-shadow: 0 10px 25px rgba(102,126,234,0.3);
    font-family:'Poppins', sans-serif;
}

.btn:hover{
    transform:translateY(-3px);
    box-shadow:0 15px 35px rgba(102,126,234,0.5);
}

.back{
    text-align:center;
    margin-top:20px;
}

.back a{
    color:#667eea;
    text-decoration:none;
    font-weight:600;
    font-size:14px;
    transition:0.3s;
    display:inline-block;
}

.back a:hover{
    color:#764ba2;
    transform:translateX(-3px);
}

/* MOBILE RESPONSIVE */
@media(max-width:576px){
    body{padding:20px 15px}
    .box{padding:30px 20px}
    h2{font-size:22px}
}
</style>
</head>
<body>

<div class="box">
    <h2>+ Add New Tutorial</h2>
    
    <?php if($msg): ?>
        <div class="msg error"><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="title">Tutorial Title</label>
        <input type="text" id="title" name="title" placeholder="Ex: Soldering USB Port" required>

        <label for="description">Description</label>
        <textarea id="description" name="description" placeholder="Explain what this tutorial is about" rows="4" required></textarea>

        <label for="video_link">YouTube Link</label>
        <input type="text" id="video_link" name="video_link" placeholder="Paste any YouTube URL or Shorts link" required>

        <label for="category">Category</label>
        <select id="category" name="category" required>
            <option value="">-- Select Category --</option>
            <option value="Soldering Charging Port">Soldering Charging Port</option>
            <option value="Reformat of Mobile Phone">Reformat of Mobile Phone</option>
            <option value="Interview Guide">Interview Guide</option>
            <option value="Disassemble and Assemble">Disassemble and Assemble of Handheld Gadget</option>
        </select>

        <button type="submit" name="add_tutorial" class="btn">Add Tutorial</button>
    </form>

    <div class="back">
        <a href="tutorials.php">← Back to Tutorials</a>
    </div>
</div>

</body>
</html>