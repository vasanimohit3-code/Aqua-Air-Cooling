<?php

require_once 'includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =========================================================
   IF USER IS ALREADY LOGGED IN
   ========================================================= */

if (isset($_SESSION['user_id'])) {

    header("Location: booking.php");
    exit();

}


$message = "";
$message_type = "";
$active_tab = "login";
$duplicate_email = "";
$register_data = [
    'firstname' => '',
    'lastname' => '',
    'email' => ''
];

if (isset($_SESSION['register_form_data'])) {
    $register_data = $_SESSION['register_form_data'];
    unset($_SESSION['register_form_data']);
}

if (isset($_SESSION['duplicate_email'])) {
    $duplicate_email = $_SESSION['duplicate_email'];
    unset($_SESSION['duplicate_email']);
}

if (isset($_SESSION['register_message'])) {

    $message = $_SESSION['register_message'];

    $message_type = $_SESSION['register_message_type'] ?? "error";

    unset($_SESSION['register_message']);
    unset($_SESSION['register_message_type']);
}

if (isset($_GET['tab']) && $_GET['tab'] === 'register') {
    $active_tab = "register";
}


/* =========================================================
   LOGIN
   ========================================================= */

if (isset($_POST['login'])) {

    $active_tab = "login";

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';


    if ($email === "" || $password === "") {

        $message = "Please enter your email and password.";
        $message_type = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, name, email, password FROM users WHERE email = ? LIMIT 1"
        );

        if ($stmt) {

            mysqli_stmt_bind_param($stmt, "s", $email);

            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);


            if (mysqli_num_rows($result) > 0) {

                $user = mysqli_fetch_assoc($result);


                if (password_verify($password, $user['password'])) {

                    /* Regenerate session ID for security */
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];

                    header("Location: booking.php");
                    exit();

                } else {

                    $message = "Incorrect password. Please try again.";
                    $message_type = "error";

                }

            } else {

                $message = "No account found with this email address.";
                $message_type = "error";

            }

            mysqli_stmt_close($stmt);

        } else {

            $message = "Something went wrong. Please try again.";
            $message_type = "error";

        }

    }

}


/* =========================================================
   CREATE ACCOUNT
   ========================================================= */

