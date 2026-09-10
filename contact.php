<?php

require_once 'includes/config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fetch Contact Information & Settings from Database
$contact_data = [
    'page_heading' => 'Contact Us',
    'page_subheading' => 'We are available for AC Installation, Repair & Maintenance Services.',
    'address' => "123 Main Street,\nRajkot, Gujarat",
    'phone' => '+91 6354911971',
    'email' => 'aquaaircoolling@gmail.com',
    'working_hours' => "Monday - Saturday\n8:00 AM - 8:00 PM",
    'services_text' => 'AC Installation, Repair, Maintenance, Gas Filling & General AC Service.',
    'whatsapp_number' => '916354911971'
];

$c_query = mysqli_query($conn, "SELECT * FROM contact_info WHERE id = 1");
if ($c_query && mysqli_num_rows($c_query) > 0) {
    $row = mysqli_fetch_assoc($c_query);
    foreach ($row as $k => $v) {
        if (!empty($v)) {
            $contact_data[$k] = $v;
        }
    }
}

// Fetch Active Services for Contact Form Dropdown
$contact_services = [];
$cs_query = mysqli_query($conn, "SELECT name FROM services WHERE is_active = 1 ORDER BY display_order ASC, id ASC");
if ($cs_query && mysqli_num_rows($cs_query) > 0) {
    while ($cs_row = mysqli_fetch_assoc($cs_query)) {
        $contact_services[] = $cs_row['name'];
    }
} else {
    $contact_services = ['AC Installation', 'AC Repair', 'AC Maintenance', 'AC Gas Refilling', 'AC Cleaning', 'AC Stand', 'AC Uninstallation'];
}
?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="utf-8">

    <title>Contact Us - Aqua Air Cooling</title>

    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <meta content="Aqua Air Cooling Contact Us" name="keywords">

    <meta
        content="Contact Aqua Air Cooling for AC installation, repair, service and maintenance."
        name="description"
    >


    <!-- Favicon -->

    <link href="img/favicon.ico" rel="icon">


    <!-- Google Web Fonts -->

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;600;800&family=Roboto:wght@400;500;700&display=swap"
        rel="stylesheet"
    >


    <!-- Font Awesome -->

    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css"
        rel="stylesheet"
    >


    <!-- Bootstrap Icons -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css"
        rel="stylesheet"
    >


    <!-- Animation -->

    <link
        href="lib/animate/animate.min.css"
        rel="stylesheet"
    >


    <!-- Bootstrap -->

    <link
        href="css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Main CSS -->

    <link
        href="css/style.css"
        rel="stylesheet"
    >


    <!-- Contact Page CSS -->

    <style>

        /* =========================================
           CONTACT SECTION
        ========================================= */

        .contact-section {
            padding: 80px 0;
            background: #f8f9fa;
        }


        /* =========================================
           PAGE TITLE
        ========================================= */

        .contact-title h1 {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .contact-title p {
            color: #666;
            font-size: 16px;
        }


        /* =========================================
           CONTACT INFORMATION BOX
        ========================================= */

        .contact-info-box {

            background: #ffffff;

            padding: 35px;

            border-radius: 15px;

            height: 100%;

            box-shadow:
                0 8px 30px rgba(0, 0, 0, 0.08);

        }


        .contact-info-box h3 {

            font-size: 25px;

            font-weight: 700;

            color: #0d6efd;

            margin-bottom: 30px;

        }


        /* =========================================
           INFORMATION ITEM
        ========================================= */

        .contact-info-item {

            display: flex;

            align-items: flex-start;

            margin-bottom: 28px;

        }


        .contact-info-icon {

            width: 48px;

            height: 48px;

            min-width: 48px;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #eaf3ff;

            color: #0d6efd;

            border-radius: 10px;

            margin-right: 15px;

            font-size: 18px;

        }


        .contact-info-content h5 {

            margin: 0 0 5px 0;

            font-size: 16px;

            font-weight: 700;

            color: #222;

        }


        .contact-info-content p {

            margin: 0;

            color: #666;

            line-height: 1.6;

            font-size: 14px;

        }


        .contact-info-content a {

            color: #666;

            text-decoration: none;

        }


        .contact-info-content a:hover {

            color: #0d6efd;

        }


        /* =========================================
           CONTACT FORM BOX
        ========================================= */

        .contact-form-box {

            background: #ffffff;

            padding: 35px;

            border-radius: 15px;

            box-shadow:
                0 8px 30px rgba(0, 0, 0, 0.08);

        }


        .contact-form-box h3 {

            font-size: 25px;

            font-weight: 700;

            color: #0d6efd;

            margin-bottom: 8px;

        }


        .contact-form-box .form-description {

            color: #777;

            font-size: 14px;

            margin-bottom: 25px;

        }


        /* =========================================
           FORM INPUT
        ========================================= */

        .contact-form-box .form-control,

        .contact-form-box .form-select {

            height: 55px;

            border: 1px solid #ddd;

            border-radius: 8px;

            padding-left: 18px;

            color: #333;

            font-size: 14px;

            box-shadow: none;

        }


        .contact-form-box textarea.form-control {

            height: 130px;

            padding-top: 15px;

            resize: vertical;

        }


        .contact-form-box .form-control:focus,

        .contact-form-box .form-select:focus {

            border-color: #0d6efd;

            box-shadow:
                0 0 0 0.15rem rgba(13, 110, 253, 0.10);

        }


        .contact-form-box .form-control::placeholder {

            color: #999;

        }


        /* =========================================
           WHATSAPP BUTTON
        ========================================= */

        .whatsapp-button {

            width: 100%;

            height: 55px;

            border: none;

            border-radius: 8px;

            background: #25D366;

            color: #ffffff;

            font-size: 16px;

            font-weight: 700;

            transition: all 0.3s ease;

        }


        .whatsapp-button:hover {

            background: #1ebe5d;

            color: #ffffff;

            transform: translateY(-2px);

            box-shadow:
                0 6px 15px rgba(37, 211, 102, 0.25);

        }


        .whatsapp-button i {

            font-size: 21px;

            margin-right: 8px;

        }


        /* =========================================
           MOBILE RESPONSIVE
        ========================================= */

        @media (max-width: 991px) {

            .contact-section {

                padding: 60px 0;

            }

            .contact-title h1 {

                font-size: 34px;

            }

        }


        @media (max-width: 575px) {

            .contact-section {

                padding: 45px 0;

            }

            .contact-info-box,

            .contact-form-box {

                padding: 25px 20px;

            }

            .contact-title h1 {

                font-size: clamp(24px, 6vw, 30px);

            }

            .contact-form-box .form-control,
            .contact-form-box .form-select {
                font-size: 16px !important;
                height: 48px;
            }

        }

    </style>

</head>

<body>

<?php include 'includes/navbar.php'; ?>
<?php include 'includes/topbar.php'; ?>


<!-- =========================================
     CONTACT SECTION START
========================================= -->

<section class="contact-section">

    <div class="container">


        <!-- PAGE TITLE -->

        <div class="contact-title text-center mb-5">

            <h1 class="text-primary">
                <?php echo htmlspecialchars($contact_data['page_heading']); ?>
            </h1>

            <p>
                <?php echo htmlspecialchars($contact_data['page_subheading']); ?>
            </p>

        </div>


        <div class="row g-5 align-items-stretch">


            <!-- =========================================
                 CONTACT INFORMATION
            ========================================== -->

            <div class="col-lg-5">

                <div class="contact-info-box">

                    <h3>
                        Contact Information
                    </h3>


                    <!-- ADDRESS -->

                    <div class="contact-info-item">

                        <div class="contact-info-icon">

                            <i class="fas fa-map-marker-alt"></i>

                        </div>

                        <div class="contact-info-content">

                            <h5>
                                Address
                            </h5>

                            <p>
                                <?php echo nl2br(htmlspecialchars($contact_data['address'])); ?>
                            </p>

                        </div>

                    </div>


                    <!-- PHONE -->

                    <div class="contact-info-item">

                        <div class="contact-info-icon">

                            <i class="fas fa-phone"></i>

                        </div>

                        <div class="contact-info-content">

                            <h5>
                                Phone
                            </h5>

                            <p>

                                <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $contact_data['phone']); ?>">
                                    <?php echo htmlspecialchars($contact_data['phone']); ?>
                                </a>

                            </p>

                        </div>

                    </div>


                    <!-- EMAIL -->

                    <div class="contact-info-item">

                        <div class="contact-info-icon">

                            <i class="fas fa-envelope"></i>

                        </div>

                        <div class="contact-info-content">

                            <h5>
                                Email
                            </h5>

                            <p>

                                <a href="mailto:<?php echo htmlspecialchars($contact_data['email']); ?>">
                                    <?php echo htmlspecialchars($contact_data['email']); ?>
                                </a>

                            </p>

                        </div>

                    </div>


                    <!-- WORKING HOURS -->

                    <div class="contact-info-item">

                        <div class="contact-info-icon">

                            <i class="fas fa-clock"></i>

                        </div>

                        <div class="contact-info-content">

                            <h5>
                                Working Hours
                            </h5>

                            <p>
                                <?php echo nl2br(htmlspecialchars($contact_data['working_hours'])); ?>
                            </p>

                        </div>

                    </div>


                    <!-- SERVICE INFORMATION -->

                    <div class="contact-info-item mb-0">

                        <div class="contact-info-icon">

                            <i class="fas fa-tools"></i>

                        </div>

                        <div class="contact-info-content">

                            <h5>
                                Services
                            </h5>

                            <p>
                                <?php echo nl2br(htmlspecialchars($contact_data['services_text'])); ?>
                            </p>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =========================================
                 CONTACT FORM
            ========================================== -->

            <div class="col-lg-7">

                <div class="contact-form-box">

                    <h3>
                        Send Us a Message
                    </h3>

                    <p class="form-description">
                        Fill in the details below and contact us directly on WhatsApp.
                    </p>


                    <form id="whatsappForm">


                        <div class="row g-3">


                            <!-- FIRST NAME -->

                            <div class="col-md-6">

                                <input
                                    type="text"
                                    id="first_name"
                                    class="form-control"
                                    placeholder="First Name"
                                    autocomplete="given-name"
                                    required
                                >

                            </div>


                            <!-- LAST NAME -->

                            <div class="col-md-6">

                                <input
                                    type="text"
                                    id="last_name"
                                    class="form-control"
                                    placeholder="Last Name"
                                    autocomplete="family-name"
                                    required
                                >

                            </div>


                            <!-- MOBILE -->

                            <div class="col-md-6">

                                <input
                                    type="tel"
                                    id="mobile"
                                    class="form-control"
                                    placeholder="Mobile Number"
                                    maxlength="10"
                                    pattern="[0-9]{10}"
                                    autocomplete="tel"
                                    required
                                >

                            </div>


                            <!-- EMAIL -->

                            <div class="col-md-6">

                                <input
                                    type="email"
                                    id="email"
                                    class="form-control"
                                    placeholder="Email Address"
                                    autocomplete="email"
                                    required
                                >

                            </div>


                            <!-- ADDRESS -->

                            <div class="col-12">

                                <input
                                    type="text"
                                    id="address"
                                    class="form-control"
                                    placeholder="Address"
                                    autocomplete="street-address"
                                    required
                                >

                            </div>


                            <!-- SERVICE TYPE -->

                            <div class="col-md-6">

                                <select
                                    id="service_type"
                                    class="form-select"
                                    required
                                >

                                    <option
                                        value=""
                                        selected
                                        disabled
                                    >
                                        Select Service Type
                                    </option>

                                    <?php foreach ($contact_services as $srv): ?>
                                        <option value="<?php echo htmlspecialchars($srv); ?>">
                                            <?php echo htmlspecialchars($srv); ?>
                                        </option>
                                    <?php endforeach; ?>

                                    <option value="Other">
                                        Other
                                    </option>

                                </select>

                            </div>


                            <!-- COMPANY TYPE -->

                            <div class="col-md-6">

                                <select
                                    id="company_type"
                                    class="form-select"
                                    required
                                >

                                    <option
                                        value=""
                                        selected
                                        disabled
                                    >
                                        Select Company Type
                                    </option>

                                    <option value="Residential">
                                        Residential
                                    </option>

                                    <option value="Commercial">
                                        Commercial
                                    </option>

                                    <option value="Office">
                                        Office
                                    </option>

                                    <option value="Shop">
                                        Shop
                                    </option>

                                    <option value="Industrial">
                                        Industrial
                                    </option>

                                    <option value="Other">
                                        Other
                                    </option>

                                </select>

                            </div>


                            <!-- MESSAGE -->

                            <div class="col-12">

                                <textarea
                                    id="message"
                                    class="form-control"
                                    placeholder="Write your message..."
                                    required
                                ></textarea>

                            </div>


                            <!-- WHATSAPP BUTTON -->

                            <div class="col-12">

                                <button
                                    type="submit"
                                    class="whatsapp-button"
                                >

                                    <i class="fab fa-whatsapp"></i>

                                    Send on WhatsApp

                                </button>

                            </div>


                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- =========================================
     CONTACT SECTION END
