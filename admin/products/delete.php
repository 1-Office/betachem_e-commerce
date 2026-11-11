<?php
/**
 * =========================================================
 * BETACHEM E-COMMERCE
 * ADMIN - DELETE PRODUCT
 * =========================================================
 */

require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$baseUrl = '/betachem/betachem_e-commerce';

$productId = (int)($_GET['id'] ?? 0);

if ($productId <= 0) {
    redirect($baseUrl . '/admin/products/index.php');
}


/*
|--------------------------------------------------------------------------
| Get Product
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        id,
        name,
        main_image
    FROM products
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $productId
);

$stmt->execute();

$product = $stmt
    ->get_result()
    ->fetch_assoc();

$stmt->close();


if (!$product) {
    redirect($baseUrl . '/admin/products/index.php');
}


/*
|--------------------------------------------------------------------------
| Check Order History
|--------------------------------------------------------------------------
|
| If the product has ever been included in an order,
| we do NOT physically delete it.
|
| This protects historical order records.
|
*/

$orderStmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM order_items
    WHERE product_id = ?
");

$orderStmt->bind_param(
    "i",
    $productId
);

$orderStmt->execute();

$orderData = $orderStmt
    ->get_result()
    ->fetch_assoc();

$orderStmt->close();

$orderCount = (int)$orderData['total'];


/*
|--------------------------------------------------------------------------
| Product Has Order History
|--------------------------------------------------------------------------
*/

if ($orderCount > 0) {

    /*
    |--------------------------------------------------------------------------
    | Instead of deleting, deactivate the product
    |--------------------------------------------------------------------------
    */

    $deactivateStmt = $conn->prepare("
        UPDATE products
        SET
            is_active = 0,
            is_featured = 0,
            updated_at = NOW()
        WHERE id = ?
    ");

    $deactivateStmt->bind_param(
        "i",
        $productId
    );

    $deactivateStmt->execute();

    $deactivateStmt->close();


    /*
    |--------------------------------------------------------------------------
    | Redirect with message
    |--------------------------------------------------------------------------
    */

    $_SESSION['product_message'] =
        "This product has order history and cannot be permanently deleted. It has been deactivated instead.";

    redirect(
        $baseUrl . '/admin/products/index.php'
    );
}


/*
|--------------------------------------------------------------------------
| Get Product Images
|--------------------------------------------------------------------------
*/

$imageStmt = $conn->prepare("
    SELECT image_path
    FROM product_images
    WHERE product_id = ?
");

$imageStmt->bind_param(
    "i",
    $productId
);

$imageStmt->execute();

$imageResult = $imageStmt->get_result();

$productImages = [];

while ($image = $imageResult->fetch_assoc()) {

    if (!empty($image['image_path'])) {
        $productImages[] = $image['image_path'];
    }
}

$imageStmt->close();


/*
|--------------------------------------------------------------------------
| Delete Product Images
|--------------------------------------------------------------------------
*/

$deleteImagesStmt = $conn->prepare("
    DELETE FROM product_images
    WHERE product_id = ?
");

$deleteImagesStmt->bind_param(
    "i",
    $productId
);

$deleteImagesStmt->execute();

$deleteImagesStmt->close();


/*
|--------------------------------------------------------------------------
| Delete Product
|--------------------------------------------------------------------------
*/

$deleteProductStmt = $conn->prepare("
    DELETE FROM products
    WHERE id = ?
");

$deleteProductStmt->bind_param(
    "i",
    $productId
);

$deleteProductStmt->execute();

$deleteProductStmt->close();


/*
|--------------------------------------------------------------------------
| Remove Image Files
|--------------------------------------------------------------------------
*/

$uploadDirectory =
    __DIR__ .
    '/../../uploads/products/';


foreach ($productImages as $imageName) {

    $imagePath =
        $uploadDirectory .
        basename($imageName);

    if (is_file($imagePath)) {
        unlink($imagePath);
    }
}


/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

$_SESSION['product_message'] =
    "Product deleted successfully.";

redirect(
    $baseUrl . '/admin/products/index.php'
);