if (isset($_POST['register'])) {

    $active_tab = "register";

    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';


    /* -----------------------------------------
       Required fields
       ----------------------------------------- */

    if (
        $firstname === "" ||
        $lastname === "" ||
        $email === "" ||
        $password === "" ||
        $confirm_password === ""
    ) {

        $message = "Please fill in all fields.";
        $message_type = "error";

    }


    /* -----------------------------------------
       Name validation
       ----------------------------------------- */

    elseif (!preg_match("/^[a-zA-Z ]+$/", $firstname)) {

        $message = "Please enter a valid first name.";
        $message_type = "error";

    }

    elseif (!preg_match("/^[a-zA-Z ]+$/", $lastname)) {

        $message = "Please enter a valid last name.";
        $message_type = "error";

    }


    /* -----------------------------------------
       Email validation
       ----------------------------------------- */

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";

    }


    /* -----------------------------------------
       Password length
       ----------------------------------------- */

    elseif (strlen($password) < 6) {

        $message = "Password must be at least 6 characters.";
        $message_type = "error";

    }


    /* -----------------------------------------
       Password confirmation
       ----------------------------------------- */

    elseif ($password !== $confirm_password) {

        $message = "Password and confirm password do not match.";
        $message_type = "error";

    }


    else {

        /* -----------------------------------------
           Check existing email
           ----------------------------------------- */

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id FROM users WHERE email = ? LIMIT 1"
        );


        if ($stmt) {

            mysqli_stmt_bind_param($stmt, "s", $email);

            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);


            if (mysqli_num_rows($result) > 0) {

                /*
                 * Save duplicate account information and preserve form fields in session.
                 * This prevents the user's entered data from being wiped and allows a smooth UX.
                 */
                $_SESSION['register_form_data'] = [
                    'firstname' => $firstname,
                    'lastname'  => $lastname,
                    'email'     => $email
                ];

                $_SESSION['duplicate_email'] = $email;
                $_SESSION['register_message'] = "This email is already registered. Please login.";
                $_SESSION['register_message_type'] = "duplicate_email";

                mysqli_stmt_close($stmt);

                /*
                 * Redirect back to Create Account tab.
                 */
                header("Location: login.php?tab=register");
                exit();

            } else {

                /* -----------------------------------------
                   Create full name
                   ----------------------------------------- */

                $name = $firstname . " " . $lastname;


                /* -----------------------------------------
                   Hash password
                   ----------------------------------------- */

                $hashPassword = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );


                /* -----------------------------------------
                   Generate 6 digit OTP
                   ----------------------------------------- */

                $otp = rand(100000, 999999);


                /* -----------------------------------------
                   Save registration information in session
                   ----------------------------------------- */

                $_SESSION['register_name'] = $name;
                $_SESSION['register_email'] = $email;
                $_SESSION['register_password'] = $hashPassword;
                $_SESSION['register_otp'] = $otp;
                $_SESSION['register_otp_time'] = time();


                /*
                 * Redirect to existing OTP page.
                 */

                header("Location: register_send_otp.php");
                exit();

            }


            mysqli_stmt_close($stmt);

        } else {

            $message = "Something went wrong. Please try again.";
            $message_type = "error";

        }

    }

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="utf-8">

    <title>Aqua Air Cooling - Login</title>

    <meta
        content="width=device-width, initial-scale=1.0"
        name="viewport"
    >

    <meta
        content="Aqua Air Cooling Login and Registration"
        name="description"
    >


    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">


    <!-- Google Fonts -->

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;600;800&family=Roboto:wght@400;500;700&display=swap"
        rel="stylesheet"
    >


    <!-- Font Awesome -->

    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css"
        rel="stylesheet"
    >


    <!-- Bootstrap Icons -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css"
        rel="stylesheet"
    >


    <!-- Animate -->

    <link
        href="lib/animate/animate.min.css"
        rel="stylesheet"
    >


    <!-- Bootstrap -->

    <link
        href="css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Main CSS -->

    <link
        href="css/style.css"
        rel="stylesheet"
    >


    <!-- =====================================================
         LOGIN PAGE CSS
         ===================================================== -->

    <style>

        * {
            box-sizing: border-box;
        }


        body {
            background: #f5f8fc;
        }


        /* Main Area */

        .auth-section {

            min-height: calc(100vh - 150px);

            padding: 55px 15px;

            display: flex;

            justify-content: center;

            align-items: center;

            position: relative;

            overflow: hidden;

        }


        /* Background circles */

        .auth-section::before {

            content: "";

            position: absolute;

            width: 420px;
            height: 420px;

            border-radius: 50%;

            background: rgba(13, 110, 253, 0.07);

            top: -180px;
            left: -160px;

        }


        .auth-section::after {

            content: "";

            position: absolute;

            width: 350px;
            height: 350px;

            border-radius: 50%;

            background: rgba(255, 127, 14, 0.07);

            bottom: -150px;
            right: -120px;

        }


        /* Main Card */

        .auth-card {

            width: 100%;

            max-width: 950px;

            min-height: 590px;

            background: #ffffff;

            border-radius: 25px;

            overflow: hidden;

            position: relative;

            z-index: 2;

            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.10);

            display: flex;

            animation: cardShow 0.7s ease;

        }


        @keyframes cardShow {

            from {

                opacity: 0;

                transform: translateY(25px);

            }

            to {

                opacity: 1;

                transform: translateY(0);

            }

        }


        /* Left Branding */

        .auth-left {

            width: 42%;

            background: linear-gradient(
                145deg,
                #06164f,
                #0d6efd
            );

            color: #ffffff;

            padding: 50px 40px;

            display: flex;

            flex-direction: column;

            justify-content: center;

            align-items: center;

            text-align: center;

            position: relative;

            overflow: hidden;

        }


        .auth-left::before {

            content: "";

            position: absolute;

            width: 250px;
            height: 250px;

            border-radius: 50%;

            background: rgba(255,255,255,0.07);

            top: -80px;

            right: -80px;

        }


        .auth-left::after {

            content: "";

            position: absolute;

            width: 180px;
            height: 180px;

            border-radius: 50%;

            background: rgba(255,255,255,0.06);

            bottom: -70px;

            left: -70px;

        }


        .brand-icon {

            width: 75px;
            height: 75px;

            border-radius: 20px;

            background: rgba(255,255,255,0.15);

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 38px;

            margin: 0 auto 25px auto;

            backdrop-filter: blur(5px);

        }


        .auth-left h1 {

            font-family: "Roboto Slab", serif;

            font-size: 38px;

            font-weight: 800;

            margin-bottom: 15px;

            position: relative;

            z-index: 2;

        }


        .auth-left p {

            font-size: 16px;

            line-height: 1.8;

            opacity: 0.90;

            position: relative;

            z-index: 2;

        }


        .service-points {

            margin-top: 25px;

            position: relative;

            z-index: 2;

        }


        .service-point {

            display: flex;

            align-items: center;

            gap: 12px;

            margin-bottom: 15px;

            font-size: 14px;

        }


        .service-point i {

            width: 30px;
            height: 30px;

            border-radius: 50%;

            background: rgba(255,255,255,0.15);

            display: flex;

            align-items: center;

        
            justify-content: center;

        }


        /* Right Form */

        .auth-right {

            width: 58%;

            padding: 45px 55px;

            display: flex;

            flex-direction: column;

            justify-content: center;

        }


        .auth-title {

            text-align: center;

            margin-bottom: 25px;

        }


        .auth-title h2 {

            font-family: "Roboto Slab", serif;

            color: #06164f;

            font-size: 32px;

            font-weight: 800;

            margin-bottom: 8px;

        }


        .auth-title p {

            color: #7a8494;

            margin: 0;

        }


        /* Tabs */

        .auth-tabs {

            display: flex;

            background: #f2f5f9;

            padding: 5px;

            border-radius: 12px;

            margin-bottom: 25px;

        }


        .auth-tab {

            flex: 1;

            border: none;

            background: transparent;

            padding: 12px 10px;

            border-radius: 9px;

            color: #697586;

            font-weight: 600;

            cursor: pointer;

            transition: all 0.3s ease;

        }


        .auth-tab.active {

            background: #ff7f0e;

            color: #ffffff;

            box-shadow: 0 5px 15px rgba(255,127,14,0.25);

        }


        /* Messages */

        .auth-message {

            padding: 12px 15px;

            border-radius: 10px;

            margin-bottom: 18px;

            font-size: 14px;

            display: flex;

            align-items: center;

            gap: 10px;

            animation: messageShow 0.3s ease;

        }


        @keyframes messageShow {

            from {

                opacity: 0;

                transform: translateY(-5px);

            }

            to {

                opacity: 1;

                transform: translateY(0);

            }

        }


        .auth-message.error {

            background: #fff1f1;

            color: #c62828;

            border: 1px solid #ffd4d4;

        }


        .auth-message.success {

            background: #effaf3;

            color: #198754;

            border: 1px solid #c8efd5;

        }


        /* Form */

        .auth-form {

            display: none;

            animation: formShow 0.35s ease;

        }


        .auth-form.active {

            display: block;

        }


        @keyframes formShow {

            from {

                opacity: 0;

                transform: translateX(10px);

            }

            to {

                opacity: 1;

                transform: translateX(0);

            }

        }


        /* Input */

        .input-group-custom {

            position: relative;

            margin-bottom: 17px;

        }


        .input-group-custom i.input-icon {

            position: absolute;

            left: 17px;

            top: 50%;

            transform: translateY(-50%);

            color: #9aa5b5;

            z-index: 3;

        }


        .custom-input {

            width: 100%;

            height: 55px;

            border: 1px solid #dce2ea;

            border-radius: 11px;

            padding: 0 48px;

            outline: none;

            color: #202939;

            background: #ffffff;

            transition: all 0.25s ease;

            font-size: 15px;

        }


        .custom-input:focus {

            border-color: #0d6efd;

            box-shadow: 0 0 0 4px rgba(13,110,253,0.08);

        }


        .custom-input::placeholder {

            color: #9aa5b5;

        }


        /* Password eye */

        .password-eye {

            position: absolute;

            right: 16px;

            top: 50%;

            transform: translateY(-50%);

            border: none;

            background: transparent;

            color: #8994a5;

            cursor: pointer;

            z-index: 5;

        }


        .password-eye:hover {

            color: #0d6efd;

        }


        /* Password strength */

        .password-strength {

            margin-top: -8px;

            margin-bottom: 15px;

            display: none;

        }


        .strength-bar {

            height: 4px;

            width: 100%;

            background: #e8edf3;

            border-radius: 10px;

            overflow: hidden;

        }


        .strength-fill {

            height: 100%;

            width: 0%;

            transition: all 0.3s ease;

        }


        .strength-text {

            font-size: 12px;

            margin-top: 5px;

            color: #8994a5;

        }


      /* Confirm Password Message */
