<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

// Approved & Completed Bookings
$result = mysqli_query($conn, "
SELECT *
FROM bookings
WHERE status IN ('Approved', 'Completed')
ORDER BY id DESC
");

// Total Revenue
$total = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT SUM(CASE WHEN final_price IS NOT NULL AND final_price > 0 THEN final_price ELSE price END) AS total
FROM bookings
WHERE status IN ('Approved', 'Completed')
"));

$grandTotal = $total['total'];

if($grandTotal=="")
{
    $grandTotal=0;
}

$html='
<!DOCTYPE html>
<html>

<head>

<style>

body{
font-family:DejaVu Sans;
font-size:12px;
}

h1{
text-align:center;
color:#dc3545;
}

h3{
text-align:center;
}

table{
width:100%;
border-collapse:collapse;
margin-top:20px;
}

table th{
background:#dc3545;
color:#fff;
padding:8px;
border:1px solid #000;
}

table td{
padding:6px;
border:1px solid #000;
text-align:center;
}

.footer{
margin-top:20px;
text-align:right;
font-size:18px;
font-weight:bold;
}

</style>

</head>

<body>

<h1>Aqua Air Cooling</h1>

<h3>Total Revenue Report</h3>

<p>
<b>Date :</b> '.date("d-m-Y").'<br>
<b>Time :</b> '.date("h:i A").'
</p>

<table>

<tr>

<th>ID</th>
<th>Customer</th>
<th>Mobile</th>
<th>Service</th>
<th>Company</th>
<th>Price (Final)</th>
<th>Visit Date</th>

</tr>';

while($row=mysqli_fetch_assoc($result))
{
$displayPrice = isset($row['final_price']) && $row['final_price'] > 0 ? $row['final_price'] : $row['price'];
$couponBadge = !empty($row['coupon_code']) ? '<br><span style="color:#28a745;font-size:10px;">(' . htmlspecialchars($row['coupon_code']) . ' -' . htmlspecialchars($row['discount_percent']) . '%)</span>' : '';

$html.='

<tr>

<td>'.$row['id'].'</td>

<td>'.$row['first_name'].' '.$row['last_name'].'</td>

<td>'.$row['mobile'].'</td>

<td>'.$row['service_type'].'</td>

<td>'.$row['company_type'].'</td>

<td>₹ '.number_format($displayPrice, 2) . $couponBadge . '</td>

<td>'.date("d-m-Y",strtotime($row['visit_date'])).'</td>

</tr>

';
}

$html.='

</table>

<div class="footer">

Grand Total Revenue : ₹ '.number_format($grandTotal, 2).'

</div>

</body>

</html>';

$dompdf->loadHtml($html);

$dompdf->setPaper("A4","landscape");

$dompdf->render();

$dompdf->stream("Revenue_Report_".date("d-m-Y").".pdf",[
"Attachment"=>true
]);

exit();