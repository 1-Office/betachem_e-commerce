<?php

/*
|--------------------------------------------------------------------------
| Betachem E-Commerce
| EFT Payment
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();


/*
|--------------------------------------------------------------------------
| Base URL
|--------------------------------------------------------------------------
*/

$baseUrl = '/betachem/betachem_e-commerce';


/*
|--------------------------------------------------------------------------
| Get Order ID
|--------------------------------------------------------------------------
*/

$orderId = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($orderId <= 0) {
    redirect($baseUrl . '/orders.php');
}


/*
|--------------------------------------------------------------------------
| Load Order
|--------------------------------------------------------------------------
*/

$userId = currentUserId();

$stmt = $conn->prepare("
    SELECT
        id,
        order_number,
        status,
        subtotal,
        delivery_fee,
        discount,
        total,
        payment_status,
        payment_method,
        created_at
    FROM orders
    WHERE id = ?
    AND user_id = ?
    LIMIT 1
");

$stmt->bind_param(
    "ii",
    $orderId,
    $userId
);

$stmt->execute();

$result = $stmt->get_result();

$order = $result->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| Order Not Found
|--------------------------------------------------------------------------
*/

if (!$order) {
    redirect($baseUrl . '/orders.php');
}


/*
|--------------------------------------------------------------------------
| Payment Status
|--------------------------------------------------------------------------
*/

if (
    strtolower(
        $order['payment_status'] ?? ''
    ) === 'paid'
) {

    redirect(
        $baseUrl
        . '/order_details.php?id='
        . $orderId
    );
}


/*
|--------------------------------------------------------------------------
| Only EFT
|--------------------------------------------------------------------------
*/

if (
    strtolower(
        $order['payment_method'] ?? ''
    ) !== 'eft'
) {

    redirect(
        $baseUrl
        . '/order_details.php?id='
        . $orderId
    );
}


/*
|--------------------------------------------------------------------------
| Load Header
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/includes/header.php';

?>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-9">


            <!-- =====================================================
                 HEADER
            ====================================================== -->

            <div class="text-center mb-5">

                <div
                    class="d-inline-flex
                           align-items-center
                           justify-content-center
                           rounded-circle
                           bg-primary
                           text-white
                           mb-3"
                    style="width:70px;height:70px;"
                >

                    <i class="bi bi-bank fs-2"></i>

                </div>


                <h1 class="fw-bold mb-2">
                    Complete Your Payment
                </h1>


                <p class="text-muted mb-0">

                    Complete your EFT payment using
                    the details below.

                </p>

            </div>


            <!-- =====================================================
                 ORDER DETAILS
            ====================================================== -->

            <div
                class="card border-0 shadow-sm mb-4"
            >

                <div class="card-body p-4">

                    <div class="row g-4">


                        <div class="col-md-6">

                            <small class="text-muted d-block">
                                Order Number
                            </small>


                            <strong class="fs-5">

                                <?= e(
                                    $order['order_number']
                                ) ?>

                            </strong>

                        </div>


                        <div
                            class="col-md-6 text-md-end"
                        >

                            <small class="text-muted d-block">
                                Amount to Pay
                            </small>


                            <strong
                                class="fs-4 text-primary"
                            >

                                <?= money(
                                    $order['total']
                                ) ?>

                            </strong>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =====================================================
                 EFT DETAILS
            ====================================================== -->

            <div
                class="card border-0 shadow-sm mb-4"
            >

                <div
                    class="card-header
                           bg-primary
                           text-white
                           py-3"
                >

                    <h5 class="mb-0">

                        <i class="bi bi-bank me-2"></i>

                        EFT / Bank Transfer

                    </h5>

                </div>


                <div class="card-body p-4">


                    <!-- WARNING -->

                    <div class="alert alert-warning">

                        <i
                            class="bi
                                   bi-exclamation-triangle
                                   me-2"
                        ></i>


                        <strong>Important:</strong>


                        The banking details below are temporary
                        placeholders for testing.


                        Replace them with the official Betachem
                        banking details before going live.

                    </div>


                    <!-- BANK DETAILS -->

                    <div class="row g-4">


                        <!-- BANK -->

                        <div class="col-md-6">

                            <div
                                class="border
                                       rounded
                                       p-3
                                       h-100"
                            >

                                <small class="text-muted">
                                    Bank
                                </small>


                                <div class="fw-bold">
                                    Betachem Bank
                                </div>

                            </div>

                        </div>


                        <!-- ACCOUNT NAME -->

                        <div class="col-md-6">

                            <div
                                class="border
                                       rounded
                                       p-3
                                       h-100"
                            >

                                <small class="text-muted">
                                    Account Name
                                </small>


                                <div class="fw-bold">
                                    Betachem
                                </div>

                            </div>

                        </div>


                        <!-- ACCOUNT NUMBER -->

                        <div class="col-md-6">

                            <div
                                class="border
                                       rounded
                                       p-3
                                       h-100"
                            >

                                <small class="text-muted">
                                    Account Number
                                </small>


                                <div class="fw-bold">
                                    0000000000
                                </div>

                            </div>

                        </div>


                        <!-- BRANCH CODE -->

                        <div class="col-md-6">

                            <div
                                class="border
                                       rounded
                                       p-3
                                       h-100"
                            >

                                <small class="text-muted">
                                    Branch Code
                                </small>


                                <div class="fw-bold">
                                    000000
                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- PAYMENT REFERENCE -->

                    <div class="mt-4">

                        <div
                            class="border
                                   border-primary
                                   rounded
                                   p-4
                                   bg-light"
                        >

                            <small
                                class="text-muted
                                       d-block
                                       mb-1"
                            >

                                Payment Reference

                            </small>


                            <div
                                class="fs-4
                                       fw-bold
                                       text-primary"
                            >

                                <?= e(
                                    $order['order_number']
                                ) ?>

                            </div>


                            <small class="text-muted">

                                Please use your order number
                                as your EFT payment reference.

                            </small>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =====================================================
                 INSTRUCTIONS
            ====================================================== -->

            <div
                class="card border-0 shadow-sm mb-4"
            >

                <div class="card-body p-4">

                    <h5 class="fw-bold mb-3">
                        How to Pay
                    </h5>


                    <ol class="mb-0">


                        <li class="mb-2">

                            Log in to your banking app
                            or online banking.

                        </li>


                        <li class="mb-2">

                            Add the Betachem banking details
                            as a beneficiary.

                        </li>


                        <li class="mb-2">

                            Pay

                            <strong>

                                <?= money(
                                    $order['total']
                                ) ?>

                            </strong>.

                        </li>


                        <li class="mb-2">

                            Use

                            <strong>

                                <?= e(
                                    $order['order_number']
                                ) ?>

                            </strong>

                            as the payment reference.

                        </li>


                        <li>

                            Betachem will confirm your payment
                            and update your order.

                        </li>

                    </ol>

                </div>

            </div>


            <!-- =====================================================
                 ACTIONS
            ====================================================== -->

            <div
                class="d-flex
                       flex-column
                       flex-md-row
                       gap-3"
            >


                <a
                    href="<?= $baseUrl; ?>/order_details.php?id=<?= $orderId ?>"
                    class="btn btn-primary btn-lg flex-fill"
                >

                    <i
                        class="bi bi-receipt me-2"
                    ></i>

                    View Order

                </a>


                <a
                    href="<?= $baseUrl; ?>/orders.php"
                    class="btn btn-outline-secondary btn-lg flex-fill"
                >

                    <i
                        class="bi bi-arrow-left me-2"
                    ></i>

                    Back to Orders

                </a>

            </div>

        </div>

    </div>

</div>


<?php

require_once __DIR__ . '/includes/footer.php';

?>