.password-column {
    position: relative;
}

.password-match {
    position: absolute;
    top: 57px;
    left: 0;
    font-size: 12px;
    line-height: 16px;
    margin: 0;
    white-space: nowrap;
    z-index: 10;
}

.email-feedback-container {
    animation: messageShow 0.25s ease;
}



        /* Forgot */

        .forgot-link {

            display: block;

            text-align: right;

            color: #0d6efd;

            text-decoration: none;

            font-size: 14px;

            margin-top: -5px;

            margin-bottom: 18px;

        }


        .forgot-link:hover {

            text-decoration: underline;

        }


        /* Buttons */

        .auth-submit {

            width: 100%;

            height: 55px;

            border: none;

            border-radius: 11px;

            color: #ffffff;

            font-size: 16px;

            font-weight: 600;

            cursor: pointer;

            transition: all 0.3s ease;

            position: relative;

        }


        .login-submit {

            background: #ff7f0e;

            box-shadow: 0 7px 18px rgba(255,127,14,0.20);

        }


        .register-submit {

            background: #198754;

            box-shadow: 0 7px 18px rgba(25,135,84,0.18);

        }


        .auth-submit:hover {

            transform: translateY(-2px);

        }


        .auth-submit:active {

            transform: translateY(0);

        }


        .auth-submit.loading {

            pointer-events: none;

            opacity: 0.8;

        }


        /* Bottom text */

        .auth-bottom-text {

            text-align: center;

            margin-top: 20px;

            font-size: 13px;

            color: #8994a5;

        }

