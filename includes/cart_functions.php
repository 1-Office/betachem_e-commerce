<?php

/*
|--------------------------------------------------------------------------
| Betachem E-Commerce Cart Functions
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| Get Cart ID
|--------------------------------------------------------------------------
|
| Logged-in customers use their user_id.
|
| Guests use their current PHP session_id.
|
*/

function getCartId()
{
    global $conn;

    $userId = $_SESSION['user_id'] ?? null;
    $sessionId = session_id();


    /*
    |--------------------------------------------------------------------------
    | Logged-in Customer Cart
    |--------------------------------------------------------------------------
    */

    if ($userId) {

        $stmt = $conn->prepare("
            SELECT id
            FROM carts
            WHERE user_id = ?
            LIMIT 1
        ");

        if (!$stmt) {
            return 0;
        }

        $stmt->bind_param(
            "i",
            $userId
        );

        $stmt->execute();

        $result = $stmt->get_result();
        $cart = $result->fetch_assoc();

        $stmt->close();


        if ($cart) {
            return (int) $cart['id'];
        }


        /*
        |--------------------------------------------------------------------------
        | Create Customer Cart
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            INSERT INTO carts
                (
                    user_id,
                    session_id
                )
            VALUES
                (
                    ?,
                    ?
                )
        ");

        if (!$stmt) {
            return 0;
        }

        $stmt->bind_param(
            "is",
            $userId,
            $sessionId
        );

        $stmt->execute();

        $cartId = $conn->insert_id;

        $stmt->close();

        return (int) $cartId;
    }


    /*
    |--------------------------------------------------------------------------
    | Guest Cart
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        SELECT id
        FROM carts
        WHERE session_id = ?
          AND user_id IS NULL
        LIMIT 1
    ");

    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param(
        "s",
        $sessionId
    );

    $stmt->execute();

    $result = $stmt->get_result();
    $cart = $result->fetch_assoc();

    $stmt->close();


    if ($cart) {
        return (int) $cart['id'];
    }


    /*
    |--------------------------------------------------------------------------
    | Create Guest Cart
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        INSERT INTO carts
            (
                user_id,
                session_id
            )
        VALUES
            (
                NULL,
                ?
            )
    ");

    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param(
        "s",
        $sessionId
    );

    $stmt->execute();

    $cartId = $conn->insert_id;

    $stmt->close();

    return (int) $cartId;
}


/*
|--------------------------------------------------------------------------
| Merge Guest Cart Into Customer Cart
|--------------------------------------------------------------------------
|
| This is used when a guest logs in.
|
| Example:
|
| Guest cart:
| Product A x 2
| Product B x 1
|
| Existing customer cart:
| Product A x 1
|
| Result:
| Product A x 3
| Product B x 1
|
| Quantities are never allowed to exceed current stock.
|
*/

function mergeGuestCartIntoCustomerCart($userId, $guestSessionId)
{
    global $conn;

    $userId = (int) $userId;
    $guestSessionId = trim((string) $guestSessionId);


    if ($userId <= 0 || $guestSessionId === '') {
        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | Find Guest Cart
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        SELECT id
        FROM carts
        WHERE session_id = ?
          AND user_id IS NULL
        LIMIT 1
    ");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        "s",
        $guestSessionId
    );

    $stmt->execute();

    $result = $stmt->get_result();
    $guestCart = $result->fetch_assoc();

    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | Nothing To Merge
    |--------------------------------------------------------------------------
    */

    if (!$guestCart) {
        return true;
    }


    $guestCartId = (int) $guestCart['id'];


    /*
    |--------------------------------------------------------------------------
    | Find Customer Cart
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        SELECT id
        FROM carts
        WHERE user_id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        "i",
        $userId
    );

    $stmt->execute();

    $result = $stmt->get_result();
    $customerCart = $result->fetch_assoc();

    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | Create Customer Cart If Needed
    |--------------------------------------------------------------------------
    */

    if (!$customerCart) {

        $currentSessionId = session_id();

        $stmt = $conn->prepare("
            INSERT INTO carts
                (
                    user_id,
                    session_id
                )
            VALUES
                (
                    ?,
                    ?
                )
        ");

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "is",
            $userId,
            $currentSessionId
        );

        $stmt->execute();

        $customerCartId = (int) $conn->insert_id;

        $stmt->close();

    } else {

        $customerCartId = (int) $customerCart['id'];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Guest Cart Items
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        SELECT
            ci.id,
            ci.product_id,
            ci.quantity,
            ci.price,

            p.price AS current_price,
            p.stock_quantity,
            p.is_active

        FROM cart_items ci

        INNER JOIN products p
            ON p.id = ci.product_id

        WHERE ci.cart_id = ?
    ");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        "i",
        $guestCartId
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $guestItems = [];

    while ($row = $result->fetch_assoc()) {
        $guestItems[] = $row;
    }

    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | Merge Each Product
    |--------------------------------------------------------------------------
    */

    foreach ($guestItems as $guestItem) {

        $productId = (int) $guestItem['product_id'];
        $guestQuantity = (int) $guestItem['quantity'];
        $stock = (int) $guestItem['stock_quantity'];
        $isActive = (int) $guestItem['is_active'];
        $currentPrice = (float) $guestItem['current_price'];


        /*
        |--------------------------------------------------------------------------
        | Ignore Products That Are No Longer Available
        |--------------------------------------------------------------------------
        */

        if ($isActive !== 1 || $stock <= 0) {
            continue;
        }


        if ($guestQuantity <= 0) {
            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | Check Existing Customer Cart Item
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            SELECT
                id,
                quantity
            FROM cart_items
            WHERE cart_id = ?
              AND product_id = ?
            LIMIT 1
        ");

        if (!$stmt) {
            continue;
        }

        $stmt->bind_param(
            "ii",
            $customerCartId,
            $productId
        );

        $stmt->execute();

        $result = $stmt->get_result();
        $existingItem = $result->fetch_assoc();

        $stmt->close();


        /*
        |--------------------------------------------------------------------------
        | Existing Product In Customer Cart
        |--------------------------------------------------------------------------
        */

        if ($existingItem) {

            $itemId =
                (int) $existingItem['id'];

            $existingQuantity =
                (int) $existingItem['quantity'];


            /*
            |------------------------------------------------------------------
            | Combine Quantities
            |------------------------------------------------------------------
            */

            $newQuantity =
                $existingQuantity +
                $guestQuantity;


            /*
            |------------------------------------------------------------------
            | Respect Stock
            |------------------------------------------------------------------
            */

            $newQuantity =
                min(
                    $newQuantity,
                    $stock
                );


            if ($newQuantity > 0) {

                $stmt = $conn->prepare("
                    UPDATE cart_items
                    SET
                        quantity = ?,
                        price = ?,
                        updated_at = NOW()
                    WHERE id = ?
                      AND cart_id = ?
                ");

                if ($stmt) {

                    $stmt->bind_param(
                        "idii",
                        $newQuantity,
                        $currentPrice,
                        $itemId,
                        $customerCartId
                    );

                    $stmt->execute();

                    $stmt->close();
                }
            }


        } else {

            /*
            |--------------------------------------------------------------------------
            | Product Does Not Exist In Customer Cart
            |--------------------------------------------------------------------------
            */

            $newQuantity =
                min(
                    $guestQuantity,
                    $stock
                );


            if ($newQuantity > 0) {

                $stmt = $conn->prepare("
                    INSERT INTO cart_items
                        (
                            cart_id,
                            product_id,
                            quantity,
                            price
                        )
                    VALUES
                        (
                            ?,
                            ?,
                            ?,
                            ?
                        )
                ");

                if ($stmt) {

                    $stmt->bind_param(
                        "iiid",
                        $customerCartId,
                        $productId,
                        $newQuantity,
                        $currentPrice
                    );

                    $stmt->execute();

                    $stmt->close();
                }
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Update Customer Cart Timestamp
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        UPDATE carts
        SET
            updated_at = NOW()
        WHERE id = ?
    ");

    if ($stmt) {

        $stmt->bind_param(
            "i",
            $customerCartId
        );

        $stmt->execute();

        $stmt->close();
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Guest Cart Items
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        DELETE FROM cart_items
        WHERE cart_id = ?
    ");

    if ($stmt) {

        $stmt->bind_param(
            "i",
            $guestCartId
        );

        $stmt->execute();

        $stmt->close();
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Guest Cart
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        DELETE FROM carts
        WHERE id = ?
          AND user_id IS NULL
    ");

    if ($stmt) {

        $stmt->bind_param(
            "i",
            $guestCartId
        );

        $stmt->execute();

        $stmt->close();
    }


    return true;
}


/*
|--------------------------------------------------------------------------
| Add Product To Cart
|--------------------------------------------------------------------------
*/

function addToCart($productId, $quantity = 1)
{
    global $conn;

    $productId = (int) $productId;
    $quantity = max(1, (int) $quantity);


    /*
    |--------------------------------------------------------------------------
    | Get Product
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        SELECT
            id,
            name,
            price,
            stock_quantity,
            is_active
        FROM products
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->bind_param(
        "i",
        $productId
    );

    $stmt->execute();

    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    $stmt->close();


    if (!$product) {

        return [
            'success' => false,
            'message' => 'Product not found.'
        ];
    }


    if ((int) $product['is_active'] !== 1) {

        return [
            'success' => false,
            'message' => 'This product is no longer available.'
        ];
    }


    $stock =
        (int) $product['stock_quantity'];


    if ($stock <= 0) {

        return [
            'success' => false,
            'message' => 'This product is out of stock.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Cart
    |--------------------------------------------------------------------------
    */

    $cartId = getCartId();


    if ($cartId <= 0) {

        return [
            'success' => false,
            'message' => 'Unable to create your shopping cart.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Check Existing Cart Item
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        SELECT
            id,
            quantity
        FROM cart_items
        WHERE cart_id = ?
          AND product_id = ?
        LIMIT 1
    ");

    $stmt->bind_param(
        "ii",
        $cartId,
        $productId
    );

    $stmt->execute();

    $result = $stmt->get_result();
    $existingItem = $result->fetch_assoc();

    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | Existing Item
    |--------------------------------------------------------------------------
    */

    if ($existingItem) {

        $newQuantity =
            (int) $existingItem['quantity'] +
            $quantity;


        if ($newQuantity > $stock) {

            return [
                'success' => false,
                'message' =>
                    'You cannot add more than '
                    . $stock
                    . ' units of this product.'
            ];
        }


        $stmt = $conn->prepare("
            UPDATE cart_items
            SET
                quantity = ?,
                price = ?,
                updated_at = NOW()
            WHERE id = ?
        ");

        $price =
            (float) $product['price'];

        $itemId =
            (int) $existingItem['id'];


        $stmt->bind_param(
            "idi",
            $newQuantity,
            $price,
            $itemId
        );

        $stmt->execute();

        $stmt->close();


    } else {

        /*
        |--------------------------------------------------------------------------
        | New Item
        |--------------------------------------------------------------------------
        */

        if ($quantity > $stock) {

            return [
                'success' => false,
                'message' =>
                    'Only '
                    . $stock
                    . ' units are available.'
            ];
        }


        $price =
            (float) $product['price'];


        $stmt = $conn->prepare("
            INSERT INTO cart_items
                (
                    cart_id,
                    product_id,
                    quantity,
                    price
                )
            VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?
                )
        ");


        $stmt->bind_param(
            "iiid",
            $cartId,
            $productId,
            $quantity,
            $price
        );

        $stmt->execute();

        $stmt->close();
    }


    /*
    |--------------------------------------------------------------------------
    | Update Cart Timestamp
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        UPDATE carts
        SET
            updated_at = NOW()
        WHERE id = ?
    ");

    $stmt->bind_param(
        "i",
        $cartId
    );

    $stmt->execute();

    $stmt->close();


    return [
        'success' => true,
        'message' =>
            $product['name']
            . ' has been added to your cart.'
    ];
}


/*
|--------------------------------------------------------------------------
| Get Cart Items
|--------------------------------------------------------------------------
*/

function getCartItems()
{
    global $conn;

    $cartId = getCartId();


    if ($cartId <= 0) {
        return [];
    }


    $stmt = $conn->prepare("
        SELECT
            ci.id,
            ci.product_id,
            ci.quantity,
            ci.price,

            p.name,
            p.slug,
            p.sku,
            p.main_image,
            p.stock_quantity,
            p.is_active

        FROM cart_items ci

        INNER JOIN products p
            ON p.id = ci.product_id

        WHERE ci.cart_id = ?

        ORDER BY ci.id DESC
    ");

    $stmt->bind_param(
        "i",
        $cartId
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $items = [];


    while ($row = $result->fetch_assoc()) {

        $row['quantity'] =
            (int) $row['quantity'];

        $row['price'] =
            (float) $row['price'];

        $row['subtotal'] =
            $row['price'] *
            $row['quantity'];

        $items[] = $row;
    }


    $stmt->close();


    return $items;
}


/*
|--------------------------------------------------------------------------
| Cart Count
|--------------------------------------------------------------------------
*/

function getCartCount()
{
    global $conn;

    $cartId = getCartId();


    if ($cartId <= 0) {
        return 0;
    }


    $stmt = $conn->prepare("
        SELECT
            COALESCE(
                SUM(quantity),
                0
            ) AS total
        FROM cart_items
        WHERE cart_id = ?
    ");

    $stmt->bind_param(
        "i",
        $cartId
    );

    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $stmt->close();


    return (int) $row['total'];
}


/*
|--------------------------------------------------------------------------
| Cart Subtotal
|--------------------------------------------------------------------------
*/

function getCartSubtotal()
{
    global $conn;

    $cartId = getCartId();


    if ($cartId <= 0) {
        return 0;
    }


    $stmt = $conn->prepare("
        SELECT
            COALESCE(
                SUM(
                    quantity * price
                ),
                0
            ) AS subtotal
        FROM cart_items
        WHERE cart_id = ?
    ");

    $stmt->bind_param(
        "i",
        $cartId
    );

    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $stmt->close();


    return (float) $row['subtotal'];
}


/*
|--------------------------------------------------------------------------
| Update Cart Item
|--------------------------------------------------------------------------
*/

function updateCartItem($itemId, $quantity)
{
    global $conn;

    $itemId =
        (int) $itemId;

    $quantity =
        (int) $quantity;


    if ($quantity < 1) {
        return removeCartItem($itemId);
    }


    $cartId =
        getCartId();


    if ($cartId <= 0) {

        return [
            'success' => false,
            'message' => 'Shopping cart not found.'
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Item + Current Stock
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        SELECT
            ci.id,
            p.stock_quantity,
            p.is_active
        FROM cart_items ci

        INNER JOIN products p
            ON p.id = ci.product_id

        WHERE ci.id = ?
          AND ci.cart_id = ?

        LIMIT 1
    ");

    $stmt->bind_param(
        "ii",
        $itemId,
        $cartId
    );

    $stmt->execute();

    $result = $stmt->get_result();
    $item = $result->fetch_assoc();

    $stmt->close();


    if (!$item) {

        return [
            'success' => false,
            'message' => 'Cart item not found.'
        ];
    }


    if ((int) $item['is_active'] !== 1) {

        return [
            'success' => false,
            'message' =>
                'This product is no longer available.'
        ];
    }


    $stock =
        (int) $item['stock_quantity'];


    if ($quantity > $stock) {

        return [
            'success' => false,
            'message' =>
                'Only '
                . $stock
                . ' units are available.'
        ];
    }


    $stmt = $conn->prepare("
        UPDATE cart_items
        SET
            quantity = ?,
            updated_at = NOW()
        WHERE id = ?
          AND cart_id = ?
    ");

    $stmt->bind_param(
        "iii",
        $quantity,
        $itemId,
        $cartId
    );

    $stmt->execute();

    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | Update Cart Timestamp
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        UPDATE carts
        SET
            updated_at = NOW()
        WHERE id = ?
    ");

    $stmt->bind_param(
        "i",
        $cartId
    );

    $stmt->execute();

    $stmt->close();


    return [
        'success' => true,
        'message' =>
            'Cart updated successfully.'
    ];
}


/*
|--------------------------------------------------------------------------
| Remove Cart Item
|--------------------------------------------------------------------------
*/

function removeCartItem($itemId)
{
    global $conn;

    $itemId =
        (int) $itemId;

    $cartId =
        getCartId();


    if ($cartId <= 0) {

        return [
            'success' => false,
            'message' =>
                'Shopping cart not found.'
        ];
    }


    $stmt = $conn->prepare("
        DELETE FROM cart_items
        WHERE id = ?
          AND cart_id = ?
    ");

    $stmt->bind_param(
        "ii",
        $itemId,
        $cartId
    );

    $stmt->execute();

    $deleted =
        $stmt->affected_rows;

    $stmt->close();


    if ($deleted > 0) {

        /*
        |--------------------------------------------------------------------------
        | Update Cart Timestamp
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            UPDATE carts
            SET
                updated_at = NOW()
            WHERE id = ?
        ");

        $stmt->bind_param(
            "i",
            $cartId
        );

        $stmt->execute();

        $stmt->close();


        return [
            'success' => true,
            'message' =>
                'Item removed from your cart.'
        ];
    }


    return [
        'success' => false,
        'message' =>
            'Cart item could not be removed.'
    ];
}


/*
|--------------------------------------------------------------------------
| Clear Cart
|--------------------------------------------------------------------------
*/

function clearCart()
{
    global $conn;

    $cartId =
        getCartId();


    if ($cartId <= 0) {
        return true;
    }


    $stmt = $conn->prepare("
        DELETE FROM cart_items
        WHERE cart_id = ?
    ");

    $stmt->bind_param(
        "i",
        $cartId
    );

    $stmt->execute();

    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | Update Cart Timestamp
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        UPDATE carts
        SET
            updated_at = NOW()
        WHERE id = ?
    ");

    $stmt->bind_param(
        "i",
        $cartId
    );

    $stmt->execute();

    $stmt->close();


    return true;
}