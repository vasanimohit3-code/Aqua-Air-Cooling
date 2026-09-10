<?php
require_once 'includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function sweetAlertAndRedirect($title, $text, $icon, $redirectUrl) {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script><script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script><style>body{font-family:sans-serif;background:#0f172a;}</style></head><body>';
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (" . json_encode($icon) . " === 'success' && typeof confetti === 'function') {
            confetti({ particleCount: 140, spread: 80, origin: { y: 0.6 } });
        }
        Swal.fire({
            title: " . json_encode($title) . ",
            text: " . json_encode($text) . ",
            icon: " . json_encode($icon) . ",
            confirmButtonColor: " . json_encode($icon === 'success' ? '#10b981' : ($icon === 'error' ? '#ef4444' : '#0284c7')) . ",
            confirmButtonText: '<i class=\"fas fa-arrow-right me-1\"></i> Continue'
        }).then(() => {
            window.location = " . json_encode($redirectUrl) . ";
        });
    });
    </script></body></html>";
    exit();
}

if (
    !isset($_SESSION['register_name']) ||
    !isset($_SESSION['register_email']) ||
    !isset($_SESSION['register_password']) ||
    !isset($_SESSION['register_otp'])
) {
    header("Location: login.php");
    exit();
}

$otp_start_time = $_SESSION['register_otp_time'] ?? time();
$otp_expires_in = max(0, 300 - (time() - $otp_start_time)); // 5 mins in seconds

$extra_script = '';

