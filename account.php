<?php

require_once __DIR__ . '/includes/auth.php';

requireLogin();

$userId = currentUserId();

/*
|--------------------------------------------------------------------------
| E-Commerce Base URL
|--------------------------------------------------------------------------
*/

$baseUrl = '/betachem/betachem_e-commerce';

/*
|--------------------------------------------------------------------------
| Get User
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        id,
        first_name,
        last_name,
        email,
        phone,
        address,
        city,
        province,
        postal_code,
        role,
        status,
        created_at
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();

if (!$user) {

    session_unset();
    session_destroy();

    header('Location: ' . $baseUrl . '/login.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Order Statistics
|--------------------------------------------------------------------------
*/

$orderCount = 0;
$pendingOrders = 0;
$completedOrders = 0;

$stmt = $conn->prepare("
    SELECT
        COUNT(*) AS total_orders,

        SUM(
            CASE
                WHEN status IN (
                    'pending',
                    'processing',
                    'ready',
                    'shipped'
                )
                THEN 1
                ELSE 0
            END
        ) AS pending_orders,

        SUM(
            CASE
                WHEN status = 'completed'
                THEN 1
                ELSE 0
            END
        ) AS completed_orders

    FROM orders

    WHERE user_id = ?
");

$stmt->bind_param("i", $userId);
$stmt->execute();

$orderStats = $stmt->get_result()->fetch_assoc();

$stmt->close();

if ($orderStats) {

    $orderCount =
        (int) ($orderStats['total_orders'] ?? 0);

    $pendingOrders =
        (int) ($orderStats['pending_orders'] ?? 0);

    $completedOrders =
        (int) ($orderStats['completed_orders'] ?? 0);
}

/*
|--------------------------------------------------------------------------
| Recent Orders
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        id,
        order_number,
        status,
        total,
        payment_status,
        created_at
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 5
");

$stmt->bind_param("i", $userId);
$stmt->execute();

$recentOrders = $stmt->get_result();

$stmt->close();

require_once __DIR__ . '/includes/header.php';

?>

<div class="container py-5">

    <!-- =========================
         WELCOME
    ========================== -->

    <div class="mb-5">

        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3"
        >

            <div>

                <span class="text-primary fw-semibold">
                    MY ACCOUNT
                </span>

                <h1 class="fw-bold mb-1">

                    Welcome,
                    <?= e($user['first_name']) ?>!

                </h1>

                <p class="text-muted mb-0">

                    Manage your account, view your orders
                    and update your details.

                </p>

            </div>


            <div>

                <a
                    href="<?= $baseUrl ?>/shop.php"
                    class="btn btn-primary"
                >

                    <i class="bi bi-cart3 me-2"></i>

                    Continue Shopping

                </a>

            </div>

        </div>

    </div>


    <!-- =========================
         STATISTICS
    ========================== -->

    <div class="row g-4 mb-5">

        <!-- TOTAL ORDERS -->

        <div class="col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body p-4">

                    <div class="d-flex align-items-center">

                        <div
                            class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3"
                        >

                            <i class="bi bi-bag fs-3"></i>

                        </div>


                        <div>

                            <div class="text-muted small">
                                Total Orders
                            </div>

                            <div class="fs-3 fw-bold">
                                <?= $orderCount ?>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- ACTIVE ORDERS -->

        <div class="col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body p-4">

                    <div class="d-flex align-items-center">

                        <div
                            class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3"
                        >

                            <i class="bi bi-clock-history fs-3"></i>

                        </div>


                        <div>

                            <div class="text-muted small">
                                Active Orders
                            </div>

                            <div class="fs-3 fw-bold">
                                <?= $pendingOrders ?>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- COMPLETED ORDERS -->

        <div class="col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body p-4">

                    <div class="d-flex align-items-center">

                        <div
                            class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3"
                        >

                            <i class="bi bi-check-circle fs-3"></i>

                        </div>


                        <div>

                            <div class="text-muted small">
                                Completed Orders
                            </div>

                            <div class="fs-3 fw-bold">
                                <?= $completedOrders ?>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <div class="row g-4">

        <!-- =========================
             PROFILE
        ========================== -->

        <div class="col-lg-7">

            <div class="card border-0 shadow-sm h-100">

                <div
                    class="card-header bg-white border-0 p-4 pb-0"
                >

                    <div
                        class="d-flex justify-content-between align-items-center"
                    >

                        <div>

                            <h4 class="fw-bold mb-1">
                                Personal Information
                            </h4>

                            <p class="text-muted small mb-0">
                                Your account and contact details.
                            </p>

                        </div>


                        <div>

                            <a
                                href="<?= $baseUrl ?>/edit_account.php"
                                class="btn btn-outline-primary btn-sm"
                            >

                                <i class="bi bi-pencil me-1"></i>

                                Edit

                            </a>

                        </div>

                    </div>

                </div>


                <div class="card-body p-4">

                    <div class="row g-4">

                        <div class="col-md-6">

                            <div class="text-muted small mb-1">
                                Full Name
                            </div>

                            <div class="fw-semibold">

                                <?= e(
                                    $user['first_name'] .
                                    ' ' .
                                    $user['last_name']
                                ) ?>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="text-muted small mb-1">
                                Email Address
                            </div>

                            <div class="fw-semibold">

                                <?= e($user['email']) ?>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="text-muted small mb-1">
                                Phone Number
                            </div>

                            <div class="fw-semibold">

                                <?= e($user['phone']) ?>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="text-muted small mb-1">
                                Account Status
                            </div>

                            <span class="badge bg-success">

                                <?= e(
                                    ucfirst($user['status'])
                                ) ?>

                            </span>

                        </div>

                    </div>


                    <hr class="my-4">


                    <h5 class="fw-bold mb-3">
                        Delivery Address
                    </h5>


                    <div class="bg-light rounded-3 p-3">

                        <div>

                            <?= nl2br(
                                e($user['address'])
                            ) ?>

                        </div>


                        <div class="mt-1">

                            <?= e($user['city']) ?>,

                            <?= e($user['province']) ?>

                            <?= e($user['postal_code']) ?>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- =========================
             QUICK ACTIONS
        ========================== -->

        <div class="col-lg-5">

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-body p-4">

                    <h4 class="fw-bold mb-4">
                        Quick Actions
                    </h4>


                    <div class="d-grid gap-2">

                        <a
                            href="<?= $baseUrl ?>/orders.php"
                            class="btn btn-outline-primary text-start"
                        >

                            <i class="bi bi-box-seam me-2"></i>

                            My Orders

                        </a>


                        <a
                            href="<?= $baseUrl ?>/shop.php"
                            class="btn btn-outline-primary text-start"
                        >

                            <i class="bi bi-shop me-2"></i>

                            Browse Products

                        </a>


                        <a
                            href="<?= $baseUrl ?>/cart.php"
                            class="btn btn-outline-primary text-start"
                        >

                            <i class="bi bi-cart3 me-2"></i>

                            View Shopping Cart

                        </a>


                        <a
                            href="<?= $baseUrl ?>/edit_account.php"
                            class="btn btn-outline-primary text-start"
                        >

                            <i class="bi bi-person-gear me-2"></i>

                            Edit Account Details

                        </a>

                    </div>

                </div>

            </div>


            <div class="card border-0 shadow-sm">

                <div class="card-body p-4">

                    <h5 class="fw-bold">
                        Need Help?
                    </h5>

                    <p class="text-muted small">

                        If you have questions about your order,
                        products or delivery, please contact Betachem.

                    </p>


                    <a
                        href="mailto:info@betachem.co.za"
                        class="btn btn-primary w-100"
                    >

                        <i class="bi bi-envelope me-2"></i>

                        Contact Betachem

                    </a>

                </div>

            </div>

        </div>

    </div>


    <!-- =========================
         RECENT ORDERS
    ========================== -->

    <div class="card border-0 shadow-sm mt-4">

        <div class="card-header bg-white border-0 p-4">

            <div
                class="d-flex justify-content-between align-items-center"
            >

                <div>

                    <h4 class="fw-bold mb-1">
                        Recent Orders
                    </h4>

                    <p class="text-muted small mb-0">
                        Your latest orders with Betachem.
                    </p>

                </div>


                <a
                    href="<?= $baseUrl ?>/orders.php"
                    class="btn btn-outline-primary btn-sm"
                >

                    View All

                </a>

            </div>

        </div>


        <div class="card-body p-0">

            <?php if ($recentOrders->num_rows > 0): ?>

                <div class="table-responsive">

                    <table class="table align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th class="px-4">
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

                                <th>
                                    Total
                                </th>

                                <th class="text-end px-4">
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php while (
                                $order =
                                    $recentOrders->fetch_assoc()
                            ): ?>

                                <?php

                                $statusClass = match (
                                    $order['status']
                                ) {

                                    'completed' => 'success',

                                    'cancelled' => 'danger',

                                    'shipped' => 'info',

                                    'ready' => 'primary',

                                    'processing' => 'warning',

                                    default => 'secondary'
                                };


                                $paymentClass = match (
                                    $order['payment_status']
                                ) {

                                    'paid' => 'success',

                                    'failed' => 'danger',

                                    'refunded' => 'warning',

                                    'partial' => 'info',

                                    default => 'secondary'
                                };

                                ?>

                                <tr>

                                    <td class="px-4">

                                        <strong>

                                            <?= e(
                                                $order['order_number']
                                            ) ?>

                                        </strong>

                                    </td>


                                    <td>

                                        <?= e(
                                            date(
                                                'd M Y',
                                                strtotime(
                                                    $order['created_at']
                                                )
                                            )
                                        ) ?>

                                    </td>


                                    <td>

                                        <span
                                            class="badge bg-<?= e($statusClass) ?>"
                                        >

                                            <?= e(
                                                ucfirst(
                                                    $order['status']
                                                )
                                            ) ?>

                                        </span>

                                    </td>


                                    <td>

                                        <span
                                            class="badge bg-<?= e($paymentClass) ?>"
                                        >

                                            <?= e(
                                                ucfirst(
                                                    $order['payment_status']
                                                )
                                            ) ?>

                                        </span>

                                    </td>


                                    <td class="fw-semibold">

                                        <?= money(
                                            $order['total']
                                        ) ?>

                                    </td>


                                    <td class="text-end px-4">

                                        <a
                                            href="<?= $baseUrl ?>/order_details.php?id=<?= (int) $order['id'] ?>"
                                            class="btn btn-sm btn-outline-primary"
                                        >

                                            View

                                        </a>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="text-center py-5 px-4">

                    <i class="bi bi-bag-x fs-1 text-muted"></i>

                    <h5 class="fw-bold mt-3">
                        No Orders Yet
                    </h5>

                    <p class="text-muted">
                        You haven't placed an order yet.
                    </p>

                    <a
                        href="<?= $baseUrl ?>/shop.php"
                        class="btn btn-primary"
                    >

                        Start Shopping

                    </a>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>