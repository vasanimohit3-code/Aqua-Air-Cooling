<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();

$page_title = 'Total Revenue';
$active_page = 'total_revenue';

$search = '';

$sql = "
SELECT *
FROM bookings
WHERE status IN ('Approved', 'Completed')
";

if(isset($_GET['search']) && $_GET['search']!="")
{
    $search = mysqli_real_escape_string($conn,$_GET['search']);

    $sql .= "
    AND (
        first_name LIKE '%$search%'
        OR last_name LIKE '%$search%'
        OR mobile LIKE '%$search%'
        OR service_type LIKE '%$search%'
        OR company_type LIKE '%$search%'
    )";
}

$sql .= " ORDER BY id DESC";

$result = mysqli_query($conn,$sql);

$totalRevenue = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT SUM(CASE WHEN final_price IS NOT NULL AND final_price > 0 THEN final_price ELSE price END) total
FROM bookings
WHERE status IN ('Approved', 'Completed')
"));

$grandTotal = $totalRevenue['total'];

if($grandTotal=="")
{
    $grandTotal=0;
}

require_once __DIR__.'/includes/header.php';
?>

<style>
@media print{
.no-print{
display:none!important;
}

.card{
border:none;
box-shadow:none;
}
}
</style>

<div class="card">

<div class="card-header bg-danger">

<div class="d-flex justify-content-between align-items-center">

<h3 class="card-title mb-0">

<i class="fas fa-indian-rupee-sign"></i>

Total Revenue

</h3>

<div>

<a href="revenue_pdf.php" class="btn btn-light btn-sm no-print">
<i class="fas fa-file-pdf"></i>
PDF
</a>

<button onclick="window.print()" class="btn btn-warning btn-sm no-print">
<i class="fas fa-print"></i>
Print
</button>

</div>

</div>

</div>

<div class="card-body">

<form method="GET" class="row mb-3 no-print">

<div class="col-md-10">

<input
type="text"
name="search"
class="form-control"
placeholder="Search Customer..."
value="<?php echo htmlspecialchars($search); ?>">

</div>

<div class="col-md-2">

<button class="btn btn-primary btn-block">

Search

</button>

</div>

</form>

<div class="table-responsive">

<table class="table table-bordered table-hover text-center">

<thead class="thead-dark">

<tr>

<th>ID</th>
<th>Customer</th>
<th>Mobile</th>
<th>Service</th>
<th>AC Brand</th>
<th>Original Parts</th>
<th>Price</th>
<th>Visit Date</th>
<th>Booking Date</th>
</tr>
</thead>
<tbody>
<?php
if($result && mysqli_num_rows($result)>0)
{
    while($row=mysqli_fetch_assoc($result))
    {
?>
<tr>
<td><?php echo $row['id']; ?></td>
<td>
<?php echo htmlspecialchars($row['first_name']." ".$row['last_name']); ?>
</td>
<td><?php echo htmlspecialchars($row['mobile']); ?></td>
<td><?php echo htmlspecialchars($row['service_type']); ?></td>
<td><span class="badge badge-info px-2 py-1"><?php echo htmlspecialchars($row['company_type']); ?></span></td>
<td>
<?php if (!empty($row['original_part']) && $row['original_part'] !== 'None (Service Only)' && $row['original_part'] !== 'None'): ?>
    <span class="badge badge-danger px-2 py-1"><i class="fas fa-cogs mr-1"></i><?php echo htmlspecialchars($row['original_part']); ?></span>
<?php else: ?>
    <span class="text-muted small">None (Service Only)</span>
<?php endif; ?>
</td>

<td>
<?php
if (!empty($row['coupon_code'])) {
    $final = isset($row['final_price']) && $row['final_price'] > 0 ? $row['final_price'] : ($row['price'] - ($row['discount_amount'] ?? 0));
    echo '<b class="text-success">₹ ' . number_format($final, 2) . '</b><br>';
    echo '<small class="text-muted"><del>₹ ' . number_format($row['price'], 2) . '</del></small><br>';
    echo '<span class="badge badge-success" title="Coupon: ' . htmlspecialchars($row['coupon_code']) . '"><i class="fas fa-tag"></i> ' . htmlspecialchars($row['coupon_code']) . ' (' . htmlspecialchars($row['discount_percent']) . '%)</span>';
} else {
    echo '<b class="text-success">₹ ' . number_format($row['price'], 2) . '</b>';
}
?>
</td>

<td>
<?php echo date("d-m-Y",strtotime($row['visit_date'])); ?>
</td>

<td>
<?php echo date("d-m-Y",strtotime($row['created_at'])); ?>
</td>

</tr>

<?php
    }
}
else
{
?>

<tr>

<td colspan="8" class="text-center text-danger">

No Revenue Found

</td>

</tr>

<?php
}
?>

</tbody>

</table>

</div>

<hr>

<div class="row">

<div class="col-md-6">

<h4>

Grand Total Revenue :

</h4>

</div>

<div class="col-md-6 text-end">

<h3 class="text-success">

₹ <?php echo number_format($grandTotal); ?>

</h3>

</div>

</div>

</div>

<div class="card-footer no-print">

<a href="dashboard.php" class="btn btn-secondary">

<i class="fas fa-arrow-left"></i>

Back

</a>

</div>

</div>

<?php require_once __DIR__.'/includes/footer.php'; ?>