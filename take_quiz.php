<?php 
session_start(); 
include 'config.php';
if(!isset($_SESSION['uid'])){ header("Location: index.php"); exit(); }

$result = mysqli_query($conn, "SELECT * FROM quiz_questions ORDER BY RAND() LIMIT 10");
?>
<!DOCTYPE html>
<html>
<head>
<title>Take Assessment - NC3</title>
<link rel="icon" type="image/jpeg" href="assets/img/logo.jpg">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
*{margin:0;padding:0;box-sizing:border-box;scroll-behavior:smooth}
body{
    font-family:'Poppins';
    background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
    padding:40px 20px;
    min-height:100vh;
    animation:fadeIn 0.8s ease;
}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
@keyframes slideUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
@keyframes blink{0%,50%,100%{opacity:1}25%,75%{opacity:0.5}}

.container{max-width:800px;margin:auto}
.box{
    background:rgba(255,255,255,0.95);
    backdrop-filter:blur(20px);
    padding:40px;
    border-radius:25px;
    box-shadow:0 25px 70px rgba(0,0,0,0.3);
    animation:slideUp 0.8s ease;
}
h1{
    text-align:center;
    margin-bottom:30px;
    font-weight:900;
    background:linear-gradient(90deg,#667eea,#764ba2);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
}

.question{
    margin-bottom:25px;
    padding:25px;
    background:linear-gradient(135deg,#f8f9ff,#eef0ff);
    border-radius:15px;
    border-left:4px solid #667eea;
    transition:0.3s;
    animation:slideUp 0.6s ease forwards;
    opacity:0;
}
.question:nth-child(1){animation-delay:0.1s}
.question:nth-child(2){animation-delay:0.2s}
.question:nth-child(3){animation-delay:0.3s}
.question:hover{transform:translateX(5px);box-shadow:0 10px 25px rgba(102,126,234,0.2)}
.question h3{color:#667eea;margin-bottom:15px;font-size:18px}

.options label{
    display:block;
    padding:14px;
    margin:10px 0;
    background:white;
    border:2px solid #e0e0e0;
    border-radius:12px;
    cursor:pointer;
    transition:0.3s;
    font-weight:500;
}
.options label:hover{
    border-color:#667eea;
    background:#f8f9ff;
    transform:translateX(5px);
}
.options input{display:none}
.options input:checked + span{
    font-weight:700;
    color:#667eea;
}
.options input:checked ~ span{
    color:#667eea;
}
.options label:has(input:checked){
    border-color:#667eea;
    background:linear-gradient(90deg,rgba(102,126,234,0.1),rgba(118,75,162,0.1));
}

.btn{
    background:linear-gradient(90deg,#667eea,#764ba2);
    color:white;
    border:none;
    padding:18px 50px;
    border-radius:15px;
    font-weight:700;
    font-size:18px;
    cursor:pointer;
    width:100%;
    transition:0.3s;
    font-family:'Poppins';
    box-shadow:0 10px 30px rgba(102,126,234,0.4);
}
.btn:hover{transform:translateY(-3px);box-shadow:0 15px 40px rgba(102,126,234,0.6)}

#timer{
    position:fixed;
    top:20px;
    right:20px;
    background:linear-gradient(90deg,#667eea,#764ba2);
    color:white;
    padding:15px 25px;
    border-radius:15px;
    font-size:20px;
    font-weight:700;
    z-index:9999;
    box-shadow:0 10px 30px rgba(0,0,0,0.3);
    transition:0.3s;
}

/* MOBILE RESPONSIVE */
@media(max-width:768px){
    body{padding:20px 15px}
    .box{padding:25px 20px}
    h1{font-size:24px}
    .question{padding:20px 15px}
    .question h3{font-size:16px}
    .options label{padding:12px;font-size:14px}
    .btn{padding:16px;font-size:16px}
    #timer{
        top:10px;
        right:10px;
        font-size:16px;
        padding:10px 15px;
    }
}
</style>
</head>
<body>

<div id="timer">⏰ <span id="time">30:00</span></div>

<div class="container">
<div class="box">
<h1>📝 NC3 Assessment</h1>
<form action="submit_quiz.php" method="POST" id="quizForm">
<?php $i=1; while($q = mysqli_fetch_assoc($result)){?>
<div class="question">
    <h3>Q<?php echo $i++; ?>. <?php echo $q['question']; ?></h3>
    <div class="options">
        <label><input type="radio" name="q[<?php echo $q['id']; ?>]" value="A" required><span> A. <?php echo $q['option_a']; ?></span></label>
        <label><input type="radio" name="q[<?php echo $q['id']; ?>]" value="B"><span> B. <?php echo $q['option_b']; ?></span></label>
        <label><input type="radio" name="q[<?php echo $q['id']; ?>]" value="C"><span> C. <?php echo $q['option_c']; ?></span></label>
        <label><input type="radio" name="q[<?php echo $q['id']; ?>]" value="D"><span> D. <?php echo $q['option_d']; ?></span></label>
    </div>
</div>
<?php } ?>
<button type="submit" name="submit" class="btn">Submit Assessment</button>
</form>
</div>
</div>

<script>
let time = 30 * 60; // 30 minutes
const timerEl = document.getElementById('time');
const form = document.getElementById('quizForm');

let countdown = setInterval(() => {
    let minutes = Math.floor(time / 60);
    let seconds = time % 60;
    timerEl.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
    
    if(time <= 300){ // 5 mins warning
        timerEl.style.background = 'linear-gradient(90deg,#ff416c,#ff4b2b)';
        timerEl.style.animation = 'blink 1s infinite';
    }
    
    if(time <= 0){
        clearInterval(countdown);
        alert('Time is up! Submitting automatically.');
        form.submit();
    }
    time--;
}, 1000);
</script>
</body>
</html>