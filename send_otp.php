<?php

session_start();

require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


// ==================================================
// CHECK SESSION
// ==================================================

if (
    !isset($_SESSION['reset_email']) ||
    !isset($_SESSION['otp'])
) {
    header("Location: forgot_password.php");
    exit();
}


$email = trim($_SESSION['reset_email']);
$otp   = $_SESSION['otp'];


// ==================================================
// GMAIL SETTINGS
// ==================================================

$gmail_username = "aquaaircoolling@gmail.com";

// અહીં તમારો NEW Gmail App Password નાખો
$gmail_app_password = "gbph snfb faok htrb";


// ==================================================
// PHPMailer
// ==================================================

$mail = new PHPMailer(true);

try {

    // ----------------------------------------------
    // SMTP Configuration
    // ----------------------------------------------

    $mail->isSMTP();

    $mail->Host = 'smtp.gmail.com';

    $mail->SMTPAuth = true;

    $mail->Username = $gmail_username;

    $mail->Password = $gmail_app_password;

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

    $mail->Port = 587;


    // ----------------------------------------------
    // Character Encoding
    // ----------------------------------------------

    $mail->CharSet = 'UTF-8';


    // ----------------------------------------------
    // Sender
    // ----------------------------------------------

    $mail->setFrom(
        $gmail_username,
        'Aqua Air Cooling'
    );


    // ----------------------------------------------
    // Receiver
    // ----------------------------------------------

    $mail->addAddress($email);


    // ----------------------------------------------
    // Email Format
    // ----------------------------------------------

    $mail->isHTML(true);


    // ----------------------------------------------
    // Subject
    // ----------------------------------------------

    $mail->Subject = 'Aqua Air Cooling - Password Reset OTP';


    // ----------------------------------------------
    // Email Body
    // ----------------------------------------------

    $mail->Body = '

    <div style="
        max-width:550px;
        margin:30px auto;
        padding:25px;
        font-family:Arial,Helvetica,sans-serif;
        border:1px solid #ddd;
        border-radius:10px;
        background:#ffffff;
    ">

        <h2 style="
            text-align:center;
            color:#0d6efd;
            margin-bottom:25px;
        ">
            Aqua Air Cooling
        </h2>


        <p>
            Hello,
        </p>


        <p>
            We received a request to reset your password.
        </p>


        <p>
            Your password reset OTP is:
        </p>


        <div style="
            text-align:center;
            background:#f1f5f9;
            padding:20px;
            margin:25px 0;
            border-radius:10px;
        ">

            <span style="
                font-size:32px;
                font-weight:bold;
                letter-spacing:8px;
                color:#0d6efd;
            ">
                ' . htmlspecialchars($otp) . '
            </span>

        </div>


        <p>
            Please enter this OTP on the verification page
            to continue resetting your password.
        </p>


        <p style="
            color:#777;
            font-size:13px;
            margin-top:25px;
        ">
            If you did not request a password reset,
            you can safely ignore this email.
        </p>


        <hr style="border:none;border-top:1px solid #ddd;">


        <p style="
            text-align:center;
            color:#888;
            font-size:12px;
        ">
            © ' . date('Y') . ' Aqua Air Cooling
        </p>

    </div>

    ';


    // ----------------------------------------------
    // Plain Text Email
    // ----------------------------------------------

    $mail->AltBody =
        "Aqua Air Cooling\n\n" .
        "Your password reset OTP is: " . $otp . "\n\n" .
        "Please enter this OTP on the verification page.";


    // ----------------------------------------------
    // Send Email
    // ----------------------------------------------

    $mail->send();


    // ----------------------------------------------
    // OTP Sent Successfully
    // ----------------------------------------------

    header("Location: verify_otp.php");
    exit();

}


// ==================================================
// ERROR
// ==================================================

catch (Exception $e) {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>OTP Error</title><script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script><style>body{font-family:sans-serif;background:#0f172a;}</style></head><body>';
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'OTP Sending Failed',
            text: " . json_encode($mail->ErrorInfo ?: 'Unable to send OTP email at this time. Please try again.') . ",
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Try Again'
        }).then(() => {
            window.location.href = 'forgot_password.php';
        });
    });
    </script></body></html>";
    exit();
}

?>