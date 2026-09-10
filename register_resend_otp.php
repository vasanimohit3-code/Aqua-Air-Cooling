<?php

session_start();

if(!isset($_SESSION['register_email']))
{
    header("Location: login.php");
    exit();
}

$_SESSION['register_otp'] = rand(100000,999999);
$_SESSION['register_otp_time'] = time();

header("Location: register_send_otp.php");
exit();

?>