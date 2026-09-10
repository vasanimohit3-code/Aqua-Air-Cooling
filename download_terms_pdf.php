<?php
require_once 'includes/config.php';
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Ensure table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'terms_conditions'");
if (mysqli_num_rows($table_check) == 0) {
    die("Terms & Conditions table not found.");
}

// Fetch active terms
$terms_query = "SELECT * FROM terms_conditions WHERE is_active = 1 ORDER BY section_number ASC, id ASC";
$terms_result = mysqli_query($conn, $terms_query);

// Fetch last updated timestamp
$updated_res = mysqli_query($conn, "SELECT MAX(updated_at) as last_updated FROM terms_conditions WHERE is_active = 1");
$last_updated_row = mysqli_fetch_assoc($updated_res);
$last_updated = (!empty($last_updated_row['last_updated'])) ? date('F d, Y', strtotime($last_updated_row['last_updated'])) : date('F d, Y');

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);

$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page {
        margin: 25px 30px 25px 30px;
    }
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 11.5px;
        color: #222222;
        line-height: 1.55;
        margin: 0;
        padding: 0;
    }
    .header {
        text-align: center;
        border-bottom: 2px solid #0d6efd;
        padding-bottom: 12px;
        margin-bottom: 20px;
    }
    .header h1 {
        color: #06164f;
        font-size: 22px;
        margin: 0 0 4px 0;
        font-weight: bold;
    }
    .header h3 {
        color: #0d6efd;
        font-size: 14px;
        margin: 0 0 6px 0;
    }
    .header p {
        color: #666666;
        font-size: 10px;
        margin: 0;
    }
    .section {
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 1px dashed #e2e8f0;
    }
    .section-title {
        color: #06164f;
        font-size: 12.5px;
        font-weight: bold;
        margin-bottom: 4px;
    }
    .section-content {
        color: #334155;
        font-size: 11px;
        text-align: justify;
    }
    .contact-box {
        background-color: #f8fafc;
        border: 1px solid #cbd5e1;
        border-left: 4px solid #ff7f0e;
        padding: 10px 14px;
        margin-top: 20px;
        font-size: 10.5px;
        text-align: center;
    }
    .contact-box strong {
        color: #06164f;
    }
    .footer {
        margin-top: 20px;
        text-align: center;
        font-size: 9.5px;
        color: #64748b;
        border-top: 1px solid #e2e8f0;
        padding-top: 8px;
    }
</style>
</head>
<body>

<div class="header">
    <h1>Aqua Air Cooling</h1>
    <h3>Terms & Conditions</h3>
    <p>Official Service & Operating Policy | Last Updated: ' . htmlspecialchars($last_updated) . '</p>
</div>

<div class="terms-container">';

if ($terms_result && mysqli_num_rows($terms_result) > 0) {
    while ($term = mysqli_fetch_assoc($terms_result)) {
        $html .= '
        <div class="section">
            <div class="section-title">' . intval($term['section_number']) . '. ' . htmlspecialchars($term['title']) . '</div>
            <div class="section-content">' . nl2br(htmlspecialchars($term['content'])) . '</div>
        </div>';
    }
} else {
    $html .= '<p style="text-align:center; color:#999;">No Terms & Conditions available.</p>';
}

$iconLocation = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="#ff7f0e"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>');
$iconPhone = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="#25d366"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>');
$iconEmail = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="#0d6efd"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>');

$html .= '
</div>

<div class="contact-box">
    <div style="font-size: 11px; font-weight: bold; color: #06164f; margin-bottom: 5px;">
        Aqua Air Cooling Service Center
    </div>
    <div style="font-size: 10px; color: #334155;">
        <span><img src="' . $iconLocation . '" width="7" height="7" style="vertical-align: 0px; margin-right: 2px;"> <strong style="color: #ff7f0e;">Location:</strong> Rajkot, Gujarat, India</span>
        &nbsp;&nbsp;&bull;&nbsp;&nbsp;
        <span><img src="' . $iconPhone . '" width="7" height="7" style="vertical-align: 0px; margin-right: 2px;"> <strong style="color: #25d366;">Mobile:</strong> +91 6354911971</span>
        &nbsp;&nbsp;&bull;&nbsp;&nbsp;
        <span><img src="' . $iconEmail . '" width="7" height="7" style="vertical-align: 0px; margin-right: 2px;"> <strong style="color: #0d6efd;">Email:</strong> aquaaircooling@gmail.com</span>
    </div>
</div>

<div class="footer">
    © ' . date('Y') . ' Aqua Air Cooling. All Rights Reserved. Delivered by Aqua Group.
</div>

</body>
</html>
';

$dompdf->loadHtml($html);
$dompdf->setPaper("A4", "portrait");
$dompdf->render();

$dompdf->stream(
    "Aqua_Air_Cooling_Terms_and_Conditions.pdf",
    array(
        "Attachment" => true
    )
);
exit();
