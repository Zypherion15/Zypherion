<?php 
session_start(); 
include 'config.php'; 

if(isset($_POST['register_btn'])){ 
    $fullname = $_POST['fullname']; 
    $email = $_POST['email']; 
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    $role = $_POST['role']; 

    $sql = "INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, ?)"; 
    $stmt = $conn->prepare($sql); 
    
    if($stmt === false){
        die("Prepare Failed: " . $conn->error); // Para makita natin kung ano error
    }

    $stmt->bind_param("ssss", $fullname, $email, $password, $role); 

    if($stmt->execute()){ 
        echo "<script>alert('Register Success!'); window.location='index.html';</script>"; 
    } else { 
        echo "Execute Error: " . $stmt->error; 
    } 
} 
?>