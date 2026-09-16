<!DOCTYPE html>
<html>
<head>
    <title>Login - GadgetServ</title>
    <style>
        body { 
            font-family: Arial; 
            background: linear-gradient(135deg, #4030D0, #008080); /* Purple to Teal gradient */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-box {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            width: 320px;
        }
        .login-box h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #4030D0; /* Purple */
        }
        .login-box input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 2px solid #008080; /* Teal border */
            border-radius: 8px;
            box-sizing: border-box;
        }
        .login-box input[type="submit"] {
            background: #4030D0; /* Purple button */
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: 0.3s;
        }
        .login-box input[type="submit"]:hover {
            background: #008080; /* Teal pag hover */
        }
        .login-box a {
            color: #4030D0;
            text-decoration: none;
            font-weight: bold;
        }
        .login-box p {
            text-align: center;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>GadgetServ Login</h2>
        <form action="login_process.php" method="POST">
            <input type="email" name="email" placeholder="Enter Email" required>
            <input type="password" name="password" placeholder="Enter Password" required>
            <input type="submit" name="login_btn" value="Login">
        </form>
        <p>Wala pang account? <a href="register.html">Register dito</a></p>
    </div>
</body>
</html>