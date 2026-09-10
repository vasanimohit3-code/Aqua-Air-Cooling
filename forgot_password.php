<?php
require_once 'includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = '';
$message_type = '';

if (isset($_POST['send_otp'])) {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $message = "Please enter your email address.";
        $message_type = "danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $message_type = "danger";
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, email FROM users WHERE email = ? LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $otp = random_int(100000, 999999);
            $_SESSION['reset_email'] = $email;
            $_SESSION['otp'] = $otp;
            $_SESSION['otp_time'] = time();

            header("Location: send_otp.php");
            exit();
        } else {
            $message = "No account found with this email address.";
            $message_type = "danger";
        }

        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Aqua Air Cooling</title>
    
    <!-- Google Fonts & Font Awesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
            background: radial-gradient(circle, #2563eb, #1d4ed8);
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
            max-width: 460px;
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

        /* Input Controls */
        .form-floating-custom {
            position: relative;
            margin-bottom: 22px;
        }

        .form-control-custom {
            width: 100%;
            height: 54px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.06);
            border: 1.5px solid rgba(255, 255, 255, 0.12);
            color: #ffffff;
            padding: 0 16px 0 46px;
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
            top: 50%;
            left: 16px;
            transform: translateY(-50%);
            color: #38bdf8;
            font-size: 17px;
            pointer-events: none;
        }

        /* Submit Button */
        .btn-auth-submit {
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

        .btn-auth-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(2, 132, 199, 0.6);
            background: linear-gradient(90deg, #0369a1 0%, #1d4ed8 100%);
        }

        /* Footer Link */
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

    <!-- Ambient Glowing Elements -->
    <div class="ambient-orb orb-1"></div>
    <div class="ambient-orb orb-2"></div>

    <div class="dynamic-auth-card">
        
        <div class="auth-logo-badge">
            <i class="fas fa-key"></i>
        </div>

        <h2 class="auth-title">Forgot Password?</h2>
        <p class="auth-subtitle">
            Enter your registered email address below. We'll send you a 6-digit OTP code to reset your password.
        </p>

        <form method="POST">
            
            <div class="form-floating-custom">
                <i class="fas fa-envelope input-icon-left"></i>
                <input 
                    type="email" 
                    name="email" 
                    class="form-control-custom" 
                    placeholder="Enter your registered email" 
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                    required 
                    autofocus
                >
            </div>

            <button type="submit" name="send_otp" class="btn-auth-submit">
                <i class="fas fa-paper-plane me-1"></i> Send Verification OTP
            </button>

            <div class="auth-card-footer">
                <a href="login.php" class="back-link">
                    <i class="fas fa-arrow-left me-1"></i> Back to Login
                </a>
            </div>

        </form>

    </div>

    <?php if (!empty($message)) { ?>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        Swal.fire({
            icon: "error",
            title: "Forgot Password",
            text: <?php echo json_encode($message); ?>,
            confirmButtonColor: "#ef4444",
            confirmButtonText: '<i class="fas fa-check me-1"></i> Try Again'
        });
    });
    </script>
    <?php } ?>

</body>
</html>