<?php
require_once 'includes/config.php';
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if(session_status() == PHP_SESSION_NONE){
    session_start();
}

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

if(!isset($_GET['id'])){
    die("Invalid Receipt");
}

$user_id = $_SESSION['user_id'];
$booking_id = (int)$_GET['id'];

$query = "
SELECT *
FROM bookings
WHERE id='$booking_id'
AND user_id='$user_id'
";

$result = mysqli_query($conn,$query);

if(mysqli_num_rows($result)==0){
    die("Receipt Not Found");
}

$row = mysqli_fetch_assoc($result);

/* User Wise Booking Number */

$countQuery = "SELECT id
               FROM bookings
               WHERE user_id='$user_id'
               AND id < '$booking_id'";

$countResult = mysqli_query($conn, $countQuery);

$booking_no = mysqli_num_rows($countResult) + 1;

if ($row['status'] != "Approved" && $row['status'] != "Completed") {
    die("Receipt Available Only After Approval or Service Completion");
}

$options = new Options();
$options->set('isRemoteEnabled',true);

$dompdf = new Dompdf($options);

$price_html = "";
if (!empty($row['coupon_code'])) {
    $price_html = '
<tr>
<th>Original Service Charge</th>
<td>₹ ' . number_format($row['price'], 2) . '</td>
</tr>
<tr>
<th>Coupon Code</th>
<td><b style="color:#0d6efd;">' . htmlspecialchars($row['coupon_code']) . '</b></td>
</tr>
<tr>
<th>Discount (' . htmlspecialchars($row['discount_percent']) . '%)</th>
<td><span style="color:#dc3545;">- ₹ ' . number_format($row['discount_amount'], 2) . '</span></td>
</tr>
<tr>
<th>Final Service Charge</th>
<td><b>₹ ' . number_format($row['final_price'], 2) . '</b></td>
</tr>';
} else {
    $price_html = '
<tr>
<th>Service Charge</th>
<td>₹ ' . number_format($row['price'], 2) . '</td>
</tr>';
}

$html='

<!DOCTYPE html>

<html>

<head>

<style>

body{
font-family:DejaVu Sans;
font-size:13px;
}

h1{
text-align:center;
color:#0d6efd;
}

h3{
text-align:center;
margin-top:-8px;
}

table{
width:100%;
border-collapse:collapse;
margin-top:20px;
}

table th{
background:#0d6efd;
color:#fff;
padding:10px;
border:1px solid #000;
width:35%;
text-align:left;
}

table td{
padding:10px;
border:1px solid #000;
}

.footer{
margin-top:30px;
text-align:center;
font-size:12px;
color:#666;
}

</style>

</head>

<body>

<h1>Aqua Air Cooling</h1>

<h3>Booking Receipt</h3>

<table>

<tr>
<th>Booking ID</th>
<td>'.$booking_no.'</td>
</tr>

<tr>
<th>Customer Name</th>
<td>'.$row['first_name'].' '.$row['last_name'].'</td>
</tr>

<tr>
<th>Mobile Number</th>
<td>'.$row['mobile'].'</td>
</tr>

<tr>
<th>Email</th>
<td>'.$row['email'].'</td>
</tr>

<tr>
<th>Address</th>
<td>'.$row['address'].'</td>
</tr>

<tr>
<th>Service Type</th>
<td>'.$row['service_type'].'</td>
</tr>

<tr>
<th>AC Brand</th>
<td>'.htmlspecialchars($row['company_type']).'</td>
</tr>

<tr>
<th>Original Spare Part</th>
<td>'.(!empty($row['original_part']) && $row['original_part'] !== 'None (Service Only)' && $row['original_part'] !== 'None / General Service Only' && $row['original_part'] !== 'None' ? htmlspecialchars($row['original_part']) : 'None (Service Only)').'</td>
</tr>

' . $price_html . '

<tr>
<th>Status</th>
<td>'.$row['status'].'</td>
</tr>

<tr>
<th>Visit Date</th>
<td>'.$row['visit_date'].'</td>
</tr>

<tr>
<th>Visit Time</th>
<td>'.date("h:i A",strtotime($row['visit_time'])).'</td>
</tr>

<tr>
<th>Booking Date</th>
<td>'.date("d-m-Y h:i A",strtotime($row['created_at'])).'</td>
</tr>

</table>

<div class="footer">

Thank you for choosing <b>Aqua Air Cooling</b><br>


</div>

</body>

</html>

';

$dompdf->loadHtml($html);

$dompdf->setPaper("A4", "portrait");

$dompdf->render();

$dompdf->stream(
    "Receipt_".$row['id'].".pdf",
    array(
        "Attachment" => true
    )
);

exit();