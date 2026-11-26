<?php require_once 'includes/helpers.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Portal - Login</title>
    <!-- IBM Plex Sans Font from Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href='../css/style.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/style.css<?= getCacheBustingVersion('/maxtest/vportal/ui/css/style.css') ?>">
</head>
<body>

    <div class="auth-container">
        <h1>Vendor Login</h1>
        <p class="h1-subtext">Access your dedicated portal</p>
        
        <form id="login-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>

        <div id="message-container"></div>
        
        <p class="sub-link">Don't have an account? <a href="register.php">Register here</a></p>
        <p class="sub-link">Forgot Your Login Details? <a id="forgotpassword" style='cursor:pointer;'>Click here</a></p>
       
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/modules/auth.js<?= getCacheBustingVersion('/maxtest/vportal/ui/js/modules/auth.js') ?>"></script>
    <script src="js/modules/login.js<?= getCacheBustingVersion('/maxtest/vportal/ui/js/modules/login.js') ?>"></script>
</body>
</html>