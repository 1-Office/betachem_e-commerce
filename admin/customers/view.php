<?php

require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$baseUrl = '/betachem/betachem_e-commerce';

require_once __DIR__ . '/../includes/header.php';


/*
|--------------------------------------------------------------------------
| Get Customer ID
|--------------------------------------------------------------------------
*/

$customerId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$customerId) {
    redirect($baseUrl . '/admin/customers/index.php');
}


/*
|--------------------------------------------------------------------------
| Messages
|--------------------------------------------------------------------------
*/

$message = '';
$error = '';


/*
|--------------------------------------------------------------------------
| Handle Customer Status Update
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    if ($action === 'update_customer_status') {

        $newStatus = $_POST['status'] ?? '';

        $allowedStatuses = [
            'active',
            'inactive',
            'blocked'
        ];

        if (!in_array($newStatus, $allowedStatuses, true)) {

            $error = 'Invalid customer status selected.';

        } else {

            $stmt = $conn->prepare("
                UPDATE users
                SET status = ?
                WHERE id = ?
                AND role = 'customer'
            ");

            $stmt->bind_param(
                "si",
                $newStatus,
                $customerId
            );

            $stmt->execute();

            $affectedRows = $stmt->affected_rows;

            $stmt->close();

            if ($affectedRows >= 0) {

                $message =
                    'Customer status updated successfully.';

            } else {

                $error =
                    'Unable to update customer status.';
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| Load Customer
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
        created_at,
        updated_at
    FROM users
    WHERE id = ?
    AND role = 'customer'
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $customerId
);

$stmt->execute();

$customerResult = $stmt->get_result();

$customer = $customerResult->fetch_assoc();

$stmt->close();


if (!$customer) {

    echo '
    <div class="container-fluid py-5">

        <div class="alert alert-danger">

            <i class="bi bi-exclamation-triangle me-2"></i>

            Customer not found.

        </div>

        <a
            href="' . $baseUrl . '/admin/customers/index.php"
            class="btn btn-primary"
        >

            <i class="bi bi-arrow-left me-1"></i>

            Back to Customers

        </a>

    </div>
    ';

    require_once __DIR__ . '/../includes/footer.php';

    exit;
}


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
    $customerId
);

$stmt->execute();

$ordersResult = $stmt->get_result();

$orders = $ordersResult->fetch_all(MYSQLI_ASSOC);

$stmt->close();


/*
|--------------------------------------------------------------------------
| Customer Statistics
|--------------------------------------------------------------------------
*/

$orderCount = count($orders);

$totalSpent = 0;

$pendingOrders = 0;
$completedOrders = 0;

foreach ($orders as $order) {

    if ($order['payment_status'] !== 'refunded') {

        $totalSpent += (float) $order['total'];
    }


    if (
        in_array(
            $order['status'],
            [
                'pending',
                'processing',
                'ready',
                'shipped'
            ],
            true
        )
    ) {

        $pendingOrders++;
    }


    if ($order['status'] === 'completed') {

        $completedOrders++;
    }
}


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function adminCustomerStatusBadge($status)
{
    switch ($status) {

        case 'active':

            return '
                <span class="badge bg-success">
                    Active
                </span>
            ';

        case 'inactive':

            return '
                <span class="badge bg-secondary">
                    Inactive
                </span>
            ';

        case 'blocked':

            return '
                <span class="badge bg-danger">
                    Blocked
                </span>
            ';

        default:

            return '
                <span class="badge bg-secondary">
                    ' . e(ucfirst($status)) . '
                </span>
            ';
    }
}


function adminCustomerOrderStatusBadge($status)
{
    switch ($status) {

        case 'pending':

            return '
                <span class="badge bg-warning text-dark">
                    Pending
                </span>
            ';

        case 'processing':

            return '
                <span class="badge bg-info text-dark">
                    Processing
                </span>
            ';

        case 'ready':

            return '
                <span class="badge bg-primary">
                    Ready
                </span>
            ';

        case 'shipped':

            return '
                <span class="badge bg-info text-dark">
                    Shipped
                </span>
            ';

        case 'completed':

            return '
                <span class="badge bg-success">
                    Completed
                </span>
            ';

        case 'cancelled':

            return '
                <span class="badge bg-danger">
                    Cancelled
                </span>
            ';

        default:

            return '
                <span class="badge bg-secondary">
                    ' . e(ucfirst($status)) . '
                </span>
            ';
    }
}