========================================= -->


<?php include 'includes/footer.php'; ?>


<!-- =========================================
     WHATSAPP DYNAMIC SCRIPT
========================================= -->

<script>

document
    .getElementById("whatsappForm")
    .addEventListener("submit", function(event) {

        event.preventDefault();


        /* =====================================
           GET FORM VALUES
        ===================================== */

        const firstName =
            document
                .getElementById("first_name")
                .value
                .trim();


        const lastName =
            document
                .getElementById("last_name")
                .value
                .trim();


        const mobile =
            document
                .getElementById("mobile")
                .value
                .trim();


        const email =
            document
                .getElementById("email")
                .value
                .trim();


        const address =
            document
                .getElementById("address")
                .value
                .trim();


        const serviceType =
            document
                .getElementById("service_type")
                .value;


        const companyType =
            document
                .getElementById("company_type")
                .value;


        const message =
            document
                .getElementById("message")
                .value
                .trim();


        /* =====================================
           MOBILE VALIDATION
        ===================================== */

        const mobilePattern = /^[0-9]{10}$/;

        if (!mobilePattern.test(mobile)) {

            if (typeof Swal === 'function') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Mobile Number',
                    text: 'Please enter a valid 10-digit mobile number.',
                    confirmButtonColor: '#0d6efd'
                });
            } else {
                alert("Please enter a valid 10-digit mobile number.");
            }

            document
                .getElementById("mobile")
                .focus();

            return;

        }


        /* =====================================
           FULL NAME
        ===================================== */

        const fullName =
            firstName + " " + lastName;


        /* =====================================
           YOUR WHATSAPP NUMBER
        ===================================== */

        const whatsappNumber =
            "<?php echo addslashes(preg_replace('/[^0-9]/', '', $contact_data['whatsapp_number'])); ?>";


        /* =====================================
           DYNAMIC WHATSAPP MESSAGE
        ===================================== */

        const whatsappMessage =