/* ==============================
   CENTER DYNAMIC MESSAGE POPUP
   ============================== */

.dynamic-message-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    z-index: 99999;
}

.dynamic-message-box {
    width: 100%;
    max-width: 390px;
    background: #fff;
    border-radius: 18px;
    padding: 30px 25px;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
    animation: popupShow 0.3s ease;
}

.dynamic-message-icon {
    width: 60px;
    height: 60px;
    margin: 0 auto 15px;
    border-radius: 50%;
    background: #fff1f1;
    color: #dc3545;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 27px;
}

.dynamic-message-box h4 {
    margin: 0 0 10px;
    color: #06164f;
    font-family: "Roboto Slab", serif;
    font-weight: 700;
}

.dynamic-message-box p {
    margin: 0 0 22px;
    color: #697586;
    font-size: 14px;
    line-height: 1.6;
}

.dynamic-message-btn {
    border: none;
    background: #ff7f0e;
    color: #fff;
    padding: 10px 32px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
}

.dynamic-message-btn:hover {
    background: #e96f00;
}


@keyframes popupShow {
    from {
        opacity: 0;
        transform: scale(0.85);
    }

    to {
        opacity: 1;
        transform: scale(1);
    }
}

        /* Mobile */

        @media (max-width: 850px) {

            .auth-card {

                max-width: 600px;

            }


            .auth-left {

                display: none;

            }


            .auth-right {

                width: 100%;

                padding: 40px 35px;

            }

        }


        @media (max-width: 500px) {

            .auth-section {

                padding: 30px 12px;

            }


            .auth-card {

                border-radius: 18px;

                min-height: auto;

            }


            .auth-right {

                padding: 30px 20px;

            }


            .auth-title h2 {

                font-size: 27px;

            }


            .auth-tabs {

                margin-bottom: 20px;

            }


            .auth-tab {

                font-size: 13px;

            }

            .custom-input {
                font-size: 16px !important;
                height: 50px;
                padding: 0 42px;
            }

            .auth-submit {
                height: 50px;
                font-size: 15px;
            }

            .password-match {
                position: relative;
                top: auto;
                margin-top: 4px;
                white-space: normal;
            }

        }

    </style>

</head>


<body>


<?php include 'includes/navbar.php'; ?>

<?php include 'includes/topbar.php'; ?>


<!-- =====================================================
     AUTH SECTION
     ===================================================== -->

