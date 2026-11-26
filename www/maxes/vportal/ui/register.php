<?php require_once 'includes/helpers.php'; ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Vendor Portal - Registration</title>
        <!-- IBM Plex Sans Font from Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
        <!-- Menghubungkan ke file CSS terpusat -->
        <link rel="stylesheet" href="../css/style.css<?= getCacheBustingVersion('/maxtest/vportal/ui/js/modules/auth.js') ?>">
    </head>

    <body>
        <div class="auth-container">
            <h1>New Vendor Registration</h1>
            <p class="h1-subtext">Create an account to access the portal</p>
            <form id="register-form">
                <!-- Container untuk layout 2 kolom -->
                <div class="form-row">
                    <!-- Kolom Kiri: Company Details -->
                    <div class="form-column">
                        <h2 class="form-section-header">Company Details</h2>
                        <div class="form-group">
                            <label for="company_name">Company Name</label>
                            <input type="text" id="company_name" name="company_name" required>
                        </div>
                        <div class="form-group">
                            <label for="vendor_code">Unique Vendor Code</label>
                            <input type="text" id="vendor_code" name="vendor_code" required>
                        </div>
                        <div class="form-group">
                            <label for="contact_name">Contact Person</label>
                            <input type="text" id="contact_name" name="contact_name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                    </div>
                    <!-- Kolom Kanan: Login Credentials -->
                    <div class="form-column">
                        <h2 class="form-section-header">Login Credentials</h2>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirmpassword">Confirm Password</label>
                            <input type="password" id="confirmpassword" name="confirmpassword" required>
                        </div>
                    </div>
                </div>
                <!-- End .form-row -->
                <!-- Tombol Submit (Full Width di bawah) -->
                <div class="form-actions">
                    <button type="submit">Register</button>
                </div>
            </form>
            <div id="message-container"></div>
            <p class="sub-link">Already have an account? <a href="login.php">Login here</a>
            </p>
        </div>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="js/modules/auth.js<?= getCacheBustingVersion('/maxtest/vportal/ui/js/modules/auth.js') ?>">
        </script>
    </body>

    </html>