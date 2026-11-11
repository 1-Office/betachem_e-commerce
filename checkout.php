<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/cart_functions.php';
require_once __DIR__ . '/includes/notification_functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| E-Commerce Base URL
|--------------------------------------------------------------------------
*/

$baseUrl = '/betachem/betachem_e-commerce';

/*
|--------------------------------------------------------------------------
| Customer must be logged in
|--------------------------------------------------------------------------
*/

if (!isLoggedIn()) {

    $_SESSION['checkout_message'] =
        'Please log in to continue to checkout.';

    header('Location: ' . $baseUrl . '/login.php');
    exit;
}

$userId = (int) currentUserId();

/*
|--------------------------------------------------------------------------
| Get Cart
|--------------------------------------------------------------------------
*/

$cartItems = getCartItems();
$cartSubtotal = getCartSubtotal();

/*
|--------------------------------------------------------------------------
| Cart cannot be empty
|--------------------------------------------------------------------------
*/

if (empty($cartItems)) {

    $_SESSION['cart_message'] =
        'Your cart is empty. Add products before checking out.';

    header('Location: ' . $baseUrl . '/cart.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Get Customer Details
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        first_name,
        last_name,
        email,
        phone,
        address,
        city,
        province,
        postal_code
    FROM users
    WHERE id = ?
    LIMIT 1
");

if (!$stmt) {
    die('Unable to load customer details.');
}

$stmt->bind_param("i", $userId);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

$stmt->close();

if (!$user) {

    session_unset();
    session_destroy();

    header('Location: ' . $baseUrl . '/login.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Checkout Values
|--------------------------------------------------------------------------
*/

$firstName = $user['first_name'] ?? '';
$lastName = $user['last_name'] ?? '';
$email = $user['email'] ?? '';
$phone = $user['phone'] ?? '';
$address = $user['address'] ?? '';
$city = $user['city'] ?? '';
$province = $user['province'] ?? '';
$postalCode = $user['postal_code'] ?? '';
$notes = '';

$deliveryFee = 0.00;
$discount = 0.00;

$paymentMethod = 'eft';

$errors = [];

/*
|--------------------------------------------------------------------------
| Process Checkout
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $postalCode = trim($_POST['postal_code'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    $paymentMethod = trim($_POST['payment_method'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($firstName === '') {
        $errors[] = 'Please enter your first name.';
    }

    if ($lastName === '') {
        $errors[] = 'Please enter your last name.';
    }

    if (
        $email === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($phone === '') {
        $errors[] = 'Please enter your phone number.';
    }

    if ($address === '') {
        $errors[] = 'Please enter your delivery address.';
    }

    if ($city === '') {
        $errors[] = 'Please enter your city.';
    }

    if ($province === '') {
        $errors[] = 'Please select your province.';
    }

    if ($postalCode === '') {
        $errors[] = 'Please enter your postal code.';
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Payment Method
    |--------------------------------------------------------------------------
    */

    if ($paymentMethod === '') {

        $errors[] = 'Please select a payment method.';

    } elseif ($paymentMethod !== 'eft') {

        $errors[] =
            'The selected payment method is not currently available.';
    }

    /*
    |--------------------------------------------------------------------------
    | Recheck Stock
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        foreach ($cartItems as $item) {

            $productId = (int) $item['product_id'];

            $stmt = $conn->prepare("
                SELECT
                    id,
                    name,
                    sku,
                    price,
                    stock_quantity,
                    is_active
                FROM products
                WHERE id = ?
                LIMIT 1
            ");

            if (!$stmt) {
                $errors[] =
                    'We could not verify one of the products in your cart.';
                break;
            }

            $stmt->bind_param("i", $productId);
            $stmt->execute();

            $product = $stmt->get_result()->fetch_assoc();

            $stmt->close();

            if (!$product) {

                $errors[] =
                    'A product in your cart is no longer available.';

                continue;
            }

            if (!(int) $product['is_active']) {

                $errors[] =
                    $product['name'] .
                    ' is no longer available.';

                continue;
            }

            if (
                (int) $product['stock_quantity'] <
                (int) $item['quantity']
            ) {

                $errors[] =
                    'Not enough stock available for ' .
                    $product['name'] .
                    '.';
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Create Order
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $conn->begin_transaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Generate Order Number
            |--------------------------------------------------------------------------
            */

            $orderNumber = generateOrderNumber();

            /*
            |--------------------------------------------------------------------------
            | Calculate Total
            |--------------------------------------------------------------------------
            */

            $total =
                $cartSubtotal
                + $deliveryFee
                - $discount;

            /*
            |--------------------------------------------------------------------------
            | Create Order
            |--------------------------------------------------------------------------
            */

            $orderStatus = 'pending';
            $paymentStatus = 'pending';

            $stmt = $conn->prepare("
                INSERT INTO orders (
                    user_id,
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
                    created_at,
                    updated_at
                )
                VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
                )
            ");

            if (!$stmt) {
                throw new Exception(
                    'Unable to create the order.'
                );
            }

            $stmt->bind_param(
                "issddddsssssssssss",
                $userId,
                $orderNumber,
                $orderStatus,
                $cartSubtotal,
                $deliveryFee,
                $discount,
                $total,
                $paymentStatus,
                $paymentMethod,
                $firstName,
                $lastName,
                $phone,
                $email,
                $address,
                $city,
                $province,
                $postalCode,
                $notes
            );

            $stmt->execute();

            $orderId = $stmt->insert_id;

            $stmt->close();

            /*
            |--------------------------------------------------------------------------
            | Create Order Items
            |--------------------------------------------------------------------------
            */

            foreach ($cartItems as $item) {

                $productId = (int) $item['product_id'];
                $quantity = (int) $item['quantity'];

                /*
                |--------------------------------------------------------------------------
                | Re-read product and lock row
                |--------------------------------------------------------------------------
                */

                $stmt = $conn->prepare("
                    SELECT
                        id,
                        name,
                        sku,
                        price,
                        stock_quantity,
                        is_active
                    FROM products
                    WHERE id = ?
                    FOR UPDATE
                ");

                if (!$stmt) {
                    throw new Exception(
                        'Unable to verify a product.'
                    );
                }

                $stmt->bind_param("i", $productId);
                $stmt->execute();

                $product = $stmt->get_result()->fetch_assoc();

                $stmt->close();

                if (!$product) {

                    throw new Exception(
                        'A product in your cart could not be found.'
                    );
                }

                if (!(int) $product['is_active']) {

                    throw new Exception(
                        $product['name'] .
                        ' is no longer available.'
                    );
                }

                if (
                    (int) $product['stock_quantity'] <
                    $quantity
                ) {

                    throw new Exception(
                        'Not enough stock available for ' .
                        $product['name'] .
                        '.'
                    );
                }

                $productName = $product['name'];
                $sku = $product['sku'];

                /*
                |--------------------------------------------------------------------------
                | Use current product price
                |--------------------------------------------------------------------------
                */

                $price = (float) $product['price'];

                /*
                |--------------------------------------------------------------------------
                | Calculate Item Subtotal
                |--------------------------------------------------------------------------
                */

                $itemSubtotal = $price * $quantity;

                /*
                |--------------------------------------------------------------------------
                | Insert Order Item
                |--------------------------------------------------------------------------
                */

                $stmt = $conn->prepare("
                    INSERT INTO order_items (
                        order_id,
                        product_id,
                        product_name,
                        sku,
                        price,
                        quantity,
                        subtotal,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
                    )
                ");

                if (!$stmt) {
                    throw new Exception(
                        'Unable to create an order item.'
                    );
                }

                $stmt->bind_param(
                    "iissdid",
                    $orderId,
                    $productId,
                    $productName,
                    $sku,
                    $price,
                    $quantity,
                    $itemSubtotal
                );

                $stmt->execute();

                $stmt->close();

                /*
                |--------------------------------------------------------------------------
                | Reduce Stock
                |--------------------------------------------------------------------------
                */

                $stmt = $conn->prepare("
                    UPDATE products
                    SET
                        stock_quantity = stock_quantity - ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");

                if (!$stmt) {
                    throw new Exception(
                        'Unable to update product stock.'
                    );
                }

                $stmt->bind_param(
                    "ii",
                    $quantity,
                    $productId
                );

                $stmt->execute();

                $stmt->close();
            }

            /*
            |--------------------------------------------------------------------------
            | Clear Cart
            |--------------------------------------------------------------------------
            */

            clearCart();

            /*
            |--------------------------------------------------------------------------
            | Commit Transaction
            |--------------------------------------------------------------------------
            */

            $conn->commit();

            /*
            |--------------------------------------------------------------------------
            | Create Customer Notification
            |--------------------------------------------------------------------------
            */

            createNotification(
                $userId,
                'order_placed',
                'Order placed successfully',
                'Your order ' . $orderNumber .
                ' has been received and is now pending processing.',
                $orderId
            );

            /*
            |--------------------------------------------------------------------------
            | Store Last Order
            |--------------------------------------------------------------------------
            */

            $_SESSION['last_order_id'] = $orderId;

            /*
            |--------------------------------------------------------------------------
            | Redirect
            |--------------------------------------------------------------------------
            */

            redirect(
                'order_success.php?id=' . $orderId
            );

            exit;

        } catch (Throwable $e) {

            if ($conn->errno === 0) {
                // Transaction state is already clean.
            }

            try {
                $conn->rollback();
            } catch (Throwable $rollbackError) {
                // Ignore rollback errors.
            }

            $errors[] =
                'We could not complete your order. ' .
                'Please try again.';
        }
    }
}

$total = $cartSubtotal + $deliveryFee - $discount;

require_once __DIR__ . '/includes/header.php';

?>

<div class="container py-5">

    <!-- =========================
         PAGE HEADER
    ========================== -->

    <div class="mb-5">

        <span class="text-primary fw-semibold">
            CHECKOUT
        </span>

        <h1 class="fw-bold mt-2 mb-2">
            Complete Your Order
        </h1>

        <p class="text-muted mb-0">
            Confirm your delivery details and choose your payment method.
        </p>

    </div>


    <?php if (!empty($errors)): ?>

        <div class="alert alert-danger">

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


    <form method="POST">

        <div class="row g-4">

            <!-- =========================
                 CUSTOMER DETAILS
            ========================== -->

            <div class="col-lg-7">

                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-body p-4 p-md-5">

                        <h4 class="fw-bold mb-1">
                            Delivery Information
                        </h4>

                        <p class="text-muted small mb-4">
                            Where should we deliver your order?
                        </p>


                        <div class="row g-3">

                            <div class="col-md-6">

                                <label class="form-label">
                                    First Name
                                </label>

                                <input
                                    type="text"
                                    name="first_name"
                                    class="form-control"
                                    value="<?= e($firstName) ?>"
                                    required
                                >

                            </div>


                            <div class="col-md-6">

                                <label class="form-label">
                                    Last Name
                                </label>

                                <input
                                    type="text"
                                    name="last_name"
                                    class="form-control"
                                    value="<?= e($lastName) ?>"
                                    required
                                >

                            </div>


                            <div class="col-md-6">

                                <label class="form-label">
                                    Email Address
                                </label>

                                <input
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    value="<?= e($email) ?>"
                                    required
                                >

                            </div>


                            <div class="col-md-6">

                                <label class="form-label">
                                    Phone Number
                                </label>

                                <input
                                    type="tel"
                                    name="phone"
                                    class="form-control"
                                    value="<?= e($phone) ?>"
                                    required
                                >

                            </div>


                            <div class="col-12">

                                <label class="form-label">
                                    Delivery Address
                                </label>

                                <textarea
                                    name="address"
                                    class="form-control"
                                    rows="3"
                                    required
                                ><?= e($address) ?></textarea>

                            </div>


                            <div class="col-md-5">

                                <label class="form-label">
                                    City
                                </label>

                                <input
                                    type="text"
                                    name="city"
                                    class="form-control"
                                    value="<?= e($city) ?>"
                                    required
                                >

                            </div>


                            <div class="col-md-4">

                                <label class="form-label">
                                    Province
                                </label>

                                <select
                                    name="province"
                                    class="form-select"
                                    required
                                >

                                    <option value="">
                                        Select Province
                                    </option>

                                    <?php

                                    $provinces = [
                                        'Eastern Cape',
                                        'Free State',
                                        'Gauteng',
                                        'KwaZulu-Natal',
                                        'Limpopo',
                                        'Mpumalanga',
                                        'Northern Cape',
                                        'North West',
                                        'Western Cape'
                                    ];

                                    ?>

                                    <?php foreach ($provinces as $provinceOption): ?>

                                        <option
                                            value="<?= e($provinceOption) ?>"
                                            <?= $province === $provinceOption
                                                ? 'selected'
                                                : '' ?>
                                        >
                                            <?= e($provinceOption) ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>


                            <div class="col-md-3">

                                <label class="form-label">
                                    Postal Code
                                </label>

                                <input
                                    type="text"
                                    name="postal_code"
                                    class="form-control"
                                    value="<?= e($postalCode) ?>"
                                    required
                                >

                            </div>


                            <div class="col-12">

                                <label class="form-label">

                                    Order Notes

                                    <span class="text-muted">
                                        (Optional)
                                    </span>

                                </label>

                                <textarea
                                    name="notes"
                                    class="form-control"
                                    rows="3"
                                    placeholder="Any special delivery instructions?"
                                ><?= e($notes) ?></textarea>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =========================
                     PAYMENT METHOD
                ========================== -->

                <div class="card border-0 shadow-sm">

                    <div class="card-body p-4 p-md-5">

                        <h4 class="fw-bold mb-1">
                            Payment Method
                        </h4>

                        <p class="text-muted small mb-4">
                            Select how you would like to pay for your order.
                        </p>


                        <!-- EFT -->

                        <label
                            class="d-block border rounded-3 p-3 mb-3"
                            style="cursor:pointer;"
                        >

                            <div class="d-flex align-items-start gap-3">

                                <div class="pt-1">

                                    <input
                                        type="radio"
                                        name="payment_method"
                                        value="eft"
                                        class="form-check-input"
                                        <?= $paymentMethod === 'eft'
                                            ? 'checked'
                                            : '' ?>
                                        required
                                    >

                                </div>


                                <div class="flex-grow-1">

                                    <div class="d-flex align-items-center">

                                        <i
                                            class="bi bi-bank2 text-primary fs-4 me-2"
                                        ></i>

                                        <strong>
                                            EFT / Bank Transfer
                                        </strong>

                                    </div>

                                    <div class="text-muted small mt-1">

                                        Pay by electronic funds transfer.
                                        Banking details and payment
                                        instructions will be provided after
                                        your order is placed.

                                    </div>

                                </div>

                            </div>

                        </label>


                        <!-- CARD -->

                        <div class="border rounded-3 p-3 bg-light">

                            <div class="d-flex align-items-start gap-3">

                                <div>

                                    <i
                                        class="bi bi-credit-card text-muted fs-4"
                                    ></i>

                                </div>


                                <div class="flex-grow-1">

                                    <div
                                        class="d-flex justify-content-between align-items-center"
                                    >

                                        <strong class="text-muted">
                                            Card Payment
                                        </strong>

                                        <span class="badge bg-secondary">
                                            Coming Soon
                                        </span>

                                    </div>

                                    <div class="text-muted small mt-1">

                                        Secure online card payments will be
                                        available soon.

                                    </div>

                                </div>

                            </div>

                        </div>


                        <div class="alert alert-info small mt-4 mb-0">

                            <i class="bi bi-shield-check me-1"></i>

                            Your order will be placed as
                            <strong>Payment Pending</strong>.
                            You will receive payment instructions after
                            submitting your order.

                        </div>

                    </div>

                </div>

            </div>


            <!-- =========================
                 ORDER SUMMARY
            ========================== -->

            <div class="col-lg-5">

                <div
                    class="card border-0 shadow-sm"
                    style="position: sticky; top: 100px;"
                >

                    <div class="card-body p-4">

                        <h4 class="fw-bold mb-4">
                            Order Summary
                        </h4>


                        <!-- PRODUCTS -->

                        <div class="mb-4">

                            <?php foreach ($cartItems as $item): ?>

                                <?php

                                $itemImageUrl =
                                    $baseUrl .
                                    '/assets/images/product-placeholder.png';

                                $itemImage = trim(
                                    (string) ($item['main_image'] ?? '')
                                );

                                if ($itemImage !== '') {

                                    $itemImage =
                                        str_replace(
                                            '\\',
                                            '/',
                                            $itemImage
                                        );

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Remove existing uploads/products/ path
                                    |--------------------------------------------------------------------------
                                    */

                                    if (
                                        strpos(
                                            $itemImage,
                                            'uploads/products/'
                                        ) !== false
                                    ) {

                                        $itemImage =
                                            substr(
                                                $itemImage,
                                                strrpos(
                                                    $itemImage,
                                                    'uploads/products/'
                                                ) +
                                                strlen(
                                                    'uploads/products/'
                                                )
                                            );
                                    }

                                    $itemImage = basename($itemImage);

                                    $productImageDirectory =
                                        __DIR__ .
                                        '/uploads/products/';

                                    $possibleImages = [
                                        $itemImage
                                    ];

                                    if (
                                        !preg_match(
                                            '/\.(jpg|jpeg|png|webp|gif)$/i',
                                            $itemImage
                                        )
                                    ) {

                                        $possibleImages[] =
                                            $itemImage . '.jpg';

                                        $possibleImages[] =
                                            $itemImage . '.jpeg';

                                        $possibleImages[] =
                                            $itemImage . '.png';

                                        $possibleImages[] =
                                            $itemImage . '.webp';

                                        $possibleImages[] =
                                            $itemImage . '.gif';
                                    }

                                    foreach (
                                        $possibleImages
                                        as $possibleImage
                                    ) {

                                        if (
                                            file_exists(
                                                $productImageDirectory .
                                                $possibleImage
                                            )
                                        ) {

                                            $itemImageUrl =
                                                $baseUrl .
                                                '/uploads/products/' .
                                                rawurlencode(
                                                    $possibleImage
                                                );

                                            break;
                                        }
                                    }
                                }

                                ?>

                                <div
                                    class="d-flex align-items-center gap-3 mb-3"
                                >

                                    <div
                                        style="
                                            width:65px;
                                            height:65px;
                                            flex-shrink:0;
                                        "
                                    >

                                        <img
                                            src="<?= e($itemImageUrl) ?>"
                                            alt="<?= e($item['name']) ?>"
                                            class="w-100 h-100 rounded object-fit-cover"
                                        >

                                    </div>


                                    <div class="flex-grow-1">

                                        <div class="fw-semibold">

                                            <?= e($item['name']) ?>

                                        </div>

                                        <div class="text-muted small">

                                            Qty:
                                            <?= (int) $item['quantity'] ?>

                                        </div>

                                    </div>


                                    <div class="fw-semibold">

                                        <?= money($item['subtotal']) ?>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        </div>


                        <hr>


                        <!-- SUBTOTAL -->

                        <div
                            class="d-flex justify-content-between mb-2"
                        >

                            <span class="text-muted">
                                Subtotal
                            </span>

                            <span>
                                <?= money($cartSubtotal) ?>
                            </span>

                        </div>


                        <!-- DELIVERY -->

                        <div
                            class="d-flex justify-content-between mb-2"
                        >

                            <span class="text-muted">
                                Delivery
                            </span>

                            <span>

                                <?= $deliveryFee > 0
                                    ? money($deliveryFee)
                                    : 'Free'
                                ?>

                            </span>

                        </div>


                        <!-- DISCOUNT -->

                        <?php if ($discount > 0): ?>

                            <div
                                class="d-flex justify-content-between mb-2"
                            >

                                <span class="text-muted">
                                    Discount
                                </span>

                                <span class="text-success">

                                    -<?= money($discount) ?>

                                </span>

                            </div>

                        <?php endif; ?>


                        <hr>


                        <!-- TOTAL -->

                        <div
                            class="d-flex justify-content-between align-items-center mb-4"
                        >

                            <span class="fs-5 fw-bold">
                                Total
                            </span>

                            <span class="fs-4 fw-bold text-primary">

                                <?= money($total) ?>

                            </span>

                        </div>


                        <!-- PAYMENT SUMMARY -->

                        <div
                            class="bg-light rounded-3 p-3 mb-4"
                        >

                            <div class="small text-muted mb-1">
                                Payment Method
                            </div>

                            <div class="fw-semibold">

                                <i
                                    class="bi bi-bank2 text-primary me-1"
                                ></i>

                                EFT / Bank Transfer

                            </div>

                        </div>


                        <!-- PLACE ORDER -->

                        <button
                            type="submit"
                            class="btn btn-primary btn-lg w-100"
                        >

                            <i class="bi bi-check-circle me-2"></i>

                            Place Order

                        </button>


                        <a
                            href="<?= $baseUrl ?>/cart.php"
                            class="btn btn-outline-secondary w-100 mt-2"
                        >

                            <i class="bi bi-arrow-left me-2"></i>

                            Back to Cart

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </form>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>