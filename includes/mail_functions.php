<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendApproveMail($to, $customer, $booking_id, $service, $company, $price, $visit_date, $visit_time, $coupon_code = '', $discount_percent = 0, $discount_amount = 0, $final_price = 0, $original_part = 'None (Service Only)')
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'aquaaircoolling@gmail.com';
        $mail->Password = 'gbph snfb faok htrb';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('aquaaircoolling@gmail.com', 'Aqua Air Cooling');
        $mail->addAddress($to);
        $mail->isHTML(true);

        $mail->Subject = "Booking Approved | Aqua Air Cooling - Confirmed Schedule";

        $price_rows = "";
        if (!empty($coupon_code) && (float)$discount_amount > 0) {
            $formatted_orig = number_format((float)$price, 2);
            $formatted_disc = number_format((float)$discount_amount, 2);
            $formatted_final = number_format((float)$final_price, 2);
            $price_rows = "
            <tr>
                <td style='padding: 10px; border: 1px solid #ddd; background: #f8f9fa; font-weight: bold;'>Original Price</td>
                <td style='padding: 10px; border: 1px solid #ddd;'><del>₹ $formatted_orig</del></td>
            </tr>
            <tr>
                <td style='padding: 10px; border: 1px solid #ddd; background: #f8f9fa; font-weight: bold;'>Applied Coupon</td>
                <td style='padding: 10px; border: 1px solid #ddd;'><span style='background: #28a745; color: #ffffff; padding: 3px 8px; border-radius: 4px; font-weight: bold;'>$coupon_code</span></td>
            </tr>
            <tr>
                <td style='padding: 10px; border: 1px solid #ddd; background: #f8f9fa; font-weight: bold;'>Discount ($discount_percent%)</td>
                <td style='padding: 10px; border: 1px solid #ddd; color: #dc3545; font-weight: bold;'>- ₹ $formatted_disc</td>
            </tr>
            <tr>
                <td style='padding: 10px; border: 1px solid #ddd; background: #e8f5e9; font-weight: bold; color: #155724;'>Final Service Charge</td>
                <td style='padding: 10px; border: 1px solid #ddd; background: #e8f5e9; color: #198754; font-size: 16px; font-weight: bold;'>₹ $formatted_final</td>
            </tr>";
        } else {
            $formatted_orig = number_format((float)$price, 2);
            $price_rows = "
            <tr>
                <td style='padding: 10px; border: 1px solid #ddd; background: #f8f9fa; font-weight: bold;'>Service Charge</td>
                <td style='padding: 10px; border: 1px solid #ddd; font-weight: bold; color: #198754; font-size: 15px;'>₹ $formatted_orig</td>
            </tr>";
        }

        $part_display = (!empty($original_part) && $original_part !== 'None (Service Only)' && $original_part !== 'None / General Service Only' && $original_part !== 'None') 
            ? "<span style='color: #dc3545; font-weight: bold;'>⚙️ " . htmlspecialchars($original_part) . "</span>"
            : "<span style='color: #6c757d;'>None (Service Only)</span>";

        $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px; background: #ffffff;'>
            <div style='text-align: center; padding-bottom: 15px; border-bottom: 2px solid #0d6efd;'>
                <h2 style='color: #0d6efd; margin: 0;'>❄️ Aqua Air Cooling</h2>
                <p style='color: #666; margin: 5px 0 0;'>Professional Doorstep AC Services</p>
            </div>
            
            <p style='font-size: 16px; margin-top: 20px;'>Dear <b>" . htmlspecialchars($customer) . "</b>,</p>
            <p style='color: #28a745; font-size: 15px; font-weight: bold;'>🎉 Great news! Your service booking has been APPROVED.</p>
            <p style='color: #555;'>Here are the complete details of your confirmed booking:</p>
            
            <table style='width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px;'>
                <tr>
                    <td style='padding: 10px; border: 1px solid #ddd; background: #f8f9fa; font-weight: bold; width: 40%;'>Booking ID</td>
                    <td style='padding: 10px; border: 1px solid #ddd; font-weight: bold; color: #0d6efd;'>#$booking_id</td>
                </tr>
                <tr>
                    <td style='padding: 10px; border: 1px solid #ddd; background: #f8f9fa; font-weight: bold;'>Service Type</td>
                    <td style='padding: 10px; border: 1px solid #ddd; font-weight: bold;'>" . htmlspecialchars($service) . "</td>
                </tr>
                <tr>
                    <td style='padding: 10px; border: 1px solid #ddd; background: #f8f9fa; font-weight: bold;'>AC Brand / Company</td>
                    <td style='padding: 10px; border: 1px solid #ddd; font-weight: bold; color: #0d6efd;'>" . htmlspecialchars($company) . "</td>
                </tr>
                <tr>
                    <td style='padding: 10px; border: 1px solid #ddd; background: #f8f9fa; font-weight: bold;'>AC Original Spare Part</td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>$part_display</td>
                </tr>
                $price_rows
                <tr>
                    <td style='padding: 10px; border: 1px solid #ddd; background: #f8f9fa; font-weight: bold;'>Scheduled Visit Date</td>
                    <td style='padding: 10px; border: 1px solid #ddd; font-weight: bold; color: #0d6efd;'>$visit_date</td>
                </tr>
                <tr>
                    <td style='padding: 10px; border: 1px solid #ddd; background: #f8f9fa; font-weight: bold;'>Scheduled Visit Time</td>
                    <td style='padding: 10px; border: 1px solid #ddd; font-weight: bold; color: #0d6efd;'>$visit_time</td>
                </tr>
            </table>
            
            <div style='background: #f1f8ff; border-left: 4px solid #0d6efd; padding: 12px; margin: 20px 0; border-radius: 4px;'>
                <p style='margin: 0; font-size: 13px; color: #333;'>
                    Our verified expert technician will visit your doorstep on the scheduled date and time. Please keep the AC unit area accessible.
                </p>
            </div>
            
            <hr style='border: none; border-top: 1px solid #eee; margin: 25px 0;'>
            
            <div style='text-align: center; color: #888; font-size: 12px;'>
                <p style='margin: 0;'>Thank you for choosing <b>Aqua Air Cooling</b>.</p>
                <p style='margin: 5px 0 0;'>Need help? Contact us at: <a href='mailto:aquaaircoolling@gmail.com' style='color: #0d6efd;'>aquaaircoolling@gmail.com</a> | +91 6354911971</p>
            </div>
        </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function sendRejectMail($to, $customer, $booking_id, $service, $company, $original_part = 'None (Service Only)')
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'aquaaircoolling@gmail.com';
        $mail->Password = 'gbph snfb faok htrb';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('aquaaircoolling@gmail.com', 'Aqua Air Cooling');
        $mail->addAddress($to);
        $mail->isHTML(true);

        $mail->Subject = "Booking Update | Aqua Air Cooling";

        $part_display = (!empty($original_part) && $original_part !== 'None (Service Only)' && $original_part !== 'None / General Service Only' && $original_part !== 'None') 
            ? htmlspecialchars($original_part)
            : "None (Service Only)";

        $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px; background: #ffffff;'>
            <div style='text-align: center; padding-bottom: 15px; border-bottom: 2px solid #dc3545;'>
                <h2 style='color: #dc3545; margin: 0;'>❄️ Aqua Air Cooling</h2>
                <p style='color: #666; margin: 5px 0 0;'>Booking Status Update</p>
            </div>
            
            <p style='font-size: 16px; margin-top: 20px;'>Dear <b>" . htmlspecialchars($customer) . "</b>,</p>
            <p style='color: #dc3545; font-size: 15px; font-weight: bold;'>We regret to inform you that your booking request has been <b>Rejected</b>.</p>
            
            <table style='width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px;'>
                <tr>
                    <td style='padding: 10px; border: 1px solid #ddd; background: #f8f9fa; font-weight: bold; width: 40%;'>Booking ID</td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>#$booking_id</td>
                </tr>
                <tr>
                    <td style='padding: 10px; border: 1px solid #ddd; background: #f8f9fa; font-weight: bold;'>Service</td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($service) . "</td>
                </tr>
                <tr>
                    <td style='padding: 10px; border: 1px solid #ddd; background: #f8f9fa; font-weight: bold;'>AC Brand / Company</td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($company) . "</td>
                </tr>
                <tr>
                    <td style='padding: 10px; border: 1px solid #ddd; background: #f8f9fa; font-weight: bold;'>Original Part</td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>$part_display</td>
                </tr>
            </table>

            <p style='color: #555; font-size: 13px;'>If you have any questions or would like to reschedule, please feel free to reach out to us.</p>
            <p style='text-align: center; color: #888; font-size: 12px; margin-top: 20px;'>Aqua Air Cooling Support Team</p>
        </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function sendCouponMail($to, $customer_name, $coupon_code, $discount_percent, $click_time = null)
{
    date_default_timezone_set('Asia/Kolkata');
    $created_ts = $click_time ? $click_time : time();
    $expires_ts = $created_ts + 3600;

    $issued_at_str = date('h:i:s A', $created_ts);
    $expires_at_str = date('h:i:s A', $expires_ts);

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'aquaaircoolling@gmail.com';
        $mail->Password = 'gbph snfb faok htrb';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('aquaaircoolling@gmail.com', 'Aqua Air Cooling');
        $mail->addAddress($to);
        $mail->isHTML(true);

        $mail->Subject = "Your Exclusive $discount_percent% OFF Coupon Code | Aqua Air Cooling";

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host_name = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $subfolder = (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'Project_Mohit_Patel') !== false) ? '/Project_Mohit_Patel/Aqua%20Air%20Cooling/booking.php' : '/booking.php';
        $dynamic_booking_url = $protocol . $host_name . $subfolder;

        $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 25px; border: 1px solid #e2e8f0; border-radius: 16px; background-color: #ffffff;'>
            <div style='text-align: center; margin-bottom: 20px;'>
                <h2 style='color: #0d6efd; margin: 0;'>❄️ Aqua Air Cooling</h2>
                <p style='color: #64748b; margin-top: 5px; font-size: 14px;'>Professional AC Service At Your Doorstep</p>
            </div>
            
            <p>Dear <b>" . htmlspecialchars($customer_name) . "</b>,</p>
            <p>Here is your brand new discount coupon requested at <strong>" . $issued_at_str . "</strong>!</p>
            
            <div style='background: linear-gradient(135deg, #0d6efd, #0044ab); color: #ffffff; padding: 25px; text-align: center; border-radius: 16px; margin: 25px 0; box-shadow: 0 10px 25px rgba(13,110,253,0.2);'>
                <span style='background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 20px; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;'>🎁 Mystery Coupon</span>
                <h1 style='margin: 10px 0; font-size: 36px; color: #FFD54F; font-weight: 800;'>" . $discount_percent . "% OFF</h1>
                <p style='margin: 5px 0 15px; font-size: 14px; opacity: 0.9;'>Use this code at booking checkout:</p>
                <div style='background: #ffffff; color: #0d6efd; font-size: 24px; font-weight: 800; letter-spacing: 3px; padding: 12px 24px; display: inline-block; border-radius: 10px; border: 2px dashed #0d6efd;'>" . $coupon_code . "</div>
            </div>

            <!-- 60-Minute Live Countdown Timer Box -->
            <div style='background: #fff8e1; border: 2px solid #f59e0b; padding: 20px; border-radius: 14px; margin-top: 20px; text-align: center;'>
                <div style='color: #d97706; font-size: 16px; font-weight: 800; margin-bottom: 8px;'>⏱️ 60-MINUTE COUNTDOWN STARTED</div>
                
                <p style='margin: 8px 0 0 0; color: #78350f; font-size: 14px; line-height: 1.6;'>
                    📅 <strong>Button Clicked / Issued At:</strong> <span style='color: #0d6efd; font-weight: bold;'>" . $issued_at_str . "</span><br>
                    ⏳ <strong style='color: #dc2626;'>Exact Expiration Time:</strong> <span style='color: #dc2626; font-size: 16px; font-weight: 800;'>" . $expires_at_str . "</span><br>
                    <small style='color: #92400e;'>(Strict 60 Minutes Countdown from your click time)</small>
                </p>
            </div>

            <p style='color: #475569; font-size: 13px; line-height: 1.6; margin-top: 20px;'>
                <strong>How to use:</strong> Copy the code above, paste it into the <strong>Promo Code</strong> input box on the <a href='" . htmlspecialchars($dynamic_booking_url) . "' style='color: #0d6efd; font-weight: bold;'>Booking Page</a>, and click <strong>Apply Code</strong> to get instant <strong>" . $discount_percent . "% OFF</strong> on your service!
            </p>

            <hr style='border: none; border-top: 1px solid #f1f5f9; margin: 20px 0;'>
            <p style='text-align: center; color: #94a3b8; font-size: 12px;'>Thank you for choosing <b>Aqua Air Cooling</b>.</p>
        </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
