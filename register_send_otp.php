<?php

session_start();

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if(
    !isset($_SESSION['register_email']) ||
    !isset($_SESSION['register_name']) ||
    !isset($_SESSION['register_otp'])
){
    header("Location: login.php");
    exit();
}

$email = $_SESSION['register_email'];
$name  = $_SESSION['register_name'];
$otp   = $_SESSION['register_otp'];

$mail = new PHPMailer(true);

try{

    $mail->isSMTP();

    $mail->Host = "smtp.gmail.com";

    $mail->SMTPAuth = true;

    $mail->Username = "aquaaircoolling@gmail.com";

    $mail->Password = "gbph snfb faok htrb";

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

    $mail->Port = 587;

    $mail->setFrom(
        "aquaaircoolling@gmail.com",
        "Aqua Air Cooling"
    );

    $mail->addAddress($email,$name);

    $mail->isHTML(true);

    $mail->Subject = "Account Verification OTP";

    $mail->Body = "

    <div style='font-family:Arial'>

    <h2 style='color:#0d6efd;'>Aqua Air Cooling</h2>

    <p>Hello <b>$name</b>,</p>

    <p>Your Account Verification OTP is</p>

    <h1 style='color:#198754;letter-spacing:6px;'>$otp</h1>

    <p>This OTP is valid for 5 Minutes.</p>

    <p>Please do not share this OTP with anyone.</p>

    <hr>

    Aqua Air Cooling

    </div>

    ";

    $mail->send();

    header("Location: register_verify_otp.php");

    exit();

}
catch(Exception $e){

    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script><style>body{font-family:sans-serif;background:#f5f7fb;}</style></head><body>';
    echo "<script>
    Swal.fire({
        title: 'OTP Sending Failed',
        text: 'Unable to send OTP email. Please verify your email or try again later.',
        icon: 'error',
        confirmButtonColor: '#d33',
        confirmButtonText: 'Back to Login'
    }).then(() => {
        window.location = 'login.php';
    });
    </script></body></html>";
    exit();

}

?>