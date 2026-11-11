<?php

/*
|--------------------------------------------------------------------------
| Betachem E-Commerce
| Order Success
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

$orderId = (int) ($_GET['id'] ?? 0);

if ($orderId <= 0) {
    redirect($baseUrl . '/account.php');
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
        shipping_first_name,
        shipping_last_name,
        shipping_phone,
        shipping_email,
        shipping_address,
        shipping_city,
        shipping_province,
        shipping_postal_code,
        notes,
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
    redirect($baseUrl . '/account.php');
}


/*
|--------------------------------------------------------------------------
| Load Order Items
|--------------------------------------------------------------------------
*/

$itemStmt = $conn->prepare("
    SELECT
        product_id,
        product_name,
        sku,
        price,
        quantity,
        subtotal
    FROM order_items
    WHERE order_id = ?
    ORDER BY id ASC
");

$itemStmt->bind_param(
    "i",
    $orderId
);

$itemStmt->execute();

$itemResult = $itemStmt->get_result();

$orderItems = [];

while ($item = $itemResult->fetch_assoc()) {
    $orderItems[] = $item;
}

$itemStmt->close();


/*
|--------------------------------------------------------------------------
| Payment Status
|--------------------------------------------------------------------------
*/

$paymentStatus = strtolower(
    $order['payment_status'] ?? 'pending'
);

$paymentBadge = 'warning';

if ($paymentStatus === 'paid') {
    $paymentBadge = 'success';
} elseif ($paymentStatus === 'failed') {
    $paymentBadge = 'danger';
} elseif ($paymentStatus === 'refunded') {
    $paymentBadge = 'secondary';
} elseif ($paymentStatus === 'partial') {
    $paymentBadge = 'info';
}


/*
|--------------------------------------------------------------------------
| Order Status
|--------------------------------------------------------------------------
*/

$orderStatus = strtolower(
    $order['status'] ?? 'pending'
);

$orderBadge = 'warning';

if ($orderStatus === 'processing') {
    $orderBadge = 'info';
} elseif ($orderStatus === 'ready') {
    $orderBadge = 'primary';
} elseif ($orderStatus === 'shipped') {
    $orderBadge = 'info';
} elseif ($orderStatus === 'completed') {
    $orderBadge = 'success';
} elseif ($orderStatus === 'cancelled') {
    $orderBadge = 'danger';
}


/*
|--------------------------------------------------------------------------
| Load Header
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/includes/header.php';

?>

<div class="container py-5">

    <!-- =========================================================
         SUCCESS MESSAGE
    ========================================================== -->

    <div class="text-center mb-5">

        <div class="success-icon mb-4">

            <i class="bi bi-check-lg"></i>

        </div>

        <h1 class="fw-bold mb-3">
            Order Placed Successfully!
        </h1>

        <p class="text-muted fs-5 mb-2">

            Thank you for your order,
            <?= e($order['shipping_first_name']) ?>.

        </p>

        <p class="text-muted mb-0">

            Your order has been received and is now being processed.

        </p>

    </div>


    <!-- =========================================================
         ORDER NUMBER
    ========================================================== -->

    <div class="order-number-card mb-4">

        <div>

            <div class="small text-white-50 mb-1">
                Order Number
            </div>

            <div class="order-number">

                <?= e($order['order_number']) ?>

            </div>

        </div>


        <div class="text-md-end mt-3 mt-md-0">

            <div class="small text-white-50 mb-1">
                Order Date
            </div>

            <div class="fw-semibold">

                <?= e(
                    date(
                        'd F Y, H:i',
                        strtotime($order['created_at'])
                    )
                ) ?>

            </div>

        </div>

    </div>


    <div class="row g-4">


        <!-- =====================================================
             ORDER DETAILS
        ====================================================== -->

        <div class="col-lg-8">


            <!-- ORDER ITEMS -->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-body p-4">

                    <h4 class="fw-bold mb-4">
                        Order Items
                    </h4>


                    <?php if (empty($orderItems)): ?>

                        <div class="text-muted">
                            No order items were found.
                        </div>

                    <?php else: ?>

                        <?php foreach ($orderItems as $item): ?>

                            <div class="order-item">

                                <div>

                                    <div class="fw-semibold">

                                        <?= e(
                                            $item['product_name']
                                        ) ?>

                                    </div>


                                    <div class="small text-muted">

                                        SKU:

                                        <?= e(
                                            $item['sku'] ?: 'N/A'
                                        ) ?>


                                        &nbsp;•&nbsp;


                                        Qty:

                                        <?= (int)
                                            $item['quantity']
                                        ?>

                                    </div>

                                </div>


                                <div class="fw-semibold">

                                    <?= money(
                                        $item['subtotal']
                                    ) ?>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>

            </div>


            <!-- SHIPPING DETAILS -->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-body p-4">

                    <h4 class="fw-bold mb-4">
                        Delivery Information
                    </h4>


                    <div class="row g-4">


                        <div class="col-md-6">

                            <div class="detail-label">
                                Customer
                            </div>

                            <div class="fw-semibold">

                                <?= e(
                                    $order['shipping_first_name']
                                    . ' '
                                    . $order['shipping_last_name']
                                ) ?>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="detail-label">
                                Phone
                            </div>

                            <div class="fw-semibold">

                                <?= e(
                                    $order['shipping_phone']
                                ) ?>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="detail-label">
                                Email
                            </div>

                            <div class="fw-semibold">

                                <?= e(
                                    $order['shipping_email']
                                ) ?>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="detail-label">
                                Address
                            </div>

                            <div class="fw-semibold">

                                <?= e(
                                    $order['shipping_address']
                                ) ?><br>

                                <?= e(
                                    $order['shipping_city']
                                ) ?><br>

                                <?= e(
                                    $order['shipping_province']
                                ) ?><br>

                                <?= e(
                                    $order['shipping_postal_code']
                                ) ?>

                            </div>

                        </div>

                    </div>


                    <?php if (!empty($order['notes'])): ?>

                        <hr class="my-4">


                        <div>

                            <div class="detail-label">
                                Order Notes
                            </div>

                            <div>

                                <?= nl2br(
                                    e($order['notes'])
                                ) ?>

                            </div>

                        </div>

                    <?php endif; ?>

                </div>

            </div>


            <!-- STATUS INFORMATION -->

            <div class="card border-0 shadow-sm">

                <div class="card-body p-4">

                    <h4 class="fw-bold mb-4">
                        Order Status
                    </h4>


                    <div class="row g-3">


                        <div class="col-md-6">

                            <div class="status-box">

                                <div class="small text-muted mb-2">
                                    Order Status
                                </div>

                                <span
                                    class="badge text-bg-<?= e(
                                        $orderBadge
                                    ) ?>"
                                >

                                    <?= e(
                                        ucfirst(
                                            $orderStatus
                                        )
                                    ) ?>

                                </span>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="status-box">

                                <div class="small text-muted mb-2">
                                    Payment Status
                                </div>

                                <span
                                    class="badge text-bg-<?= e(
                                        $paymentBadge
                                    ) ?>"
                                >

                                    <?= e(
                                        ucfirst(
                                            $paymentStatus
                                        )
                                    ) ?>

                                </span>

                            </div>

                        </div>

                    </div>


                    <?php if (
                        $paymentStatus === 'pending'
                    ): ?>

                        <div
                            class="alert alert-info mt-4 mb-0"
                        >

                            <i
                                class="bi bi-info-circle me-2"
                            ></i>

                            Your order has been received.
                            Payment instructions will be provided
                            once your order has been reviewed.

                        </div>

                    <?php endif; ?>


                    <?php if (
                        $paymentStatus === 'paid'
                    ): ?>

                        <div
                            class="alert alert-success mt-4 mb-0"
                        >

                            <i
                                class="bi bi-check-circle me-2"
                            ></i>

                            Your payment has been confirmed.

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>


        <!-- =====================================================
             ORDER SUMMARY
        ====================================================== -->

        <div class="col-lg-4">

            <div
                class="card border-0 shadow-sm order-summary-card"
            >

                <div class="card-body p-4">

                    <h4 class="fw-bold mb-4">
                        Order Summary
                    </h4>


                    <!-- SUBTOTAL -->

                    <div
                        class="d-flex
                               justify-content-between
                               mb-3"
                    >

                        <span class="text-muted">
                            Subtotal
                        </span>

                        <span>

                            <?= money(
                                $order['subtotal']
                            ) ?>

                        </span>

                    </div>


                    <!-- DELIVERY -->

                    <div
                        class="d-flex
                               justify-content-between
                               mb-3"
                    >

                        <span class="text-muted">
                            Delivery
                        </span>

                        <span>

                            <?php if (
                                (float)
                                $order['delivery_fee'] > 0
                            ): ?>

                                <?= money(
                                    $order['delivery_fee']
                                ) ?>

                            <?php else: ?>

                                Free

                            <?php endif; ?>

                        </span>

                    </div>


                    <!-- DISCOUNT -->

                    <?php if (
                        (float)
                        $order['discount'] > 0
                    ): ?>

                        <div
                            class="d-flex
                                   justify-content-between
                                   mb-3"
                        >

                            <span class="text-muted">
                                Discount
                            </span>

                            <span class="text-success">

                                -
                                <?= money(
                                    $order['discount']
                                ) ?>

                            </span>

                        </div>

                    <?php endif; ?>


                    <hr>


                    <!-- TOTAL -->

                    <div
                        class="d-flex
                               justify-content-between
                               mb-4"
                    >

                        <strong>
                            Total
                        </strong>

                        <strong class="order-total">

                            <?= money(
                                $order['total']
                            ) ?>

                        </strong>

                    </div>


                    <!-- PAYMENT METHOD -->

                    <div class="summary-info mb-4">

                        <div class="small text-muted">
                            Payment Method
                        </div>

                        <div class="fw-semibold">

                            <?php

                            $paymentMethod =
                                $order['payment_method']
                                ?? 'pending';

                            if (
                                $paymentMethod === 'pending'
                            ) {

                                echo 'Payment Pending';

                            } elseif (
                                $paymentMethod === 'eft'
                            ) {

                                echo 'EFT / Bank Transfer';

                            } else {

                                echo e(
                                    ucfirst(
                                        $paymentMethod
                                    )
                                );

                            }

                            ?>

                        </div>

                    </div>


                    <!-- BUTTONS -->

                    <a
                        href="<?= $baseUrl; ?>/account.php"
                        class="btn btn-primary w-100 mb-2"
                    >

                        <i
                            class="bi bi-person me-2"
                        ></i>

                        My Account

                    </a>


                    <a
                        href="<?= $baseUrl; ?>/order_details.php?id=<?= (int) $orderId ?>"
                        class="btn btn-outline-primary w-100 mb-2"
                    >

                        <i
                            class="bi bi-box-seam me-2"
                        ></i>

                        Track Order

                    </a>


                    <a
                        href="<?= $baseUrl; ?>/shop.php"
                        class="btn btn-outline-primary w-100"
                    >

                        <i
                            class="bi bi-shop me-2"
                        ></i>

                        Continue Shopping

                    </a>

                </div>

            </div>

        </div>

    </div>

</div>


<style>

/*
|--------------------------------------------------------------------------
| Order Success
|--------------------------------------------------------------------------
*/

.success-icon {

    width: 90px;

    height: 90px;

    margin-left: auto;

    margin-right: auto;

    border-radius: 50%;

    background: #e8f7ee;

    color: #198754;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 48px;
}


.order-number-card {

    background:
        linear-gradient(
            135deg,
            #1D88E9,
            #2D208A
        );

    color: #ffffff;

    border-radius: 16px;

    padding: 25px 30px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    box-shadow:
        0 10px 30px rgba(
            29,
            136,
            233,
            0.18
        );
}


.order-number {

    font-size: 1.4rem;

    font-weight: 800;

    letter-spacing: 0.5px;
}


.order-item {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;

    padding: 18px 0;

    border-bottom:
        1px solid #eeeeee;
}


.order-item:first-of-type {
    padding-top: 0;
}


.order-item:last-child {

    border-bottom: 0;

    padding-bottom: 0;
}


.detail-label {

    font-size: 0.8rem;

    text-transform: uppercase;

    letter-spacing: 0.5px;

    color: #6c757d;

    margin-bottom: 5px;
}


.status-box {

    background: #f8faff;

    border-radius: 12px;

    padding: 18px;
}


.order-summary-card {

    position: sticky;

    top: 90px;
}


.order-total {

    color: #1D88E9;

    font-size: 1.4rem;
}


.summary-info {

    background: #f8faff;

    border-radius: 10px;

    padding: 15px;
}


/*
|--------------------------------------------------------------------------
| Mobile
|--------------------------------------------------------------------------
*/

@media (max-width: 767px) {

    .order-number-card {

        display: block;

    }


    .order-item {

        align-items: flex-start;

        flex-direction: column;

        gap: 8px;

    }

}

</style>


<?php

require_once __DIR__ . '/includes/footer.php';

?>