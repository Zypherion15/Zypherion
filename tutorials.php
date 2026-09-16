<?php 
session_start(); 
include 'config.php'; 

if(!isset($_SESSION['uid'])){ 
    header("Location: index.php"); 
    exit(); 
}

$uid = (int)$_SESSION['uid'];

// Helper function to turn normal YouTube links into Embed URLs
function getYouTubeEmbedUrl($url) {
    if (strpos($url, 'youtube.com/embed/') !== false) {
        return $url;
    }
    preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/', $url, $matches);
    return isset($matches[1]) ? "https://www.youtube.com/embed/" . $matches[1] : $url;
}

// DELETE TUTORIAL
if(isset($_GET['delete'])){
    $del_id = (int)$_GET['delete'];
    
    // Check if the tutorial belongs to the logged-in instructor
    $check = mysqli_query($conn, "SELECT * FROM tutorials WHERE id=$del_id AND instructor_id=$uid");
    if(mysqli_num_rows($check) > 0){
        mysqli_query($conn, "DELETE FROM tutorials WHERE id=$del_id");
        header("Location: tutorials.php");
        exit();
    }
}

// FETCH USER ROLE
$user_res = mysqli_query($conn, "SELECT role FROM users WHERE ID=$uid");
$user_check = mysqli_fetch_assoc($user_res);

// FETCH TUTORIALS WITH INSTRUCTOR NAME
$tutorials = mysqli_query($conn, "SELECT t.*, u.fullname FROM tutorials t 
                                  LEFT JOIN users u ON t.instructor_id = u.ID 
                                  ORDER BY t.created_at DESC");
?>
<!DOCTYPE html> 
<html lang="en"> 
<head> 
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tutorials - NC3 MP&HG Simulation</title>
<link rel="icon" type="image/jpeg" href="assets/img/logo.jpg">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<style> 
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(rgba(10, 10, 26, 0.9), rgba(118, 75, 162, 0.9)),
                url('https://images.unsplash.com/photo-1519389950473-47ba0277781c?q=80&w=1170') center/cover fixed;
    min-height: 100vh;
    color: #ffffff;
}

/* Navigation Topbar */
.topbar {
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(15px);
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 100;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.topbar .logo {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 700;
    font-size: 18px;
}

.topbar .logo img {
    height: 36px;
    width: 36px;
    border-radius: 8px;
    object-fit: cover;
}

.topbar a.back-link {
    color: #ffffff;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: 0.3s;
    background: rgba(255, 255, 255, 0.1);
    padding: 8px 16px;
    border-radius: 10px;
}

.topbar a.back-link:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #f093fb;
}

/* Main Container */
.container {
    padding: 40px 20px;
    max-width: 1300px;
    margin: auto;
}

.page-title {
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.page-title h1 {
    font-size: 26px;
    font-weight: 700;
}

.btn-add {
    background: linear-gradient(90deg, #f093fb, #f5576c);
    color: #ffffff;
    padding: 10px 22px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 700;
    font-size: 14px;
    transition: 0.3s;
    box-shadow: 0 4px 15px rgba(240, 147, 251, 0.4);
    display: inline-block;
}

.btn-add:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(240, 147, 251, 0.6);
}

/* Tutorial Cards Grid */
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 25px;
}

.tut-card {
    background: rgba(255, 255, 255, 0.95);
    color: #333333;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    transition: 0.3s ease;
    position: relative;
    display: flex;
    flex-direction: column;
}

.tut-card:hover {
    transform: translateY(-8px);
}

.video-wrapper {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
    height: 0;
    background: #000000;
}

.video-wrapper iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: none;
}

.tut-content {
    padding: 20px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.badge {
    align-self: flex-start;
    background: linear-gradient(90deg, #667eea, #764ba2);
    color: #ffffff;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.tut-content h3 {
    margin-top: 12px;
    margin-bottom: 6px;
    font-size: 18px;
    font-weight: 700;
    color: #1a202c;
    line-height: 1.3;
}

.instructor-name {
    font-size: 12px;
    color: #718096;
    margin-bottom: 12px;
    font-weight: 500;
}

.tut-description {
    font-size: 13px;
    color: #4a5568;
    line-height: 1.5;
}

.btn-delete {
    position: absolute;
    top: 12px;
    right: 12px;
    background: rgba(255, 71, 87, 0.9);
    color: #ffffff;
    border: none;
    padding: 6px 12px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    z-index: 10;
    backdrop-filter: blur(5px);
    transition: 0.3s;
}

.btn-delete:hover {
    background: #e84118;
    transform: scale(1.05);
}

.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 50px 20px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    color: #ffffff;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .topbar {
        padding: 12px 15px;
    }
    
    .topbar .logo span {
        display: none;
    }

    .container {
        padding: 25px 15px;
    }

    .page-title h1 {
        font-size: 20px;
    }

    .grid {
        grid-template-columns: 1fr;
    }
}
</style> 
</head> 
<body> 

<div class="topbar">
    <div class="logo">
        <img src="assets/img/logo.jpg" alt="Logo">
        <span>NC3 MP&HG Tutorials</span>
    </div>
    <a href="dashboard.php" class="back-link">← Dashboard</a>
</div>

<div class="container">
    <div class="page-title">
        <h1>📱 Mobile Phone & Gadget Tutorials</h1>
        <?php if(isset($user_check['role']) && $user_check['role'] == 'instructor'): ?>
            <a href="add_tutorial.php" class="btn-add">+ Add Tutorial</a>
        <?php endif; ?>
    </div>

    <div class="grid">
        <?php if(mysqli_num_rows($tutorials) > 0): ?>
            <?php while($tut = mysqli_fetch_assoc($tutorials)): ?>
                <div class="tut-card">
                    <?php if($tut['instructor_id'] == $uid): ?>
                        <a href="tutorials.php?delete=<?php echo $tut['id']; ?>" 
                           class="btn-delete" 
                           onclick="return confirm('Are you sure you want to delete this tutorial?')">
                           🗑️ Delete
                        </a>
                    <?php endif; ?>

                    <div class="video-wrapper">
                        <iframe src="<?php echo htmlspecialchars(getYouTubeEmbedUrl($tut['video_link'])); ?>" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen>
                        </iframe>
                    </div>

                    <div class="tut-content">
                        <span class="badge"><?php echo htmlspecialchars($tut['category']); ?></span>
                        <h3><?php echo htmlspecialchars($tut['title']); ?></h3>
                        <p class="instructor-name">Instructor: <?php echo htmlspecialchars($tut['fullname'] ?? 'Unknown'); ?></p>
                        <p class="tut-description"><?php echo nl2br(htmlspecialchars($tut['description'])); ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <h3>No tutorials available yet.</h3>
                <p style="margin-top: 5px; color: #ddd;">Check back later or ask an instructor to upload lessons.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>