<?php 
session_start(); 
include 'config.php';

if(!isset($_SESSION['uid'])){ 
    header("Location: index.php"); 
    exit(); 
}

$uid = $_SESSION['uid'];

$stmt = $conn->prepare("SELECT role FROM users WHERE ID = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$user || strtolower($user['role']) !== 'instructor'){
    header("Location: dashboard.php");
    exit();
}

$scores = mysqli_query($conn, "SELECT qr.*, u.fullname, u.email 
                               FROM quiz_results qr 
                               JOIN users u ON qr.user_id = u.ID 
                               ORDER BY qr.taken_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Scores - Instructor</title>
<link rel="icon" type="image/jpeg" href="assets/img/logo.jpg">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;scroll-behavior:smooth}
body{
    font-family:'Poppins', sans-serif;
    background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
    padding:40px 20px;
    min-height:100vh;
    animation:fadeIn 0.8s ease;
}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
@keyframes slideUp{from{opacity:0;transform:translateY(40px)}to{opacity:1;transform:translateY(0)}}
.container{max-width:1300px;margin:auto;animation:slideUp 0.8s ease}

.back{margin-bottom:25px}
.back a{
    color:white;
    text-decoration:none;
    font-weight:700;
    font-size:16px;
    transition:0.3s;
    display:inline-flex;
    align-items:center;
    gap:8px;
    background: rgba(255, 255, 255, 0.15);
    padding: 10px 20px;
    border-radius: 12px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}
.back a:hover{transform:translateX(-5px);background:rgba(255,255,255,0.3)}

.box{
    background:rgba(255,255,255,0.95);
    backdrop-filter:blur(20px);
    padding:40px;
    border-radius:30px;
    box-shadow:0 25px 70px rgba(0,0,0,0.3);
    border:1px solid rgba(255,255,255,0.2);
}

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:30px;
    padding-bottom:20px;
    border-bottom:3px solid #f0f0f0;
}
.header h1{
    font-size:32px;
    font-weight:900;
    background:linear-gradient(90deg,#667eea,#764ba2);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
}
.total{
    background:linear-gradient(90deg,#667eea,#764ba2);
    color:white;
    padding:12px 25px;
    border-radius:15px;
    font-weight:700;
    box-shadow:0 10px 25px rgba(102,126,234,0.4);
}

.table-wrapper{overflow-x:auto;border-radius:20px}

table{
    width:100%;
    border-collapse:separate;
    border-spacing:0;
    animation:fadeIn 1s ease;
}
th{
    background:linear-gradient(90deg,#667eea,#764ba2);
    color:white;
    padding:18px 15px;
    text-align:left;
    font-weight:700;
    font-size:14px;
    text-transform:uppercase;
    letter-spacing:0.5px;
}
th:first-child{border-top-left-radius:15px}
th:last-child{border-top-right-radius:15px}

tr{
    transition:0.3s;
    animation:slideUp 0.6s ease forwards;
    opacity:0;
}

td{
    padding:18px 15px;
    border-bottom:1px solid #f0f0f0;
    color:#333;
    font-weight:500;
    transition:0.3s;
}
tr:hover{
    background:linear-gradient(90deg,rgba(102,126,234,0.1),rgba(118,75,162,0.1));
}

.student-name{font-weight:700;color:#667eea}
.score-box{
    background:#f8f9ff;
    padding:8px 15px;
    border-radius:10px;
    display:inline-block;
    font-weight:700;
    color:#667eea;
}
.perc{font-size:18px;font-weight:900}
.status{
    padding:8px 18px;
    border-radius:20px;
    font-weight:700;
    font-size:13px;
    display:inline-block;
}
.pass{background:linear-gradient(90deg,#00b09b,#96c93d);color:white}
.fail{background:linear-gradient(90deg,#ff416c,#ff4b2b);color:white}

.date{color:#888;font-size:13px}

.empty{
    text-align:center;
    padding:60px;
    color:#888;
    font-size:18px;
}
.empty-icon{font-size:80px;margin-bottom:20px}

/* MOBILE RESPONSIVE CARD VIEW */
@media (max-width: 768px) {
    body { padding: 15px 10px; }
    .box { padding: 20px 15px; border-radius: 20px; }
    .header { flex-direction: column; gap: 15px; text-align: center; }
    .header h1 { font-size: 22px; }

    /* Itago ang default Table Headers sa Mobile */
    table thead { display: none; }

    table, tbody, tr, td {
        display: block;
        width: 100%;
    }

    tr {
        margin-bottom: 15px;
        background: #ffffff;
        border: 1px solid #e0e7ff;
        border-radius: 15px;
        padding: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }

    td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 5px;
        border-bottom: 1px solid #f0f0f0;
        text-align: right;
        font-size: 13px;
    }

    td:last-child { border-bottom: none; }

    /* Lalagyan ng Label bago ang Data */
    td::before {
        content: attr(data-label);
        font-weight: 700;
        color: #667eea;
        text-transform: uppercase;
        font-size: 11px;
        text-align: left;
    }

    .perc { font-size: 15px; }
}
</style>
</head>
<body>
<div class="container">
    <div class="back">
        <a href="dashboard.php">← Back to Dashboard</a>
    </div>

    <div class="box">
        <div class="header">
            <h1>📊 Student Assessment Scores</h1>
            <div class="total">Total Attempts: <?php echo mysqli_num_rows($scores); ?></div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Email</th>
                        <th>Score</th>
                        <th>Percentage</th>
                        <th>Status</th>
                        <th>Date Taken</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                if(mysqli_num_rows($scores) > 0){
                    $i = 1; 
                    while($s = mysqli_fetch_assoc($scores)){ 
                        $total = ($s['total'] > 0) ? $s['total'] : 1; 
                        $perc = round(($s['score'] / $total) * 100, 1);
                        $status = $perc >= 75 ? 'Passed' : 'Failed';
                        $delay = min($i * 0.1, 1.0);
                ?>
                    <style>tr.row-<?php echo $i; ?>{ animation-delay: <?php echo $delay; ?>s; }</style>
                    <tr class="row-<?php echo $i; ?>">
                        <td data-label="#">#<?php echo $i++; ?></td>
                        <td data-label="Student" class="student-name"><?php echo htmlspecialchars($s['fullname'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td data-label="Email"><?php echo htmlspecialchars($s['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td data-label="Score"><div class="score-box"><?php echo (int)$s['score']; ?>/<?php echo (int)$s['total']; ?></div></td>
                        <td data-label="Percentage" class="perc"><?php echo $perc; ?>%</td>
                        <td data-label="Status"><span class="status <?php echo strtolower($status); ?>"><?php echo $status; ?></span></td>
                        <td data-label="Date Taken" class="date"><?php echo date("M d, Y h:i A", strtotime($s['taken_at'])); ?></td>
                    </tr>
                <?php 
                    }
                } else { 
                ?>
                    <tr>
                        <td colspan="7" class="empty">
                            <div class="empty-icon">📝</div>
                            No assessment attempts yet
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>