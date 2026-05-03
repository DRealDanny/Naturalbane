<?php
require_once 'config.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['u'] === ADMIN_USER && $_POST['p'] === ADMIN_PASS) {
        $_SESSION['logged_in'] = true;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = 'Access Denied. Please check your credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Creativo Creates CMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lora&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Lora', serif; background-color: #F7F5F2; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .wrapper { display: flex; width: 100%; max-width: 1000px; height: 600px; background: #fff; box-shadow: 0 10px 40px rgba(0,0,0,0.1); overflow: hidden; }
        .form-side { flex: 1; padding: 60px; display: flex; flex-direction: column; justify-content: center; }
        .img-side { flex: 1; background: url('../assets/login-image.jpg') no-repeat center center; background-size: cover; }
        h1 { font-family: 'Playfair Display', serif; font-size: 32px; margin-bottom: 10px; color: #1a1a1a; }
        .sub { color: #666; margin-bottom: 30px; }
        
        .input-group { margin-bottom: 20px; position: relative; }
        label { display: block; font-size: 13px; margin-bottom: 8px; font-weight: 600; color: #333; }
        input { width: 100%; padding: 14px; border: 1px solid #ddd; border-radius: 4px; outline: none; font-size: 15px; }
        input:focus { border-color: #C8102E; }

        /* Password Wrapper and Eye Icon */
        .pass-wrapper { position: relative; }
        .toggle-btn { 
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%); 
            cursor: pointer; background: none; border: none; display: flex; align-items: center; opacity: 0.5;
        }
        .toggle-btn svg { width: 20px; height: 20px; stroke: #333; fill: none; stroke-width: 2; }
        .toggle-btn:hover { opacity: 1; }

        .btn { background: #C8102E; color: #fff; border: none; padding: 16px; border-radius: 4px; cursor: pointer; font-weight: 600; font-family: 'Playfair Display', serif; width: 100%; font-size: 16px; }
        .error { color: #C8102E; font-size: 14px; margin-bottom: 20px; }

        @media (max-width: 900px) { .img-side { display: none; } .wrapper { max-width: 450px; } }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="form-side">
        <h1>Welcome back!</h1>
        <p class="sub">Login to make changes on website</p>
        
        <?php if($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
        
        <form method="POST">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="u" required placeholder="Linus">
            </div>
            
            <div class="input-group">
                <label>Password</label>
                <div class="pass-wrapper">
                    <input type="password" name="p" id="passInput" required placeholder="Enter password">
                    <button type="button" class="toggle-btn" onclick="toggleVisibility()">
                        <svg id="eyeIcon" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </div>
            
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
    <div class="img-side"></div>
</div>

<script>
function toggleVisibility() {
    const input = document.getElementById('passInput');
    const icon = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.style.stroke = '#C8102E';
    } else {
        input.type = 'password';
        icon.style.stroke = '#333';
    }
}
</script>
</body>
</html>