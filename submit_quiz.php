<?php 
session_start();
include 'config.php';

if(!isset($_SESSION['uid'])){ 
    header("Location: index.php"); 
    exit(); 
}
$uid = $_SESSION['uid'];

if(isset($_POST['submit'])){
    $answers = $_POST['q'];
    $total = count($answers);
    $score = 0;

    foreach($answers as $qid => $ans){
        $res = mysqli_query($conn, "SELECT correct_answer FROM quiz_questions WHERE id='$qid'");
        $row = mysqli_fetch_assoc($res);
        
        if($row && strtoupper($row['correct_answer']) == strtoupper($ans)){
            $score++;
        }
    }

    $percentage = round(($score / $total) * 100, 1);
    $status = $percentage >= 75 ? 'Passed' : 'Failed'; // DITO INAYOS

    // 1. SAVE SCORE
    $insert = mysqli_query($conn, "INSERT INTO quiz_results (user_id, score, total, taken_at) 
              VALUES ('$uid', '$score', '$total', NOW())"); // DITO INAYOS

    if($insert){ // Check muna kung success
        // 2. SAVE ACTIVITY
        $title = "Completed NC3 Assessment";
        $details = "Score: $score/$total ($percentage%) - Status: $status";
        $type = "assessment";
        $link = "dashboard.php";
        mysqli_query($conn, "INSERT INTO activities (user_id, type, title, details, link, created_at) 
                  VALUES ('$uid', '$type', '$title', '$details', '$link', NOW())");
    } else {
        die("DB Error: " . mysqli_error($conn)); // Para makita natin error
    }

?>
<!DOCTYPE html>
<html>
<head>
<title>Result - NC3</title>
<link rel="icon" type="image/jpeg" href="assets/img/logo.jpg">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Poppins';background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;display:flex;justify-content:center;align-items:center;padding:20px}
.box{background:rgba(255,255,255,0.95);backdrop-filter:blur(20px);padding:50px;border-radius:25px;width:500px;text-align:center;box-shadow:0 25px 70px rgba(0,0,0,0.3);animation:fadeIn 0.6s ease}
@keyframes fadeIn{from{opacity:0;transform:scale(0.9)}to{opacity:1;transform:scale(1)}}
.score{font-size:72px;font-weight:700;background:linear-gradient(90deg,#667eea,#764ba2);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.status{font-size:24px;margin:20px 0;font-weight:600}
.pass{color:#28a745}
.fail{color:#dc3545}
.btn{display:inline-block;background:linear-gradient(90deg,#667eea,#764ba2);color:white;padding:15px 40px;border-radius:12px;text-decoration:none;font-weight:700;margin:10px;transition:0.3s}
.btn:hover{transform:translateY(-3px)}
</style>
</head>
<body>
<div class="box">
<h2>Assessment Result</h2>
<div class="score"><?php echo $score; ?>/<?php echo $total; ?></div>
<div style="font-size:20px;margin-bottom:20px"><?php echo $percentage; ?>%</div>

<?php if($percentage >= 75): ?>
<div class="status pass">✅ PASSED</div>
<p>Congratulations!</p>
<?php else: ?>
<div class="status fail">❌ FAILED</div>
<p>You need 75% to pass.</p>
<?php endif; ?>

<a href="assessment.php" class="btn">Take Again</a>
<a href="dashboard.php" class="btn">Dashboard</a>
</div>
</body>
</html>
<?php
} else {
    header("Location: assessment.php");
}
?>