function adminCustomerPaymentBadge($status)
{
    switch ($status) {

        case 'pending':

            return '
                <span class="badge bg-warning text-dark">
                    Pending
                </span>
            ';

        case 'paid':

            return '
                <span class="badge bg-success">
                    Paid
                </span>
            ';

        case 'failed':

            return '
                <span class="badge bg-danger">
                    Failed
                </span>
            ';

        case 'refunded':

            return '
                <span class="badge bg-secondary">
                    Refunded
                </span>
            ';

        case 'partial':

            return '
                <span class="badge bg-info text-dark">
                    Partial
                </span>
            ';

        default:

            return '
                <span class="badge bg-secondary">
                    ' . e(ucfirst($status)) . '
                </span>
            ';
    }
}


$allowedStatuses = [
    'active',
    'inactive',
    'blocked'
];

$customerName = trim(
    $customer['first_name']
    . ' '
    . $customer['last_name']
);

if ($customerName === '') {

    $customerName = 'Unnamed Customer';
}

?>

<div class="container-fluid py-4">


    <!-- PAGE HEADER -->

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">

        <div>

            <div class="mb-2">

                <a
                    href="<?= $baseUrl; ?>/admin/customers/index.php"
                    class="text-decoration-none"
                >

                    <i class="bi bi-arrow-left me-1"></i>

                    Back to Customers

                </a>

            </div>


            <h1 class="fw-bold mb-1">

                <i class="bi bi-person-circle me-2"></i>

                <?= e($customerName) ?>

            </h1>


            <p class="text-muted mb-0">

                Customer #<?= (int) $customer['id'] ?>

            </p>

        </div>


        <div class="mt-3 mt-md-0">

            <?= adminCustomerStatusBadge(
                $customer['status']
            ) ?>

        </div>

    </div>


    <!-- MESSAGES -->

    <?php if ($message): ?>

        <div
            class="alert alert-success alert-dismissible fade show"
            role="alert"
        >

            <i class="bi bi-check-circle me-2"></i>

            <?= e($message) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>


    <?php if ($error): ?>

        <div
            class="alert alert-danger alert-dismissible fade show"
            role="alert"
        >

            <i class="bi bi-exclamation-triangle me-2"></i>

            <?= e($error) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>


    <!-- STATISTICS -->

    <div class="row g-4 mb-4">


        <!-- ORDERS -->

        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <p class="text-muted mb-1">
                                Total Orders
                            </p>

                            <h3 class="fw-bold mb-0">

                                <?= $orderCount ?>

                            </h3>

                        </div>

                        <div class="fs-1 text-primary">

                            <i class="bi bi-bag"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- ACTIVE ORDERS -->

        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <p class="text-muted mb-1">
                                Active Orders
                            </p>

                            <h3 class="fw-bold mb-0">

                                <?= $pendingOrders ?>

                            </h3>

                        </div>

                        <div class="fs-1 text-warning">

                            <i class="bi bi-hourglass-split"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- COMPLETED -->

        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <p class="text-muted mb-1">
                                Completed
                            </p>

                            <h3 class="fw-bold mb-0">

                                <?= $completedOrders ?>

                            </h3>

                        </div>

                        <div class="fs-1 text-success">

                            <i class="bi bi-check-circle"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- TOTAL SPENT -->

        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <p class="text-muted mb-1">
                                Total Spent
                            </p>

                            <h3 class="fw-bold mb-0 text-primary">

                                <?= money($totalSpent) ?>

                            </h3>

                        </div>

                        <div class="fs-1 text-primary">

                            <i class="bi bi-cash-stack"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <div class="row g-4">


        <!-- LEFT COLUMN -->

        <div class="col-lg-8">


            <!-- CUSTOMER INFORMATION -->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white border-0 py-3">

                    <h5 class="fw-bold mb-0">

                        <i class="bi bi-person-vcard me-2"></i>

                        Customer Information

                    </h5>

                </div>


                <div class="card-body">

                    <div class="row g-4">


                        <!-- FIRST NAME -->

                        <div class="col-md-6">

                            <small class="text-muted d-block">
                                First Name
                            </small>

                            <strong>

                                <?= e(
                                    $customer['first_name']
                                ) ?>

                            </strong>

                        </div>


                        <!-- LAST NAME -->

                        <div class="col-md-6">

                            <small class="text-muted d-block">
                                Last Name
                            </small>

                            <strong>

                                <?= e(
                                    $customer['last_name']
                                ) ?>

                            </strong>

                        </div>


                        <!-- EMAIL -->

                        <div class="col-md-6">

                            <small class="text-muted d-block">
                                Email Address
                            </small>

                            <strong>

                                <?= e(
                                    $customer['email']
                                ) ?>

                            </strong>

                        </div>


                        <!-- PHONE -->

                        <div class="col-md-6">

                            <small class="text-muted d-block">
                                Phone Number
                            </small>

                            <strong>

                                <?= e(
                                    $customer['phone']
                                    ?: 'Not provided'
                                ) ?>

                            </strong>

                        </div>


                        <!-- JOINED -->

                        <div class="col-md-6">

                            <small class="text-muted d-block">
                                Registered
                            </small>

                            <strong>

                                <?= e(
                                    date(
                                        'd M Y H:i',
                                        strtotime(
                                            $customer['created_at']
                                        )
                                    )
                                ) ?>

                            </strong>

                        </div>


                        <!-- LAST UPDATED -->

                        <div class="col-md-6">

                            <small class="text-muted d-block">
                                Last Updated
                            </small>

                            <strong>

                                <?= e(
                                    date(
                                        'd M Y H:i',
                                        strtotime(
                                            $customer['updated_at']
                                        )
                                    )
                                ) ?>

                            </strong>

                        </div>

                    </div>

                </div>

            </div>


            <!-- DELIVERY ADDRESS -->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white border-0 py-3">

                    <h5 class="fw-bold mb-0">

                        <i class="bi bi-geo-alt me-2"></i>

                        Delivery Address

                    </h5>

                </div>


                <div class="card-body">

                    <?php if (
                        !empty($customer['address']) ||
                        !empty($customer['city']) ||
                        !empty($customer['province']) ||
                        !empty($customer['postal_code'])
                    ): ?>

                        <?php if (!empty($customer['address'])): ?>

                            <p class="mb-1">

                                <?= nl2br(
                                    e(
                                        $customer['address']
                                    )
                                ) ?>

                            </p>

                        <?php endif; ?>


                        <?php if (
                            !empty($customer['city']) ||
                            !empty($customer['province'])
                        ): ?>

                            <p class="mb-1">

                                <?= e(
                                    $customer['city']
                                ) ?>

                                <?php if (
                                    !empty($customer['city']) &&
                                    !empty($customer['province'])
                                ): ?>

                                    ,

                                <?php endif; ?>

                                <?= e(
                                    $customer['province']
                                ) ?>

                            </p>

                        <?php endif; ?>


                        <?php if (
                            !empty($customer['postal_code'])
                        ): ?>

                            <p class="mb-0">

                                <?= e(
                                    $customer['postal_code']
                                ) ?>

                            </p>

                        <?php endif; ?>

                    <?php else: ?>

                        <div class="text-muted">

                            <i class="bi bi-info-circle me-1"></i>

                            No delivery address has been provided.

                        </div>

                    <?php endif; ?>

                </div>

            </div>


            <!-- ORDER HISTORY -->

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white border-0 py-3">

                    <div class="d-flex justify-content-between align-items-center">

                        <h5 class="fw-bold mb-0">

                            <i class="bi bi-clock-history me-2"></i>

                            Order History

                        </h5>

                        <span class="text-muted">

                            <?= $orderCount ?> order(s)

                        </span>

                    </div>

                </div>


                <div class="card-body p-0">

                    <?php if (empty($orders)): ?>

                        <div class="text-center py-5 px-3">

                            <div class="display-5 text-muted mb-3">

                                <i class="bi bi-bag-x"></i>

                            </div>

                            <h5 class="fw-bold">
                                No Orders Yet
                            </h5>

                            <p class="text-muted mb-0">

                                This customer has not placed any orders.

                            </p>

                        </div>

                    <?php else: ?>


                        <div class="table-responsive">

                            <table class="table table-hover align-middle mb-0">

                                <thead class="table-light">

                                    <tr>

                                        <th class="px-4">
                                            Order
                                        </th>

                                        <th>
                                            Date
                                        </th>

                                        <th>
                                            Total
                                        </th>

                                        <th>
                                            Payment
                                        </th>

                                        <th>
                                            Status
                                        </th>

                                        <th class="text-end px-4">
                                            Action
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    <?php foreach ($orders as $order): ?>

                                        <tr>

                                            <td class="px-4">

                                                <strong class="text-primary">

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

                                                <small class="text-muted d-block">

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


                                            <td>

                                                <strong>

                                                    <?= money(
                                                        $order['total']
                                                    ) ?>

                                                </strong>

                                            </td>


                                            <td>

                                                <?= adminCustomerPaymentBadge(
                                                    $order['payment_status']
                                                ) ?>

                                            </td>


                                            <td>

                                                <?= adminCustomerOrderStatusBadge(
                                                    $order['status']
                                                ) ?>

                                            </td>


                                            <td class="text-end px-4">

                                                <a
                                                    href="<?= $baseUrl; ?>/admin/orders/view.php?id=<?= (int) $order['id'] ?>"
                                                    class="btn btn-sm btn-outline-primary"
                                                >

                                                    <i class="bi bi-eye me-1"></i>

                                                    View

                                                </a>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>


        <!-- RIGHT COLUMN -->

        <div class="col-lg-4">


            <!-- CUSTOMER STATUS -->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white border-0 py-3">

                    <h5 class="fw-bold mb-0">

                        <i class="bi bi-person-gear me-2"></i>

                        Account Status

                    </h5>

                </div>


                <div class="card-body">

                    <div class="mb-3">

                        <small class="text-muted d-block mb-2">
                            Current Status
                        </small>

                        <?= adminCustomerStatusBadge(
                            $customer['status']
                        ) ?>

                    </div>


                    <form
                        method="POST"
                        action="<?= $baseUrl; ?>/admin/customers/view.php?id=<?= (int) $customerId ?>"
                    >

                        <input
                            type="hidden"
                            name="action"
                            value="update_customer_status"
                        >


                        <label class="form-label fw-semibold">

                            Change Status

                        </label>


                        <select
                            name="status"
                            class="form-select mb-3"
                        >

                            <?php foreach ($allowedStatuses as $itemStatus): ?>

                                <option
                                    value="<?= e($itemStatus) ?>"
                                    <?= $customer['status'] === $itemStatus
                                        ? 'selected'
                                        : '' ?>
                                >

                                    <?= e(
                                        ucfirst($itemStatus)
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>


                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >

                            <i class="bi bi-check-lg me-1"></i>

                            Update Status

                        </button>

                    </form>

                </div>

            </div>


            <!-- CUSTOMER CONTACT -->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white border-0 py-3">

                    <h5 class="fw-bold mb-0">

                        <i class="bi bi-envelope me-2"></i>

                        Contact Customer

                    </h5>

                </div>


                <div class="card-body">

                    <?php if (!empty($customer['email'])): ?>

                        <a
                            href="mailto:<?= e($customer['email']) ?>"
                            class="btn btn-outline-primary w-100 mb-2"
                        >

                            <i class="bi bi-envelope me-1"></i>

                            Email Customer

                        </a>

                    <?php endif; ?>


                    <?php if (!empty($customer['phone'])): ?>

                        <a
                            href="tel:<?= e($customer['phone']) ?>"
                            class="btn btn-outline-secondary w-100"
                        >

                            <i class="bi bi-telephone me-1"></i>

                            Call Customer

                        </a>

                    <?php endif; ?>

                </div>

            </div>


            <!-- QUICK ACTIONS -->

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white border-0 py-3">

                    <h5 class="fw-bold mb-0">

                        <i class="bi bi-lightning me-2"></i>

                        Quick Actions

                    </h5>

                </div>


                <div class="card-body">

                    <a
                        href="<?= $baseUrl; ?>/admin/customers/index.php"
                        class="btn btn-outline-primary w-100 mb-2"
                    >

                        <i class="bi bi-arrow-left me-1"></i>

                        Back to Customers

                    </a>


                    <a
                        href="<?= $baseUrl; ?>/admin/orders/index.php"
                        class="btn btn-outline-secondary w-100 mb-2"
                    >

                        <i class="bi bi-bag me-1"></i>

                        All Orders

                    </a>


                    <a
                        href="<?= $baseUrl; ?>/admin/index.php"
                        class="btn btn-outline-secondary w-100"
                    >

                        <i class="bi bi-speedometer2 me-1"></i>

                        Dashboard

                    </a>

                </div>

            </div>

        </div>

    </div>

</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>