<?php

/*
|--------------------------------------------------------------------------
| Betachem E-Commerce
| Contact Us
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| E-Commerce Base URL
|--------------------------------------------------------------------------
*/

$baseUrl = '/betachem/betachem_e-commerce';

$pageTitle = 'Contact Us';

$errors = [];
$success = false;


/*
|--------------------------------------------------------------------------
| Logged-In Customer
|--------------------------------------------------------------------------
*/

$userId = $_SESSION['user_id'] ?? null;


/*
|--------------------------------------------------------------------------
| Form Values
|--------------------------------------------------------------------------
*/

$firstName = trim($_POST['first_name'] ?? '');
$lastName  = trim($_POST['last_name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$phone     = trim($_POST['phone'] ?? '');
$subject   = trim($_POST['subject'] ?? '');
$message   = trim($_POST['message'] ?? '');


/*
|--------------------------------------------------------------------------
| Pre-Fill Logged-In Customer Information
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $userId) {

    $stmt = $conn->prepare("
        SELECT
            first_name,
            last_name,
            email,
            phone
        FROM users
        WHERE id = ?
        LIMIT 1
    ");

    if ($stmt) {

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $stmt->close();

        if ($user) {

            $firstName = $user['first_name'] ?? '';
            $lastName  = $user['last_name'] ?? '';
            $email     = $user['email'] ?? '';
            $phone     = $user['phone'] ?? '';

        }
    }
}


/*
|--------------------------------------------------------------------------
| Handle Contact Form
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |--------------------------------------------------------------------------
    | Validate First Name
    |--------------------------------------------------------------------------
    */

    if ($firstName === '') {

        $errors[] = 'Please enter your first name.';

    } elseif (strlen($firstName) > 100) {

        $errors[] = 'First name is too long.';

    }


    /*
    |--------------------------------------------------------------------------
    | Validate Last Name
    |--------------------------------------------------------------------------
    */

    if ($lastName === '') {

        $errors[] = 'Please enter your last name.';

    } elseif (strlen($lastName) > 100) {

        $errors[] = 'Last name is too long.';

    }


    /*
    |--------------------------------------------------------------------------
    | Validate Email
    |--------------------------------------------------------------------------
    */

    if ($email === '') {

        $errors[] = 'Please enter your email address.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errors[] = 'Please enter a valid email address.';

    } elseif (strlen($email) > 190) {

        $errors[] = 'Email address is too long.';

    }


    /*
    |--------------------------------------------------------------------------
    | Validate Phone
    |--------------------------------------------------------------------------
    */

    if (strlen($phone) > 50) {

        $errors[] = 'Phone number is too long.';

    }


    /*
    |--------------------------------------------------------------------------
    | Validate Subject
    |--------------------------------------------------------------------------
    */

    if ($subject === '') {

        $errors[] = 'Please enter a subject.';

    } elseif (strlen($subject) > 200) {

        $errors[] = 'Subject is too long.';

    }


    /*
    |--------------------------------------------------------------------------
    | Validate Message
    |--------------------------------------------------------------------------
    */

    if ($message === '') {

        $errors[] = 'Please enter your message.';

    } elseif (strlen($message) < 10) {

        $errors[] =
            'Your message must contain at least 10 characters.';

    }


    /*
    |--------------------------------------------------------------------------
    | Save Message
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $stmt = $conn->prepare("
            INSERT INTO contact_messages
            (
                user_id,
                first_name,
                last_name,
                email,
                phone,
                subject,
                message,
                status
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                'new'
            )
        ");

        if ($stmt) {

            $stmt->bind_param(
                "issssss",
                $userId,
                $firstName,
                $lastName,
                $email,
                $phone,
                $subject,
                $message
            );

            if ($stmt->execute()) {

                $success = true;

                /*
                |--------------------------------------------------------------------------
                | Clear Form
                |--------------------------------------------------------------------------
                */

                $firstName = '';
                $lastName  = '';
                $email     = '';
                $phone     = '';
                $subject   = '';
                $message   = '';

            } else {

                $errors[] =
                    'We could not send your message. Please try again.';

            }

            $stmt->close();

        } else {

            $errors[] =
                'We could not process your message. Please try again.';
        }
    }
}


require_once __DIR__ . '/includes/header.php';

?>


<style>

/*
|--------------------------------------------------------------------------
| Contact Page
|--------------------------------------------------------------------------
*/

.contact-page {
    max-width: 1220px;
    margin: 0 auto;
}


/*
|--------------------------------------------------------------------------
| Hero
|--------------------------------------------------------------------------
*/

.contact-hero {
    background: linear-gradient(
        135deg,
        #0056b3 0%,
        #003d80 100%
    );

    color: #ffffff;

    border-radius: 14px;

    padding: 48px 30px;

    margin-bottom: 32px;

    text-align: center;
}

.contact-hero h1 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 10px;
}

.contact-hero p {
    max-width: 680px;
    margin: 0 auto;
    opacity: .92;
    font-size: .98rem;
}


/*
|--------------------------------------------------------------------------
| Contact Information Cards
|--------------------------------------------------------------------------
*/

