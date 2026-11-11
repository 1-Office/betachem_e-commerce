<?php

/*
|--------------------------------------------------------------------------
| Betachem E-Commerce
| Shopping Cart
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/cart_functions.php';

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
| Handle Cart Actions
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | Add Product
    |--------------------------------------------------------------------------
    */

    if ($action === 'add') {

        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity = (int) ($_POST['quantity'] ?? 1);

        $result = addToCart(
            $productId,
            $quantity
        );

        $_SESSION['cart_message'] =
            $result['message'];

        $_SESSION['cart_message_type'] =
            $result['success']
                ? 'success'
                : 'danger';

        header("Location: {$baseUrl}/cart.php");
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Update Quantity
    |--------------------------------------------------------------------------
    */

    if ($action === 'update') {

        $itemId = (int) ($_POST['item_id'] ?? 0);
        $quantity = (int) ($_POST['quantity'] ?? 1);

        $result = updateCartItem(
            $itemId,
            $quantity
        );

        $_SESSION['cart_message'] =
            $result['message'];

        $_SESSION['cart_message_type'] =
            $result['success']
                ? 'success'
                : 'danger';

        header("Location: {$baseUrl}/cart.php");
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Remove Item
    |--------------------------------------------------------------------------
    */

    if ($action === 'remove') {

        $itemId = (int) ($_POST['item_id'] ?? 0);

        $result = removeCartItem($itemId);

        $_SESSION['cart_message'] =
            $result['message'];

        $_SESSION['cart_message_type'] =
            $result['success']
                ? 'success'
                : 'danger';

        header("Location: {$baseUrl}/cart.php");
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Clear Cart
    |--------------------------------------------------------------------------
    */

    if ($action === 'clear') {

        clearCart();

        $_SESSION['cart_message'] =
            'Your cart has been cleared.';

        $_SESSION['cart_message_type'] =
            'success';

        header("Location: {$baseUrl}/cart.php");
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| Load Header
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/includes/header.php';


/*
|--------------------------------------------------------------------------
| Get Cart Information
|--------------------------------------------------------------------------
*/

$cartItems = getCartItems();

$cartSubtotal = getCartSubtotal();

$cartCount = getCartCount();

?>

<div class="container py-5">

    <!-- =========================================================
         PAGE HEADER
    ========================================================== -->

    <div class="mb-4">

        <h1 class="fw-bold mb-2">
            Shopping Cart
        </h1>

        <p class="text-muted mb-0">
            Review your products before proceeding
            to checkout.
        </p>

    </div>


    <!-- =========================================================
         CART MESSAGE
    ========================================================== -->

    <?php if (!empty($_SESSION['cart_message'])): ?>

        <div
            class="alert alert-<?= e($_SESSION['cart_message_type'] ?? 'success') ?>
            alert-dismissible fade show"
            role="alert"
        >

            <i class="bi bi-info-circle me-2"></i>

            <?= e($_SESSION['cart_message']) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

        <?php

        unset($_SESSION['cart_message']);
        unset($_SESSION['cart_message_type']);

        ?>

    <?php endif; ?>


    <?php if (empty($cartItems)): ?>

        <!-- =====================================================
             EMPTY CART
        ====================================================== -->

        <div class="empty-state py-5">

            <div class="empty-state-icon">

                <i class="bi bi-cart-x"></i>

            </div>

            <h2>
                Your Cart Is Empty
            </h2>

            <p class="text-muted mb-4">
                You haven't added any products yet.
            </p>

            <a
                href="<?= $baseUrl ?>/shop.php"
                class="btn btn-primary"
            >

                <i class="bi bi-shop me-2"></i>

                Continue Shopping

            </a>

        </div>


    <?php else: ?>


        <div class="row g-4">


            <!-- =================================================
                 CART ITEMS
            ================================================== -->

            <div class="col-lg-8">

                <div class="card border-0 shadow-sm">

                    <div class="card-body p-0">

                        <?php foreach ($cartItems as $item): ?>

                            <div class="cart-item">


                                <!-- PRODUCT IMAGE -->

                                <div class="cart-item-image">

                                    <?php if (!empty($item['main_image'])): ?>

                                        <?php

                                        /*
                                        |--------------------------------------------------------------------------
                                        | Build Product Image URL
                                        |--------------------------------------------------------------------------
                                        */

                                        $cartImage =
                                            trim(
                                                $item['main_image'],
                                                "/\\"
                                            );

                                        $cartImage =
                                            str_replace(
                                                '\\',
                                                '/',
                                                $cartImage
                                            );


                                        /*
                                        |--------------------------------------------------------------------------
                                        | Remove existing uploads/products path
                                        |--------------------------------------------------------------------------
                                        */

                                        if (
                                            strpos(
                                                $cartImage,
                                                'uploads/products/'
                                            ) !== false
                                        ) {

                                            $cartImage =
                                                substr(
                                                    $cartImage,
                                                    strrpos(
                                                        $cartImage,
                                                        'uploads/products/'
                                                    )
                                                    + strlen(
                                                        'uploads/products/'
                                                    )
                                                );
                                        }


                                        $cartImage =
                                            ltrim(
                                                $cartImage,
                                                '/'
                                            );


                                        /*
                                        |--------------------------------------------------------------------------
                                        | If extension is missing,
                                        | find the actual file
                                        |--------------------------------------------------------------------------
                                        */

                                        $possibleImage = null;

                                        if (
                                            preg_match(
                                                '/\.(jpg|jpeg|png|webp|gif)$/i',
                                                $cartImage
                                            )
                                        ) {

                                            $possibleImage =
                                                $cartImage;

                                        } else {

                                            foreach (
                                                [
                                                    'jpg',
                                                    'jpeg',
                                                    'png',
                                                    'webp',
                                                    'gif'
                                                ]
                                                as $extension
                                            ) {

                                                $testFile =
                                                    __DIR__
                                                    . '/uploads/products/'
                                                    . $cartImage
                                                    . '.'
                                                    . $extension;

                                                if (
                                                    file_exists($testFile)
                                                ) {

                                                    $possibleImage =
                                                        $cartImage
                                                        . '.'
                                                        . $extension;

                                                    break;
                                                }
                                            }
                                        }


                                        /*
                                        |--------------------------------------------------------------------------
                                        | Display image if found
                                        |--------------------------------------------------------------------------
                                        */

                                        if ($possibleImage !== null) {

                                            $cartImageUrl =
                                                $baseUrl
                                                . '/uploads/products/'
                                                . rawurlencode(
                                                    $possibleImage
                                                );

                                        } else {

                                            $cartImageUrl =
                                                $baseUrl
                                                . '/assets/images/product-placeholder.png';
                                        }

                                        ?>

                                        <img
                                            src="<?= e($cartImageUrl) ?>"
                                            alt="<?= e($item['name']) ?>"
                                        >

                                    <?php else: ?>

                                        <div class="cart-no-image">

                                            <i class="bi bi-image"></i>

                                        </div>

                                    <?php endif; ?>

                                </div>


                                <!-- PRODUCT DETAILS -->

                                <div class="cart-item-details">

                                    <a
                                        href="<?= $baseUrl ?>/product.php?slug=<?= urlencode($item['slug']) ?>"
                                        class="cart-product-name"
                                    >

                                        <?= e($item['name']) ?>

                                    </a>

                                    <div class="small text-muted">

                                        SKU:
                                        <?= e($item['sku']) ?>

                                    </div>

                                    <div class="cart-item-price">

                                        <?= money($item['price']) ?>

                                    </div>

                                </div>


                                <!-- QUANTITY -->

                                <div class="cart-item-quantity">

                                    <form
                                        method="POST"
                                        action="<?= $baseUrl ?>/cart.php"
                                    >

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="update"
                                        >

                                        <input
                                            type="hidden"
                                            name="item_id"
                                            value="<?= (int) $item['id'] ?>"
                                        >

                                        <input
                                            type="number"
                                            name="quantity"
                                            value="<?= (int) $item['quantity'] ?>"
                                            min="1"
                                            max="<?= (int) $item['stock_quantity'] ?>"
                                            class="form-control"
                                            onchange="this.form.submit()"
                                        >

                                    </form>

                                </div>


                                <!-- SUBTOTAL -->

                                <div class="cart-item-subtotal">

                                    <strong>

                                        <?= money($item['subtotal']) ?>

                                    </strong>

                                </div>


                                <!-- REMOVE -->

                                <div class="cart-item-remove">

                                    <form
                                        method="POST"
                                        action="<?= $baseUrl ?>/cart.php"
                                    >

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="remove"
                                        >

                                        <input
                                            type="hidden"
                                            name="item_id"
                                            value="<?= (int) $item['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Remove"
                                        >

                                            <i class="bi bi-trash"></i>

                                        </button>

                                    </form>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                </div>


                <!-- =================================================
                     CART ACTIONS
                ================================================== -->

                <div
                    class="d-flex justify-content-between
                           align-items-center mt-3"
                >

                    <a
                        href="<?= $baseUrl ?>/shop.php"
                        class="btn btn-outline-primary"
                    >

                        <i class="bi bi-arrow-left me-2"></i>

                        Continue Shopping

                    </a>


                    <form
                        method="POST"
                        action="<?= $baseUrl ?>/cart.php"
                        onsubmit="
                            return confirm(
                                'Are you sure you want to clear your cart?'
                            );
                        "
                    >

                        <input
                            type="hidden"
                            name="action"
                            value="clear"
                        >

                        <button
                            type="submit"
                            class="btn btn-outline-danger"
                        >

                            <i class="bi bi-trash me-2"></i>

                            Clear Cart

                        </button>

                    </form>

                </div>

            </div>


            <!-- =================================================
                 ORDER SUMMARY
            ================================================== -->

            <div class="col-lg-4">

                <div
                    class="card border-0 shadow-sm
                           cart-summary-card"
                >

                    <div class="card-body p-4">

                        <h4 class="fw-bold mb-4">
                            Order Summary
                        </h4>


                        <!-- Items -->

                        <div
                            class="d-flex
                                   justify-content-between
                                   mb-3"
                        >

                            <span class="text-muted">
                                Items
                            </span>

                            <span>
                                <?= $cartCount ?>
                            </span>

                        </div>


                        <!-- Subtotal -->

                        <div
                            class="d-flex
                                   justify-content-between
                                   mb-3"
                        >

                            <span class="text-muted">
                                Subtotal
                            </span>

                            <strong>
                                <?= money($cartSubtotal) ?>
                            </strong>

                        </div>


                        <hr>


                        <!-- Total -->

                        <div
                            class="d-flex
                                   justify-content-between
                                   mb-4"
                        >

                            <strong>
                                Total
                            </strong>

                            <strong class="cart-total">
                                <?= money($cartSubtotal) ?>
                            </strong>

                        </div>


                        <!-- Delivery Notice -->

                        <div class="alert alert-info small">

                            <i class="bi bi-info-circle me-2"></i>

                            Delivery charges and any applicable
                            discounts will be calculated during
                            checkout.

                        </div>


                        <!-- CHECKOUT -->

                        <a
                            href="<?= $baseUrl ?>/checkout.php"
                            class="btn btn-primary btn-lg w-100"
                        >

                            <i class="bi bi-credit-card me-2"></i>

                            Proceed to Checkout

                        </a>

                    </div>

                </div>

            </div>

        </div>

    <?php endif; ?>

</div>


<style>

/*
|--------------------------------------------------------------------------
| Cart Styling
|--------------------------------------------------------------------------
*/

.cart-item {

    display: grid;

    grid-template-columns:
        100px
        1fr
        100px
        120px
        50px;

    gap: 20px;

    align-items: center;

    padding: 20px;

    border-bottom:
        1px solid #e9ecef;
}


.cart-item:last-child {
    border-bottom: 0;
}


.cart-item-image {

    width: 100px;

    height: 100px;

    background: #f8faff;

    border-radius: 12px;

    display: flex;

    align-items: center;

    justify-content: center;

    overflow: hidden;
}


.cart-item-image img {

    width: 100%;

    height: 100%;

    object-fit: contain;

    padding: 8px;
}


.cart-no-image {

    color: #adb5bd;

    font-size: 30px;
}


.cart-product-name {

    font-weight: 700;

    font-size: 1rem;

    color: #212529;

    text-decoration: none;
}


.cart-product-name:hover {

    color: #1D88E9;
}


.cart-item-price {

    color: #1D88E9;

    font-weight: 600;

    margin-top: 5px;
}


.cart-item-quantity input {

    width: 90px;
}


.cart-item-subtotal {

    color: #212529;
}


.cart-total {

    color: #1D88E9;

    font-size: 1.3rem;
}


.cart-summary-card {

    position: sticky;

    top: 90px;
}


/*
|--------------------------------------------------------------------------
| Tablet
|--------------------------------------------------------------------------
*/

@media (max-width: 991px) {

    .cart-item {

        grid-template-columns:
            80px
            1fr
            80px;
    }


    .cart-item-image {

        width: 80px;

        height: 80px;
    }


    .cart-item-subtotal {

        grid-column: 2;
    }


    .cart-item-remove {

        grid-column: 3;

        grid-row: 1;
    }

}


/*
|--------------------------------------------------------------------------
| Mobile
|--------------------------------------------------------------------------
*/

@media (max-width: 575px) {

    .cart-item {

        grid-template-columns:
            70px
            1fr
            40px;

        gap: 12px;

        padding: 15px;
    }


    .cart-item-image {

        width: 70px;

        height: 70px;
    }


    .cart-item-quantity {

        grid-column: 2;
    }


    .cart-item-subtotal {

        grid-column: 2;
    }


    .cart-item-remove {

        grid-column: 3;

        grid-row: 1;
    }

}

</style>


<?php

require_once __DIR__ . '/includes/footer.php';

?>