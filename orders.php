<?php

/*
|--------------------------------------------------------------------------
| Betachem E-Commerce
| Customer Orders
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
| Current User
|--------------------------------------------------------------------------
*/

$userId = currentUserId();


/*
|--------------------------------------------------------------------------
| Load Customer Orders
|--------------------------------------------------------------------------
*/

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
    WHERE user_id = ?
    ORDER BY created_at DESC
");

$stmt->bind_param(
    "i",
    $userId
);

$stmt->execute();

$result = $stmt->get_result();

$orders = [];

while ($order = $result->fetch_assoc()) {
    $orders[] = $order;
}

$stmt->close();


/*
|--------------------------------------------------------------------------
| Load Header
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/includes/header.php';

?>

<div class="container py-5">

    <!-- =========================================================
         PAGE HEADER
    ========================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="fw-bold mb-2">
                My Orders
            </h1>

            <p class="text-muted mb-0">
                View and track your previous orders.
            </p>

        </div>


        <a
            href="<?= $baseUrl; ?>/account.php"
            class="btn btn-outline-primary"
        >

            <i class="bi bi-arrow-left me-2"></i>

            My Account

        </a>

    </div>


    <?php if (empty($orders)): ?>

        <!-- =====================================================
             NO ORDERS
        ====================================================== -->

        <div class="card border-0 shadow-sm">

            <div class="card-body text-center py-5">

                <div class="empty-orders-icon mb-4">

                    <i class="bi bi-bag-x"></i>

                </div>

                <h3 class="fw-bold mb-2">
                    No Orders Yet
                </h3>

                <p class="text-muted mb-4">
                    You haven't placed any orders yet.
                </p>


                <a
                    href="<?= $baseUrl; ?>/shop.php"
                    class="btn btn-primary"
                >

                    <i class="bi bi-shop me-2"></i>

                    Start Shopping

                </a>

            </div>

        </div>


    <?php else: ?>


        <!-- =====================================================
             ORDERS
        ====================================================== -->

        <div class="card border-0 shadow-sm">

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table align-middle mb-0">

                        <thead>

                            <tr>

                                <th>
                                    Order
                                </th>

                                <th>
                                    Date
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Payment
                                </th>

                                <th class="text-end">
                                    Total
                                </th>

                                <th class="text-end">
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach ($orders as $order): ?>

                                <?php

                                /*
                                |--------------------------------------------------------------------------
                                | Order Status Badge
                                |--------------------------------------------------------------------------
                                */

                                $orderStatus =
                                    strtolower(
                                        $order['status'] ?? 'pending'
                                    );

                                $orderBadge = 'warning';


                                if (
                                    $orderStatus === 'processing'
                                ) {

                                    $orderBadge = 'info';

                                } elseif (
                                    $orderStatus === 'ready'
                                ) {

                                    $orderBadge = 'primary';

                                } elseif (
                                    $orderStatus === 'shipped'
                                ) {

                                    $orderBadge = 'info';

                                } elseif (
                                    $orderStatus === 'completed'
                                ) {

                                    $orderBadge = 'success';

                                } elseif (
                                    $orderStatus === 'cancelled'
                                ) {

                                    $orderBadge = 'danger';

                                }


                                /*
                                |--------------------------------------------------------------------------
                                | Payment Badge
                                |--------------------------------------------------------------------------
                                */

                                $paymentStatus =
                                    strtolower(
                                        $order['payment_status']
                                        ?? 'pending'
                                    );

                                $paymentBadge = 'warning';


                                if (
                                    $paymentStatus === 'paid'
                                ) {

                                    $paymentBadge = 'success';

                                } elseif (
                                    $paymentStatus === 'failed'
                                ) {

                                    $paymentBadge = 'danger';

                                } elseif (
                                    $paymentStatus === 'refunded'
                                ) {

                                    $paymentBadge = 'secondary';

                                } elseif (
                                    $paymentStatus === 'partial'
                                ) {

                                    $paymentBadge = 'info';

                                }

                                ?>

                                <tr>


                                    <!-- ORDER NUMBER -->

                                    <td>

                                        <strong>

                                            <?= e(
                                                $order['order_number']
                                            ) ?>

                                        </strong>

                                    </td>


                                    <!-- DATE -->

                                    <td>

                                        <div>

                                            <?= e(
                                                date(
                                                    'd M Y',
                                                    strtotime(
                                                        $order['created_at']
                                                    )
                                                )
                                            ) ?>

                                        </div>


                                        <small class="text-muted">

                                            <?= e(
                                                date(
                                                    'H:i',
                                                    strtotime(
                                                        $order['created_at']
                                                    )
                                                )
                                            ) ?>

                                        </small>

                                    </td>


                                    <!-- STATUS -->

                                    <td>

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

                                    </td>


                                    <!-- PAYMENT -->

                                    <td>

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

                                    </td>


                                    <!-- TOTAL -->

                                    <td class="text-end">

                                        <strong class="order-price">

                                            <?= money(
                                                $order['total']
                                            ) ?>

                                        </strong>

                                    </td>


                                    <!-- ACTION -->

                                    <td class="text-end">

                                        <a
                                            href="<?= $baseUrl; ?>/order_details.php?id=<?= (int) $order['id'] ?>"
                                            class="btn btn-sm btn-outline-primary"
                                        >

                                            <i
                                                class="bi bi-eye me-1"
                                            ></i>

                                            View

                                        </a>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        <!-- =====================================================
             ORDER INFORMATION
        ====================================================== -->

        <div class="alert alert-info mt-4 mb-0">

            <i class="bi bi-info-circle me-2"></i>

            Select <strong>View</strong> on an order to see
            the complete order details and delivery information.

        </div>

    <?php endif; ?>

</div>


<style>

/*
|--------------------------------------------------------------------------
| Orders Page
|--------------------------------------------------------------------------
*/

.table thead th {

    background: #f8faff;

    border-bottom:
        1px solid #e9ecef;

    color: #495057;

    font-size: 0.85rem;

    text-transform: uppercase;

    letter-spacing: 0.4px;

    white-space: nowrap;
}


.table tbody td {

    padding-top: 18px;

    padding-bottom: 18px;
}


.order-price {

    color: #1D88E9;
}


.empty-orders-icon {

    width: 80px;

    height: 80px;

    margin-left: auto;

    margin-right: auto;

    border-radius: 50%;

    background: #f0f6ff;

    color: #1D88E9;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 38px;
}


/*
|--------------------------------------------------------------------------
| Mobile
|--------------------------------------------------------------------------
*/

@media (max-width: 767px) {

    .d-flex.justify-content-between.align-items-center {

        align-items: flex-start !important;

        gap: 15px;

    }

}

</style>


<?php

require_once __DIR__ . '/includes/footer.php';

?>