`*HELLO , AQUA AIR COOLING*,

*NEW SERVICE ENQUIRY*

━━━━━━━━━━━━━━━━━━━━

*CUSTOMER DETAILS*

Name: ${fullName}
Mobile: ${mobile}
Email: ${email}
Address: ${address}

━━━━━━━━━━━━━━━━━━━━

*SERVICE DETAILS*

Service Type: ${serviceType}
Company Type: ${companyType}

━━━━━━━━━━━━━━━━━━━━

*CUSTOMER MESSAGE*

${message}

━━━━━━━━━━━━━━━━━━━━

Thank You.
AQUA AIR COOLING .`;


        /* =====================================
           CREATE WHATSAPP URL
        ===================================== */

        const whatsappURL =
            "https://wa.me/" +
            whatsappNumber +
            "?text=" +
            encodeURIComponent(whatsappMessage);


        if (typeof confetti === 'function') {
            confetti({ particleCount: 100, spread: 70, origin: { y: 0.6 } });
        }

        Swal.fire({
            icon: 'success',
            title: '💬 Enquiry Ready!',
            html: '<p style="color:#475569; font-size:14.5px;">Redirecting you directly to our official WhatsApp support with your enquiry details...</p>',
            confirmButtonColor: '#25D366',
            confirmButtonText: '<i class="fab fa-whatsapp me-1"></i> Open WhatsApp',
            timer: 3000,
            timerProgressBar: true
        }).then(() => {
            window.open(whatsappURL, "_blank");
        });

    });

</script>


<!-- Bootstrap JS -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>