.contact-card {
    background: #ffffff;

    border: 1px solid #e5e9ef;

    border-radius: 12px;

    padding: 25px;

    height: 100%;

    box-shadow: 0 3px 12px rgba(0, 0, 0, .04);
}

.contact-info-card {
    text-align: center;
}

.contact-icon {
    width: 50px;
    height: 50px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    background: #eaf3ff;

    color: #0056b3;

    border-radius: 10px;

    font-size: 20px;

    margin-bottom: 15px;
}

.contact-card h5 {
    font-weight: 700;
    color: #172b4d;
}

.contact-card p {
    color: #6c757d;
}

.contact-card a {
    color: #0056b3;
    text-decoration: none;
    font-weight: 600;
}

.contact-card a:hover {
    text-decoration: underline;
}


/*
|--------------------------------------------------------------------------
| Main Content Cards
|--------------------------------------------------------------------------
*/

.contact-form-card {
    background: #ffffff;

    border: 1px solid #e5e9ef;

    border-radius: 12px;

    padding: 28px;

    box-shadow: 0 3px 12px rgba(0, 0, 0, .04);
}

.contact-form-card h3,
.help-card h3 {
    color: #172b4d;
    font-weight: 700;
}


/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

.form-label {
    font-weight: 600;
    color: #34495e;
}

.form-control {
    border-radius: 8px;

    padding: 11px 13px;

    border-color: #dce2e8;
}

.form-control:focus {
    border-color: #0056b3;

    box-shadow:
        0 0 0 .2rem rgba(0, 86, 179, .12);
}

textarea.form-control {
    resize: vertical;
}


/*
|--------------------------------------------------------------------------
| Button
|--------------------------------------------------------------------------
*/

.btn-contact {
    background: #0056b3;

    border-color: #0056b3;

    color: #ffffff;

    border-radius: 8px;

    padding: 11px 24px;

    font-weight: 600;
}

.btn-contact:hover {
    background: #004494;

    border-color: #004494;

    color: #ffffff;
}


/*
|--------------------------------------------------------------------------
| Help Boxes
|--------------------------------------------------------------------------
*/

.business-box {
    background: #f4f7fb;

    border: 1px solid #e8edf3;

    border-radius: 10px;

    padding: 18px;
}

.business-box h6 {
    color: #172b4d;
    font-weight: 700;
}

.business-box p {
    color: #6c757d;
}


/*
|--------------------------------------------------------------------------
| Alerts
|--------------------------------------------------------------------------
*/

.contact-alert {
    border-radius: 10px;
}


/*
|--------------------------------------------------------------------------
| Responsive
|--------------------------------------------------------------------------
*/

@media (max-width: 767.98px) {

    .contact-page {
        width: 100%;
    }

    .contact-hero {
        padding: 38px 20px;
    }

    .contact-hero h1 {
        font-size: 1.7rem;
    }

    .contact-form-card,
    .contact-card {
        padding: 22px;
    }

}

</style>