if (isset($_POST['verify'])) {
    if ((time() - $_SESSION['register_otp_time']) > 300) {
        unset($_SESSION['register_otp']);
        unset($_SESSION['register_otp_time']);
        sweetAlertAndRedirect('OTP Expired', 'Your OTP has expired. Please register again.', 'warning', 'login.php?tab=register');
    }

    $userOtp = trim($_POST['otp'] ?? '');

    if ($userOtp == $_SESSION['register_otp']) {
        $name = mysqli_real_escape_string($conn, $_SESSION['register_name']);
        $email = mysqli_real_escape_string($conn, $_SESSION['register_email']);
        $password = $_SESSION['register_password'];

        // Double check email
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");

        if (mysqli_num_rows($check) > 0) {
            sweetAlertAndRedirect('Email Already Exists', 'This email is already registered. Please login.', 'error', 'login.php');
        }

        $insert = mysqli_query($conn, "
            INSERT INTO users(name, email, password)
            VALUES('$name', '$email', '$password')
        ");

        if ($insert) {
            $user_id = mysqli_insert_id($conn);
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $name;

            unset($_SESSION['register_name']);
            unset($_SESSION['register_email']);
            unset($_SESSION['register_password']);
            unset($_SESSION['register_otp']);
            unset($_SESSION['register_otp_time']);

            sweetAlertAndRedirect('Account Created Successfully! 🎉', 'Welcome to Aqua Air Cooling! Your registration is complete and verified.', 'success', 'user_dashboard.php');
        }
    } else {
        $extra_script = "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Invalid OTP',
                text: 'The OTP code you entered is incorrect. Please try again.',
                confirmButtonColor: '#ef4444'
            });
        });
        </script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Registration OTP | Aqua Air Cooling</title>
    
    <!-- Google Fonts & Font Awesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            min-height: 100vh;
            background: #030712;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            position: relative;
            overflow-x: hidden;
        }

        /* Ambient Animated Neon Glow Background Orbs */
        .ambient-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(90px);
            pointer-events: none;
            opacity: 0.55;
            animation: orbFloat 10s infinite alternate ease-in-out;
        }

        .orb-1 {
            width: 380px;
            height: 380px;
            background: radial-gradient(circle, #0284c7, #0369a1);
            top: -50px;
            left: -80px;
        }

        .orb-2 {
            width: 320px;
            height: 320px;
            background: radial-gradient(circle, #2563eb, #1d4ed8);
            bottom: -60px;
            right: -60px;
            animation-delay: -5s;
        }

        .orb-3 {
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, #10b981, #059669);
            top: 40%;
            right: 25%;
            opacity: 0.3;
        }

        @keyframes orbFloat {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(30px, 40px) scale(1.1); }
            100% { transform: translate(-20px, 20px) scale(0.95); }
        }

        /* Dynamic Glassmorphic Card */
        .dynamic-auth-card {
            width: 100%;
            max-width: 480px;
            background: rgba(15, 23, 42, 0.78);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 28px;
            padding: 40px 32px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5), 0 0 35px rgba(2, 132, 199, 0.18);
            position: relative;
            z-index: 10;
            animation: cardSlideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes cardSlideUp {
            from { opacity: 0; transform: translateY(30px) scale(0.96); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .auth-logo-badge {
            width: 64px;
            height: 64px;
            border-radius: 20px;
            background: linear-gradient(135deg, #0ea5e9, #2563eb);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 28px;
            margin: 0 auto 20px;
            box-shadow: 0 8px 24px rgba(14, 165, 233, 0.4);
            animation: pulseGlow 2.5s infinite;
        }

        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 8px 24px rgba(14, 165, 233, 0.4); transform: scale(1); }
            50% { box-shadow: 0 12px 32px rgba(14, 165, 233, 0.7); transform: scale(1.04); }
        }

        .auth-title {
            font-family: 'Outfit', sans-serif;
            font-size: 26px;
            font-weight: 800;
            color: #ffffff;
            text-align: center;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
        }

        .auth-subtitle {
            color: #94a3b8;
            font-size: 14px;
            text-align: center;
            line-height: 1.5;
            margin-bottom: 28px;
        }

        .email-pill {
            display: inline-block;
            background: rgba(14, 165, 233, 0.15);
            border: 1px solid rgba(14, 165, 233, 0.3);
            color: #38bdf8;
            padding: 3px 12px;
            border-radius: 20px;
            font-weight: 600;
            margin-top: 4px;
        }

        /* 6-Digit OTP Grid Inputs */
        .otp-inputs-wrapper {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 24px;
        }

        .otp-digit-input {
            width: 52px;
            height: 60px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.12);
            color: #ffffff;
            font-family: 'Outfit', sans-serif;
            font-size: 24px;
            font-weight: 800;
            text-align: center;
            transition: all 0.25s ease;
            outline: none;
        }

        .otp-digit-input:focus {
            border-color: #38bdf8;
            background: rgba(14, 165, 233, 0.12);
            box-shadow: 0 0 20px rgba(56, 189, 248, 0.4);
            transform: translateY(-2px);
        }

        .otp-digit-input.filled {
            border-color: #0ea5e9;
            background: rgba(14, 165, 233, 0.08);
        }

        /* Countdown Badge Box */
        .timer-box {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            padding: 12px;
            text-align: center;
            margin-bottom: 24px;
        }

        .timer-badge {
            color: #f59e0b;
            font-weight: 700;
            font-size: 15px;
        }

        /* Submit Button */
        .btn-verify-submit {
            width: 100%;
            height: 52px;
            border-radius: 14px;
            background: linear-gradient(90deg, #0284c7 0%, #2563eb 100%);
            border: none;
            color: #ffffff;
            font-size: 16px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 8px 25px rgba(2, 132, 199, 0.4);
            transition: all 0.25s ease;
            cursor: pointer;
        }

        .btn-verify-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(2, 132, 199, 0.6);
            background: linear-gradient(90deg, #0369a1 0%, #1d4ed8 100%);
        }

        /* Footer Links */
        .auth-footer-links {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .auth-link {
            color: #94a3b8;
            font-size: 13.5px;
            text-decoration: none;
            transition: color 0.2s ease;
            font-weight: 500;
        }

        .auth-link:hover {
            color: #38bdf8;
        }

        .resend-btn {
            background: none;
            border: none;
            color: #38bdf8;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .resend-btn:disabled {
            color: #64748b;
            cursor: not-allowed;
            opacity: 0.6;
        }
    </style>
</head>
<body>

    <!-- Ambient Glowing Background Elements -->
    <div class="ambient-orb orb-1"></div>
    <div class="ambient-orb orb-2"></div>
    <div class="ambient-orb orb-3"></div>

    <!-- Main Auth Card -->
    <div class="dynamic-auth-card">
        
        <div class="auth-logo-badge">
            <i class="fas fa-shield-halved"></i>
        </div>

        <h2 class="auth-title">Verify Account</h2>
        <p class="auth-subtitle">
            We have sent a 6-digit verification code to:<br>
            <span class="email-pill"><?php echo htmlspecialchars($_SESSION['register_email']); ?></span>
        </p>

        <form method="POST" id="otpForm">
            
            <!-- Hidden combined OTP input for POST -->
            <input type="hidden" name="otp" id="combinedOtp" value="">

            <!-- 6-Digit PIN Boxes -->
            <div class="otp-inputs-wrapper">
                <input type="text" maxlength="1" class="otp-digit-input" data-index="0" autofocus inputmode="numeric" autocomplete="one-time-code">
                <input type="text" maxlength="1" class="otp-digit-input" data-index="1" inputmode="numeric">
                <input type="text" maxlength="1" class="otp-digit-input" data-index="2" inputmode="numeric">
                <input type="text" maxlength="1" class="otp-digit-input" data-index="3" inputmode="numeric">
                <input type="text" maxlength="1" class="otp-digit-input" data-index="4" inputmode="numeric">
                <input type="text" maxlength="1" class="otp-digit-input" data-index="5" inputmode="numeric">
            </div>

            <!-- Live Countdown Timer Widget -->
            <div class="timer-box">
                <span class="text-slate-400" style="color: #94a3b8; font-size: 13px;">
                    <i class="far fa-clock me-1 text-warning"></i> Code expires in:
                </span>
                <strong id="timerDisplay" class="timer-badge">05:00</strong>
            </div>

            <button type="submit" name="verify" class="btn-verify-submit" id="verifyBtn">
                <i class="fas fa-check-circle"></i> Verify & Activate Account
            </button>

            <div class="auth-footer-links">
                <a href="login.php" class="auth-link">
                    <i class="fas fa-arrow-left me-1"></i> Back to Login
                </a>

                <a href="register_resend_otp.php" id="resendLink" class="resend-btn">
                    <i class="fas fa-rotate-right me-1"></i> Resend OTP
                </a>
            </div>

        </form>

    </div>

    <script>
    // 6-Digit Auto-advance OTP Input Logic
    const otpInputs = document.querySelectorAll('.otp-digit-input');
    const combinedOtp = document.getElementById('combinedOtp');
    const otpForm = document.getElementById('otpForm');

    otpInputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            const val = e.target.value.replace(/[^0-9]/g, '');
            e.target.value = val ? val[0] : '';
            
            if (val) {
                input.classList.add('filled');
                if (index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            } else {
                input.classList.remove('filled');
            }
            updateCombinedOtp();
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !input.value && index > 0) {
                otpInputs[index - 1].focus();
            }
        });

        // Support full copy-paste of 6-digit OTP
        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const pastedData = (e.clipboardData || window.clipboardData).getData('text').trim().replace(/[^0-9]/g, '');
            if (pastedData.length >= 6) {
                for (let i = 0; i < 6; i++) {
                    otpInputs[i].value = pastedData[i];
                    otpInputs[i].classList.add('filled');
                }
                otpInputs[5].focus();
                updateCombinedOtp();
            }
        });
    });

    function updateCombinedOtp() {
        let code = '';
        otpInputs.forEach(input => code += input.value);
        combinedOtp.value = code;
    }

    otpForm.addEventListener('submit', (e) => {
        updateCombinedOtp();
        if (combinedOtp.value.length < 6) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Incomplete OTP',
                text: 'Please enter all 6 digits of the OTP verification code.',
                confirmButtonColor: '#0284c7'
            });
        }
    });

    // 5-Minute Live Reverse Countdown
    let remainingSeconds = <?php echo (int)$otp_expires_in; ?>;
    const timerDisplay = document.getElementById('timerDisplay');
    const resendLink = document.getElementById('resendLink');

    function updateCountdown() {
        if (remainingSeconds <= 0) {
            timerDisplay.innerHTML = "<span style='color:#ef4444;'>Expired</span>";
            return;
        }
        let mins = Math.floor(remainingSeconds / 60);
        let secs = remainingSeconds % 60;
        timerDisplay.innerText = (mins < 10 ? '0' : '') + mins + ':' + (secs < 10 ? '0' : '') + secs;
        remainingSeconds--;
    }

    updateCountdown();
    setInterval(updateCountdown, 1000);
    </script>

    <?php if (!empty($extra_script)) { echo $extra_script; } ?>
</body>
</html>