<section class="auth-section">


    <div class="auth-card">


        <!-- =================================================
             LEFT SIDE
             ================================================= -->

        <div class="auth-left">


            <div class="brand-icon">

                ❄️

            </div>


            <h1>
                Aqua Air Cooling
            </h1>


            <p>
                Your trusted partner for professional
                AC installation, servicing and cooling
                solutions.
            </p>


            <div class="service-points">


                <div class="service-point">

                    <i class="fas fa-check"></i>

                    <span>
                        Professional AC Service
                    </span>

                </div>


                <div class="service-point">

                    <i class="fas fa-check"></i>

                    <span>
                        Easy Online Booking
                    </span>

                </div>


                <div class="service-point">

                    <i class="fas fa-check"></i>

                    <span>
                        Reliable Cooling Solutions
                    </span>

                </div>


                <div class="service-point">

                    <i class="fas fa-check"></i>

                    <span>
                        Quick Customer Support
                    </span>

                </div>


            </div>


        </div>



        <!-- =================================================
             RIGHT SIDE
             ================================================= -->

        <div class="auth-right">


            <!-- Title -->

            <div class="auth-title">

                <h2>
                    Welcome
                </h2>

                <p>
                    Login or create your account
                </p>

            </div>



            <!-- Tabs -->

            <div class="auth-tabs">


                <button
                    type="button"
                    id="loginTab"
                    class="auth-tab <?php echo $active_tab === 'login' ? 'active' : ''; ?>"
                    onclick="switchAuthTab('login')"
                >

                    <i class="fas fa-sign-in-alt me-2"></i>

                    Login

                </button>


                <button
                    type="button"
                    id="registerTab"
                    class="auth-tab <?php echo $active_tab === 'register' ? 'active' : ''; ?>"
                    onclick="switchAuthTab('register')"
                >

                    <i class="fas fa-user-plus me-2"></i>

                    Create Account

                </button>


            </div>


            <!-- =================================================
                 LOGIN FORM
                 ================================================= -->

            <form
                method="POST"
                action="login.php"
                class="auth-form <?php echo $active_tab === 'login' ? 'active' : ''; ?>"
                id="loginForm"
            >


                <!-- Email -->

                <div class="input-group-custom">

                    <i class="fas fa-envelope input-icon"></i>

                    <input
                        type="email"
                        name="email"
                        id="loginEmail"
                        class="custom-input"
                        placeholder="Email Address"
                        autocomplete="email"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? $duplicate_email ?? ''); ?>"
                        required
                    >

                </div>



                <!-- Password -->

                <div class="input-group-custom">

                    <i class="fas fa-lock input-icon"></i>

                    <input
                        type="password"
                        name="password"
                        id="loginPassword"
                        class="custom-input"
                        placeholder="Password"
                        autocomplete="current-password"
                        required
                    >


                    <button
                        type="button"
                        class="password-eye"
                        onclick="togglePassword('loginPassword', this)"
                    >

                        <i class="fas fa-eye"></i>

                    </button>

                </div>



                <!-- Forgot -->

                <a
                    href="forgot_password.php"
                    class="forgot-link"
                >
                    Forgot Password?
                </a>



                <!-- Submit -->

                <button
                    type="submit"
                    name="login"
                    class="auth-submit login-submit"
                    id="loginButton"
                >

                    <span class="button-text">

                        <i class="fas fa-sign-in-alt me-2"></i>

                        Login

                    </span>

                </button>


                <div class="auth-bottom-text">

                    Login to book your AC service.

                </div>


            </form>



            <!-- =================================================
                 REGISTER FORM
                 ================================================= -->

            <form
                method="POST"
                action="login.php"
                class="auth-form <?php echo $active_tab === 'register' ? 'active' : ''; ?>"
                id="registerForm"
            >


                <div class="row g-3">


                    <!-- First Name -->

                    <div class="col-md-6">

                        <div class="input-group-custom mb-0">

                            <i class="fas fa-user input-icon"></i>

                            <input
                                type="text"
                                name="firstname"
                                class="custom-input"
                                placeholder="First Name"
                                autocomplete="given-name"
                                value="<?php echo htmlspecialchars($_POST['firstname'] ?? $register_data['firstname'] ?? ''); ?>"
                                required
                            >

                        </div>

                    </div>



                    <!-- Last Name -->

                    <div class="col-md-6">

                        <div class="input-group-custom mb-0">

                            <i class="fas fa-user input-icon"></i>

                            <input
                                type="text"
                                name="lastname"
                                class="custom-input"
                                placeholder="Last Name"
                                autocomplete="family-name"
                                value="<?php echo htmlspecialchars($_POST['lastname'] ?? $register_data['lastname'] ?? ''); ?>"
                                required
                            >

                        </div>

                    </div>



                    <!-- Email -->

                    <div class="col-12">

                        <div class="input-group-custom mb-0">

                            <i class="fas fa-envelope input-icon"></i>

                            <input
                                type="email"
                                name="email"
                                id="registerEmail"
                                class="custom-input"
                                placeholder="Email Address"
                                autocomplete="email"
                                value="<?php echo htmlspecialchars($_POST['email'] ?? $register_data['email'] ?? ''); ?>"
                                required
                            >

                        </div>

                        <div id="emailFeedback" class="email-feedback-container mt-1" style="display: none;"></div>

                    </div>



                    <!-- Password -->

                    <div class="col-md-6">

                        <div class="input-group-custom mb-0">

                            <i class="fas fa-lock input-icon"></i>

                            <input
                                type="password"
                                name="password"
                                id="registerPassword"
                                class="custom-input"
                                placeholder="Create Password"
                                autocomplete="new-password"
                                oninput="checkPasswordStrength()"
                                required
                            >


                            <button
                                type="button"
                                class="password-eye"
                                onclick="togglePassword('registerPassword', this)"
                            >

                                <i class="fas fa-eye"></i>

                            </button>

                        </div>


                        <!-- Strength -->

                        <div
                            class="password-strength"
                            id="passwordStrength"
                        >

                            <div class="strength-bar">

                                <div
                                    class="strength-fill"
                                    id="strengthFill"
                                ></div>

                            </div>


                            <div
                                class="strength-text"
                                id="strengthText"
                            >
                                Password strength
                            </div>

                        </div>

                    </div>



                    <!-- Confirm Password -->

                    <div class="col-md-6 password-column">

                        <div class="input-group-custom mb-0">

                            <i class="fas fa-lock input-icon"></i>

                            <input
                                type="password"
                                name="confirm_password"
                                id="confirmPassword"
                                class="custom-input"
                                placeholder="Confirm Password"
                                autocomplete="new-password"
                                oninput="checkPasswordMatch()"
                                required
                            >


                            <button
                                type="button"
                                class="password-eye"
                                onclick="togglePassword('confirmPassword', this)"
                            >

                                <i class="fas fa-eye"></i>

                            </button>

                        </div>


                        <div
                            class="password-match"
                            id="passwordMatch"
                        ></div>

                    </div>



                    <!-- Register Button -->

                    <div class="col-12">

                        <button
                            type="submit"
                            name="register"
                            class="auth-submit register-submit"
                            id="registerButton"
                        >

                            <span class="button-text">

                                <i class="fas fa-user-plus me-2"></i>

                                Create Account

                            </span>

                        </button>

                    </div>


                </div>


                <div class="auth-bottom-text">

                    Create your account and book our services.

                </div>


            </form>


        </div>

    </div>

