<?php
require_once 'includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==================================================
// SECURITY CHECK
// ==================================================
if (
    !isset($_SESSION['reset_email']) ||
    !isset($_SESSION['otp_verified']) ||
    $_SESSION['otp_verified'] !== true
) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['reset_email'];
$message = "";
$message_type = "";
$password_updated = false;

// ==================================================
// RESET PASSWORD
// ==================================================
if (isset($_POST['reset'])) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($password) || empty($confirm_password)) {
        $message = "Please fill in all password fields.";
        $message_type = "danger";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
        $message_type = "danger";
    } elseif ($password !== $confirm_password) {
        $message = "New password and confirmation password do not match.";
        $message_type = "danger";
    } else {
        $hashPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE users SET password = ? WHERE email = ? LIMIT 1"
        );

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $hashPassword, $email);

            if (mysqli_stmt_execute($stmt)) {
                unset($_SESSION['otp']);
                unset($_SESSION['otp_time']);
                unset($_SESSION['reset_email']);
                unset($_SESSION['otp_verified']);

                $password_updated = true;
            } else {
                $message = "Password could not be updated. Please try again.";
                $message_type = "danger";
            }
            mysqli_stmt_close($stmt);
        } else {
            $message = "Database error. Please try again.";
            $message_type = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Password | Aqua Air Cooling</title>
    
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

        /* Ambient Glowing Background Elements */
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
            background: radial-gradient(circle, #10b981, #059669);
            bottom: -60px;
            right: -60px;
            animation-delay: -5s;
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
            background: linear-gradient(135deg, #10b981, #0284c7);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 28px;
            margin: 0 auto 20px;
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4);
            animation: pulseGlow 2.5s infinite;
        }

        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4); transform: scale(1); }
            50% { box-shadow: 0 12px 32px rgba(16, 185, 129, 0.7); transform: scale(1.04); }
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
            margin-bottom: 26px;
        }

        /* Form Inputs */
        .form-floating-custom {
            position: relative;
            margin-bottom: 20px;
        }

        .form-label-custom {
            color: #cbd5e1;
            font-size: 13.5px;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }

        .form-control-custom {
            width: 100%;
            height: 52px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.06);
            border: 1.5px solid rgba(255, 255, 255, 0.12);
            color: #ffffff;
            padding: 0 46px 0 44px;
            font-size: 14.5px;
            outline: none;
            transition: all 0.25s ease;
        }

        .form-control-custom:focus {
            background: rgba(14, 165, 233, 0.1);
            border-color: #38bdf8;
            box-shadow: 0 0 20px rgba(56, 189, 248, 0.35);
        }

        .form-control-custom::placeholder {
            color: #64748b;
        }

        .input-icon-left {
            position: absolute;
            top: 42px;
            left: 16px;
            color: #38bdf8;
            font-size: 16px;
            pointer-events: none;
        }

        .toggle-password-icon {
            position: absolute;
            top: 42px;
            right: 16px;
            color: #94a3b8;
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .toggle-password-icon:hover {
            color: #38bdf8;
        }

        /* Password Strength Bar */
        .strength-meter-wrap {
            margin-top: -12px;
            margin-bottom: 18px;
        }

        .strength-bar {
            height: 4px;
            border-radius: 4px;
            background: rgba(255, 255, 255, 0.1);
            overflow: hidden;
            position: relative;
        }

        .strength-fill {
            height: 100%;
            width: 0%;
            transition: width 0.3s ease, background 0.3s ease;
        }

        /* Submit Button */
        .btn-auth-submit {
            width: 100%;
            height: 52px;
            border-radius: 14px;
            background: linear-gradient(90deg, #10b981 0%, #0284c7 100%);
            border: none;
            color: #ffffff;
            font-size: 16px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.35);
            transition: all 0.25s ease;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn-auth-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(16, 185, 129, 0.55);
            background: linear-gradient(90deg, #059669 0%, #0369a1 100%);
        }

        .auth-card-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .back-link {
            color: #94a3b8;
            font-size: 13.5px;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .back-link:hover {
            color: #38bdf8;
        }
    </style>
</head>
<body>

    <!-- Ambient Glowing Background Elements -->
    <div class="ambient-orb orb-1"></div>
    <div class="ambient-orb orb-2"></div>

    <div class="dynamic-auth-card">
        
        <div class="auth-logo-badge">
            <i class="fas fa-shield-check"></i>
        </div>

        <h2 class="auth-title">Create New Password</h2>
        <p class="auth-subtitle">
            Your OTP has been verified. Set a strong password for your Aqua Air Cooling account.
        </p>

        <form method="POST">
            
            <!-- New Password -->
            <div class="form-floating-custom">
                <label class="form-label-custom">New Password</label>
                <i class="fas fa-key input-icon-left"></i>
                <input 
                    type="password" 
                    name="password" 
                    id="passwordInput" 
                    class="form-control-custom" 
                    placeholder="Enter at least 6 characters" 
                    minlength="6" 
                    required 
                    autofocus
                >
                <i class="fas fa-eye toggle-password-icon" onclick="togglePass('passwordInput', this)"></i>
            </div>

            <!-- Password Strength Indicator -->
            <div class="strength-meter-wrap">
                <div class="strength-bar">
                    <div class="strength-fill" id="strengthFill"></div>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="form-floating-custom">
                <label class="form-label-custom">Confirm New Password</label>
                <i class="fas fa-lock input-icon-left"></i>
                <input 
                    type="password" 
                    name="confirm_password" 
                    id="confirmInput" 
                    class="form-control-custom" 
                    placeholder="Re-type your new password" 
                    minlength="6" 
                    required
                >
                <i class="fas fa-eye toggle-password-icon" onclick="togglePass('confirmInput', this)"></i>
            </div>

            <button type="submit" name="reset" class="btn-auth-submit">
                <i class="fas fa-check-circle me-1"></i> Update Password & Login
            </button>

            <div class="auth-card-footer">
                <a href="login.php" class="back-link">
                    <i class="fas fa-arrow-left me-1"></i> Back to Login
                </a>
            </div>

        </form>

    </div>

    <script>
    function togglePass(inputId, icon) {
        const input = document.getElementById(inputId);
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            input.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    }

    // Dynamic Password Strength Meter
    const passInput = document.getElementById('passwordInput');
    const strengthFill = document.getElementById('strengthFill');

    passInput.addEventListener('input', function() {
        const val = this.value;
        let score = 0;
        if (val.length >= 6) score += 25;
        if (val.length >= 8) score += 25;
        if (/[A-Z]/.test(val) && /[0-9]/.test(val)) score += 25;
        if (/[^A-Za-z0-9]/.test(val)) score += 25;

        strengthFill.style.width = score + '%';
        if (score <= 25) {
            strengthFill.style.background = '#ef4444';
        } else if (score <= 50) {
            strengthFill.style.background = '#f59e0b';
        } else if (score <= 75) {
            strengthFill.style.background = '#38bdf8';
        } else {
            strengthFill.style.background = '#10b981';
        }
    });
    </script>

    <?php if ($password_updated === true) { ?>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        if (typeof confetti === 'function') {
            confetti({ particleCount: 130, spread: 80, origin: { y: 0.6 } });
        }

        Swal.fire({
            icon: 'success',
            title: '🎉 Password Updated Successfully!',
            html: '<p style="color:#475569; font-size:15px; margin-bottom:8px;">Your account password has been changed securely.</p><small class="text-muted">You can now login with your new credentials.</small>',
            confirmButtonColor: '#10b981',
            confirmButtonText: '<i class="fas fa-sign-in-alt me-1"></i> Continue to Login',
            timer: 4000,
            timerProgressBar: true,
            allowOutsideClick: false
        }).then(() => {
            window.location.href = "login.php";
        });
    });
    </script>
    <?php } ?>

    <?php if (!empty($message)) { ?>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        Swal.fire({
            icon: "error",
            title: "Password Error",
            text: <?php echo json_encode($message); ?>,
            confirmButtonColor: "#ef4444",
            confirmButtonText: "Try Again"
        });
    });
    </script>
    <?php } ?>

</body>
</html>