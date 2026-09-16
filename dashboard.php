<?php 
session_start(); 
include 'config.php'; 

if (!isset($_SESSION['uid'])) { 
    header("Location: index.php"); 
    exit(); 
} 

$uid = (int)$_SESSION['uid']; 

// Security Check - Fetch User Profile
$stmt = $conn->prepare("SELECT fullname, profile_pic, role FROM users WHERE id= ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header("Location: index.php");
    exit();
}

$role = strtolower($user['role']); 

// Fetch Metrics Data
$tut_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM tutorials"); 
$tut_data = mysqli_fetch_assoc($tut_query); 
$total_tutorials = $tut_data['total'] ?? 0; 

// Fetch Latest Quiz Result via Prepared Statement
$stmt_score = $conn->prepare("SELECT score, total FROM quiz_results WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$stmt_score->bind_param("i", $uid);
$stmt_score->execute();
$last_score = $stmt_score->get_result()->fetch_assoc();
$stmt_score->close();

if ($last_score && $last_score['total'] > 0) { 
    $score = $last_score['score']; 
    $total = $last_score['total']; 
    $percentage = round(($score / $total) * 100, 1); 
    $score_display = $score . " / " . $total; 
    $score_sub = $percentage . "% Proficiency";
} else { 
    $score_display = "N/A"; 
    $score_sub = "No assessment completed";
} 

// Fetch Recent Activities via Prepared Statement
$stmt_act = $conn->prepare("SELECT type, title, details, link, created_at FROM activities WHERE user_id = ? ORDER BY activity_id DESC LIMIT 5");
$stmt_act->bind_param("i", $uid);
$stmt_act->execute();
$act_result = $stmt_act->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard - NC3 MP&HG Simulation</title>
    <link rel="icon" type="image/jpeg" href="assets/img/logo.jpg">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-accent: #f093fb;
            --secondary-accent: #f5576c;
            --success-color: #00ff88;
            --text-main: #ffffff;
            --text-muted: #cbd5e1;
            --bg-glass: rgba(15, 23, 42, 0.65);
            --border-glass: rgba(255, 255, 255, 0.12);
            --ease-out-cubic: cubic-bezier(0.33, 1, 0.68, 1);
            --ease-spring: cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; scroll-behavior: smooth; }
        
        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-main);
            background: #0f172a;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Animated Dynamic Background */
        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.92) 0%, rgba(30, 27, 75, 0.85) 100%), 
                        url('https://images.unsplash.com/photo-1558494949-ef010cbdcc31?q=80&w=2034') center/cover fixed;
            z-index: -2;
        }

        /* Ambient Pulsing Glow */
        body::after {
            content: '';
            position: fixed;
            top: -20%; left: -10%;
            width: 60vw; height: 60vw;
            background: radial-gradient(circle, rgba(240, 147, 251, 0.12) 0%, rgba(0,0,0,0) 70%);
            z-index: -1;
            pointer-events: none;
            animation: floatGlow 12s infinite alternate ease-in-out;
        }

        @keyframes floatGlow {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(20vw, 15vh) scale(1.2); }
        }

        /* TOP NAVBAR */
        .navbar {
            background: rgba(10, 15, 30, 0.7);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            padding: 16px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-glass);
            position: sticky;
            top: 0;
            z-index: 1000;
            animation: slideDown 0.6s var(--ease-out-cubic);
        }

        .navbar .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 20px;
            font-weight: 900;
            color: var(--text-main);
            transition: transform 0.3s var(--ease-spring);
        }

        .navbar .logo:hover { transform: scale(1.03); }
        .navbar .logo span { color: var(--primary-accent); }
        .navbar .logo img { 
            height: 38px; width: 38px; 
            border-radius: 8px; 
            object-fit: cover; 
            box-shadow: 0 4px 12px rgba(240, 147, 251, 0.2);
            transition: transform 0.4s ease;
        }
        .navbar .logo:hover img { transform: rotate(5deg); }

        .nav-menu { display: flex; gap: 28px; align-items: center; }
        .nav-menu a {
            color: var(--text-main);
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            position: relative;
            padding: 4px 0;
            transition: color 0.3s ease;
        }

        /* Animated Nav Underline */
        .nav-menu a::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0;
            width: 0%; height: 2px;
            background: linear-gradient(90deg, var(--primary-accent), var(--secondary-accent));
            transition: width 0.3s var(--ease-out-cubic);
            border-radius: 2px;
        }
        .nav-menu a:hover { color: var(--primary-accent); }
        .nav-menu a:hover::after { width: 100%; }

        /* USER PROFILE & DROPDOWN */
        .profile-dropdown { position: relative; }
        .profile-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 6px 16px;
            border-radius: 50px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid var(--border-glass);
            transition: all 0.3s var(--ease-out-cubic);
        }
        .profile-btn:hover { 
            background: rgba(255, 255, 255, 0.16); 
            border-color: rgba(240, 147, 251, 0.4);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.25);
        }
        .profile-btn img { width: 34px; height: 34px; border-radius: 50%; object-fit: cover; }
        .profile-btn span { font-size: 14px; font-weight: 600; }

        .dropdown-content {
            opacity: 0;
            visibility: hidden;
            transform: translateY(12px) scale(0.95);
            position: absolute;
            right: 0;
            top: 52px;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(20px);
            min-width: 230px;
            border-radius: 14px;
            padding: 10px;
            border: 1px solid var(--border-glass);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
            z-index: 1001;
            transition: all 0.3s var(--ease-out-cubic);
            transform-origin: top right;
        }

        .dropdown-content.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
        }

        .dropdown-content a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            color: var(--text-main);
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .dropdown-content a:hover { 
            background: rgba(240, 147, 251, 0.15); 
            color: var(--primary-accent);
            transform: translateX(4px);
        }
        .dropdown-divider { height: 1px; background: var(--border-glass); margin: 6px 0; }

        /* HERO SECTION */
        .hero { 
            text-align: center; 
            padding: 60px 20px 35px; 
            animation: fadeIn 0.8s var(--ease-out-cubic);
        }
        .hero h1 { font-size: 42px; font-weight: 900; letter-spacing: -0.5px; }
        .hero h1 span { 
            background: linear-gradient(135deg, var(--primary-accent), var(--secondary-accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero p { color: var(--text-muted); font-size: 16px; margin-top: 8px; }

        /* EXECUTIVE METRICS BAR */
        .metrics-container {
            max-width: 1200px;
            margin: 0 auto 50px;
            padding: 0 20px;
            animation: fadeUp 0.8s 0.15s var(--ease-out-cubic) backwards;
        }

        .metrics-bar {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            background: var(--bg-glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-glass);
            border-radius: 20px;
            padding: 24px 0;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }

        .metric-item {
            padding: 10px 35px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            border-right: 1px solid var(--border-glass);
            transition: transform 0.3s ease;
        }

        .metric-item:hover { transform: translateY(-2px); }
        .metric-item:last-child { border-right: none; }

        .metric-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        .metric-value-group { display: flex; align-items: baseline; gap: 12px; }
        .metric-value { font-size: 32px; font-weight: 800; color: var(--text-main); line-height: 1; }
        .metric-sub { font-size: 13px; color: var(--primary-accent); font-weight: 500; }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
            font-weight: 600;
            color: var(--success-color);
        }

        .status-dot {
            width: 10px;
            height: 10px;
            background-color: var(--success-color);
            border-radius: 50%;
            box-shadow: 0 0 12px var(--success-color);
            animation: pulse 2s infinite ease-in-out;
        }

        /* MAIN CONTENT AREA */
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 60px;
        }

        .section-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* MODULE CARDS GRID */
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 28px;
            margin-bottom: 50px;
            animation: fadeUp 0.8s 0.3s var(--ease-out-cubic) backwards;
        }

        .module-card {
            background: var(--bg-glass);
            backdrop-filter: blur(15px);
            border: 1px solid var(--border-glass);
            border-radius: 20px;
            overflow: hidden;
            text-decoration: none;
            color: var(--text-main);
            position: relative;
            transition: all 0.4s var(--ease-out-cubic);
        }

        .module-card:hover {
            transform: translateY(-8px);
            border-color: rgba(240, 147, 251, 0.5);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4), 0 0 20px rgba(240, 147, 251, 0.15);
        }

        .module-card-img-wrapper {
            width: 100%;
            height: 170px;
            overflow: hidden;
            position: relative;
            border-bottom: 1px solid var(--border-glass);
        }

        .module-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s var(--ease-out-cubic);
        }

        .module-card:hover img { transform: scale(1.08); }

        .module-content { padding: 22px; }
        .module-content h3 { 
            font-size: 19px; 
            color: var(--primary-accent); 
            margin-bottom: 8px; 
            transition: color 0.3s ease;
        }
        .module-card:hover .module-content h3 { color: #ffffff; }
        .module-content p { color: var(--text-muted); font-size: 14px; line-height: 1.6; }

        /* RECENT ACTIVITY CONTAINER */
        .activity-panel {
            background: var(--bg-glass);
            backdrop-filter: blur(15px);
            border: 1px solid var(--border-glass);
            border-radius: 20px;
            padding: 12px 24px;
            animation: fadeUp 0.8s 0.45s var(--ease-out-cubic) backwards;
        }

        .activity-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
            padding: 16px 12px;
            border-bottom: 1px solid var(--border-glass);
            text-decoration: none;
            color: var(--text-main);
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .activity-item:last-child { border-bottom: none; }

        .activity-item:hover {
            background: rgba(255, 255, 255, 0.05);
            transform: translateX(6px);
        }

        .activity-title { font-weight: 600; font-size: 15px; color: var(--text-main); }
        .activity-details { color: var(--text-muted); font-size: 13px; }
        .activity-time { color: rgba(255, 255, 255, 0.4); font-size: 12px; margin-top: 2px; }

        /* HAMBURGER MENU */
        .hamburger { 
            display: none; 
            cursor: pointer; 
            flex-direction: column; 
            gap: 5px; 
            padding: 6px;
            border-radius: 6px;
            transition: background 0.2s ease;
        }
        .hamburger span { 
            width: 22px; 
            height: 2px; 
            background: var(--text-main); 
            border-radius: 2px;
            transition: all 0.3s var(--ease-out-cubic);
        }

        /* KEYFRAME ANIMATIONS */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideDown {
            from { transform: translateY(-100%); }
            to { transform: translateY(0); }
        }

        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(0, 255, 136, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(0, 255, 136, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(0, 255, 136, 0); }
        }

        /* RESPONSIVE DESIGN */
        @media (max-width: 868px) {
            .metrics-bar { grid-template-columns: 1fr; row-gap: 20px; }
            .metric-item { border-right: none; border-bottom: 1px solid var(--border-glass); padding-bottom: 20px; }
            .metric-item:last-child { border-bottom: none; padding-bottom: 0; }
            
            .hamburger { display: flex; }
            .nav-menu {
                opacity: 0;
                visibility: hidden;
                position: absolute;
                top: 100%; right: 0; width: 100%;
                background: rgba(15, 23, 42, 0.98);
                backdrop-filter: blur(25px);
                flex-direction: column;
                padding: 24px;
                border-bottom: 1px solid var(--border-glass);
                transform: translateY(-10px);
                transition: all 0.3s var(--ease-out-cubic);
            }
            .nav-menu.active { 
                opacity: 1;
                visibility: visible;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="logo">
        <img src="assets/img/logo.jpg" alt="NC3 Logo">
        NC3 <span>MP&HG</span>
    </div>
    
    <div class="nav-menu" id="navMenu">
        <a href="#home">Home</a>
        <a href="#modules">Modules</a>
        <a href="assessment.php">Assessment</a>
        <a href="simulation.php">Virtual Simulation</a>
    </div>

    <div style="display:flex;align-items:center;gap:15px">
        <div class="profile-dropdown">
            <div class="profile-btn" onclick="toggleProfileMenu(event)">
                <img src="<?php echo $user['profile_pic'] ? htmlspecialchars($user['profile_pic']) : 'https://ui-avatars.com/api/?name='.urlencode($user['fullname']);?>" alt="Profile">
                <span><?php echo htmlspecialchars($user['fullname']);?></span>
            </div>
            <div id="profileMenu" class="dropdown-content">
                <?php if($role === 'instructor'): ?>
                    <span style="color:var(--primary-accent); font-size: 11px; font-weight:700; padding: 6px 14px; text-transform: uppercase;">Instructor Panel</span>
                    <a href="add_tutorial.php">➕ Add Tutorial</a>
                    <a href="student_list.php">👥 Student List</a>
                    <a href="view_scores.php">📊 View Scores</a>
                    <div class="dropdown-divider"></div>
                <?php endif; ?>
                <a href="profile.php">⚙️ Account Settings</a>
                <a href="logout.php">🚪 Logout</a>
            </div>
        </div>

        <div class="hamburger" onclick="toggleMenu()">
            <span></span><span></span><span></span>
        </div>
    </div>
</nav>

<section class="hero" id="home">
    <h1>Welcome Back, <span><?php echo htmlspecialchars($user['fullname']);?></span></h1>
    <p>Role: <b><?php echo ucfirst($user['role']);?></b> &bull; Mobile Phone & Handheld Gadget Simulation System</p>
</section>

<!-- EXECUTIVE METRICS BAR -->
<div class="metrics-container">
    <div class="metrics-bar">
        <div class="metric-item">
            <span class="metric-label">Course Modules</span>
            <div class="metric-value-group">
                <span class="metric-value"><?php echo (int)$total_tutorials; ?></span>
                <span class="metric-sub">Modules Available</span>
            </div>
        </div>

        <div class="metric-item">
            <span class="metric-label">Performance Assessment</span>
            <div class="metric-value-group">
                <span class="metric-value"><?php echo htmlspecialchars($score_display); ?></span>
                <span class="metric-sub"><?php echo htmlspecialchars($score_sub); ?></span>
            </div>
        </div>

        <div class="metric-item">
            <span class="metric-label">Server Connection</span>
            <div class="metric-value-group">
                <div class="status-badge">
                    <span class="status-dot"></span> Online
                </div>
            </div>
        </div>
    </div>
</div>

<div class="main-container">
    <!-- MODULES SECTION -->
    <section id="modules" style="margin-bottom: 50px;">
        <h2 class="section-title">Training Modules</h2>
        <div class="modules-grid">
            <a href="tutorials.php" class="module-card">
                <div class="module-card-img-wrapper">
                    <img src="https://images.unsplash.com/photo-1519389950473-47ba0277781c?q=80&w=2070" alt="Tutorials">
                </div>
                <div class="module-content">
                    <h3>📱 Tutorials</h3>
                    <p>Learn step-by-step Mobile Phone & HandheldGadget procedures.</p>
                </div>
            </a>
            <a href="assessment.php" class="module-card">
                <div class="module-card-img-wrapper">
                    <img src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=2070" alt="Assessment">
                </div>
                <div class="module-content">
                    <h3>📝 Assessment</h3>
                    <p>Take the Mock Exam of MP&HG examination and evaluate skill readiness.</p>
                </div>
            </a>
            <a href="simulation.php" class="module-card">
                <div class="module-card-img-wrapper">
                    <img src="https://images.unsplash.com/photo-1581092580497-e0d23cbdf1dc?q=80&w=2070" alt="Simulation">
                </div>
                <div class="module-content">
                    <h3>🔧 Virtual Simulation</h3>
                    <p>Practice mobile hardware troubleshooting and component replacement interactively.</p>
                </div>
            </a>
        </div>
    </section>

    <!-- RECENT ACTIVITY SECTION -->
    <section>
        <h2 class="section-title">Recent Activity</h2>
        <div class="activity-panel">
            <?php if ($act_result && $act_result->num_rows > 0): ?>
                <?php while($act = $act_result->fetch_assoc()): ?>
                    <a href="<?php echo !empty($act['link']) ? htmlspecialchars($act['link']) : '#'; ?>" class="activity-item">
                        <div class="activity-title">[<?php echo strtoupper(htmlspecialchars($act['type'])); ?>] <?php echo htmlspecialchars($act['title']); ?></div>
                        <div class="activity-details"><?php echo htmlspecialchars($act['details']); ?></div>
                        <div class="activity-time"><?php echo date("M d, Y • h:i A", strtotime($act['created_at'])); ?></div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align:center; color: var(--text-muted); padding: 20px 0;">No recent system activity logged.</p>
            <?php endif; ?>
            <?php $stmt_act->close(); ?>
        </div>
    </section>
</div>

<footer style="text-align:center; padding: 30px; color: var(--text-muted); border-top: 1px solid var(--border-glass);">
    <p>© 2026 NC3 MP&HG Simulation. All rights reserved.</p>
</footer>

<script>
function toggleProfileMenu(e) { 
    e.stopPropagation(); 
    document.getElementById("profileMenu").classList.toggle("show"); 
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.profile-dropdown')) {
        document.getElementById("profileMenu").classList.remove("show");
    }
});

function toggleMenu() {
    document.getElementById("navMenu").classList.toggle("active");
}
</script>
</body>
</html>