</section>



<!-- Bootstrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>



<!-- =========================================================
     JAVASCRIPT
     ========================================================= -->

<script>


/* =========================================================
   SWITCH LOGIN / REGISTER & PREFILL
   ========================================================= */

function switchAuthTab(tab) {


    const loginTab =
        document.getElementById("loginTab");

    const registerTab =
        document.getElementById("registerTab");


    const loginForm =
        document.getElementById("loginForm");

    const registerForm =
        document.getElementById("registerForm");


    if (tab === "login") {

        loginTab.classList.add("active");

        registerTab.classList.remove("active");


        loginForm.classList.add("active");

        registerForm.classList.remove("active");


    } else {

        registerTab.classList.add("active");

        loginTab.classList.remove("active");


        registerForm.classList.add("active");

        loginForm.classList.remove("active");

    }

}

function switchToLoginWithEmail(email) {
    if (!email) {
        const regEmail = document.getElementById("registerEmail");
        email = regEmail ? regEmail.value.trim() : "";
    }
    switchAuthTab("login");
    const loginEmail = document.getElementById("loginEmail");
    if (loginEmail && email) {
        loginEmail.value = email;
    }
    const loginPassword = document.getElementById("loginPassword");
    if (loginPassword) {
        setTimeout(function() {
            loginPassword.focus();
        }, 150);
    }
}

/* =========================================================
   REAL-TIME EMAIL AVAILABILITY CHECK
   ========================================================= */
