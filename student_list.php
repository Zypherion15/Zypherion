<?php 
session_start();
include 'config.php';

if(!isset($_SESSION['uid'])){ 
    header("Location: index.php"); 
    exit(); 
}

$uid = $_SESSION['uid'];

// Prepared Statement for Instructor Security Verification
$stmt = $conn->prepare("SELECT fullname, role FROM users WHERE id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$user || strtolower($user['role']) !== 'instructor'){
    header("Location: dashboard.php");
    exit();
}

// Fetch Students with Quiz Count
$students = mysqli_query($conn, "SELECT u.*, 
    (SELECT COUNT(*) FROM quiz_results qr WHERE qr.user_id = u.id) as quiz_count
    FROM users u WHERE LOWER(u.role)='student' ORDER BY u.fullname ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student List - NC3</title>
<link rel="icon" type="image/jpeg" href="assets/img/logo.jpg">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{
    font-family:'Poppins', sans-serif;
    background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
    min-height:100vh;
    animation:fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

/* KEYFRAMES */
@keyframes fadeIn { from{opacity:0;} to{opacity:1;} }
@keyframes slideDown { from{opacity:0;transform:translateY(-30px);} to{opacity:1;transform:translateY(0);} }
@keyframes popIn { from{opacity:0;transform:scale(0.88) translateY(20px);} to{opacity:1;transform:scale(1) translateY(0);} }
@keyframes float { 0%, 100%{transform:translateY(0);} 50%{transform:translateY(-6px);} }
@keyframes pulseGlow { 0%, 100%{box-shadow:0 0 15px rgba(255,255,255,0.2);} 50%{box-shadow:0 0 25px rgba(255,255,255,0.4);} }

/* TOPBAR */
.topbar{
    background:rgba(0,0,0,0.2);
    backdrop-filter:blur(20px);
    color:white;
    padding:15px 40px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    border-bottom:1px solid rgba(255,255,255,0.15);
    transition: all 0.3s ease;
}
.logo{
    display:flex;
    align-items:center;
    gap:12px;
    font-weight:700;
    font-size:18px;
    transition: transform 0.3s ease;
}
.logo:hover{ transform: scale(1.02); }
.logo img{
    height:35px;
    border-radius:10px;
    transition: transform 0.4s ease;
}
.logo:hover img { transform: rotate(5deg) scale(1.05); }

/* BACK BUTTON */
.btn-back {
    color: white;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    background: rgba(255, 255, 255, 0.15);
    padding: 10px 18px;
    border-radius: 12px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.25);
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.btn-back:hover {
    background: rgba(255, 255, 255, 0.35);
    transform: translateX(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

/* CONTAINER & CONTENT */
.container{padding:40px 20px;max-width:1200px;margin:auto}
.header{text-align:center;color:white;margin-bottom:40px;animation:slideDown 0.7s cubic-bezier(0.16, 1, 0.3, 1)}
.header h1{font-size:36px;margin-bottom:10px;font-weight:700}
.header p{opacity:0.9;font-size:15px}

.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;margin-bottom:30px}
.stat-card{
    background:rgba(255,255,255,0.15);
    backdrop-filter:blur(20px);
    padding:25px;
    border-radius:20px;
    color:white;
    border:1px solid rgba(255,255,255,0.25);
    animation:popIn 0.6s ease forwards;
    transition: all 0.3s ease;
}
.stat-card:hover{
    background:rgba(255,255,255,0.25);
    transform:translateY(-5px);
    box-shadow:0 15px 30px rgba(0,0,0,0.2);
}
.stat-card .num{font-size:36px;font-weight:700;margin-top:5px;transition: transform 0.3s ease;}
.stat-card:hover .num{ transform: scale(1.08); }

/* GRID & CARDS */
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:25px}
.card{
    background:rgba(255,255,255,0.95);
    backdrop-filter:blur(20px);
    border-radius:20px;
    padding:25px;
    box-shadow:0 15px 35px rgba(0,0,0,0.15);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    animation:popIn 0.5s ease forwards;
    opacity: 0;
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,0.6);
}
.card::before {
    content:'';
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:4px;
    background:linear-gradient(90deg,#667eea,#764ba2);
    opacity:0;
    transition:opacity 0.3s ease;
}
.card:hover{
    transform:translateY(-10px) scale(1.02);
    box-shadow:0 25px 50px rgba(0,0,0,0.25);
}
.card:hover::before { opacity: 1; }

.avatar{
    width:55px;
    height:55px;
    border-radius:50%;
    background:linear-gradient(135deg,#667eea,#764ba2);
    display:flex;
    align-items:center;
    justify-content:center;
    color:white;
    font-weight:700;
    font-size:22px;
    flex-shrink:0;
    transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow:0 5px 15px rgba(102,126,234,0.3);
}
.card:hover .avatar{ transform: scale(1.1) rotate(5deg); }

.name{font-weight:700;font-size:18px;color:#2d3748;word-break:break-word;line-height:1.2}
.username-id{font-size:11px;color:#888;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-top:3px}
.email{
    font-size:12px;
    color:#555;
    margin:6px 0 8px 0;
    word-break:break-all;
    display:flex;
    align-items:center;
    gap:5px;
    transition: color 0.3s ease;
}
.card:hover .email { color:#667eea; }

.badge{
    background:linear-gradient(90deg,#28a745,#20c997);
    color:white;
    padding:4px 12px;
    border-radius:20px;
    font-size:11px;
    font-weight:600;
    display:inline-block;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.card:hover .badge {
    transform: scale(1.05);
    box-shadow: 0 4px 10px rgba(40,167,69,0.3);
}

.info{
    display:flex;
    justify-content:space-around;
    margin-top:15px;
    padding-top:15px;
    border-top:1px solid #edf2f7;
    transition: border-color 0.3s ease;
}
.info-item{text-align:center; transition: transform 0.3s ease;}
.card:hover .info-item { transform: translateY(-2px); }
.info-item .label{font-size:12px;color:#718096;font-weight:600}
.info-item .value{font-size:22px;font-weight:700;color:#667eea;transition: color 0.3s ease;}

/* MOBILE RESPONSIVE */
@media(max-width:768px){
    .topbar{padding:15px 20px}
    .logo span{display:none}
    .container{padding:20px 15px}
    .header h1{font-size:28px}
}
</style>
</head>
<body>

<div class="topbar">
    <div class="logo">
        <img src="assets/img/logo.jpg" alt="Logo"> 
        <span>NC3 Instructor Panel</span>
    </div>

    <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
</div>

<div class="container">
    <div class="header">
        <h1>👥 Student List</h1>
        <p>Manage and monitor your enrolled students</p>
    </div>

    <div class="stats">
        <div class="stat-card">
            <h3>Total Students</h3>
            <div class="num"><?php echo mysqli_num_rows($students); ?></div>
        </div>
    </div>

    <?php if(mysqli_num_rows($students) > 0): ?>
        <div class="grid">
            <?php 
            $delay = 0; 
            while($s = mysqli_fetch_assoc($students)): 
                $fullname = htmlspecialchars($s['fullname'], ENT_QUOTES, 'UTF-8');
                $email = htmlspecialchars($s['email'] ?? 'No email provided', ENT_QUOTES, 'UTF-8');
                $initial = !empty($fullname) ? strtoupper(substr($fullname, 0, 1)) : '?';
            ?>
                <div class="card" style="animation-delay: <?php echo min($delay * 0.08, 0.8); ?>s">
                    <div style="display:flex;align-items:flex-start;gap:15px;">
                        <div class="avatar"><?php echo $initial; ?></div>
                        <div style="flex:1; min-width:0;">
                            <div class="name"><?php echo $fullname; ?></div>
                            <div class="username-id">ID: #<?php echo (int)$s['id']; ?></div>
                            <div class="email">✉️ <?php echo $email; ?></div>
                            <span class="badge">Active</span>
                        </div>
                    </div>
                    <div class="info">
                        <div class="info-item">
                            <div class="label">Quizzes Taken</div>
                            <div class="value"><?php echo (int)$s['quiz_count']; ?></div>
                        </div>
                    </div>
                </div>
            <?php 
                $delay++; 
            endwhile; 
            ?>
        </div>
    <?php else: ?>
        <p style="text-align:center;color:white;font-size:18px;">No students enrolled yet.</p>
    <?php endif; ?>
</div>

</body>
</html>