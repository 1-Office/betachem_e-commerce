<?php
/**
 * =========================================================
 * BETACHEM E-COMMERCE
 * ADMIN - ADD PRODUCT
 * =========================================================
 */

require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$pageTitle = "Add Product";

$baseUrl = '/betachem/betachem_e-commerce';

$errors = [];

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
| Load Categories
|--------------------------------------------------------------------------
*/

$categories = [];

$categoryResult = $conn->query("
    SELECT id, name
    FROM categories
    WHERE is_active = 1
    ORDER BY name ASC
");

while ($category = $categoryResult->fetch_assoc()) {
    $categories[] = $category;
}


/*
|--------------------------------------------------------------------------
| Submit Product
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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

    if ($price === '' || !is_numeric($price) || (float)$price < 0) {
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
    | Check Category
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
    | Generate Slug
    |--------------------------------------------------------------------------
    */

    $slug = createSlug($name);


    /*
    |--------------------------------------------------------------------------
    | Check Duplicate Name / SKU / Slug
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $duplicateStmt = $conn->prepare("
            SELECT id, name, slug, sku
            FROM products
            WHERE name = ?
               OR slug = ?
               OR sku = ?
            LIMIT 1
        ");

        $duplicateStmt->bind_param(
            "sss",
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

            if (strcasecmp($duplicate['name'], $name) === 0) {
                $errors[] = "A product with this name already exists.";
            }

            if ($duplicate['slug'] === $slug) {
                $errors[] = "A product with this slug already exists.";
            }

            if ($duplicate['sku'] === $sku) {
                $errors[] = "A product with this SKU already exists.";
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Image Validation
    |--------------------------------------------------------------------------
    */

    $imageName = null;

    if (
        isset($_FILES['main_image']) &&
        $_FILES['main_image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['main_image']['error'] !== UPLOAD_ERR_OK) {

            $errors[] = "There was a problem uploading the product image.";

        } else {

            $maxSize = 5 * 1024 * 1024;

            if ($_FILES['main_image']['size'] > $maxSize) {

                $errors[] = "Product image must not exceed 5MB.";

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

                    $errors[] = "Only JPG, PNG and WEBP images are allowed.";

                } else {

                    $extension = $allowedMimeTypes[$mimeType];

                    $imageName =
                        'product_' .
                        date('Ymd_His') .
                        '_' .
                        bin2hex(random_bytes(4)) .
                        '.' .
                        $extension;
                }
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Save Product
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

        $stmt = $conn->prepare("
            INSERT INTO products (
                category_id,
                name,
                slug,
                sku,
                description,
                specifications,
                price,
                stock_quantity,
                minimum_stock,
                weight,
                main_image,
                is_active,
                is_featured,
                created_at,
                updated_at
            )
            VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
            )
        ");

        $stmt->bind_param(
            "isssssdiidsii",
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
            $imageName,
            $isActive,
            $isFeatured
        );

        $stmt->execute();

        $productId = $stmt->insert_id;

        $stmt->close();


        /*
        |--------------------------------------------------------------------------
        | Move Uploaded Image
        |--------------------------------------------------------------------------
        */

        if ($imageName !== null) {

            $uploadDirectory =
                __DIR__ .
                '/../../uploads/products/';

            if (!is_dir($uploadDirectory)) {
                mkdir($uploadDirectory, 0755, true);
            }

            $destination =
                $uploadDirectory .
                $imageName;

            if (
                !move_uploaded_file(
                    $_FILES['main_image']['tmp_name'],
                    $destination
                )
            ) {

                /*
                |--------------------------------------------------------------------------
                | Image move failed - remove database record
                |--------------------------------------------------------------------------
                */

                $deleteStmt = $conn->prepare("
                    DELETE FROM products
                    WHERE id = ?
                ");

                $deleteStmt->bind_param(
                    "i",
                    $productId
                );

                $deleteStmt->execute();

                $deleteStmt->close();

                $errors[] =
                    "The product was saved, but the image could not be uploaded.";

            } else {

                /*
                |--------------------------------------------------------------------------
                | Save Main Image to product_images
                |--------------------------------------------------------------------------
                */

                $imageStmt = $conn->prepare("
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

                $imageStmt->bind_param(
                    "iss",
                    $productId,
                    $imageName,
                    $name
                );

                $imageStmt->execute();

                $imageStmt->close();
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

    <!-- Page Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>

            <h1 class="fw-bold mb-1">
                Add Product
            </h1>

            <p class="text-muted mb-0">
                Add a new product to the Betachem online store.
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
        action="<?= $baseUrl; ?>/admin/products/add.php"
        enctype="multipart/form-data"
    >

        <div class="row g-4">

            <!-- Main Product Information -->
            <div class="col-lg-8">

                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-header bg-white py-3">

                        <h5 class="mb-0 fw-bold">
                            Product Information
                        </h5>

                    </div>

                    <div class="card-body">

                        <div class="row g-3">

                            <!-- Product Name -->
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
                                    placeholder="e.g. Hydrochloric Acid 32%"
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
                                    placeholder="e.g. HCL-32-001"
                                    required
                                >

                                <small class="text-muted">
                                    Unique product code.
                                </small>

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
                                        placeholder="0.00"
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
                                        placeholder="0.000"
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
                                    placeholder="Describe the product, its uses and important information..."
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
                                    placeholder="Enter product specifications, concentration, grade, packaging information, etc."
                                ><?= e($specifications) ?></textarea>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- Stock -->
                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-header bg-white py-3">

                        <h5 class="mb-0 fw-bold">
                            Inventory
                        </h5>

                    </div>

                    <div class="card-body">

                        <div class="row g-3">

                            <!-- Stock Quantity -->
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

                                <small class="text-muted">
                                    Current quantity available for sale.
                                </small>

                            </div>


                            <!-- Minimum Stock -->
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

                                <small class="text-muted">
                                    Used to identify low-stock products.
                                </small>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- Right Column -->
            <div class="col-lg-4">

                <!-- Image -->
                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-header bg-white py-3">

                        <h5 class="mb-0 fw-bold">
                            Product Image
                        </h5>

                    </div>

                    <div class="card-body">

                        <label class="form-label fw-semibold">
                            Main Image
                        </label>

                        <input
                            type="file"
                            name="main_image"
                            class="form-control"
                            accept=".jpg,.jpeg,.png,.webp"
                        >

                        <small class="text-muted d-block mt-2">
                            JPG, PNG or WEBP.
                            Maximum 5MB.
                        </small>

                    </div>

                </div>


                <!-- Visibility -->
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
                                <?= !isset($_POST['is_active']) || isset($_POST['is_active']) ? 'checked' : '' ?>
                            >

                            <label
                                class="form-check-label fw-semibold"
                                for="is_active"
                            >
                                Active Product
                            </label>

                            <div class="text-muted small">
                                Customers can see and purchase this product.
                            </div>

                        </div>


                        <div class="form-check form-switch">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="is_featured"
                                id="is_featured"
                                <?= isset($_POST['is_featured']) ? 'checked' : '' ?>
                            >

                            <label
                                class="form-check-label fw-semibold"
                                for="is_featured"
                            >
                                Featured Product
                            </label>

                            <div class="text-muted small">
                                Show this product in featured product sections.
                            </div>

                        </div>

                    </div>

                </div>


                <!-- Save -->
                <div class="card border-0 shadow-sm">

                    <div class="card-body">

                        <button
                            type="submit"
                            class="btn btn-primary w-100 btn-lg"
                        >
                            <i class="bi bi-check-circle me-1"></i>
                            Save Product
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