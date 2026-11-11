<?php
/**
 * =========================================================
 * BETACHEM E-COMMERCE
 * ADMIN - EDIT PRODUCT
 * =========================================================
 */

require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$pageTitle = "Edit Product";

$baseUrl = '/betachem/betachem_e-commerce';

$errors = [];

$productId = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if ($productId <= 0) {
    redirect($baseUrl . '/admin/products/index.php');
}


/*
|--------------------------------------------------------------------------
| Load Product
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT *
    FROM products
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $productId);
$stmt->execute();

$product = $stmt->get_result()->fetch_assoc();

$stmt->close();

if (!$product) {
    redirect($baseUrl . '/admin/products/index.php');
}


/*
|--------------------------------------------------------------------------
| Load Categories
|--------------------------------------------------------------------------
*/

$categories = [];

$categoryResult = $conn->query("
    SELECT id, name
    FROM categories
    WHERE is_active = 1
       OR id = " . (int)$product['category_id'] . "
    ORDER BY name ASC
");

while ($category = $categoryResult->fetch_assoc()) {
    $categories[] = $category;
}


/*
|--------------------------------------------------------------------------
| Default Values
|--------------------------------------------------------------------------
*/

$name = $product['name'];
$sku = $product['sku'];
$description = $product['description'];
$specifications = $product['specifications'];
$price = $product['price'];
$stockQuantity = $product['stock_quantity'];
$minimumStock = $product['minimum_stock'];
$weight = $product['weight'];
$categoryId = (int)$product['category_id'];

$isActive = (int)$product['is_active'];
$isFeatured = (int)$product['is_featured'];

$currentImage = $product['main_image'];


/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $specifications = trim($_POST['specifications'] ?? '');

    $price = $_POST['price'] ?? '';
    $stockQuantity = $_POST['stock_quantity'] ?? '0';
    $minimumStock = $_POST['minimum_stock'] ?? '0';
    $weight = $_POST['weight'] ?? '';

    $categoryId = (int)($_POST['category_id'] ?? 0);

    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($name === '') {
        $errors[] = "Product name is required.";
    }

    if ($sku === '') {
        $errors[] = "SKU is required.";
    }

    if ($categoryId <= 0) {
        $errors[] = "Please select a category.";
    }

    if (
        $price === '' ||
        !is_numeric($price) ||
        (float)$price < 0
    ) {
        $errors[] = "Please enter a valid product price.";
    }

    if (
        $stockQuantity === '' ||
        !is_numeric($stockQuantity) ||
        (int)$stockQuantity < 0
    ) {
        $errors[] = "Please enter a valid stock quantity.";
    }

    if (
        $minimumStock === '' ||
        !is_numeric($minimumStock) ||
        (int)$minimumStock < 0
    ) {
        $errors[] = "Please enter a valid minimum stock level.";
    }

    if (
        $weight !== '' &&
        (!is_numeric($weight) || (float)$weight < 0)
    ) {
        $errors[] = "Please enter a valid weight.";
    }


    /*
    |--------------------------------------------------------------------------
    | Category Validation
    |--------------------------------------------------------------------------
    */

    if ($categoryId > 0) {

        $categoryStmt = $conn->prepare("
            SELECT id
            FROM categories
            WHERE id = ?
              AND is_active = 1
            LIMIT 1
        ");

        $categoryStmt->bind_param(
            "i",
            $categoryId
        );

        $categoryStmt->execute();

        $categoryExists = $categoryStmt
            ->get_result()
            ->fetch_assoc();

        $categoryStmt->close();

        if (!$categoryExists) {
            $errors[] = "The selected category is invalid.";
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Slug
    |--------------------------------------------------------------------------
    */

    $slug = createSlug($name);


    /*
    |--------------------------------------------------------------------------
    | Duplicate Check
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $duplicateStmt = $conn->prepare("
            SELECT id, name, slug, sku
            FROM products
            WHERE id != ?
              AND (
                    name = ?
                    OR slug = ?
                    OR sku = ?
              )
            LIMIT 1
        ");

        $duplicateStmt->bind_param(
            "isss",
            $productId,
            $name,
            $slug,
            $sku
        );

        $duplicateStmt->execute();

        $duplicate = $duplicateStmt
            ->get_result()
            ->fetch_assoc();

        $duplicateStmt->close();

        if ($duplicate) {

            if (
                strcasecmp(
                    $duplicate['name'],
                    $name
                ) === 0
            ) {
                $errors[] =
                    "Another product with this name already exists.";
            }

            if ($duplicate['slug'] === $slug) {
                $errors[] =
                    "Another product with this slug already exists.";
            }

            if ($duplicate['sku'] === $sku) {
                $errors[] =
                    "Another product with this SKU already exists.";
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Image
    |--------------------------------------------------------------------------
    */

    $newImageName = $currentImage;
    $newImageUploaded = false;

    if (
        isset($_FILES['main_image']) &&
        $_FILES['main_image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['main_image']['error'] !== UPLOAD_ERR_OK) {

            $errors[] =
                "There was a problem uploading the product image.";

        } else {

            $maxSize = 5 * 1024 * 1024;

            if ($_FILES['main_image']['size'] > $maxSize) {

                $errors[] =
                    "Product image must not exceed 5MB.";

            } else {

                $allowedMimeTypes = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp'
                ];

                $fileInfo = finfo_open(FILEINFO_MIME_TYPE);

                $mimeType = finfo_file(
                    $fileInfo,
                    $_FILES['main_image']['tmp_name']
                );

                finfo_close($fileInfo);

                if (!isset($allowedMimeTypes[$mimeType])) {

                    $errors[] =
                        "Only JPG, PNG and WEBP images are allowed.";

                } else {

                    $extension =
                        $allowedMimeTypes[$mimeType];

                    $newImageName =
                        'product_' .
                        date('Ymd_His') .
                        '_' .
                        bin2hex(random_bytes(4)) .
                        '.' .
                        $extension;

                    $newImageUploaded = true;
                }
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Save
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $priceValue = (float)$price;
        $stockValue = (int)$stockQuantity;
        $minimumStockValue = (int)$minimumStock;

        $weightValue = (
            $weight === ''
                ? null
                : (float)$weight
        );


        $updateStmt = $conn->prepare("
            UPDATE products
            SET
                category_id = ?,
                name = ?,
                slug = ?,
                sku = ?,
                description = ?,
                specifications = ?,
                price = ?,
                stock_quantity = ?,
                minimum_stock = ?,
                weight = ?,
                main_image = ?,
                is_active = ?,
                is_featured = ?,
                updated_at = NOW()
            WHERE id = ?
        ");

        $updateStmt->bind_param(
            "isssssdiidsiii",
            $categoryId,
            $name,
            $slug,
            $sku,
            $description,
            $specifications,
            $priceValue,
            $stockValue,
            $minimumStockValue,
            $weightValue,
            $newImageName,
            $isActive,
            $isFeatured,
            $productId
        );

        $updateStmt->execute();

        $updateStmt->close();


        /*
        |--------------------------------------------------------------------------
        | Move New Image
        |--------------------------------------------------------------------------
        */

        if ($newImageUploaded) {

            $uploadDirectory =
                __DIR__ .
                '/../../uploads/products/';

            if (!is_dir($uploadDirectory)) {
                mkdir(
                    $uploadDirectory,
                    0755,
                    true
                );
            }

            $destination =
                $uploadDirectory .
                $newImageName;

            if (
                move_uploaded_file(
                    $_FILES['main_image']['tmp_name'],
                    $destination
                )
            ) {

                /*
                |--------------------------------------------------------------------------
                | Delete Old Main Image
                |--------------------------------------------------------------------------
                */

                if (
                    !empty($currentImage) &&
                    $currentImage !== $newImageName
                ) {

                    $oldImage =
                        $uploadDirectory .
                        basename($currentImage);

                    if (is_file($oldImage)) {
                        unlink($oldImage);
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Update Primary Product Image
                |--------------------------------------------------------------------------
                */

                $imageStmt = $conn->prepare("
                    UPDATE product_images
                    SET
                        image_path = ?,
                        alt_text = ?,
                        updated_at = NOW()
                    WHERE product_id = ?
                      AND is_primary = 1
                    LIMIT 1
                ");

                $imageStmt->bind_param(
                    "ssi",
                    $newImageName,
                    $name,
                    $productId
                );

                $imageStmt->execute();

                $affectedRows = $imageStmt->affected_rows;

                $imageStmt->close();


                /*
                |--------------------------------------------------------------------------
                | If no primary image exists
                |--------------------------------------------------------------------------
                */

                if ($affectedRows === 0) {

                    $insertImageStmt = $conn->prepare("
                        INSERT INTO product_images (
                            product_id,
                            image_path,
                            alt_text,
                            sort_order,
                            is_primary,
                            created_at,
                            updated_at
                        )
                        VALUES (
                            ?, ?, ?, 0, 1, NOW(), NOW()
                        )
                    ");

                    $insertImageStmt->bind_param(
                        "iss",
                        $productId,
                        $newImageName,
                        $name
                    );

                    $insertImageStmt->execute();

                    $insertImageStmt->close();
                }

            } else {

                $errors[] =
                    "The product was updated, but the new image could not be uploaded.";
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        if (empty($errors)) {

            redirect(
                $baseUrl . '/admin/products/index.php'
            );
        }
    }
}


include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>

            <h1 class="fw-bold mb-1">
                Edit Product
            </h1>

            <p class="text-muted mb-0">
                Update product information, pricing and stock.
            </p>

        </div>

        <a
            href="<?= $baseUrl; ?>/admin/products/index.php"
            class="btn btn-outline-secondary"
        >
            <i class="bi bi-arrow-left me-1"></i>
            Back to Products
        </a>

    </div>


    <!-- Errors -->
    <?php if (!empty($errors)): ?>

        <div class="alert alert-danger">

            <div class="fw-bold mb-2">
                Please correct the following:
            </div>

            <ul class="mb-0">

                <?php foreach ($errors as $error): ?>

                    <li>
                        <?= e($error) ?>
                    </li>

                <?php endforeach; ?>

            </ul>

        </div>

    <?php endif; ?>


    <form
        method="POST"
        action="<?= $baseUrl; ?>/admin/products/edit.php?id=<?= $productId ?>"
        enctype="multipart/form-data"
    >

        <input
            type="hidden"
            name="id"
            value="<?= $productId ?>"
        >


        <div class="row g-4">

            <!-- Main Information -->
            <div class="col-lg-8">

                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-header bg-white py-3">

                        <h5 class="mb-0 fw-bold">
                            Product Information
                        </h5>

                    </div>

                    <div class="card-body">

                        <div class="row g-3">

                            <!-- Name -->
                            <div class="col-md-8">

                                <label class="form-label fw-semibold">
                                    Product Name
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="name"
                                    class="form-control"
                                    value="<?= e($name) ?>"
                                    required
                                >

                            </div>


                            <!-- SKU -->
                            <div class="col-md-4">

                                <label class="form-label fw-semibold">
                                    SKU
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="sku"
                                    class="form-control"
                                    value="<?= e($sku) ?>"
                                    required
                                >

                            </div>


                            <!-- Category -->
                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Category
                                    <span class="text-danger">*</span>
                                </label>

                                <select
                                    name="category_id"
                                    class="form-select"
                                    required
                                >

                                    <option value="">
                                        Select Category
                                    </option>

                                    <?php foreach ($categories as $category): ?>

                                        <option
                                            value="<?= (int)$category['id'] ?>"
                                            <?= $categoryId === (int)$category['id'] ? 'selected' : '' ?>
                                        >
                                            <?= e($category['name']) ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>


                            <!-- Price -->
                            <div class="col-md-3">

                                <label class="form-label fw-semibold">
                                    Price
                                    <span class="text-danger">*</span>
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text">
                                        R
                                    </span>

                                    <input
                                        type="number"
                                        name="price"
                                        class="form-control"
                                        value="<?= e($price) ?>"
                                        min="0"
                                        step="0.01"
                                        required
                                    >

                                </div>

                            </div>


                            <!-- Weight -->
                            <div class="col-md-3">

                                <label class="form-label fw-semibold">
                                    Weight
                                </label>

                                <div class="input-group">

                                    <input
                                        type="number"
                                        name="weight"
                                        class="form-control"
                                        value="<?= e($weight) ?>"
                                        min="0"
                                        step="0.001"
                                    >

                                    <span class="input-group-text">
                                        kg
                                    </span>

                                </div>

                            </div>


                            <!-- Description -->
                            <div class="col-12">

                                <label class="form-label fw-semibold">
                                    Product Description
                                </label>

                                <textarea
                                    name="description"
                                    class="form-control"
                                    rows="5"
                                ><?= e($description) ?></textarea>

                            </div>


                            <!-- Specifications -->
                            <div class="col-12">

                                <label class="form-label fw-semibold">
                                    Specifications
                                </label>

                                <textarea
                                    name="specifications"
                                    class="form-control"
                                    rows="5"
                                ><?= e($specifications) ?></textarea>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- Inventory -->
                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-header bg-white py-3">

                        <h5 class="mb-0 fw-bold">
                            Inventory
                        </h5>

                    </div>

                    <div class="card-body">

                        <div class="row g-3">

                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Stock Quantity
                                </label>

                                <input
                                    type="number"
                                    name="stock_quantity"
                                    class="form-control"
                                    value="<?= e($stockQuantity) ?>"
                                    min="0"
                                    step="1"
                                >

                            </div>


                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Minimum Stock Level
                                </label>

                                <input
                                    type="number"
                                    name="minimum_stock"
                                    class="form-control"
                                    value="<?= e($minimumStock) ?>"
                                    min="0"
                                    step="1"
                                >

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- Right -->
            <div class="col-lg-4">

                <!-- Current Image -->
                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-header bg-white py-3">

                        <h5 class="mb-0 fw-bold">
                            Product Image
                        </h5>

                    </div>

                    <div class="card-body">

                        <?php if (!empty($currentImage)): ?>

                            <div class="text-center mb-3">

                                <img
                                    src="<?= $baseUrl; ?>/uploads/products/<?= rawurlencode(basename($currentImage)) ?>"
                                    alt="<?= e($name) ?>"
                                    class="img-fluid rounded border"
                                    style="
                                        max-height:220px;
                                        object-fit:contain;
                                    "
                                >

                            </div>

                        <?php else: ?>

                            <div class="text-center text-muted py-4">

                                <i class="bi bi-image fs-1"></i>

                                <div>
                                    No image uploaded.
                                </div>

                            </div>

                        <?php endif; ?>


                        <label class="form-label fw-semibold">
                            Replace Main Image
                        </label>

                        <input
                            type="file"
                            name="main_image"
                            class="form-control"
                            accept=".jpg,.jpeg,.png,.webp"
                        >

                        <small class="text-muted">
                            Leave empty to keep the current image.
                            Maximum 5MB.
                        </small>

                    </div>

                </div>


                <!-- Store Settings -->
                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-header bg-white py-3">

                        <h5 class="mb-0 fw-bold">
                            Store Settings
                        </h5>

                    </div>

                    <div class="card-body">

                        <div class="form-check form-switch mb-3">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="is_active"
                                id="is_active"
                                <?= $isActive ? 'checked' : '' ?>
                            >

                            <label
                                class="form-check-label fw-semibold"
                                for="is_active"
                            >
                                Active Product
                            </label>

                        </div>


                        <div class="form-check form-switch">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="is_featured"
                                id="is_featured"
                                <?= $isFeatured ? 'checked' : '' ?>
                            >

                            <label
                                class="form-check-label fw-semibold"
                                for="is_featured"
                            >
                                Featured Product
                            </label>

                        </div>

                    </div>

                </div>


                <!-- Save -->
                <div class="card border-0 shadow-sm">

                    <div class="card-body">

                        <button
                            type="submit"
                            class="btn btn-primary btn-lg w-100"
                        >
                            <i class="bi bi-save me-1"></i>
                            Update Product
                        </button>

                        <a
                            href="<?= $baseUrl; ?>/admin/products/index.php"
                            class="btn btn-outline-secondary w-100 mt-2"
                        >
                            Cancel
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </form>

</div>

<?php
include __DIR__ . '/../includes/footer.php';
?>