<div class="container py-4">

    <div class="contact-page">


        <!-- =====================================================
             HERO
             ===================================================== -->

        <div class="contact-hero">

            <h1>
                Contact Us
            </h1>

            <p>
                Have a question about our products, orders or services?
                Our team is here to help.
            </p>

        </div>


        <!-- =====================================================
             FORM MESSAGES
             ===================================================== -->

        <?php if (!empty($errors)): ?>

            <div class="alert alert-danger contact-alert">

                <strong>
                    Please correct the following:
                </strong>

                <ul class="mb-0 mt-2">

                    <?php foreach ($errors as $error): ?>

                        <li>
                            <?= e($error) ?>
                        </li>

                    <?php endforeach; ?>

                </ul>

            </div>

        <?php endif; ?>


        <?php if ($success): ?>

            <div
                class="alert alert-success contact-alert d-flex align-items-center"
            >

                <i class="bi bi-check-circle-fill fs-4 me-3"></i>

                <div>

                    <strong>
                        Message sent successfully.
                    </strong>

                    <div>
                        Thank you for contacting Betachem.
                        Our team will review your enquiry.
                    </div>

                </div>

            </div>

        <?php endif; ?>


        <!-- =====================================================
             CONTACT INFORMATION
             ===================================================== -->

        <div class="row g-4 mb-4">


            <!-- PHONE -->

            <div class="col-md-4">

                <div class="contact-card contact-info-card">

                    <div class="contact-icon">

                        <i class="bi bi-telephone"></i>

                    </div>

                    <h5>
                        Phone
                    </h5>

                    <p class="mb-3">
                        Speak to our team directly.
                    </p>

                    <a href="tel:+27117828789">
                        +27 11 782 8789
                    </a>

                </div>

            </div>


            <!-- EMAIL -->

            <div class="col-md-4">

                <div class="contact-card contact-info-card">

                    <div class="contact-icon">

                        <i class="bi bi-envelope"></i>

                    </div>

                    <h5>
                        Email
                    </h5>

                    <p class="mb-3">
                        Send us an enquiry and our team will assist you.
                    </p>

                    <a href="mailto:contact@bchem.co.za">
                        contact@bchem.co.za
                    </a>

                </div>

            </div>


            <!-- ADDRESS -->

            <div class="col-md-4">

                <div class="contact-card contact-info-card">

                    <div class="contact-icon">

                        <i class="bi bi-geo-alt"></i>

                    </div>

                    <h5>
                        Address
                    </h5>

                    <p class="mb-0">
                        Contact our team for information about
                        our location and business operations.
                    </p>

                </div>

            </div>

        </div>


        <!-- =====================================================
             MAIN CONTENT
             ===================================================== -->

        <div class="row g-4">


            <!-- =================================================
                 CONTACT FORM
                 ================================================= -->

            <div class="col-lg-7">

                <div class="contact-form-card">

                    <h3 class="mb-2">
                        Send Us a Message
                    </h3>

                    <p class="text-muted mb-4">
                        Complete the form below and our team will
                        assist you with your enquiry.
                    </p>


                    <form
                        method="POST"
                        action="<?= $baseUrl ?>/contact.php"
                    >

                        <div class="row g-3">


                            <!-- FIRST NAME -->

                            <div class="col-md-6">

                                <label
                                    for="first_name"
                                    class="form-label"
                                >
                                    First Name
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="first_name"
                                    name="first_name"
                                    value="<?= e($firstName) ?>"
                                    maxlength="100"
                                    autocomplete="given-name"
                                    required
                                >

                            </div>


                            <!-- LAST NAME -->

                            <div class="col-md-6">

                                <label
                                    for="last_name"
                                    class="form-label"
                                >
                                    Last Name
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="last_name"
                                    name="last_name"
                                    value="<?= e($lastName) ?>"
                                    maxlength="100"
                                    autocomplete="family-name"
                                    required
                                >

                            </div>


                            <!-- EMAIL -->

                            <div class="col-md-6">

                                <label
                                    for="email"
                                    class="form-label"
                                >
                                    Email Address
                                </label>

                                <input
                                    type="email"
                                    class="form-control"
                                    id="email"
                                    name="email"
                                    value="<?= e($email) ?>"
                                    maxlength="190"
                                    autocomplete="email"
                                    required
                                >

                            </div>


                            <!-- PHONE -->

                            <div class="col-md-6">

                                <label
                                    for="phone"
                                    class="form-label"
                                >
                                    Phone Number
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="phone"
                                    name="phone"
                                    value="<?= e($phone) ?>"
                                    maxlength="50"
                                    autocomplete="tel"
                                >

                            </div>


                            <!-- SUBJECT -->

                            <div class="col-12">

                                <label
                                    for="subject"
                                    class="form-label"
                                >
                                    Subject
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="subject"
                                    name="subject"
                                    value="<?= e($subject) ?>"
                                    maxlength="200"
                                    required
                                >

                            </div>


                            <!-- MESSAGE -->

                            <div class="col-12">

                                <label
                                    for="message"
                                    class="form-label"
                                >
                                    Message
                                </label>

                                <textarea
                                    class="form-control"
                                    id="message"
                                    name="message"
                                    rows="6"
                                    required
                                ><?= e($message) ?></textarea>

                            </div>


                            <!-- BUTTON -->

                            <div class="col-12">

                                <button
                                    type="submit"
                                    class="btn btn-contact"
                                >

                                    <i class="bi bi-send me-2"></i>

                                    Send Message

                                </button>

                            </div>


                        </div>

                    </form>

                </div>

            </div>


            <!-- =================================================
                 HELP SECTION
                 ================================================= -->

            <div class="col-lg-5">

                <div class="contact-card help-card">

                    <h3 class="mb-2">
                        How Can We Help?
                    </h3>

                    <p class="text-muted mb-4">

                        Our team can assist with product information,
                        quotations, orders, delivery enquiries and
                        general product support.

                    </p>


                    <!-- PRODUCT -->

                    <div class="business-box mb-3">

                        <h6 class="mb-2">

                            <i class="bi bi-box-seam me-2"></i>

                            Product Enquiries

                        </h6>

                        <p class="small mb-0">

                            Need information about a product,
                            specifications or availability?
                            Send us your enquiry.

                        </p>

                    </div>


                    <!-- ORDERS -->

                    <div class="business-box mb-3">

                        <h6 class="mb-2">

                            <i class="bi bi-truck me-2"></i>

                            Orders & Delivery

                        </h6>

                        <p class="small mb-0">

                            Contact us regarding an existing order,
                            delivery status or order-related questions.

                        </p>

                    </div>


                    <!-- GENERAL -->

                    <div class="business-box">

                        <h6 class="mb-2">

                            <i class="bi bi-chat-dots me-2"></i>

                            General Enquiries

                        </h6>

                        <p class="small mb-0">

                            For any other questions, send us a message
                            and our team will assist you.

                        </p>

                    </div>

                </div>

            </div>


        </div>

    </div>

</div>


<?php require_once __DIR__ . '/includes/footer.php'; ?>