<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Maxes Software">
    <title>Maxes ORION</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
	<?php
    include ("applications/ui/ui.php");
	?>

	<style>
	 :root {
            --maxes-login-primary: #1b263b;
            --maxes-login-primary-dark: #0d1b2a;
            --maxes-login-primary-light: #415a77;
            --maxes-login-secondary: #2e4057;
            --maxes-login-accent: #c9b037;
            --maxes-login-success: #d4af37;
            --maxes-login-background: #0a1628;
            --maxes-login-surface: #1a2332;
            --maxes-login-surface-light: #232f42;
            --maxes-login-text-primary: #e8e9ed;
            --maxes-login-text-secondary: #b8bcc8;
            --maxes-login-text-muted: #8891a3;
            --maxes-login-border: #2e3a4f;
            --maxes-login-border-light: rgba(201, 176, 55, 0.2);
            --maxes-login-glass-bg: rgba(26, 35, 50, 0.85);
            --maxes-login-shadow: 0 25px 50px -12px rgba(13, 27, 42, 0.6);
            --maxes-login-shadow-lg: 0 35px 60px -12px rgba(13, 27, 42, 0.8);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        #maxes-login-body {
            font-family: "Blinker", sans-serif;
            background: linear-gradient(135deg, #0a1628 0%, #1b263b 50%, #0d1b2a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Three.js Canvas Background */
        #threejs-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
        }

        #maxes-login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 1100px;
            padding: 2rem;
        }

        #maxes-login-card {
            background: transparent; /* TRANSPARAN */
            backdrop-filter: none; 
            border: none; 
            border-radius: 24px;
            overflow: hidden;
            box-shadow: none; /* Hapus shadow */
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 600px;
            position: relative;
        }

        #maxes-login-visual {
            background: transparent; /* TRANSPARAN */
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem;
            overflow: hidden;
        }
       
        #maxes-login-visual::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(201,176,55,0.15)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.6;
        }
      
        #maxes-login-visual-content {
            position: relative;
            z-index: 2;
            text-align: center;
            animation: maxes-login-slideUp 0.8s ease-out;
            
        }

        #maxes-login-logo-icon {
            width: 80px;
            height: 80px;
            background: rgba(201, 176, 55, 0.15);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(201, 176, 55, 0.3);
            box-shadow: 0 8px 32px rgba(201, 176, 55, 0.2);
        }

        #maxes-login-logo-icon i {
            font-size: 2rem;
            color: #d4af37;
            text-shadow: 0 0 20px rgba(212, 175, 55, 0.5);
        }

        #maxes-login-brand-title {
            font-family: "Blinker", sans-serif;
            font-size: 3.5rem;
            font-weight: 700;
            color: white;
            letter-spacing: 4px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5), 0 0 30px rgba(201, 176, 55, 0.3);
        }
        .maxes-login-software-type{
            font-size: 9pt;
            color: rgba(255, 255, 255, 0.85);
            letter-spacing:4px;
            position:relative;
            top:-15px;
        }
        .maxes-login-brand-tagline {
            font-size: 10pt;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 400;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
      

        #maxes-login-feature-highlights {
            display: flex;
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .maxes-login-feature-item {
            background: rgba(201, 176, 55, 0.08);
            padding: 1rem;
            border-radius: 12px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(201, 176, 55, 0.2);
            flex: 1;
            text-align: center;
            transition: all 0.3s ease;
        }

        .maxes-login-feature-item:hover {
            background: rgba(201, 176, 55, 0.15);
            border-color: rgba(201, 176, 55, 0.4);
            transform: translateY(-2px);
        }

        .maxes-login-feature-item i {
            font-size: 1.5rem;
            color: #d4af37;
            margin-bottom: 0.5rem;
            text-shadow: 0 0 10px rgba(212, 175, 55, 0.4);
        }

        .maxes-login-feature-item span {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
        }

        #maxes-login-form-section {
           padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            /* Pindahkan properti glassmorphism dari #maxes-login-card ke sini */
            background: var(--maxes-login-glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--maxes-login-border-light);
            box-shadow: var(--maxes-login-shadow-lg);
            border-radius: 0 24px 24px 0; /* Hanya sisi kanan yang melengkung */
            position: relative;

        }

        #maxes-login-form-header {
            text-align: center;
            margin-bottom: 2.5rem;
            animation: maxes-login-slideUp 0.8s ease-out 0.2s both;
            
        }

        #maxes-login-form-title {
            font-family: "Blinker", sans-serif;
            font-size: 2rem;
            font-weight: 600;
            color: var(--maxes-login-text-primary);
            margin-bottom: 0.5rem;
        }

        #maxes-login-form-subtitle {
            color: var(--maxes-login-text-secondary);
            font-size: 1rem;
        }

        #maxes-login-form {
            animation: maxes-login-slideUp 0.8s ease-out 0.4s both;
            
        }

        .maxes-login-form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .maxes-login-form-label {
            display: block;
            color: var(--maxes-login-text-secondary);
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .maxes-login-form-input {
           width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            background: rgba(35, 47, 66, 0.2); 
            backdrop-filter: blur(10px); /* Tambahkan efek blur */
            border: 1px solid var(--maxes-login-border);
            border-radius: 12px;
            font-size: 1rem;
            color: var(--maxes-login-text-primary);
            transition: all 0.3s ease;
            position: relative;
        }

        .maxes-login-form-input:focus {
             outline: none;
            border-color: var(--maxes-login-accent);
            box-shadow: 0 0 0 3px rgba(201, 176, 55, 0.15);
            /* Ganti ke RGBA (alpha lebih tinggi, misal 0.7) */
            background: rgba(35, 47, 66, 0.7); 
            backdrop-filter: blur(10px); /* Pastikan efek blur tetap ada */
        }

        .maxes-login-form-input::placeholder {
            color: var(--maxes-login-text-muted);
        }

        .maxes-login-input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--maxes-login-text-muted);
            font-size: 1rem;
            z-index: 2;
        }

        #maxes-login-password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--maxes-login-text-muted);
            cursor: pointer;
            font-size: 1rem;
            transition: color 0.3s ease;
        }

        #maxes-login-password-toggle:hover {
            color: var(--maxes-login-accent);
        }

        #maxes-login-button {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--maxes-login-primary-dark) 0%, var(--maxes-login-primary) 50%, var(--maxes-login-primary-light) 100%);
            border: 1px solid rgba(201, 176, 55, 0.3);
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(201, 176, 55, 0.2);
        }

        #maxes-login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(201, 176, 55, 0.4);
            border-color: rgba(201, 176, 55, 0.6);
        }

        #maxes-login-button:active {
            transform: translateY(0);
        }

        #maxes-login-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(201, 176, 55, 0.3), transparent);
            transition: left 0.5s;
        }

        #maxes-login-button:hover::before {
            left: 100%;
        }

        #maxes-login-divider {
            text-align: center;
            margin: 2rem 0;
            position: relative;
        }

        #maxes-login-divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--maxes-login-border);
        }

        #maxes-login-divider span {
            background: var(--maxes-login-surface);
            color: var(--maxes-login-accent);
            padding: 0 1rem;
            font-size: 0.9rem;
            font-weight: 500;
            position: relative;
        }

        #maxes-login-footer-text {
            text-align: center;
            color: var(--maxes-login-text-muted);
            font-size: 0.9rem;
            margin-top: 2rem;
            animation: maxes-login-slideUp 0.8s ease-out 0.6s both;
        }

        @keyframes maxes-login-slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            #maxes-login-card {
                grid-template-columns: 1fr;
                max-width: 400px;
            }

            #maxes-login-visual {
                padding: 2rem;
                min-height: 200px;
            }

            #maxes-login-brand-title {
                font-size: 2rem;
            }

            #maxes-login-feature-highlights {
                flex-direction: column;
                gap: 1rem;
            }

            #maxes-login-form-section {
                padding: 2rem;
            }

            #maxes-login-form-title {
                font-size: 1.5rem;
            }
        }

        .letter-dropcap {
            font-size: 12pt;
            line-height: 1;
            padding:1px;
            font-weight:bold;
            color:var(--gold-primary);
        }
	</style>