let emailCheckTimeout = null;

function checkEmailAvailability(emailVal) {
    const feedback = document.getElementById("emailFeedback");
    const regEmailInput = document.getElementById("registerEmail");
    if (!feedback || !regEmailInput) return;

    emailVal = (emailVal || "").trim();
    if (emailVal === "") {
        feedback.style.display = "none";
        feedback.innerHTML = "";
        regEmailInput.style.borderColor = "";
        return;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(emailVal)) {
        feedback.style.display = "none";
        feedback.innerHTML = "";
        regEmailInput.style.borderColor = "";
        return;
    }

    fetch("check_email.php?email=" + encodeURIComponent(emailVal))
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.status === "exists") {
                regEmailInput.style.borderColor = "#f59e0b";
                feedback.style.display = "block";
                feedback.innerHTML = `
                    <div class="d-flex align-items-center justify-content-between p-2 rounded" style="background:#fff7ed; border:1px solid #fed7aa; color:#c2410c; font-size:13px;">
                        <span><i class="fas fa-exclamation-circle text-warning me-1"></i> This email is already registered.</span>
                        <button type="button" class="btn btn-sm btn-primary py-0 px-2 ms-2" style="font-size:12px; border-radius:6px; white-space:nowrap;" onclick="switchToLoginWithEmail('${data.email.replace(/'/g, "\\'")}')">
                            <i class="fas fa-sign-in-alt me-1"></i> Login here &rarr;
                        </button>
                    </div>
                `;
            } else if (data.status === "available") {
                regEmailInput.style.borderColor = "#10b981";
                feedback.style.display = "block";
                feedback.innerHTML = `
                    <div class="text-success p-1" style="font-size:12.5px;">
                        <i class="fas fa-check-circle me-1"></i> Email is available
                    </div>
                `;
            } else {
                feedback.style.display = "none";
                regEmailInput.style.borderColor = "";
            }
        })
        .catch(function(err) {
            console.error("Email check error:", err);
        });
}

document.addEventListener("DOMContentLoaded", function() {
    const regEmail = document.getElementById("registerEmail");
    if (regEmail) {
        regEmail.addEventListener("input", function() {
            clearTimeout(emailCheckTimeout);
            const val = this.value;
            emailCheckTimeout = setTimeout(function() {
                checkEmailAvailability(val);
            }, 450);
        });

        regEmail.addEventListener("blur", function() {
            clearTimeout(emailCheckTimeout);
            checkEmailAvailability(this.value);
        });

        if (regEmail.value.trim() !== "") {
            checkEmailAvailability(regEmail.value);
        }
    }
});


/* =========================================================
   SHOW / HIDE PASSWORD
   ========================================================= */

