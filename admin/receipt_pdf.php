<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_GET['id'])) {
    die("Invalid Receipt");
}

$booking_id = (int)$_GET['id'];

$query = mysqli_query($conn, "
SELECT * FROM bookings
WHERE id='$booking_id'
");

if(mysqli_num_rows($query)==0){
    die("Receipt Not Found");
}

$row = mysqli_fetch_assoc($query);

$options = new Options();
$options->set('isRemoteEnabled', true);

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
<td><b style="color:#28a745;">' . htmlspecialchars($row['coupon_code']) . '</b></td>
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
font-size:14px;
}

h1{
text-align:center;
color:#007bff;
}

h3{
text-align:center;
margin-top:-10px;
}

table{
width:100%;
border-collapse:collapse;
margin-top:20px;
}

table th{
background:#007bff;
color:white;
padding:10px;
border:1px solid black;
text-align:left;
width:35%;
}

table td{
padding:10px;
border:1px solid black;
}

.footer{
margin-top:30px;
text-align:center;
font-size:13px;
}

</style>

</head>

<body>

<h1>Aqua Air Cooling</h1>

<h3>Booking Receipt</h3>

<table>

<tr>
<th>Booking ID</th>
<td>'.$row['id'].'</td>
</tr>

<tr>
<th>Customer Name</th>
<td>'.$row['first_name'].' '.$row['last_name'].'</td>
</tr>

<tr>
<th>Mobile</th>
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
<th>Service</th>
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
<th>Visit Date</th>
<td>'.$row['visit_date'].'</td>
</tr>

<tr>
<th>Visit Time</th>
<td>'.date("h:i A", strtotime($row['visit_time'])).'</td>
</tr>

<tr>
<th>Booking Date</th>
<td>'.date("d-m-Y h:i A",strtotime($row['created_at'])).'</td>
</tr>

<tr>
<th>Status</th>
<td>'.$row['status'].'</td>
</tr>

</table>

';
$html .= '

<div class="footer">

<p><b>Thank You For Choosing Aqua Air Cooling</b></p>


</div>

</body>

</html>

';

$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'portrait');

$dompdf->render();

$dompdf->stream(
    "Receipt_".$row['id'].".pdf",
    array(
        "Attachment" => true
    )
);

exit;