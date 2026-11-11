<?php

/*
|--------------------------------------------------------------------------
| Betachem E-Commerce
| Customer Order Details & Tracking
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/notification_functions.php';

requireLogin();

$userId = (int) $_SESSION['user_id'];

$baseUrl = '/betachem/betachem_e-commerce';

$uploadError = '';
$uploadSuccess = '';


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
    redirect($baseUrl . '/orders.php');
}


/*
|--------------------------------------------------------------------------
| Load Order
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        o.*
    FROM orders o
    WHERE o.id = ?
    AND o.user_id = ?
    LIMIT 1
");

$stmt->bind_param(
    "ii",
    $orderId,
    $userId
);

$stmt->execute();

$orderResult = $stmt->get_result();

$order = $orderResult->fetch_assoc();

$stmt->close();


if (!$order) {
    redirect($baseUrl . '/orders.php');
}


/*
|--------------------------------------------------------------------------
| Order Values
|--------------------------------------------------------------------------
*/

$orderStatus = strtolower(
    trim($order['status'] ?? '')
);

$paymentStatus = strtolower(
    trim($order['payment_status'] ?? '')
);

$paymentMethod = strtolower(
    trim($order['payment_method'] ?? '')
);


/*
|--------------------------------------------------------------------------
| Proof of Payment Upload
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['upload_proof'])
) {

    /*
    |--------------------------------------------------------------------------
    | Only EFT Orders
    |--------------------------------------------------------------------------
    */

    if ($paymentMethod !== 'eft') {

        $uploadError =
            'Proof of payment upload is only available for EFT / Bank Transfer orders.';

    }

    /*
    |--------------------------------------------------------------------------
    | Do Not Upload After Payment Is Confirmed
    |--------------------------------------------------------------------------
    */

    elseif ($paymentStatus === 'paid') {

        $uploadError =
            'Payment has already been confirmed. A new proof of payment cannot be uploaded.';

    }

    elseif ($paymentStatus === 'refunded') {

        $uploadError =
            'This payment has been refunded and proof of payment cannot be uploaded.';

    }

    /*
    |--------------------------------------------------------------------------
    | Check File
    |--------------------------------------------------------------------------
    */

    elseif (
        !isset($_FILES['proof_of_payment'])
        || $_FILES['proof_of_payment']['error'] === UPLOAD_ERR_NO_FILE
    ) {

        $uploadError =
            'Please select a proof of payment file to upload.';

    }

    elseif ($_FILES['proof_of_payment']['error'] !== UPLOAD_ERR_OK) {

        $uploadError =
            'There was a problem uploading the file. Please try again.';

    }

    else {

        $file = $_FILES['proof_of_payment'];


        /*
        |--------------------------------------------------------------------------
        | Maximum File Size
        |--------------------------------------------------------------------------
        */

        $maxFileSize = 5 * 1024 * 1024;

        if ($file['size'] > $maxFileSize) {

            $uploadError =
                'The proof of payment file must not exceed 5 MB.';

        }

        else {

            /*
            |--------------------------------------------------------------------------
            | Detect MIME Type
            |--------------------------------------------------------------------------
            */

            $finfo = new finfo(FILEINFO_MIME_TYPE);

            $mimeType = $finfo->file(
                $file['tmp_name']
            );

            $allowedTypes = [

                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'application/pdf' => 'pdf'

            ];


            if (!isset($allowedTypes[$mimeType])) {

                $uploadError =
                    'Invalid file type. Please upload a JPG, PNG or PDF file.';

            }

            else {

                /*
                |--------------------------------------------------------------------------
                | Upload Directory
                |--------------------------------------------------------------------------
                */

                $uploadDirectory =
                    __DIR__ . '/uploads/payments/';

                if (!is_dir($uploadDirectory)) {

                    if (!mkdir(
                        $uploadDirectory,
                        0755,
                        true
                    )) {

                        $uploadError =
                            'The payment upload folder could not be created.';

                    }

                }


                if (!$uploadError) {

                    /*
                    |--------------------------------------------------------------------------
                    | Generate Safe Filename
                    |--------------------------------------------------------------------------
                    */

                    $extension =
                        $allowedTypes[$mimeType];

                    $randomName =
                        bin2hex(
                            random_bytes(16)
                        );

                    $fileName =
                        'payment_'
                        . $orderId
                        . '_'
                        . $randomName
                        . '.'
                        . $extension;

                    $destination =
                        $uploadDirectory
                        . $fileName;


                    /*
                    |--------------------------------------------------------------------------
                    | Move Uploaded File
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !move_uploaded_file(
                            $file['tmp_name'],
                            $destination
                        )
                    ) {

                        $uploadError =
                            'The proof of payment could not be saved. Please try again.';

                    }

                    else {

                        /*
                        |--------------------------------------------------------------------------
                        | Load Existing Payment
                        |--------------------------------------------------------------------------
                        */

                        $paymentStmt = $conn->prepare("
                            SELECT
                                id,
                                proof_of_payment
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

                        $existingPayment =
                            $paymentResult->fetch_assoc();

                        $paymentStmt->close();


                        /*
                        |--------------------------------------------------------------------------
                        | Delete Previous Proof
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $existingPayment
                            && !empty(
                                $existingPayment['proof_of_payment']
                            )
                        ) {

                            $oldFile =
                                $uploadDirectory
                                . basename(
                                    $existingPayment['proof_of_payment']
                                );

                            if (
                                is_file($oldFile)
                            ) {

                                @unlink($oldFile);

                            }

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Save Payment Record
                        |--------------------------------------------------------------------------
                        */

                        if ($existingPayment) {

                            $updatePayment =
                                $conn->prepare("
                                    UPDATE payments
                                    SET
                                        proof_of_payment = ?,
                                        proof_uploaded_at = NOW(),
                                        status = 'pending'
                                    WHERE id = ?
                                ");

                            $updatePayment->bind_param(
                                "si",
                                $fileName,
                                $existingPayment['id']
                            );

                            $updatePayment->execute();

                            $updatePayment->close();

                        } else {

                            $orderNumber =
                                $order['order_number'];

                            $orderTotal =
                                (float) $order['total'];

                            $paymentMethodValue =
                                'eft';

                            $paymentStatusValue =
                                'pending';

                            $insertPayment =
                                $conn->prepare("
                                    INSERT INTO payments
                                    (
                                        order_id,
                                        transaction_reference,
                                        amount,
                                        payment_method,
                                        status,
                                        proof_of_payment,
                                        proof_uploaded_at
                                    )
                                    VALUES
                                    (
                                        ?,
                                        ?,
                                        ?,
                                        ?,
                                        ?,
                                        ?,
                                        NOW()
                                    )
                                ");

                            $insertPayment->bind_param(
                                "isdsss",
                                $orderId,
                                $orderNumber,
                                $orderTotal,
                                $paymentMethodValue,
                                $paymentStatusValue,
                                $fileName
                            );

                            $insertPayment->execute();

                            $insertPayment->close();

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Keep Order Payment Pending
                        |--------------------------------------------------------------------------
                        */

                        $updateOrder =
                            $conn->prepare("
                                UPDATE orders
                                SET payment_status = 'pending'
                                WHERE id = ?
                                AND user_id = ?
                            ");

                        $updateOrder->bind_param(
                            "ii",
                            $orderId,
                            $userId
                        );

                        $updateOrder->execute();

                        $updateOrder->close();


                        /*
                        |--------------------------------------------------------------------------
                        | Success
                        |--------------------------------------------------------------------------
                        */

                        $uploadSuccess =
                            'Your proof of payment has been uploaded successfully. Betachem will review it and update your payment status.';


                        /*
                        |--------------------------------------------------------------------------
                        | Refresh Payment Status
                        |--------------------------------------------------------------------------
                        */

                        $paymentStatus = 'pending';

                    }

                }

            }

        }

    }

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

$itemsResult = $stmt->get_result();

$orderItems =
    $itemsResult->fetch_all(
        MYSQLI_ASSOC
    );

$stmt->close();


/*
|--------------------------------------------------------------------------
| Product Image Helper
|--------------------------------------------------------------------------
*/

function customerOrderProductImage($image)
{
    global $baseUrl;

    if (!$image) {
        return null;
    }

    $baseDirectory =
        __DIR__ . '/uploads/products/';

    $image = trim($image);

    $image = str_replace(
        '\\',
        '/',
        $image
    );


    /*
    |--------------------------------------------------------------------------
    | Remove Existing Upload Path
    |--------------------------------------------------------------------------
    */

    if (
        strpos(
            $image,
            'uploads/products/'
        ) !== false
    ) {

        $image = substr(
            $image,
            strrpos(
                $image,
                'uploads/products/'
            ) + strlen('uploads/products/')
        );
    }

    $image = ltrim(
        $image,
        '/'
    );


    /*
    |--------------------------------------------------------------------------
    | Possible Files
    |--------------------------------------------------------------------------
    */

    $possibleFiles = [];

    $possibleFiles[] = $image;


    if (
        !preg_match(
            '/\.(jpg|jpeg|png|webp|gif)$/i',
            $image
        )
    ) {

        $possibleFiles[] =
            $image . '.jpg';

        $possibleFiles[] =
            $image . '.jpeg';

        $possibleFiles[] =
            $image . '.png';

        $possibleFiles[] =
            $image . '.webp';

        $possibleFiles[] =
            $image . '.gif';
    }


    /*
    |--------------------------------------------------------------------------
    | Find Existing File
    |--------------------------------------------------------------------------
    */

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


/*
|--------------------------------------------------------------------------
| Get Current Payment Proof
|--------------------------------------------------------------------------
*/

$paymentProof = null;
$paymentProofDate = null;

$proofStmt = $conn->prepare("
    SELECT
        proof_of_payment,
        proof_uploaded_at
    FROM payments
    WHERE order_id = ?
    ORDER BY id DESC
    LIMIT 1
");

$proofStmt->bind_param(
    "i",
    $orderId
);

$proofStmt->execute();

$proofResult =
    $proofStmt->get_result();

$paymentRecord =
    $proofResult->fetch_assoc();

$proofStmt->close();


if ($paymentRecord) {

    $paymentProof =
        $paymentRecord['proof_of_payment']
        ?? null;

    $paymentProofDate =
        $paymentRecord['proof_uploaded_at']
        ?? null;
}


/*
|--------------------------------------------------------------------------
| Payment Status Display
|--------------------------------------------------------------------------
*/

switch ($paymentStatus) {

    case 'paid':

        $paymentBadge = 'bg-success';
        $paymentLabel = 'Paid';

        break;


    case 'failed':

        $paymentBadge = 'bg-danger';
        $paymentLabel = 'Failed';

        break;


    case 'refunded':

        $paymentBadge = 'bg-secondary';
        $paymentLabel = 'Refunded';

        break;


    case 'partial':

        $paymentBadge = 'bg-info text-dark';
        $paymentLabel = 'Partial';

        break;


    default:

        $paymentBadge = 'bg-warning text-dark';
        $paymentLabel = 'Pending';

        break;

}


/*
|--------------------------------------------------------------------------
| Order Timeline
|--------------------------------------------------------------------------
*/

$timeline = [

    'pending' => [
        'title' => 'Order Placed',
        'description' =>
            'Your order has been received by Betachem.',
        'icon' => 'bi-bag-check'
    ],

    'processing' => [
        'title' => 'Processing',
        'description' =>
            'Your order is being prepared.',
        'icon' => 'bi-gear'
    ],

    'ready' => [
        'title' => 'Ready',
        'description' =>
            'Your order is ready for the next stage.',
        'icon' => 'bi-box-seam'
    ],

    'shipped' => [
        'title' => 'Shipped',
        'description' =>
            'Your order has been shipped.',
        'icon' => 'bi-truck'
    ],

    'completed' => [
        'title' => 'Completed',
        'description' =>
            'Your order has been completed successfully.',
        'icon' => 'bi-check-circle'
    ]

];


$statusOrder = [
    'pending',
    'processing',
    'ready',
    'shipped',
    'completed'
];


$currentStatusIndex =
    array_search(
        $orderStatus,
        $statusOrder,
        true
    );


if ($currentStatusIndex === false) {
    $currentStatusIndex = 0;
}


/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/includes/header.php';

?>

<div class="container py-5">

    <!-- BACK -->

    <div class="mb-4">

        <a
            href="<?= $baseUrl; ?>/orders.php"
            class="text-decoration-none"
        >

            <i class="bi bi-arrow-left me-1"></i>

            Back to My Orders

        </a>

    </div>


    <!-- ORDER HEADER -->

    <div
        class="card border-0 shadow-sm rounded-4 mb-4"
    >

        <div class="card-body p-4 p-lg-5">

            <div
                class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3"
            >

                <div>

                    <div
                        class="text-uppercase small fw-semibold mb-2"
                        style="
                            color:#1769aa;
                            letter-spacing:1.5px;
                        "
                    >
                        ORDER TRACKING
                    </div>


                    <h1
                        class="fw-bold mb-2"
                        style="color:#102b49;"
                    >

                        <?= e(
                            $order['order_number']
                        ) ?>

                    </h1>


                    <p class="text-muted mb-0">

                        Placed on

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


                <div>

                    <?php if ($orderStatus === 'cancelled'): ?>

                        <span
                            class="badge bg-danger fs-6 px-3 py-2"
                        >

                            <i class="bi bi-x-circle me-1"></i>

                            Cancelled

                        </span>

                    <?php else: ?>

                        <span
                            class="badge bg-primary fs-6 px-3 py-2"
                        >

                            <?= e(
                                ucfirst(
                                    $orderStatus
                                )
                            ) ?>

                        </span>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>


    <!-- CANCELLED -->

    <?php if ($orderStatus === 'cancelled'): ?>

        <div
            class="alert alert-danger rounded-4 shadow-sm mb-4"
        >

            <div class="d-flex align-items-start">

                <i
                    class="bi bi-exclamation-circle-fill fs-4 me-3"
                ></i>

                <div>

                    <strong>
                        This order has been cancelled.
                    </strong>

                    <div class="small mt-1">

                        Please contact Betachem if you need
                        further information about this order.

                    </div>

                </div>

            </div>

        </div>

    <?php else: ?>


        <!-- ORDER PROGRESS -->

        <div
            class="card border-0 shadow-sm rounded-4 mb-4"
        >

            <div class="card-body p-4 p-lg-5">

                <div class="mb-4">

                    <h4
                        class="fw-bold mb-1"
                        style="color:#102b49;"
                    >
                        Order Progress
                    </h4>

                    <p class="text-muted mb-0">
                        Follow your order from placement to completion.
                    </p>

                </div>


                <div class="order-tracker">

                    <?php foreach ($statusOrder as $index => $status): ?>

                        <?php

                        $step =
                            $timeline[$status];

                        $isCompleted =
                            $index < $currentStatusIndex;

                        $isCurrent =
                            $index === $currentStatusIndex;

                        $isPending =
                            $index > $currentStatusIndex;

                        ?>

                        <div
                            class="
                                order-tracker-step
                                <?= $isCompleted ? 'completed' : '' ?>
                                <?= $isCurrent ? 'current' : '' ?>
                                <?= $isPending ? 'pending' : '' ?>
                            "
                        >

                            <div class="order-tracker-icon">

                                <i
                                    class="bi <?= e(
                                        $step['icon']
                                    ) ?>"
                                ></i>

                            </div>


                            <div class="order-tracker-content">

                                <div
                                    class="fw-bold"
                                    style="color:#102b49;"
                                >

                                    <?= e(
                                        $step['title']
                                    ) ?>

                                </div>


                                <small class="text-muted">

                                    <?= e(
                                        $step['description']
                                    ) ?>

                                </small>

                            </div>


                            <?php if (
                                $index <
                                count($statusOrder) - 1
                            ): ?>

                                <div
                                    class="order-tracker-line"
                                ></div>

                            <?php endif; ?>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

        </div>

    <?php endif; ?>


    <div class="row g-4">


        <!-- LEFT -->

        <div class="col-lg-8">


            <!-- PRODUCTS -->

            <div
                class="card border-0 shadow-sm rounded-4 mb-4"
            >

                <div
                    class="card-header bg-white border-0 p-4"
                >

                    <h5
                        class="fw-bold mb-0"
                        style="color:#102b49;"
                    >

                        <i
                            class="bi bi-box-seam me-2"
                            style="color:#1769aa;"
                        ></i>

                        Products Ordered

                    </h5>

                </div>


                <div class="card-body p-0">

                    <?php foreach ($orderItems as $item): ?>

                        <?php

                        $imageUrl =
                            customerOrderProductImage(
                                $item['main_image']
                                ?? null
                            );

                        ?>

                        <div class="p-4 border-top">

                            <div
                                class="d-flex align-items-center gap-3"
                            >

                                <?php if ($imageUrl): ?>

                                    <img
                                        src="<?= e($imageUrl) ?>"
                                        alt="<?= e(
                                            $item['product_name']
                                        ) ?>"
                                        style="
                                            width:75px;
                                            height:75px;
                                            object-fit:cover;
                                            border-radius:10px;
                                            border:1px solid #e5edf3;
                                        "
                                    >

                                <?php else: ?>

                                    <div
                                        class="d-flex align-items-center justify-content-center bg-light"
                                        style="
                                            width:75px;
                                            height:75px;
                                            border-radius:10px;
                                        "
                                    >

                                        <i
                                            class="bi bi-image text-muted fs-4"
                                        ></i>

                                    </div>

                                <?php endif; ?>


                                <div class="flex-grow-1">

                                    <h6 class="fw-bold mb-1">

                                        <?= e(
                                            $item['product_name']
                                        ) ?>

                                    </h6>


                                    <div
                                        class="small text-muted mb-1"
                                    >

                                        SKU:

                                        <?= e(
                                            $item['sku']
                                            ?: 'N/A'
                                        ) ?>

                                    </div>


                                    <div
                                        class="small text-muted"
                                    >

                                        Quantity:

                                        <?= (int)
                                            $item['quantity']
                                        ?>

                                    </div>

                                </div>


                                <div class="text-end">

                                    <div
                                        class="small text-muted"
                                    >
                                        Price
                                    </div>


                                    <strong>

                                        <?= money(
                                            $item['price']
                                        ) ?>

                                    </strong>


                                    <div
                                        class="small text-muted mt-1"
                                    >
                                        Subtotal
                                    </div>


                                    <strong
                                        style="color:#1769aa;"
                                    >

                                        <?= money(
                                            $item['subtotal']
                                        ) ?>

                                    </strong>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>


            <!-- DELIVERY INFORMATION -->

            <div
                class="card border-0 shadow-sm rounded-4 mb-4"
            >

                <div
                    class="card-header bg-white border-0 p-4"
                >

                    <h5
                        class="fw-bold mb-0"
                        style="color:#102b49;"
                    >

                        <i
                            class="bi bi-truck me-2"
                            style="color:#1769aa;"
                        ></i>

                        Delivery Information

                    </h5>

                </div>


                <div class="card-body p-4">

                    <div class="row g-4">

                        <div class="col-md-6">

                            <div
                                class="small text-muted mb-1"
                            >
                                Recipient
                            </div>

                            <div class="fw-semibold">

                                <?= e(
                                    trim(
                                        $order['shipping_first_name']
                                        . ' '
                                        . $order['shipping_last_name']
                                    )
                                ) ?>

                            </div>


                            <div
                                class="small text-muted mt-3"
                            >
                                Phone
                            </div>

                            <div>

                                <?= e(
                                    $order['shipping_phone']
                                ) ?>

                            </div>


                            <div
                                class="small text-muted mt-3"
                            >
                                Email
                            </div>

                            <div>

                                <?= e(
                                    $order['shipping_email']
                                ) ?>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div
                                class="small text-muted mb-1"
                            >
                                Delivery Address
                            </div>

                            <div>

                                <?= nl2br(
                                    e(
                                        $order['shipping_address']
                                    )
                                ) ?>

                            </div>

                            <div>

                                <?= e(
                                    $order['shipping_city']
                                ) ?>

                            </div>

                            <div>

                                <?= e(
                                    $order['shipping_province']
                                ) ?>

                            </div>

                            <div>

                                <?= e(
                                    $order['shipping_postal_code']
                                ) ?>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- NOTES -->

            <?php if (!empty($order['notes'])): ?>

                <div
                    class="card border-0 shadow-sm rounded-4 mb-4"
                >

                    <div
                        class="card-header bg-white border-0 p-4"
                    >

                        <h5
                            class="fw-bold mb-0"
                            style="color:#102b49;"
                        >

                            <i
                                class="bi bi-chat-left-text me-2"
                                style="color:#1769aa;"
                            ></i>

                            Order Notes

                        </h5>

                    </div>


                    <div class="card-body p-4">

                        <div
                            class="p-3 rounded-3"
                            style="background:#f5f8fb;"
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

        </div>


        <!-- RIGHT -->

        <div class="col-lg-4">


            <!-- ORDER SUMMARY -->

            <div
                class="card border-0 shadow-sm rounded-4 mb-4"
            >

                <div
                    class="card-header bg-white border-0 p-4"
                >

                    <h5
                        class="fw-bold mb-0"
                        style="color:#102b49;"
                    >

                        <i
                            class="bi bi-receipt me-2"
                            style="color:#1769aa;"
                        ></i>

                        Order Summary

                    </h5>

                </div>


                <div class="card-body p-4">

                    <div
                        class="d-flex justify-content-between mb-3"
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
                        class="d-flex justify-content-between mb-3"
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
                            class="fs-5"
                            style="color:#1769aa;"
                        >

                            <?= money(
                                $order['total']
                            ) ?>

                        </strong>

                    </div>

                </div>

            </div>


            <!-- PAYMENT -->

            <div
                class="card border-0 shadow-sm rounded-4 mb-4"
            >

                <div
                    class="card-header bg-white border-0 p-4"
                >

                    <h5
                        class="fw-bold mb-0"
                        style="color:#102b49;"
                    >

                        <i
                            class="bi bi-credit-card me-2"
                            style="color:#1769aa;"
                        ></i>

                        Payment

                    </h5>

                </div>


                <div class="card-body p-4">


                    <!-- PAYMENT STATUS -->

                    <div
                        class="d-flex justify-content-between align-items-center"
                    >

                        <span class="text-muted">
                            Payment Status
                        </span>

                        <span
                            class="badge <?= $paymentBadge ?>"
                        >

                            <?= e(
                                $paymentLabel
                            ) ?>

                        </span>

                    </div>


                    <!-- PAYMENT METHOD -->

                    <div
                        class="d-flex justify-content-between mt-3"
                    >

                        <span class="text-muted">
                            Payment Method
                        </span>

                        <strong>

                            <?php if (
                                $paymentMethod === 'eft'
                            ): ?>

                                EFT / Bank Transfer

                            <?php else: ?>

                                <?= e(
                                    ucfirst(
                                        $order['payment_method']
                                        ?: 'Pending'
                                    )
                                ) ?>

                            <?php endif; ?>

                        </strong>

                    </div>


                    <!-- EFT PAYMENT -->

                    <?php if (
                        $paymentMethod === 'eft'
                        && $paymentStatus !== 'paid'
                        && $paymentStatus !== 'refunded'
                    ): ?>

                        <hr class="my-4">


                        <div
                            class="p-3 rounded-3 mb-4"
                            style="
                                background:#eef6fc;
                                border:1px solid #d7e9f7;
                            "
                        >

                            <div
                                class="fw-bold mb-1"
                                style="color:#102b49;"
                            >

                                <i
                                    class="bi bi-bank me-2"
                                    style="color:#1769aa;"
                                ></i>

                                EFT Payment Required

                            </div>


                            <div class="small text-muted">

                                Please make payment using the
                                banking details below.

                            </div>

                        </div>


                        <!-- AMOUNT -->

                        <div class="mb-4">

                            <div
                                class="small text-muted mb-1"
                            >
                                Amount to Pay
                            </div>

                            <div
                                class="fs-3 fw-bold"
                                style="color:#1769aa;"
                            >

                                <?= money(
                                    $order['total']
                                ) ?>

                            </div>

                        </div>


                        <!-- BANK DETAILS -->

                        <div class="mb-4">

                            <div
                                class="small text-muted mb-1"
                            >
                                Bank
                            </div>

                            <div class="fw-semibold">
                                Betachem Bank
                            </div>


                            <div
                                class="small text-muted mt-3 mb-1"
                            >
                                Account Name
                            </div>

                            <div class="fw-semibold">
                                Betachem
                            </div>


                            <div
                                class="small text-muted mt-3 mb-1"
                            >
                                Account Number
                            </div>

                            <div class="fw-semibold">
                                0000000000
                            </div>


                            <div
                                class="small text-muted mt-3 mb-1"
                            >
                                Branch Code
                            </div>

                            <div class="fw-semibold">
                                000000
                            </div>

                        </div>


                        <!-- REFERENCE -->

                        <div
                            class="p-3 rounded-3 mb-4"
                            style="
                                background:#f5f8fb;
                                border:1px solid #e5edf3;
                            "
                        >

                            <div
                                class="small text-muted mb-1"
                            >
                                Payment Reference
                            </div>


                            <div
                                class="fw-bold"
                                style="color:#1769aa;"
                            >

                                <?= e(
                                    $order['order_number']
                                ) ?>

                            </div>


                            <div
                                class="small text-muted mt-1"
                            >

                                Use your order number as your
                                EFT payment reference.

                            </div>

                        </div>


                        <!-- HOW TO PAY -->

                        <div class="mb-4">

                            <div
                                class="fw-bold mb-2"
                                style="color:#102b49;"
                            >

                                How to Pay

                            </div>


                            <ol
                                class="small text-muted ps-3 mb-0"
                            >

                                <li class="mb-2">

                                    Make an EFT payment using the
                                    banking details above.

                                </li>


                                <li class="mb-2">

                                    Use your order number as the
                                    payment reference.

                                </li>


                                <li>

                                    Upload your proof of payment
                                    below.

                                </li>

                            </ol>

                        </div>


                        <!-- PROOF OF PAYMENT -->

                        <hr class="my-4">


                        <div>

                            <div
                                class="fw-bold mb-2"
                                style="color:#102b49;"
                            >

                                <i
                                    class="bi bi-upload me-2"
                                    style="color:#1769aa;"
                                ></i>

                                Proof of Payment

                            </div>


                            <p class="small text-muted mb-3">

                                Once you have completed your EFT
                                payment, upload your proof of
                                payment below.

                            </p>


                            <!-- SUCCESS -->

                            <?php if ($uploadSuccess): ?>

                                <div
                                    class="alert alert-success small"
                                >

                                    <i
                                        class="bi bi-check-circle-fill me-2"
                                    ></i>

                                    <?= e(
                                        $uploadSuccess
                                    ) ?>

                                </div>

                            <?php endif; ?>


                            <!-- ERROR -->

                            <?php if ($uploadError): ?>

                                <div
                                    class="alert alert-danger small"
                                >

                                    <i
                                        class="bi bi-exclamation-circle-fill me-2"
                                    ></i>

                                    <?= e(
                                        $uploadError
                                    ) ?>

                                </div>

                            <?php endif; ?>


                            <!-- EXISTING PROOF -->

                            <?php if (
                                !empty($paymentProof)
                            ): ?>

                                <div
                                    class="p-3 rounded-3 mb-3"
                                    style="
                                        background:#f5f8fb;
                                        border:1px solid #e5edf3;
                                    "
                                >

                                    <div
                                        class="fw-semibold mb-1"
                                        style="color:#102b49;"
                                    >

                                        <i
                                            class="bi bi-file-earmark-check me-2"
                                            style="color:#1769aa;"
                                        ></i>

                                        Proof Uploaded

                                    </div>


                                    <?php if (
                                        $paymentProofDate
                                    ): ?>

                                        <div
                                            class="small text-muted"
                                        >

                                            Uploaded on

                                            <?= e(
                                                date(
                                                    'd M Y H:i',
                                                    strtotime(
                                                        $paymentProofDate
                                                    )
                                                )
                                            ) ?>

                                        </div>

                                    <?php endif; ?>


                                    <div class="mt-2">

                                        <a
                                            href="<?= e(
                                                $baseUrl
                                                . '/uploads/payments/'
                                                . basename(
                                                    $paymentProof
                                                )
                                            ) ?>"
                                            target="_blank"
                                            class="btn btn-sm btn-outline-primary"
                                        >

                                            <i
                                                class="bi bi-eye me-1"
                                            ></i>

                                            View Uploaded Proof

                                        </a>

                                    </div>

                                </div>

                            <?php endif; ?>


                            <!-- UPLOAD FORM -->

                            <form
                                method="POST"
                                enctype="multipart/form-data"
                            >

                                <div class="mb-3">

                                    <label
                                        for="proof_of_payment"
                                        class="form-label fw-semibold"
                                    >

                                        Select File

                                    </label>


                                    <input
                                        type="file"
                                        name="proof_of_payment"
                                        id="proof_of_payment"
                                        class="form-control"
                                        accept=".jpg,.jpeg,.png,.pdf"
                                        required
                                    >


                                    <div
                                        class="form-text"
                                    >

                                        Accepted formats:
                                        JPG, PNG or PDF.
                                        Maximum size: 5 MB.

                                    </div>

                                </div>


                                <button
                                    type="submit"
                                    name="upload_proof"
                                    class="btn btn-primary w-100"
                                >

                                    <i
                                        class="bi bi-cloud-arrow-up me-2"
                                    ></i>

                                    Upload Proof of Payment

                                </button>

                            </form>

                        </div>


                        <!-- TESTING NOTICE -->

                        <div
                            class="alert alert-warning small mt-4 mb-0"
                        >

                            <i
                                class="bi bi-exclamation-triangle me-1"
                            ></i>

                            Banking details are currently
                            temporary testing details. They must
                            be replaced with the official Betachem
                            banking details before the website
                            goes live.

                        </div>

                    <?php endif; ?>


                    <!-- PAID -->

                    <?php if (
                        $paymentStatus === 'paid'
                    ): ?>

                        <hr class="my-4">


                        <div
                            class="alert alert-success mb-0"
                        >

                            <i
                                class="bi bi-check-circle-fill me-2"
                            ></i>

                            <strong>
                                Payment Confirmed
                            </strong>


                            <div class="small mt-1">

                                Your payment has been confirmed
                                by Betachem.

                            </div>

                        </div>

                    <?php endif; ?>


                    <!-- PARTIAL -->

                    <?php if (
                        $paymentStatus === 'partial'
                    ): ?>

                        <hr class="my-4">


                        <div
                            class="alert alert-info mb-0"
                        >

                            <i
                                class="bi bi-info-circle-fill me-2"
                            ></i>

                            <strong>
                                Partial Payment Received
                            </strong>


                            <div class="small mt-1">

                                Betachem has received a partial
                                payment. Please contact Betachem
                                if you need assistance with the
                                remaining balance.

                            </div>

                        </div>

                    <?php endif; ?>


                    <!-- FAILED -->

                    <?php if (
                        $paymentStatus === 'failed'
                    ): ?>

                        <hr class="my-4">


                        <div
                            class="alert alert-danger mb-0"
                        >

                            <i
                                class="bi bi-x-circle-fill me-2"
                            ></i>

                            <strong>
                                Payment Failed
                            </strong>


                            <div class="small mt-1">

                                Your payment could not be
                                confirmed. Please contact
                                Betachem for assistance.

                            </div>

                        </div>

                    <?php endif; ?>


                    <!-- REFUNDED -->

                    <?php if (
                        $paymentStatus === 'refunded'
                    ): ?>

                        <hr class="my-4">


                        <div
                            class="alert alert-secondary mb-0"
                        >

                            <i
                                class="bi bi-arrow-counterclockwise me-2"
                            ></i>

                            <strong>
                                Payment Refunded
                            </strong>


                            <div class="small mt-1">

                                This payment has been refunded.

                            </div>

                        </div>

                    <?php endif; ?>

                </div>

            </div>


            <!-- ACTIONS -->

            <div
                class="card border-0 shadow-sm rounded-4"
            >

                <div class="card-body p-4">

                    <a
                        href="<?= $baseUrl; ?>/orders.php"
                        class="btn btn-outline-primary w-100 mb-2"
                    >

                        <i
                            class="bi bi-list-ul me-2"
                        ></i>

                        My Orders

                    </a>


                    <a
                        href="<?= $baseUrl; ?>/shop.php"
                        class="btn btn-primary w-100"
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

.order-tracker {
    position: relative;
    display: flex;
    justify-content: space-between;
    gap: 10px;
}

.order-tracker-step {
    position: relative;
    flex: 1;
    text-align: center;
}

.order-tracker-icon {
    width: 48px;
    height: 48px;
    margin: 0 auto 12px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #edf2f6;
    color: #8b9aa8;
    position: relative;
    z-index: 2;
    transition: .2s ease;
}

.order-tracker-step.completed .order-tracker-icon {
    background: #1769aa;
    color: #ffffff;
}

.order-tracker-step.current .order-tracker-icon {
    background: #1769aa;
    color: #ffffff;
    box-shadow: 0 0 0 6px rgba(23,105,170,.12);
}

.order-tracker-step.pending .order-tracker-icon {
    background: #edf2f6;
    color: #8b9aa8;
}

.order-tracker-line {
    position: absolute;
    top: 24px;
    left: calc(50% + 24px);
    width: calc(100% - 48px);
    height: 3px;
    background: #e2e9ef;
    z-index: 1;
}

.order-tracker-step.completed .order-tracker-line {
    background: #1769aa;
}

.order-tracker-content {
    padding: 0 5px;
}

@media (max-width: 767px) {

    .order-tracker {
        display: block;
    }

    .order-tracker-step {
        display: flex;
        align-items: flex-start;
        text-align: left;
        min-height: 85px;
    }

    .order-tracker-icon {
        flex-shrink: 0;
        margin: 0 15px 0 0;
    }

    .order-tracker-line {
        top: 48px;
        left: 22px;
        width: 3px;
        height: 45px;
    }

    .order-tracker-content {
        padding-top: 3px;
    }

}

</style>


<?php require_once __DIR__ . '/includes/footer.php'; ?>