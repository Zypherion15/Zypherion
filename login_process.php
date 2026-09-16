<?php
session_start();
include 'config.php';

if(isset($_POST['login_btn'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT id, fullname, password, role FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 1){
        $user = $result->fetch_assoc();
        
        // ITO DAPAT MERON: para ma-check yung hashed password
        if(password_verify($password, $user['password'])){ 
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role'] = $user['role'];
            header("Location: dashboard.php");
        } else {
            echo "Maling Password";
        }
    } else {
        echo "Email not found";
    }
}
?>