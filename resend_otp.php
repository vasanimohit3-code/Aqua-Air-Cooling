<?php

session_start();

require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


// ==================================================
// SESSION CHECK
// ==================================================

if (!isset($_SESSION['reset_email'])) {

    header("Location: forgot_password.php");
    exit();

}


$email = $_SESSION['reset_email'];


// ==================================================
// GENERATE NEW OTP
// ==================================================

$otp = random_int(100000, 999999);


// Save new OTP
$_SESSION['otp'] = $otp;

// Reset OTP timer
$_SESSION['otp_time'] = time();


// ==================================================
// GMAIL SETTINGS
// ==================================================

$gmail_username = "aquaaircoolling@gmail.com";

$gmail_app_password = "gbph snfb faok htrb";


// ==================================================
// SEND OTP
// ==================================================

$mail = new PHPMailer(true);

try {

    $mail->isSMTP();

    $mail->Host = "smtp.gmail.com";

    $mail->SMTPAuth = true;

    $mail->Username = $gmail_username;

    $mail->Password = $gmail_app_password;

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

    $mail->Port = 587;

    $mail->CharSet = "UTF-8";


    // Sender

    $mail->setFrom(
        $gmail_username,
        "Aqua Air Cooling"
    );


    // Receiver

    $mail->addAddress($email);


    // HTML Email

    $mail->isHTML(true);

    $mail->Subject =
        "Aqua Air Cooling - New Password Reset OTP";


    $mail->Body = '

        <div style="
            max-width:550px;
            margin:30px auto;
            padding:25px;
            font-family:Arial,sans-serif;
            border:1px solid #ddd;
            border-radius:10px;
        ">

            <h2 style="
                text-align:center;
                color:#0d6efd;
            ">
                Aqua Air Cooling
            </h2>

            <p>
                Your new password reset OTP is:
            </p>

            <div style="
                text-align:center;
                background:#f1f5f9;
                padding:20px;
                border-radius:10px;
                margin:20px 0;
            ">

                <span style="
                    font-size:32px;
                    font-weight:bold;
                    letter-spacing:8px;
                    color:#0d6efd;
                ">
                    ' . $otp . '
                </span>

            </div>

            <p>
                This OTP is valid for 10 minutes.
            </p>

            <p style="
                color:#777;
                font-size:13px;
            ">
                If you did not request a password reset,
                please ignore this email.
            </p>

        </div>

    ';


    // Plain text

    $mail->AltBody =
        "Your new Aqua Air Cooling password reset OTP is: "
        . $otp;


    // Send

    $mail->send();


    // ==================================================
    // SUCCESS
    // ==================================================

    header("Location: verify_otp.php?resent=1");
    exit();

}


catch (Exception $e) {

    header(
        "Location: verify_otp.php?error=1"
    );

    exit();

}

?>