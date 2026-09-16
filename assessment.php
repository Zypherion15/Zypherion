<?php session_start(); if(!isset($_SESSION['uid'])){ header("Location: index.php"); exit(); } ?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Assessment - NC3</title>
<link rel="icon" type="image/jpeg" href="assets/img/logo.jpg">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{ 
    font-family:'Poppins', sans-serif; 
    background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); 
    min-height:100vh; 
    display:flex; 
    justify-content:center; 
    align-items:center; 
    padding:40px 20px;
    animation:fadeIn 0.8s ease;
}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
@keyframes fadeInUp{ from{opacity:0;transform:translateY(40px)} to{opacity:1;transform:translateY(0)} }
@keyframes float{ 0%,100%{transform:translateY(0)} 50%{transform:translateY(-30px)} }
@keyframes bounce{ 0%,100%{transform:translateY(0)} 50%{transform:translateY(-15px)} }

/* ANIMATED BACKGROUND */
.bg-shapes span{ 
    position:fixed; 
    border-radius:50%; 
    background:rgba(255,255,255,0.1); 
    animation:float 6s ease-in-out infinite; 
    z-index:-1; 
}
.bg-shapes span:nth-child(1){width:80px;height:80px;top:10%;left:10%;animation-delay:0s}
.bg-shapes span:nth-child(2){width:120px;height:120px;top:70%;left:80%;animation-delay:2s}
.bg-shapes span:nth-child(3){width:60px;height:60px;top:40%;left:90%;animation-delay:4s}

/* MAIN CARD */
.container{ 
    max-width:700px; 
    width:100%; 
    animation:fadeInUp 0.8s ease; 
    z-index:1; 
}

/* BACK TO DASHBOARD BUTTON */
.back-container {
    margin-bottom: 20px;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #ffffff;
    text-decoration: none;
    font-weight: 600;
    font-size: 15px;
    background: rgba(255, 255, 255, 0.15);
    padding: 10px 20px;
    border-radius: 12px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: 0.3s ease;
}

.btn-back:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateX(-5px);
}

.box{ 
    background:rgba(255,255,255,0.95); 
    backdrop-filter:blur(20px); 
    padding:50px 40px; 
    border-radius:30px; 
    box-shadow:0 25px 70px rgba(0,0,0,0.3); 
    text-align:center; 
    position:relative; 
    overflow:hidden; 
    transition:0.3s;
}
.box:hover{transform:translateY(-5px)}
.box::before{ 
    content:''; 
    position:absolute; 
    top:0;left:0; 
    width:100%;height:5px; 
    background:linear-gradient(90deg,#667eea,#764ba2); 
}
.icon{ 
    font-size:80px; 
    margin-bottom:20px; 
    animation:bounce 2s infinite; 
}
h1{ 
    font-size:36px; 
    font-weight:800; 
    background:linear-gradient(90deg,#667eea,#764ba2); 
    -webkit-background-clip:text; 
    -webkit-text-fill-color:transparent; 
    margin-bottom:15px; 
}
.subtitle{ color:#666; font-size:16px; margin-bottom:30px; }

.info-grid{ 
    display:grid; 
    grid-template-columns:repeat(3,1fr); 
    gap:15px; 
    margin:30px 0; 
}
.info-card{
    background:linear-gradient(135deg,#f8f9ff,#eef0ff); 
    padding:20px; 
    border-radius:15px; 
    border:2px solid #e0e0e0; 
    transition:0.3s;
}
.info-card:hover{ 
    transform:translateY(-5px) scale(1.05); 
    border-color:#667eea; 
    box-shadow:0 10px 25px rgba(102,126,234,0.2); 
}
.info-card .num{ font-size:28px; font-weight:700; color:#667eea; }
.info-card .label{ font-size:12px; color:#666; margin-top:5px; }

.rules{ 
    background:#fff3cd; 
    border-left:4px solid #ffc107; 
    padding:20px; 
    border-radius:10px; 
    text-align:left; 
    margin:25px 0;
    transition:0.3s;
}
.rules:hover{transform:scale(1.02)}
.rules h3{ color:#856404; margin-bottom:10px; font-size:16px; }
.rules ul{ list-style:none; color:#856404; font-size:14px; }
.rules ul li{margin:8px 0}
.rules ul li::before{content:'✓ ';font-weight:700}

.btn{ 
    background:linear-gradient(90deg,#667eea,#764ba2); 
    color:white; 
    border:none; 
    padding:18px 50px; 
    border-radius:15px; 
    font-size:18px; 
    font-weight:700; 
    cursor:pointer; 
    transition:0.3s; 
    box-shadow:0 10px 30px rgba(102,126,234,0.4);
    font-family:'Poppins', sans-serif;
}
.btn:hover{ 
    transform:translateY(-3px) scale(1.05); 
    box-shadow:0 15px 40px rgba(102,126,234,0.6); 
}

/* MOBILE RESPONSIVE */
@media(max-width:768px){
    body{padding:20px 15px}
    .box{padding:30px 20px}
    h1{font-size:28px}
    .subtitle{font-size:14px}
    .info-grid{grid-template-columns:1fr}
    .info-card{padding:15px}
    .info-card .num{font-size:24px}
    .btn{width:100%;padding:16px;font-size:16px}
    .rules{padding:15px}
    .btn-back{width:100%;justify-content:center}
}
</style>
</head>
<body>
<div class="bg-shapes">
    <span></span><span></span><span></span>
</div>

<div class="container">
    <!-- BACK TO DASHBOARD LINK -->
    <div class="back-container">
        <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
    </div>

    <div class="box">
        <div class="icon">📝</div>
        <h1>NC3 Assessment</h1>
        <p class="subtitle">Mobile Phone & Handheld Gadget Servicing</p>

        <div class="info-grid">
            <div class="info-card">
                <div class="num">10</div>
                <div class="label">Questions</div>
            </div>
            <div class="info-card">
                <div class="num">30</div>
                <div class="label">Minutes</div>
            </div>
            <div class="info-card">
                <div class="num">75%</div>
                <div class="label">Passing Score</div>
            </div>
        </div>

        <div class="rules">
            <h3>📋 Instructions:</h3>
            <ul>
                <li>Read each question carefully</li>
                <li>Choose the best answer</li>
                <li>You cannot go back once submitted</li>
                <li>Good luck!</li>
            </ul>
        </div>

        <form action="take_quiz.php" method="GET">
            <button type="submit" class="btn">Start Assessment →</button>
        </form>
    </div>
</div>
</body>
</html>