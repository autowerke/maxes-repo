<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Maxes Software">
    <title>Maxes EAM</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
	<?php
    include ("applications/ui/ui.php");
	?>
	<style>
        
	  :root {
        /* --- Corporate White Theme Variables --- */
        
        /* Warna Dasar */
        --maxes-login-primary: #6060d7;      /* Corporate Blue Utama */
        --maxes-login-primary-dark: #003366; 
        --maxes-login-primary-light: #2684FF; 
        
        /* Backgrounds (Light / Clean) */
        --maxes-login-background: #F4F7FA;   /* Off-White / Cool Gray Background */
        --maxes-login-secondary: #FFFFFF;    
        
        /* Surfaces (Card) */
        --maxes-login-surface: #FFFFFF;      /* Kartu menjadi Putih */
        --maxes-login-surface-light: #F0F2F5; 
        
        /* Text (Dark for contrast) */
        --maxes-login-text-primary: #172B4D; /* Dark Navy Text (Hampir Hitam) */
        --maxes-login-text-secondary: #5E6C84; /* Abu-abu Medium */
        --maxes-login-text-muted: #97A0AF;     /* Abu-abu Muda */
        
        /* Borders & Glass */
        --maxes-login-border: #DFE1E6;       /* Border Abu-abu Halus */
        --maxes-login-border-light: rgba(0, 82, 204, 0.15); /* Border fokus biru tipis */
        
        /* Glass Effect untuk Light Mode (Putih Transparan) */
        --maxes-login-glass-bg: rgba(255, 255, 255, 0.85);      
        
        /* Status & Accents */
        --maxes-login-accent: #0052CC;       /* Icon jadi biru tua agar terlihat */
        
        /* Shadows (Soft Gray Shadows) */
        --maxes-login-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        --maxes-login-shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    #maxes-login-body {
        font-family: "Blinker", sans-serif;
        /* Gradasi Putih ke Abu-abu Kebiruan (Sangat Halus) */
        background: linear-gradient(135deg, #FFFFFF 0%, #F0F4F8 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        color: var(--maxes-login-text-primary); /* Pastikan text default gelap */
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
        background: transparent;
        backdrop-filter: none; 
        border: none; 
        border-radius: 24px;
        overflow: hidden;
        box-shadow: none;
        display: grid;
        grid-template-columns: 1fr 1fr;
        min-height: 600px;
        position: relative;
    }

    #maxes-login-visual {
        background: transparent;
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
        /* PENTING: Grid diubah menjadi abu-abu tipis agar terlihat di background putih */
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(23, 43, 77, 0.05)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
        opacity: 1;
    }
  
    #maxes-login-visual-content {
        position: relative;
        z-index: 2;
        text-align: center;
        animation: maxes-login-slideUp 0.8s ease-out;
    }
.maxes-brand-header {
    display: inline-flex;
    flex-direction: column;
    align-items: stretch;
    margin-top:30px;
}
.maxes-brand-main {
    display: flex;
    align-items: center;
    gap: 16px;
}


#maxes-login-logo-icon img {
    width: 50px;
    height: 50px;
    object-fit: contain;
}

.maxes-brand-text {
    text-align: left;
}

#maxes-login-brand-title {
    margin: 0;
    font-size: 12pt;
    font-weight: 600;
    line-height: 1.1;
    font-family: "Blinker", sans-serif;
}

.maxes-login-software-type {
    margin: 4px 0 0;
    font-size: 12px;
    color: #6b7280;
}
    #maxes-login-logo-icon {
        display: flex;
        justify-content: center;
        
    }

    #maxes-login-logo-icon i {
        font-size: 2rem;
        color: var(--maxes-login-primary); /* Icon biru */
    }
   
    
    .maxes-login-brand-tagline {
        background: black;
        text-align: center;
        font-size: 12px;
        color: #dbdbdb;
        padding: 0 5px;
        border-radius:3px;
        margin-top:10px;
    }
  
    #maxes-login-feature-highlights {
        display: flex;
        gap: 1.5rem;
        margin-top: 2rem;
    }

    .maxes-login-feature-item {
        background: #FFFFFF;
        padding: 1rem;
        border-radius: 12px;
        /* Border abu-abu tipis */
        border: 1px solid var(--maxes-login-border);
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        flex: 1;
        text-align: center;
        transition: all 0.3s ease;
    }

    .maxes-login-feature-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 82, 204, 0.1);
        border-color: var(--maxes-login-primary-light);
    }

    .maxes-login-feature-item i {
        font-size: 1.5rem;
        color: var(--maxes-login-primary);
        margin-bottom: 0.5rem;
    }

    .maxes-login-feature-item span {
        font-size: 0.9rem;
        color: var(--maxes-login-text-primary);
        font-weight: 600;
    }

    #maxes-login-form-section {
        padding: 3rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        /* Background Putih Glass */
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(20px);
        border: 1px solid #FFFFFF;
        box-shadow: var(--maxes-login-shadow-lg);
        border-radius: 0 24px 24px 0;
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
        font-weight: 700;
        color: var(--maxes-login-text-primary);
        margin-bottom: 0.5rem;
    }

    #maxes-login-form-subtitle {
        color: var(--maxes-login-text-secondary);
        font-size: 10pt;
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
        color: var(--maxes-login-text-primary);
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .maxes-login-form-input {
        width: 100%;
        padding: 1rem 1rem 1rem 3rem;
        /* Input Background Putih dengan Border */
        background: #FFFFFF; 
        border: 2px solid var(--maxes-login-border);
        border-radius: 12px;
        font-size: 1rem;
        color: var(--maxes-login-text-primary);
        transition: all 0.2s ease;
        position: relative;
    }

    .maxes-login-form-input:focus {
        outline: none;
        border-color: var(--maxes-login-primary);
        box-shadow: 0 0 0 4px rgba(38, 132, 255, 0.15);
        background: #FFFFFF; 
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
    
    /* Ubah warna icon saat input focus */
    .maxes-login-form-input:focus + .maxes-login-input-icon,
    .maxes-login-form-group:focus-within .maxes-login-input-icon {
        color: var(--maxes-login-primary);
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
        color: var(--maxes-login-primary);
    }

    #maxes-login-button {
        width: 100%;
        padding: 1rem;
        background: var(--maxes-login-primary);
        border: none;
        border-radius: 12px;
        color: white;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        /* Shadow Biru yang indah */
        box-shadow: 0 10px 20px -10px rgba(0, 82, 204, 0.5);
    }

    #maxes-login-button:hover {
        transform: translateY(-2px);
        background: var(--maxes-login-primary-dark);
        box-shadow: 0 15px 25px -10px rgba(0, 82, 204, 0.6);
    }

    #maxes-login-button:active {
        transform: translateY(0);
    }

