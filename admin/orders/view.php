<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../includes/notification_functions.php';

requireAdmin();

$baseUrl = '/betachem/betachem_e-commerce';


/*
|--------------------------------------------------------------------------
| Get Order ID
|--------------------------------------------------------------------------
*/

$orderId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$orderId) {
    redirect($baseUrl . '/admin/orders/index.php');
}


$message = '';
$error = '';


/*
|--------------------------------------------------------------------------
| Handle POST Actions
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | UPDATE ORDER STATUS
    |--------------------------------------------------------------------------
    */

    if ($action === 'update_order_status') {

        $newStatus = $_POST['status'] ?? '';

        $allowedStatuses = [
            'pending',
            'processing',
            'ready',
            'shipped',
            'completed',
            'cancelled'
        ];

        if (!in_array($newStatus, $allowedStatuses, true)) {

            $error = 'Invalid order status selected.';

        } else {

            $stmt = $conn->prepare("
                SELECT
                    user_id,
                    order_number,
                    status
                FROM orders
                WHERE id = ?
                LIMIT 1
            ");

            $stmt->bind_param(
                "i",
                $orderId
            );

            $stmt->execute();

            $currentResult = $stmt->get_result();

            $currentOrder = $currentResult->fetch_assoc();

            $stmt->close();


            if (!$currentOrder) {

                $error = 'Order not found.';

            } else {

                $oldStatus = $currentOrder['status'];


                $stmt = $conn->prepare("
                    UPDATE orders
                    SET
                        status = ?,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ");

                $stmt->bind_param(
                    "si",
                    $newStatus,
                    $orderId
                );

                $stmt->execute();

                $stmt->close();


                /*
                |--------------------------------------------------------------------------
                | Customer Notification
                |--------------------------------------------------------------------------
                */

                if (
                    $oldStatus !== $newStatus
                    && !empty($currentOrder['user_id'])
                ) {

                    $notificationTitle = '';
                    $notificationMessage = '';

                    switch ($newStatus) {

                        case 'pending':

                            $notificationTitle =
                                'Order is Pending';

                            $notificationMessage =
                                'Your order '
                                . $currentOrder['order_number']
                                . ' is currently pending.';

                            break;


                        case 'processing':

                            $notificationTitle =
                                'Order is Being Processed';

                            $notificationMessage =
                                'Your order '
                                . $currentOrder['order_number']
                                . ' is now being processed by Betachem.';

                            break;


                        case 'ready':

                            $notificationTitle =
                                'Order is Ready';

                            $notificationMessage =
                                'Your order '
                                . $currentOrder['order_number']
                                . ' is ready for the next stage.';

                            break;


                        case 'shipped':

                            $notificationTitle =
                                'Order Has Been Shipped';

                            $notificationMessage =
                                'Your order '
                                . $currentOrder['order_number']
                                . ' has been shipped and is on its way.';

                            break;


                        case 'completed':

                            $notificationTitle =
                                'Order Completed';

                            $notificationMessage =
                                'Your order '
                                . $currentOrder['order_number']
                                . ' has been completed successfully.';

                            break;


                        case 'cancelled':

                            $notificationTitle =
                                'Order Cancelled';

                            $notificationMessage =
                                'Your order '
                                . $currentOrder['order_number']
                                . ' has been cancelled.';

                            break;
                    }


                    if (
                        $notificationTitle !== ''
                        && $notificationMessage !== ''
                    ) {

                        createNotification(
                            (int) $currentOrder['user_id'],
                            'order_status',
                            $notificationTitle,
                            $notificationMessage,
                            $orderId
                        );
                    }
                }


                $message =
                    'Order status updated successfully.';
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE PAYMENT STATUS
    |--------------------------------------------------------------------------
    */

    elseif ($action === 'update_payment_status') {

        $newPaymentStatus =
            strtolower(
                trim(
                    $_POST['payment_status'] ?? ''
                )
            );


        $allowedPaymentStatuses = [
            'pending',
            'paid',
            'failed',
            'refunded',
            'partial'
        ];


        if (
            !in_array(
                $newPaymentStatus,
                $allowedPaymentStatuses,
                true
            )
        ) {

            $error =
                'Invalid payment status selected.';

        } else {

            /*
            |--------------------------------------------------------------------------
            | Get Current Order
            |--------------------------------------------------------------------------
            */

            $stmt = $conn->prepare("
                SELECT
                    user_id,
                    order_number,
                    total,
                    payment_method,
                    payment_status
                FROM orders
                WHERE id = ?
                LIMIT 1
            ");

            $stmt->bind_param(
                "i",
                $orderId
            );

            $stmt->execute();

            $currentResult =
                $stmt->get_result();

            $currentOrder =
                $currentResult->fetch_assoc();

            $stmt->close();


            if (!$currentOrder) {

                $error =
                    'Order not found.';

            } else {

                $oldPaymentStatus =
                    strtolower(
                        $currentOrder['payment_status']
                    );


                /*
                |--------------------------------------------------------------------------
                | Update orders.payment_status
                |--------------------------------------------------------------------------
                */

                $stmt = $conn->prepare("
                    UPDATE orders
                    SET
                        payment_status = ?,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ");

                $stmt->bind_param(
                    "si",
                    $newPaymentStatus,
                    $orderId
                );

                $stmt->execute();

                $stmt->close();


                /*
                |--------------------------------------------------------------------------
                | Map Order Payment Status To payments.status
                |--------------------------------------------------------------------------
                */

                $paymentTableStatus = 'pending';


                if ($newPaymentStatus === 'paid') {

                    $paymentTableStatus =
                        'successful';

                } elseif (
                    $newPaymentStatus === 'failed'
                ) {

                    $paymentTableStatus =
                        'failed';

                } elseif (
                    $newPaymentStatus === 'refunded'
                ) {

                    $paymentTableStatus =
                        'refunded';

                } elseif (
                    $newPaymentStatus === 'partial'
                ) {

                    $paymentTableStatus =
                        'pending';
                }


                /*
                |--------------------------------------------------------------------------
                | Find Existing Payment Record
                |--------------------------------------------------------------------------
                */

                $paymentStmt = $conn->prepare("
                    SELECT
                        id
                    FROM payments
                    WHERE order_id = ?
                    ORDER BY id DESC
                    LIMIT 1
                ");

                $paymentStmt->bind_param(
                    "i",
                    $orderId
                );

                $paymentStmt->execute();

                $paymentResult =
                    $paymentStmt->get_result();

                $paymentRecord =
                    $paymentResult->fetch_assoc();

                $paymentStmt->close();


                /*
                |--------------------------------------------------------------------------
                | Existing Payment
                |--------------------------------------------------------------------------
                */

                if ($paymentRecord) {

                    if (
                        $newPaymentStatus === 'paid'
                    ) {

                        $updatePayment =
                            $conn->prepare("
                                UPDATE payments
                                SET
                                    status = ?,
                                    paid_at = NOW()
                                WHERE id = ?
                            ");

                        $updatePayment->bind_param(
                            "si",
                            $paymentTableStatus,
                            $paymentRecord['id']
                        );

                    } else {

                        $updatePayment =
                            $conn->prepare("
                                UPDATE payments
                                SET
                                    status = ?
                                WHERE id = ?
                            ");

                        $updatePayment->bind_param(
                            "si",
                            $paymentTableStatus,
                            $paymentRecord['id']
                        );
                    }

                    $updatePayment->execute();

                    $updatePayment->close();

                }


                /*
                |--------------------------------------------------------------------------
                | No Payment Record Yet
                |--------------------------------------------------------------------------
                */

                else {

                    $transactionReference =
                        $currentOrder['order_number'];

                    $amount =
                        (float) $currentOrder['total'];

                    $paymentMethod =
                        strtolower(
                            trim(
                                $currentOrder['payment_method']
                                ?: 'eft'
                            )
                        );


                    if (
                        $newPaymentStatus === 'paid'
                    ) {

                        $insertPayment =
                            $conn->prepare("
                                INSERT INTO payments
                                (
                                    order_id,
                                    transaction_reference,
                                    amount,
                                    payment_method,
                                    status,
                                    paid_at
                                )
                                VALUES
                                (
                                    ?,
                                    ?,
                                    ?,
                                    ?,
                                    ?,
                                    NOW()
                                )
                            ");

                        $insertPayment->bind_param(
                            "isdss",
                            $orderId,
                            $transactionReference,
                            $amount,
                            $paymentMethod,
                            $paymentTableStatus
                        );

                    } else {

                        $insertPayment =
                            $conn->prepare("
                                INSERT INTO payments
                                (
                                    order_id,
                                    transaction_reference,
                                    amount,
                                    payment_method,
                                    status
                                )
                                VALUES
                                (
                                    ?,
                                    ?,
                                    ?,
                                    ?,
                                    ?
                                )
                            ");

                        $insertPayment->bind_param(
                            "isdss",
                            $orderId,
                            $transactionReference,
                            $amount,
                            $paymentMethod,
                            $paymentTableStatus
                        );
                    }

                    $insertPayment->execute();

                    $insertPayment->close();
                }


                /*
                |--------------------------------------------------------------------------
                | Customer Payment Notification
                |--------------------------------------------------------------------------
                */

                if (
                    $oldPaymentStatus !== $newPaymentStatus
                    && !empty($currentOrder['user_id'])
                ) {

                    $notificationTitle = '';
                    $notificationMessage = '';

                    switch ($newPaymentStatus) {

                        case 'pending':

                            $notificationTitle =
                                'Payment Pending';

                            $notificationMessage =
                                'Payment for order '
                                . $currentOrder['order_number']
                                . ' is currently pending.';

                            break;


                        case 'paid':

                            $notificationTitle =
                                'Payment Confirmed';

                            $notificationMessage =
                                'Payment for order '
                                . $currentOrder['order_number']
                                . ' has been successfully confirmed by Betachem.';

                            break;


                        case 'failed':

                            $notificationTitle =
                                'Payment Failed';

                            $notificationMessage =
                                'Payment for order '
                                . $currentOrder['order_number']
                                . ' could not be confirmed. Please contact Betachem if you need assistance.';

                            break;


                        case 'partial':

                            $notificationTitle =
                                'Partial Payment Received';

                            $notificationMessage =
                                'A partial payment has been recorded for order '
                                . $currentOrder['order_number']
                                . '.';

                            break;


                        case 'refunded':

                            $notificationTitle =
                                'Payment Refunded';

                            $notificationMessage =
                                'Payment for order '
                                . $currentOrder['order_number']
                                . ' has been refunded.';

                            break;
                    }


                    if (
                        $notificationTitle !== ''
                        && $notificationMessage !== ''
                    ) {

                        createNotification(
                            (int) $currentOrder['user_id'],
                            'payment',
                            $notificationTitle,
                            $notificationMessage,
                            $orderId
                        );
                    }
                }


                $message =
                    'Payment status updated successfully.';
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| Load Order
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        o.*,
        u.first_name AS customer_first_name,
        u.last_name AS customer_last_name,
        u.email AS customer_email,
        u.phone AS customer_phone
    FROM orders o
    LEFT JOIN users u
        ON u.id = o.user_id
    WHERE o.id = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $orderId
);

$stmt->execute();

$orderResult =
    $stmt->get_result();

$order =
    $orderResult->fetch_assoc();

$stmt->close();


if (!$order) {

    require_once __DIR__ . '/../includes/header.php';

    echo '
    <div class="container-fluid py-5">

        <div class="alert alert-danger">

            <i class="bi bi-exclamation-triangle me-2"></i>

            Order not found.

        </div>

        <a
            href="' . e($baseUrl) . '/admin/orders/index.php"
            class="btn btn-primary"
        >

            <i class="bi bi-arrow-left me-1"></i>

            Back to Orders

        </a>

    </div>
    ';

    require_once __DIR__ . '/../includes/footer.php';

    exit;
}


/*
|--------------------------------------------------------------------------
| Load Order Items
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        oi.*,
        p.main_image
    FROM order_items oi
    LEFT JOIN products p
        ON p.id = oi.product_id
    WHERE oi.order_id = ?
    ORDER BY oi.id ASC
");

$stmt->bind_param(
    "i",
    $orderId
);

$stmt->execute();

$itemsResult =
    $stmt->get_result();

$orderItems =
    $itemsResult->fetch_all(
        MYSQLI_ASSOC
    );

$stmt->close();


/*
|--------------------------------------------------------------------------
| Load Payment Record
|--------------------------------------------------------------------------
*/

$paymentRecord = null;

$stmt = $conn->prepare("
    SELECT
        id,
        transaction_reference,
        amount,
        payment_method,
        status,
        paid_at,
        gateway_response,
        proof_of_payment,
        proof_uploaded_at,
        created_at
    FROM payments
    WHERE order_id = ?
    ORDER BY id DESC
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $orderId
);

$stmt->execute();

$paymentResult =
    $stmt->get_result();

$paymentRecord =
    $paymentResult->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| Customer Information
|--------------------------------------------------------------------------
*/

$customerName = trim(
    ($order['customer_first_name'] ?? '')
    . ' '
    . ($order['customer_last_name'] ?? '')
);

if ($customerName === '') {

    $customerName = trim(
        ($order['shipping_first_name'] ?? '')
        . ' '
        . ($order['shipping_last_name'] ?? '')
    );
}

if ($customerName === '') {
    $customerName = 'Guest Customer';
}

$customerEmail =
    $order['customer_email']
    ?: $order['shipping_email'];

$customerPhone =
    $order['customer_phone']
    ?: $order['shipping_phone'];


/*
|--------------------------------------------------------------------------
| Status Helpers
|--------------------------------------------------------------------------
*/

function adminOrderStatusBadge($status)
{
    switch ($status) {

        case 'pending':
            return '<span class="badge bg-warning text-dark">Pending</span>';

        case 'processing':
            return '<span class="badge bg-info text-dark">Processing</span>';

        case 'ready':
            return '<span class="badge bg-primary">Ready</span>';

        case 'shipped':
            return '<span class="badge bg-info text-dark">Shipped</span>';

        case 'completed':
            return '<span class="badge bg-success">Completed</span>';

        case 'cancelled':
            return '<span class="badge bg-danger">Cancelled</span>';

        default:
            return '<span class="badge bg-secondary">'
                . e(ucfirst($status))
                . '</span>';
    }
}


function adminPaymentStatusBadge($status)
{
    switch ($status) {

        case 'pending':
            return '<span class="badge bg-warning text-dark">Pending</span>';

        case 'paid':
            return '<span class="badge bg-success">Paid</span>';

        case 'failed':
            return '<span class="badge bg-danger">Failed</span>';

        case 'refunded':
            return '<span class="badge bg-secondary">Refunded</span>';

        case 'partial':
            return '<span class="badge bg-info text-dark">Partial</span>';

        default:
            return '<span class="badge bg-secondary">'
                . e(ucfirst($status))
                . '</span>';
    }
}


/*
|--------------------------------------------------------------------------
| Product Image Helper
|--------------------------------------------------------------------------
*/

function adminOrderProductImage($image)
{
    global $baseUrl;

    if (!$image) {
        return null;
    }

    $image = trim($image);

    if ($image === '') {
        return null;
    }

    $image = str_replace('\\', '/', $image);

    if (strpos($image, 'uploads/products/') !== false) {

        $image = substr(
            $image,
            strrpos($image, 'uploads/products/')
            + strlen('uploads/products/')
        );
    }

    $image = ltrim($image, '/');

    $baseDirectory =
        __DIR__
        . '/../../uploads/products/';


    $possibleFiles = [];

    $possibleFiles[] = $image;


    if (
        !preg_match(
            '/\.(jpg|jpeg|png|webp|gif)$/i',
            $image
        )
    ) {

        $possibleFiles[] = $image . '.jpg';
        $possibleFiles[] = $image . '.jpeg';
        $possibleFiles[] = $image . '.png';
        $possibleFiles[] = $image . '.webp';
        $possibleFiles[] = $image . '.gif';
    }


    foreach ($possibleFiles as $possibleFile) {

        if (
            file_exists(
                $baseDirectory . $possibleFile
            )
        ) {

            return
                $baseUrl
                . '/uploads/products/'
                . rawurlencode($possibleFile);
        }
    }

    return null;
}


$allowedStatuses = [
    'pending',
    'processing',
    'ready',
    'shipped',
    'completed',
    'cancelled'
];


$allowedPaymentStatuses = [
    'pending',
    'paid',
    'failed',
    'refunded',
    'partial'
];


/*
|--------------------------------------------------------------------------
| Payment Proof URL
|--------------------------------------------------------------------------
*/

$proofUrl = null;

if (
    $paymentRecord
    && !empty(
        $paymentRecord['proof_of_payment']
    )
) {

    $proofFilename =
        basename(
            $paymentRecord['proof_of_payment']
        );

    $proofPath =
        __DIR__
        . '/../../uploads/payments/'
        . $proofFilename;

    if (is_file($proofPath)) {

        $proofUrl =
            $baseUrl
            . '/uploads/payments/'
            . rawurlencode(
                $proofFilename
            );
    }
}


/*
|--------------------------------------------------------------------------
| Header
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/header.php';

?>

<div class="container-fluid py-4">

    <!-- PAGE HEADER -->

    <div
        class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4"
    >

        <div>

            <div class="mb-2">

                <a
                    href="<?= $baseUrl; ?>/admin/orders/index.php"
                    class="text-decoration-none"
                >

                    <i class="bi bi-arrow-left me-1"></i>

                    Back to Orders

                </a>

            </div>


            <h1 class="fw-bold mb-1">

                <i class="bi bi-receipt me-2"></i>

                <?= e(
                    $order['order_number']
                ) ?>

            </h1>


            <p class="text-muted mb-0">

                Order placed on

                <?= e(
                    date(
                        'd M Y',
                        strtotime(
                            $order['created_at']
                        )
                    )
                ) ?>

                at

                <?= e(
                    date(
                        'H:i',
                        strtotime(
                            $order['created_at']
                        )
                    )
                ) ?>

            </p>

        </div>


        <div class="mt-3 mt-md-0">

            <?= adminOrderStatusBadge(
                $order['status']
            ) ?>

            <span class="ms-2">

                <?= adminPaymentStatusBadge(
                    $order['payment_status']
                ) ?>

            </span>

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


    <div class="row g-4">

        <!-- LEFT -->

        <div class="col-lg-8">


            <!-- PRODUCTS -->

            <div class="card border-0 shadow-sm mb-4">

                <div
                    class="card-header bg-white border-0 py-3"
                >

                    <h5 class="fw-bold mb-0">

                        <i
                            class="bi bi-box-seam me-2"
                        ></i>

                        Products Ordered

                    </h5>

                </div>


                <div class="card-body p-0">

                    <?php if (
                        empty($orderItems)
                    ): ?>

                        <div class="p-4">

                            <div
                                class="alert alert-warning mb-0"
                            >

                                No products were found for
                                this order.

                            </div>

                        </div>

                    <?php else: ?>

                        <div class="table-responsive">

                            <table
                                class="table align-middle mb-0"
                            >

                                <thead class="table-light">

                                    <tr>

                                        <th class="px-4">
                                            Product
                                        </th>

                                        <th>
                                            SKU
                                        </th>

                                        <th>
                                            Price
                                        </th>

                                        <th>
                                            Qty
                                        </th>

                                        <th
                                            class="text-end px-4"
                                        >
                                            Subtotal
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    <?php foreach (
                                        $orderItems
                                        as $item
                                    ): ?>

                                        <?php

                                        $imageUrl =
                                            adminOrderProductImage(
                                                $item['main_image']
                                                ?? null
                                            );

                                        ?>

                                        <tr>

                                            <td class="px-4">

                                                <div
                                                    class="d-flex align-items-center"
                                                >

                                                    <?php if (
                                                        $imageUrl
                                                    ): ?>

                                                        <img
                                                            src="<?= e(
                                                                $imageUrl
                                                            ) ?>"
                                                            alt="<?= e(
                                                                $item['product_name']
                                                            ) ?>"
                                                            style="
                                                                width:60px;
                                                                height:60px;
                                                                object-fit:cover;
                                                                border-radius:8px;
                                                            "
                                                            class="me-3"
                                                        >

                                                    <?php else: ?>

                                                        <div
                                                            class="bg-light d-flex align-items-center justify-content-center me-3"
                                                            style="
                                                                width:60px;
                                                                height:60px;
                                                                border-radius:8px;
                                                            "
                                                        >

                                                            <i
                                                                class="bi bi-image text-muted"
                                                            ></i>

                                                        </div>

                                                    <?php endif; ?>


                                                    <div>

                                                        <div
                                                            class="fw-semibold"
                                                        >

                                                            <?= e(
                                                                $item['product_name']
                                                            ) ?>

                                                        </div>

                                                    </div>

                                                </div>

                                            </td>


                                            <td>

                                                <small
                                                    class="text-muted"
                                                >

                                                    <?= e(
                                                        $item['sku']
                                                        ?: 'N/A'
                                                    ) ?>

                                                </small>

                                            </td>


                                            <td>

                                                <?= money(
                                                    $item['price']
                                                ) ?>

                                            </td>


                                            <td>

                                                <?= (int)
                                                    $item['quantity']
                                                ?>

                                            </td>


                                            <td
                                                class="text-end px-4"
                                            >

                                                <strong>

                                                    <?= money(
                                                        $item['subtotal']
                                                    ) ?>

                                                </strong>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>

                    <?php endif; ?>

                </div>

            </div>


            <!-- CUSTOMER -->

            <div
                class="card border-0 shadow-sm mb-4"
            >

                <div
                    class="card-header bg-white border-0 py-3"
                >

                    <h5 class="fw-bold mb-0">

                        <i
                            class="bi bi-person me-2"
                        ></i>

                        Customer Information

                    </h5>

                </div>


                <div class="card-body">

                    <div class="row g-4">

                        <div class="col-md-4">

                            <small
                                class="text-muted d-block"
                            >
                                Customer
                            </small>

                            <strong>

                                <?= e(
                                    $customerName
                                ) ?>

                            </strong>

                        </div>


                        <div class="col-md-4">

                            <small
                                class="text-muted d-block"
                            >
                                Email
                            </small>

                            <strong>

                                <?= e(
                                    $customerEmail
                                    ?: 'N/A'
                                ) ?>

                            </strong>

                        </div>


                        <div class="col-md-4">

                            <small
                                class="text-muted d-block"
                            >
                                Phone
                            </small>

                            <strong>

                                <?= e(
                                    $customerPhone
                                    ?: 'N/A'
                                ) ?>

                            </strong>

                        </div>

                    </div>

                </div>

            </div>


            <!-- DELIVERY -->

            <div
                class="card border-0 shadow-sm mb-4"
            >

                <div
                    class="card-header bg-white border-0 py-3"
                >

                    <h5 class="fw-bold mb-0">

                        <i
                            class="bi bi-truck me-2"
                        ></i>

                        Delivery Information

                    </h5>

                </div>


                <div class="card-body">

                    <div class="row g-4">

                        <div class="col-md-6">

                            <h6 class="fw-bold">
                                Recipient
                            </h6>

                            <p class="mb-1">

                                <?= e(
                                    trim(
                                        $order['shipping_first_name']
                                        . ' '
                                        . $order['shipping_last_name']
                                    )
                                ) ?>

                            </p>

                            <p class="mb-1">

                                <?= e(
                                    $order['shipping_phone']
                                ) ?>

                            </p>

                            <p class="mb-0">

                                <?= e(
                                    $order['shipping_email']
                                ) ?>

                            </p>

                        </div>


                        <div class="col-md-6">

                            <h6 class="fw-bold">
                                Delivery Address
                            </h6>

                            <p class="mb-1">

                                <?= nl2br(
                                    e(
                                        $order['shipping_address']
                                    )
                                ) ?>

                            </p>

                            <p class="mb-1">

                                <?= e(
                                    $order['shipping_city']
                                ) ?>

                            </p>

                            <p class="mb-1">

                                <?= e(
                                    $order['shipping_province']
                                ) ?>

                            </p>

                            <p class="mb-0">

                                <?= e(
                                    $order['shipping_postal_code']
                                ) ?>

                            </p>

                        </div>

                    </div>

                </div>

            </div>


            <!-- NOTES -->

            <?php if (
                !empty($order['notes'])
            ): ?>

                <div
                    class="card border-0 shadow-sm mb-4"
                >

                    <div
                        class="card-header bg-white border-0 py-3"
                    >

                        <h5 class="fw-bold mb-0">

                            <i
                                class="bi bi-chat-left-text me-2"
                            ></i>

                            Customer Notes

                        </h5>

                    </div>


                    <div class="card-body">

                        <div
                            class="alert alert-light border mb-0"
                        >

                            <?= nl2br(
                                e(
                                    $order['notes']
                                )
                            ) ?>

                        </div>

                    </div>

                </div>

            <?php endif; ?>


            <!-- PAYMENT INFORMATION -->

            <div
                class="card border-0 shadow-sm mb-4"
            >

                <div
                    class="card-header bg-white border-0 py-3"
                >

                    <h5 class="fw-bold mb-0">

                        <i
                            class="bi bi-credit-card me-2"
                        ></i>

                        Payment Information

                    </h5>

                </div>


                <div class="card-body">

                    <div class="row g-4">

                        <div class="col-md-4">

                            <small
                                class="text-muted d-block"
                            >
                                Payment Status
                            </small>

                            <?= adminPaymentStatusBadge(
                                $order['payment_status']
                            ) ?>

                        </div>


                        <div class="col-md-4">

                            <small
                                class="text-muted d-block"
                            >
                                Payment Method
                            </small>

                            <strong>

                                <?= e(
                                    $order['payment_method']
                                    ? strtoupper(
                                        $order['payment_method']
                                    )
                                    : 'Not specified'
                                ) ?>

                            </strong>

                        </div>


                        <div class="col-md-4">

                            <small
                                class="text-muted d-block"
                            >
                                Amount
                            </small>

                            <strong>

                                <?= money(
                                    $order['total']
                                ) ?>

                            </strong>

                        </div>

                    </div>


                    <?php if (
                        $paymentRecord
                    ): ?>

                        <hr class="my-4">


                        <div class="row g-4">

                            <div class="col-md-4">

                                <small
                                    class="text-muted d-block"
                                >
                                    Payment Record
                                </small>

                                <strong>

                                    #<?= (int)
                                        $paymentRecord['id']
                                    ?>

                                </strong>

                            </div>


                            <div class="col-md-4">

                                <small
                                    class="text-muted d-block"
                                >
                                    Transaction Reference
                                </small>

                                <strong>

                                    <?= e(
                                        $paymentRecord[
                                            'transaction_reference'
                                        ]
                                        ?: 'N/A'
                                    ) ?>

                                </strong>

                            </div>


                            <div class="col-md-4">

                                <small
                                    class="text-muted d-block"
                                >
                                    Payment Record Status
                                </small>

                                <?php

                                if (
                                    $paymentRecord['status']
                                    === 'successful'
                                ) {

                                    echo '
                                    <span class="badge bg-success">
                                        Successful
                                    </span>
                                    ';

                                } elseif (
                                    $paymentRecord['status']
                                    === 'failed'
                                ) {

                                    echo '
                                    <span class="badge bg-danger">
                                        Failed
                                    </span>
                                    ';

                                } elseif (
                                    $paymentRecord['status']
                                    === 'refunded'
                                ) {

                                    echo '
                                    <span class="badge bg-secondary">
                                        Refunded
                                    </span>
                                    ';

                                } else {

                                    echo '
                                    <span class="badge bg-warning text-dark">
                                        Pending
                                    </span>
                                    ';
                                }

                                ?>

                            </div>

                        </div>

                    <?php endif; ?>

                </div>

            </div>


            <!-- PROOF OF PAYMENT -->

            <div
                class="card border-0 shadow-sm mb-4"
            >

                <div
                    class="card-header bg-white border-0 py-3"
                >

                    <h5 class="fw-bold mb-0">

                        <i
                            class="bi bi-file-earmark-check me-2"
                        ></i>

                        Proof of Payment

                    </h5>

                </div>


                <div class="card-body">

                    <?php if ($proofUrl): ?>

                        <div
                            class="alert alert-success"
                        >

                            <div
                                class="d-flex align-items-start"
                            >

                                <i
                                    class="bi bi-check-circle-fill fs-4 me-3"
                                ></i>

                                <div>

                                    <strong>
                                        Proof of payment uploaded
                                    </strong>

                                    <div class="small mt-1">

                                        The customer has uploaded
                                        a payment proof for this
                                        order.

                                    </div>

                                </div>

                            </div>

                        </div>


                        <div class="p-3 bg-light rounded-3">

                            <div class="row g-3 align-items-center">

                                <div class="col-md-7">

                                    <div
                                        class="small text-muted"
                                    >
                                        File
                                    </div>

                                    <div
                                        class="fw-semibold text-break"
                                    >

                                        <?= e(
                                            basename(
                                                $paymentRecord[
                                                    'proof_of_payment'
                                                ]
                                            )
                                        ) ?>

                                    </div>


                                    <?php if (
                                        !empty(
                                            $paymentRecord[
                                                'proof_uploaded_at'
                                            ]
                                        )
                                    ): ?>

                                        <div
                                            class="small text-muted mt-1"
                                        >

                                            Uploaded:

                                            <?= e(
                                                date(
                                                    'd M Y H:i',
                                                    strtotime(
                                                        $paymentRecord[
                                                            'proof_uploaded_at'
                                                        ]
                                                    )
                                                )
                                            ) ?>

                                        </div>

                                    <?php endif; ?>

                                </div>


                                <div class="col-md-5 text-md-end">

                                    <a
                                        href="<?= e(
                                            $proofUrl
                                        ) ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="btn btn-outline-primary"
                                    >

                                        <i
                                            class="bi bi-eye me-1"
                                        ></i>

                                        View Proof

                                    </a>

                                </div>

                            </div>

                        </div>


                        <?php if (
                            $order['payment_status']
                            !== 'paid'
                        ): ?>

                            <div
                                class="alert alert-warning mt-3 mb-0"
                            >

                                <i
                                    class="bi bi-exclamation-triangle me-2"
                                ></i>

                                <strong>
                                    Action required:
                                </strong>

                                Review the proof above and
                                update the payment status if
                                the payment has been received.

                            </div>

                        <?php endif; ?>

                    <?php else: ?>

                        <div
                            class="alert alert-light border mb-0"
                        >

                            <i
                                class="bi bi-file-earmark-x me-2"
                            ></i>

                            No proof of payment has been
                            uploaded for this order yet.

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>


        <!-- RIGHT -->

        <div class="col-lg-4">


            <!-- ORDER SUMMARY -->

            <div
                class="card border-0 shadow-sm mb-4"
            >

                <div
                    class="card-header bg-white border-0 py-3"
                >

                    <h5 class="fw-bold mb-0">

                        <i
                            class="bi bi-calculator me-2"
                        ></i>

                        Order Summary

                    </h5>

                </div>


                <div class="card-body">

                    <div
                        class="d-flex justify-content-between mb-2"
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


                    <div
                        class="d-flex justify-content-between mb-2"
                    >

                        <span class="text-muted">
                            Delivery
                        </span>

                        <span>

                            <?= money(
                                $order['delivery_fee']
                            ) ?>

                        </span>

                    </div>


                    <div
                        class="d-flex justify-content-between mb-3"
                    >

                        <span class="text-muted">
                            Discount
                        </span>

                        <span>

                            - <?= money(
                                $order['discount']
                            ) ?>

                        </span>

                    </div>


                    <hr>


                    <div
                        class="d-flex justify-content-between"
                    >

                        <strong class="fs-5">
                            Total
                        </strong>

                        <strong
                            class="fs-5 text-primary"
                        >

                            <?= money(
                                $order['total']
                            ) ?>

                        </strong>

                    </div>

                </div>

            </div>


            <!-- UPDATE ORDER -->

            <div
                class="card border-0 shadow-sm mb-4"
            >

                <div
                    class="card-header bg-white border-0 py-3"
                >

                    <h5 class="fw-bold mb-0">

                        <i
                            class="bi bi-arrow-repeat me-2"
                        ></i>

                        Update Order Status

                    </h5>

                </div>


                <div class="card-body">

                    <form
                        method="POST"
                        action="<?= $baseUrl; ?>/admin/orders/view.php?id=<?= (int) $orderId ?>"
                    >

                        <input
                            type="hidden"
                            name="action"
                            value="update_order_status"
                        >


                        <label
                            class="form-label fw-semibold"
                        >
                            Status
                        </label>


                        <select
                            name="status"
                            class="form-select mb-3"
                        >

                            <?php foreach (
                                $allowedStatuses
                                as $itemStatus
                            ): ?>

                                <option
                                    value="<?= e(
                                        $itemStatus
                                    ) ?>"
                                    <?= $order['status']
                                        === $itemStatus
                                        ? 'selected'
                                        : '' ?>
                                >

                                    <?= e(
                                        ucfirst(
                                            $itemStatus
                                        )
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>


                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >

                            <i
                                class="bi bi-check-lg me-1"
                            ></i>

                            Update Order Status

                        </button>

                    </form>

                </div>

            </div>


            <!-- PAYMENT REVIEW -->

            <div
                class="card border-0 shadow-sm mb-4"
            >

                <div
                    class="card-header bg-white border-0 py-3"
                >

                    <h5 class="fw-bold mb-0">

                        <i
                            class="bi bi-shield-check me-2"
                        ></i>

                        Payment Review

                    </h5>

                </div>


                <div class="card-body">

                    <?php if (
                        $proofUrl
                        && $order['payment_status']
                        !== 'paid'
                    ): ?>

                        <div
                            class="alert alert-info small"
                        >

                            <i
                                class="bi bi-info-circle me-2"
                            ></i>

                            A proof of payment has been
                            uploaded. Review it before
                            confirming the payment.

                        </div>

                    <?php endif; ?>


                    <form
                        method="POST"
                        action="<?= $baseUrl; ?>/admin/orders/view.php?id=<?= (int) $orderId ?>"
                    >

                        <input
                            type="hidden"
                            name="action"
                            value="update_payment_status"
                        >


                        <label
                            class="form-label fw-semibold"
                        >
                            Payment Status
                        </label>


                        <select
                            name="payment_status"
                            class="form-select mb-3"
                        >

                            <?php foreach (
                                $allowedPaymentStatuses
                                as $itemPaymentStatus
                            ): ?>

                                <option
                                    value="<?= e(
                                        $itemPaymentStatus
                                    ) ?>"
                                    <?= $order[
                                        'payment_status'
                                    ]
                                        === $itemPaymentStatus
                                        ? 'selected'
                                        : '' ?>
                                >

                                    <?php

                                    $statusLabel =
                                        ucfirst(
                                            $itemPaymentStatus
                                        );

                                    if (
                                        $itemPaymentStatus
                                        === 'paid'
                                    ) {

                                        $statusLabel =
                                            'Paid / Confirmed';
                                    }

                                    ?>

                                    <?= e(
                                        $statusLabel
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>


                        <button
                            type="submit"
                            class="btn btn-success w-100"
                        >

                            <i
                                class="bi bi-check-circle me-1"
                            ></i>

                            Update Payment Status

                        </button>

                    </form>


                    <?php if (
                        $proofUrl
                        && $order['payment_status']
                        !== 'paid'
                    ): ?>

                        <div class="mt-3">

                            <a
                                href="<?= e(
                                    $proofUrl
                                ) ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn btn-outline-primary w-100"
                            >

                                <i
                                    class="bi bi-file-earmark-pdf me-1"
                                ></i>

                                Open Proof of Payment

                            </a>

                        </div>

                    <?php endif; ?>

                </div>

            </div>


            <!-- ACTIONS -->

            <div
                class="card border-0 shadow-sm"
            >

                <div
                    class="card-header bg-white border-0 py-3"
                >

                    <h5 class="fw-bold mb-0">

                        <i
                            class="bi bi-lightning me-2"
                        ></i>

                        Actions

                    </h5>

                </div>


                <div class="card-body">

                    <a
                        href="<?= $baseUrl; ?>/admin/orders/index.php"
                        class="btn btn-outline-primary w-100 mb-2"
                    >

                        <i
                            class="bi bi-arrow-left me-1"
                        ></i>

                        Back to Orders

                    </a>


                    <a
                        href="<?= $baseUrl; ?>/admin/index.php"
                        class="btn btn-outline-secondary w-100"
                    >

                        <i
                            class="bi bi-speedometer2 me-1"
                        ></i>

                        Dashboard

                    </a>

                </div>

            </div>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>