<?php
require_once __DIR__ . '/includes/auth.php';
redirectIfLoggedIn();

$error = '';

if (isset($_POST['login'])) {

    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = $_POST['password'];

    $sql = "SELECT * FROM admin WHERE username='$username'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) == 1) {

        $admin = mysqli_fetch_assoc($result);

        if (password_verify($password, $admin['password'])) {

            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];

            header("Location: dashboard.php");
            exit();

        } else {

            $error = "Wrong Password.";

        }

    } else {

        $error = "Username Not Found.";

    }

}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Aqua Air Cooling | Admin Login</title>

<link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
<link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
<link rel="stylesheet" href="dist/css/adminlte.min.css">

<style>

body{

margin:0;
padding:0;
font-family:'Segoe UI',sans-serif;

background: url("../img/ap1.webp") center center no-repeat;
background-size:cover;

height:100vh;
overflow:hidden;

}

.overlay{

position:absolute;

top:0;
left:0;

width:100%;
height:100%;

background:rgba(9, 10, 10, 0.73);

}

.login-container{

position:absolute;

top:50%;
left:50%;

transform:translate(-50%,-50%);

width:390px;

z-index:10;

}

.login-card{

background:rgba(255,255,255,.15);

backdrop-filter:blur(15px);

border-radius:20px;

padding:35px;

box-shadow:0 10px 40px rgba(0,0,0,.45);

}

.logo{

text-align:center;

margin-bottom:20px;

}

.logo img{

width:90px;

margin-bottom:10px;

}

.logo h2{

color:#fff;

font-weight:bold;

margin:0;

}

.logo p{

color:#ddd;

}

.form-control{

height:48px;

border-radius:10px;

}

.input-group-text{

cursor:pointer;

}

.btn-login{

height:48px;

border-radius:10px;

font-size:18px;

font-weight:bold;

}

.alert{

border-radius:10px;

}

@media(max-width:450px){

.login-container{

width:95%;

}

}

</style>

</head>

<body>

<div class="overlay"></div>

<div class="login-container">

<div class="logo">

<h2>❄️</h2>

<h2><b>Aqua Air Cooling</b></h2>

<p>Admin Panel</p>

</div>

<div class="login-card">

<?php if($error!=""){ ?>

<div class="alert alert-danger">

<?php echo $error; ?>

</div>

<?php } ?>

<form method="POST">

<div class="form-group">

<label style="color:white;">Username</label>

<input
type="text"
name="username"
class="form-control"
placeholder="Enter Username"
required>

</div>

<div class="form-group">

<label style="color:white;">Password</label>

<div class="input-group">

<input
type="password"
name="password"
id="password"
class="form-control"
placeholder="Enter Password"
required>

<div class="input-group-append">

<div
class="input-group-text"
onclick="togglePassword()">

<i class="fas fa-eye" id="eye"></i>

</div>

</div>

</div>

</div>

<button
type="submit"
name="login"
class="btn btn-primary btn-block btn-login">

<i class="fas fa-sign-in-alt"></i>

Sign In

</button>

</form>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>

function togglePassword(){

var pass=document.getElementById("password");

var eye=document.getElementById("eye");

if(pass.type==="password"){

pass.type="text";

eye.classList.remove("fa-eye");

eye.classList.add("fa-eye-slash");

}
else{

pass.type="password";

eye.classList.remove("fa-eye-slash");

eye.classList.add("fa-eye");

}

}

<?php if($error!=""){ ?>
document.addEventListener("DOMContentLoaded", function() {
    Swal.fire({
        icon: 'error',
        title: 'Login Failed',
        text: <?php echo json_encode($error); ?>,
        confirmButtonColor: '#0d6efd'
    });
});
<?php } ?>

</script>

</body>

</html>