function togglePassword(inputId, button) {


    const input =
        document.getElementById(inputId);


    const icon =
        button.querySelector("i");


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


/* =========================================================
   PASSWORD STRENGTH
   ========================================================= */

function checkPasswordStrength() {


    const password =
        document.getElementById("registerPassword").value;


    const strengthBox =
        document.getElementById("passwordStrength");


    const fill =
        document.getElementById("strengthFill");


    const text =
        document.getElementById("strengthText");


    if (password.length === 0) {

        strengthBox.style.display = "none";

        fill.style.width = "0%";

        return;

    }


    strengthBox.style.display = "block";


    let score = 0;


    if (password.length >= 6) {

        score++;

    }


    if (password.length >= 8) {

        score++;

    }


    if (/[A-Z]/.test(password)) {

        score++;

    }


    if (/[0-9]/.test(password)) {

        score++;

    }


    if (/[^A-Za-z0-9]/.test(password)) {

        score++;

    }


    if (score <= 1) {

        fill.style.width = "25%";

        text.innerHTML = "Weak password";

        text.style.color = "#dc3545";

    }

    else if (score <= 3) {

        fill.style.width = "60%";

        text.innerHTML = "Medium password";

        text.style.color = "#f59f00";

    }

    else {

        fill.style.width = "100%";

        text.innerHTML = "Strong password";

        text.style.color = "#198754";

    }

}


/* =========================================================
   PASSWORD MATCH
   ========================================================= */

function checkPasswordMatch() {


    const password =
        document.getElementById("registerPassword").value;


    const confirmPassword =
        document.getElementById("confirmPassword").value;


    const message =
        document.getElementById("passwordMatch");


    if (confirmPassword.length === 0) {

        message.innerHTML = "";

        return;

    }


    if (password === confirmPassword) {

        message.innerHTML =
            '<i class="fas fa-check-circle"></i> Passwords match';

        message.style.color = "#198754";

    } else {

        message.innerHTML =
            '<i class="fas fa-times-circle"></i> Passwords do not match';

        message.style.color = "#dc3545";

    }

}


/* =========================================================
   LOGIN LOADING
   ========================================================= */

document
    .getElementById("loginForm")
    .addEventListener("submit", function() {


        const button =
            document.getElementById("loginButton");


        button.classList.add("loading");


        button.innerHTML =
            '<i class="fas fa-spinner fa-spin me-2"></i> Logging in...';

    });



/* =========================================================
   REGISTER LOADING
   ========================================================= */

document
    .getElementById("registerForm")
    .addEventListener("submit", function(event) {


        const password =
            document.getElementById("registerPassword").value;


        const confirmPassword =
            document.getElementById("confirmPassword").value;


        if (password !== confirmPassword) {

            event.preventDefault();

            checkPasswordMatch();

            return;

        }


        const button =
            document.getElementById("registerButton");


        button.classList.add("loading");


        button.innerHTML =
            '<i class="fas fa-spinner fa-spin me-2"></i> Creating Account...';

    });



function closeDynamicMessage() {

    const popup = document.getElementById("dynamicMessage");

    if (popup) {
        popup.style.opacity = "0";

        setTimeout(function () {
            popup.remove();
        }, 200);
    }

}



</script>


<?php if ($message !== ""): ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    <?php
    $swal_title = "Notification";
    $swal_icon = $message_type === "error" ? "error" : "success";
    $btn_color = $message_type === "error" ? "#ef4444" : "#10b981";

    if ($message_type === "duplicate_email" || strpos($message, "already registered") !== false):
    ?>
        Swal.fire({
            icon: 'info',
            title: 'Account Already Exists',
            html: `
                <div class="text-center">
                    <p class="mb-2" style="font-size:15px; color:#475569;">
                        An account with <strong><?php echo htmlspecialchars($duplicate_email !== '' ? $duplicate_email : ($register_data['email'] ?? 'this email')); ?></strong> already exists.
                    </p>
                    <p class="mb-0" style="font-size:14px; color:#64748b;">
                        Would you like to log in to your existing account?
                    </p>
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="fas fa-sign-in-alt me-1"></i> Go to Login',
            cancelButtonText: '<i class="fas fa-pen me-1"></i> Use Different Email',
            reverseButtons: true,
            customClass: {
                popup: 'swal2-glass-popup'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                switchToLoginWithEmail(<?php echo json_encode($duplicate_email !== '' ? $duplicate_email : ($register_data['email'] ?? '')); ?>);
            } else {
                const regEmail = document.getElementById('registerEmail');
                if (regEmail) {
                    regEmail.focus();
                    regEmail.select();
                }
            }
        });
    <?php
    else:
        if ($message_type === "error") {
            if (strpos($message, "Password must be at least 6 characters") !== false) {
                $swal_title = "Invalid Password";
            } elseif (strpos($message, "do not match") !== false) {
                $swal_title = "Password Mismatch";
            } elseif (strpos($message, "valid email") !== false) {
                $swal_title = "Invalid Email";
            } elseif (strpos($message, "Incorrect password") !== false) {
                $swal_title = "Incorrect Password";
            } elseif (strpos($message, "No account found") !== false) {
                $swal_title = "Account Not Found";
            } else {
                $swal_title = "Attention Required";
            }
        } else {
            $swal_title = "Success!";
        }
    ?>
        Swal.fire({
            icon: <?php echo json_encode($swal_icon); ?>,
            title: <?php echo json_encode($swal_title); ?>,
            html: <?php echo json_encode('<p class="mb-0" style="font-size:15px;">' . htmlspecialchars($message) . '</p>'); ?>,
            confirmButtonColor: <?php echo json_encode($btn_color); ?>,
            confirmButtonText: '<i class="fas fa-check me-1"></i> Understood',
            customClass: {
                popup: 'swal2-glass-popup'
            }
        });
    <?php endif; ?>
});
</script>
<?php endif; ?>

</body>


<?php include 'includes/footer.php'; ?>


</html>