#maxes-login-divider {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin: 20px 0;
    position:relative;
    animation: maxes-login-slideUp 0.8s ease-out 0.6s both;

}

.footer-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 14px;
    border-radius: 5px;
    background: #f6f8fb;
    color: #4b5563;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.18s ease;
}

.footer-pill:hover {
    background: #ffffff;
    color: #111827;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
    transform: translateY(-1px);
}

.footer-pill:active {
    transform: translateY(0);
}
    .divider-dot {
        color: #c7ced8;
    }

    #maxes-login-divider::before {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        height: 1px;
    }

    #maxes-login-divider span {
        background: #FFFFFF; /* Tutup garis dengan background putih */
        color: var(--maxes-login-text-muted);
        padding: 0 1rem;
        font-size: 0.9rem;
        font-weight: 500;
        position: relative;
        text-shadow: none;
    }

    #maxes-login-footer-text {
        text-align: center;
        color: var(--maxes-login-text-muted);
        font-size: 0.9rem;
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
            display: none; /* Sembunyikan visual di mobile agar form lebih fokus */
        }

        #maxes-login-form-section {
             border-radius: 24px;
        }
    }

    .letter-dropcap {
        font-size: 12pt;
        line-height: 1;
        padding:1px;
        font-weight:bold;
        color: var(--maxes-login-primary);
    }
	</style>
</head>
<body id="maxes-login-body">
    <canvas id="threejs-background"></canvas>
    
    <div id="maxes-login-container">
        <div id="maxes-login-card">
            <div id="maxes-login-visual">
                <div id="maxes-login-visual-content">
                    <div style="margin-bottom:30px;">
                        <center>
                            <table >
                                <tr>
                                    <td valign="top">
                                    <!--
                                    <img src="client_logo.png" alt="client_log" style="margin-right:15px;">
                                    -->
                                    </td>
                                    <td>
                                    <h2 id="maxes-login-form-title" style="margin:0; text-align:center;">
                                        ORION 
                                    </h2>
                                    <p id="maxes-login-form-subtitle" style="margin:0; text-align:left;">
                                        Operational Readiness Intelligence and Orchestration Networks
                                    </p>
                                    </td>
                                </tr>
                                </table>
                        </center>
                    </div>
                    
                    <div id="maxes-login-feature-highlights">
                        <div class="maxes-login-feature-item">
                            <i class="fas fa-chart-line"></i>
                            <div><span>Excellence</span></div>
                        </div>
                        <div class="maxes-login-feature-item">
                            <i class="fas fa-warehouse"></i>
                            <div><span>Sustainable</span></div>
                        </div>
                        <div class="maxes-login-feature-item">
                            <i class="fas fa-sync-alt"></i>
                            <div><span>Quality</span></div>
                        </div>
                    </div>
                     <div class="maxes-brand-header">

                        <div class="maxes-brand-main">
                            <div id="maxes-login-logo-icon">
                                <img src="https://maxes.id/img/logo_maxes.png" alt="Maxes Logo">
                            </div>

                            <div class="maxes-brand-text">
                                <h1 id="maxes-login-brand-title">MAXES AIP</h1>
                                <p class="maxes-login-software-type">
                                    <span>Asset Intelligence Platform</span>
                                </p>
                            </div>
                        </div>

                        <p class="maxes-login-brand-tagline">
                            Act with Insight. Amplify Performance
                        </p>

                    </div>
                                        
                    
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
                    <a href="https://maxes.id" class="footer-pill" target="_blank">About Maxes</a>
                    <a href="https://maxes.id/contact" class="footer-pill" target="_blank">Training & Support</a>
                </div>
                  
                <div id="maxes-login-footer-text">
                    <p style="color:green; font-weight:bold;font-size:15pt">- Production Instance - </p>
                    <p>&copy; <?= date('Y'); ?> Maxes Software. All rights reserved.</p>
                </div>
                
            </div>
        </div>
    </div>

    <script src="js/three.js"></script>
	<script src="js/animhome_light.min.js"></script>
	
</body>
</html>