</head>
<body id="maxes-login-body">
    <canvas id="threejs-background"></canvas>
    
    <div id="maxes-login-container">
        <div id="maxes-login-card">
            <div id="maxes-login-visual">
                <div id="maxes-login-visual-content">
                    <div id="maxes-login-logo-icon">
                        <i class="fas fa-cubes"></i>
                    </div>
                    <h1 id="maxes-login-brand-title">Maxes</h1>
                    <p class="maxes-login-software-type">
                        <span style="letter-spacing:4px"> Asset Management </span>
                    </p>
                    <br><br>
                    <p class="maxes-login-brand-tagline">
                        <span class="letter-dropcap">O</span>perational 
                        <span class="letter-dropcap">R</span>eadiness 
                        <span class="letter-dropcap">I</span>ntelligence & 
                        <span class="letter-dropcap">O</span>rchestration 
                        <span class="letter-dropcap">N</span>etwork <br> 
                    </p>

                    
                    
                    <div id="maxes-login-feature-highlights">
                        <div class="maxes-login-feature-item">
                            <i class="fas fa-chart-line"></i>
                            <div><span>Analytics</span></div>
                        </div>
                        <div class="maxes-login-feature-item">
                            <i class="fas fa-warehouse"></i>
                            <div><span>Warehouse</span></div>
                        </div>
                        <div class="maxes-login-feature-item">
                            <i class="fas fa-sync-alt"></i>
                            <div><span>Real-time</span></div>
                        </div>
                    </div>
                    <br><br>
                    <p class="maxes-login-brand-tagline">Act with Insight. Amplify Performance.</p>
                    
                </div>
            </div>

            <div id="maxes-login-form-section">
                <div id="maxes-login-form-header">
                    <h2 id="maxes-login-form-title">Welcome Back</h2>
                    <p id="maxes-login-form-subtitle">Enter your credentials to access your dashboard</p>
                </div>

                <form id="maxes-login-form" novalidate>
                    <div class="maxes-login-form-group">
                        <label class="maxes-login-form-label" for="maxes-login-username">Username</label>
                        <div style="position: relative;">
                            <i class="fas fa-user maxes-login-input-icon"></i>
                            <input 
                                type="text" 
                                id="maxes-login-username"
                                class="maxes-login-form-input" 
                                data-attribute="username" 
                                placeholder="Enter your username"
                                required
                            >
                        </div>
                    </div>

                    <div class="maxes-login-form-group">
                        <label class="maxes-login-form-label" for="maxes-login-password">Password</label>
                        <div style="position: relative;">
                            <i class="fas fa-lock maxes-login-input-icon"></i>
                            <input 
                                type="password" 
                                id="maxes-login-password"
                                class="maxes-login-form-input" 
                                data-attribute="password" 
                                placeholder="Enter your password"
                                required
                            >
                            <button type="button" id="maxes-login-password-toggle" onclick="maxesTogglePassword()">
                                <i class="fas fa-eye" id="maxes-login-password-toggle-icon"></i>
                            </button>
                        </div>
                    </div>
					<!--
                    <div id="maxes-login-remember-forgot">
                        <label id="maxes-login-remember-me">
                            <input type="checkbox" id="maxes-login-checkbox">
                            <span>Remember me</span>
                        </label>
                        <a href="#" id="maxes-login-forgot-password">Forgot password?</a>
                    </div>
					//-->
                    <button type="button" id="maxes-login-button" >
                        <span id="maxes-login-text">Sign In</span>
                    </button>
                </form>

                <div id="maxes-login-divider">
                    <span>Secure Access</span>
                </div>

                <div id="maxes-login-footer-text">
                    <p>&copy; 2025 Maxes Software. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="js/three.js"></script>
	<script src="js/animhome.min.js"></script>
	
</body>
</html>