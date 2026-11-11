<?php

require_once __DIR__ . '/includes/header.php';

/*
|--------------------------------------------------------------------------
| E-Commerce Base URL
|--------------------------------------------------------------------------
*/

$baseUrl = '/betachem/betachem_e-commerce';


/*
|--------------------------------------------------------------------------
| Get Product
|--------------------------------------------------------------------------
*/

$slug = trim($_GET['slug'] ?? '');

if ($slug === '') {
    header("Location: {$baseUrl}/shop.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT
        p.*,
        c.name AS category_name,
        c.slug AS category_slug
    FROM products p
    INNER JOIN categories c
        ON c.id = p.category_id
    WHERE p.slug = ?
      AND p.is_active = 1
    LIMIT 1
");

$stmt->bind_param("s", $slug);
$stmt->execute();

$result = $stmt->get_result();
$product = $result->fetch_assoc();

$stmt->close();

if (!$product) {

    http_response_code(404);
    ?>

    <div class="container py-5">

        <div class="empty-state text-center">

            <div class="empty-state-icon">
                <i class="bi bi-box-seam"></i>
            </div>

            <h2>Product Not Found</h2>

            <p class="text-muted mb-4">
                The product you are looking for does not exist
                or is no longer available.
            </p>

            <a
                href="<?= $baseUrl ?>/shop.php"
                class="btn btn-primary"
            >
                <i class="bi bi-arrow-left me-2"></i>
                Back to Shop
            </a>

        </div>

    </div>

    <?php

    require_once __DIR__ . '/includes/footer.php';
    exit;
}


/*
|--------------------------------------------------------------------------
| Get Product Images
|--------------------------------------------------------------------------
*/

$imageStmt = $conn->prepare("
    SELECT
        id,
        image_path,
        alt_text,
        sort_order,
        is_primary
    FROM product_images
    WHERE product_id = ?
    ORDER BY is_primary DESC, sort_order ASC, id ASC
");

$imageStmt->bind_param("i", $product['id']);
$imageStmt->execute();

$imageResult = $imageStmt->get_result();

$images = [];

while ($image = $imageResult->fetch_assoc()) {
    $images[] = $image;
}

$imageStmt->close();


/*
|--------------------------------------------------------------------------
| Product Image Helper
|--------------------------------------------------------------------------
*/

function productImageUrl(?string $image): string
{
    global $baseUrl;

    if (empty($image)) {
        return $baseUrl . '/assets/images/product-placeholder.png';
    }

    $image = trim($image);

    /*
    |--------------------------------------------------------------------------
    | Remove old/full paths if stored in database
    |--------------------------------------------------------------------------
    */

    $image = str_replace('\\', '/', $image);

    /*
    |--------------------------------------------------------------------------
    | If database contains a complete upload path,
    | keep only the filename.
    |--------------------------------------------------------------------------
    */

    if (strpos($image, 'uploads/products/') !== false) {

        $image = substr(
            $image,
            strrpos($image, 'uploads/products/') + strlen('uploads/products/')
        );
    }

    $image = ltrim($image, '/');

    /*
    |--------------------------------------------------------------------------
    | Check the actual file
    |--------------------------------------------------------------------------
    */

    $basePath = __DIR__ . '/uploads/products/';

    $possibleFiles = [];

    /*
    |--------------------------------------------------------------------------
    | Exact filename
    |--------------------------------------------------------------------------
    */

    $possibleFiles[] = $image;

    /*
    |--------------------------------------------------------------------------
    | If extension is missing, check common extensions
    |--------------------------------------------------------------------------
    */

    if (!preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $image)) {

        $possibleFiles[] = $image . '.jpg';
        $possibleFiles[] = $image . '.jpeg';
        $possibleFiles[] = $image . '.png';
        $possibleFiles[] = $image . '.webp';
        $possibleFiles[] = $image . '.gif';
    }

    foreach ($possibleFiles as $possibleFile) {

        $fullPath = $basePath . $possibleFile;

        if (file_exists($fullPath)) {

            return $baseUrl
                . '/uploads/products/'
                . rawurlencode($possibleFile);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Image does not exist
    |--------------------------------------------------------------------------
    */

    return $baseUrl . '/assets/images/product-placeholder.png';
}


/*
|--------------------------------------------------------------------------
| Determine Main Image
|--------------------------------------------------------------------------
*/

$mainImage = '';

if (!empty($product['main_image'])) {

    $mainImage = productImageUrl($product['main_image']);

} elseif (!empty($images)) {

    $mainImage = productImageUrl($images[0]['image_path']);

}


/*
|--------------------------------------------------------------------------
| Prepare Gallery Images
|--------------------------------------------------------------------------
*/

$galleryImages = [];

foreach ($images as $image) {

    $galleryImages[] = [
        'url' => productImageUrl($image['image_path']),
        'alt' => $image['alt_text'] ?: $product['name']
    ];
}


/*
|--------------------------------------------------------------------------
| Stock Status
|--------------------------------------------------------------------------
*/

$stockQuantity = (int) $product['stock_quantity'];
$minimumStock = (int) $product['minimum_stock'];

if ($stockQuantity <= 0) {

    $stockClass = 'danger';
    $stockText = 'Out of Stock';
    $canPurchase = false;

} elseif ($stockQuantity <= $minimumStock) {

    $stockClass = 'warning';
    $stockText = 'Low Stock';
    $canPurchase = true;

} else {

    $stockClass = 'success';
    $stockText = 'In Stock';
    $canPurchase = true;
}


/*
|--------------------------------------------------------------------------
| Product Description
|--------------------------------------------------------------------------
*/

$description = trim($product['description'] ?? '');
$specifications = trim($product['specifications'] ?? '');

?>

<div class="container py-5">

    <!-- Breadcrumb -->

    <div class="mb-4">

        <a
            href="<?= $baseUrl ?>/shop.php"
            class="text-decoration-none"
        >
            <i class="bi bi-arrow-left me-1"></i>
            Back to Shop
        </a>

        <span class="text-muted mx-2">/</span>

        <a
            href="<?= $baseUrl ?>/category.php?slug=<?= urlencode($product['category_slug']) ?>"
            class="text-decoration-none"
        >
            <?= e($product['category_name']) ?>
        </a>

        <span class="text-muted mx-2">/</span>

        <span class="text-muted">
            <?= e($product['name']) ?>
        </span>

    </div>


    <div class="row g-5">

        <!-- =====================================================
             PRODUCT IMAGES
        ====================================================== -->

        <div class="col-lg-6">

            <div class="product-detail-image-wrapper">

                <?php if ($mainImage): ?>

                    <img
                        src="<?= e($mainImage) ?>"
                        alt="<?= e($product['name']) ?>"
                        id="mainProductImage"
                        class="product-detail-main-image"
                    >

                <?php else: ?>

                    <div class="product-detail-no-image">

                        <i class="bi bi-image"></i>

                        <span>
                            No Image Available
                        </span>

                    </div>

                <?php endif; ?>

            </div>


            <!-- Image Gallery -->

            <?php if (count($galleryImages) > 1): ?>

                <div class="product-thumbnail-gallery mt-3">

                    <?php foreach ($galleryImages as $index => $image): ?>

                        <button
                            type="button"
                            class="product-thumbnail <?= $index === 0 ? 'active' : '' ?>"
                            onclick="changeProductImage(
                                '<?= e($image['url']) ?>',
                                this
                            )"
                        >

                            <img
                                src="<?= e($image['url']) ?>"
                                alt="<?= e($image['alt']) ?>"
                            >

                        </button>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </div>


        <!-- =====================================================
             PRODUCT INFORMATION
        ====================================================== -->

        <div class="col-lg-6">

            <!-- Category -->

            <div class="mb-2">

                <a
                    href="<?= $baseUrl ?>/category.php?slug=<?= urlencode($product['category_slug']) ?>"
                    class="text-decoration-none"
                >
                    <?= e($product['category_name']) ?>
                </a>

            </div>


            <!-- Product Name -->

            <h1 class="display-6 fw-bold mb-3">
                <?= e($product['name']) ?>
            </h1>


            <!-- SKU -->

            <div class="text-muted mb-3">

                <strong>SKU:</strong>

                <?= e($product['sku']) ?>

            </div>


            <!-- Price -->

            <div class="product-detail-price mb-4">

                <?= money($product['price']) ?>

            </div>


            <!-- Stock -->

            <div class="mb-4">

                <span class="badge bg-<?= $stockClass ?> fs-6 px-3 py-2">

                    <?php if ($stockQuantity > 0): ?>

                        <i class="bi bi-check-circle me-1"></i>

                    <?php else: ?>

                        <i class="bi bi-x-circle me-1"></i>

                    <?php endif; ?>

                    <?= e($stockText) ?>

                </span>

                <?php if ($stockQuantity > 0): ?>

                    <span class="text-muted ms-2">
                        <?= $stockQuantity ?> available
                    </span>

                <?php endif; ?>

            </div>


            <!-- Description -->

            <?php if ($description !== ''): ?>

                <div class="mb-4">

                    <h5 class="fw-bold">
                        Description
                    </h5>

                    <div class="text-muted product-description">

                        <?= nl2br(e($description)) ?>

                    </div>

                </div>

            <?php endif; ?>


            <!-- Add To Cart -->

            <?php if ($canPurchase): ?>

                <div class="product-purchase-box">

                    <form
                        action="<?= $baseUrl ?>/cart.php"
                        method="POST"
                        class="row g-3 align-items-end"
                    >

                        <input
                            type="hidden"
                            name="action"
                            value="add"
                        >

                        <input
                            type="hidden"
                            name="product_id"
                            value="<?= (int) $product['id'] ?>"
                        >


                        <div class="col-sm-4">

                            <label
                                for="quantity"
                                class="form-label fw-semibold"
                            >
                                Quantity
                            </label>

                            <input
                                type="number"
                                id="quantity"
                                name="quantity"
                                class="form-control form-control-lg"
                                value="1"
                                min="1"
                                max="<?= $stockQuantity ?>"
                                required
                            >

                        </div>


                        <div class="col-sm-8">

                            <button
                                type="submit"
                                class="btn btn-primary btn-lg w-100"
                            >

                                <i class="bi bi-cart-plus me-2"></i>

                                Add to Cart

                            </button>

                        </div>

                    </form>

                </div>

            <?php else: ?>

                <div class="alert alert-danger">

                    <i class="bi bi-exclamation-circle me-2"></i>

                    This product is currently out of stock.

                </div>

            <?php endif; ?>


            <!-- Product Details -->

            <div class="product-meta-box mt-4">

                <div class="row g-3">

                    <div class="col-sm-6">

                        <div class="small text-muted">
                            Category
                        </div>

                        <div class="fw-semibold">
                            <?= e($product['category_name']) ?>
                        </div>

                    </div>


                    <div class="col-sm-6">

                        <div class="small text-muted">
                            SKU
                        </div>

                        <div class="fw-semibold">
                            <?= e($product['sku']) ?>
                        </div>

                    </div>


                    <?php if (!empty($product['weight'])): ?>

                        <div class="col-sm-6">

                            <div class="small text-muted">
                                Weight
                            </div>

                            <div class="fw-semibold">

                                <?= e($product['weight']) ?> kg

                            </div>

                        </div>

                    <?php endif; ?>


                    <div class="col-sm-6">

                        <div class="small text-muted">
                            Availability
                        </div>

                        <div class="fw-semibold">
                            <?= e($stockText) ?>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- =====================================================
         SPECIFICATIONS
    ====================================================== -->

    <?php if ($specifications !== ''): ?>

        <div class="row mt-5">

            <div class="col-12">

                <div class="product-information-section">

                    <h3 class="fw-bold mb-4">

                        <i class="bi bi-list-check me-2"></i>

                        Specifications

                    </h3>

                    <div class="text-muted">

                        <?= nl2br(e($specifications)) ?>

                    </div>

                </div>

            </div>

        </div>

    <?php endif; ?>


    <!-- =====================================================
         BETACHEM QUALITY
    ====================================================== -->

    <div class="row mt-5">

        <div class="col-12">

            <div class="product-information-section">

                <h3 class="fw-bold mb-4">

                    <i class="bi bi-shield-check me-2"></i>

                    Betachem Quality

                </h3>

                <div class="row g-4">

                    <div class="col-md-4">

                        <div class="d-flex">

                            <div class="feature-icon me-3">
                                <i class="bi bi-check-lg"></i>
                            </div>

                            <div>

                                <h6 class="fw-bold">
                                    Quality Products
                                </h6>

                                <p class="text-muted small mb-0">
                                    Carefully selected products
                                    for reliable laboratory and
                                    industrial applications.
                                </p>

                            </div>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <div class="d-flex">

                            <div class="feature-icon me-3">
                                <i class="bi bi-truck"></i>
                            </div>

                            <div>

                                <h6 class="fw-bold">
                                    Reliable Service
                                </h6>

                                <p class="text-muted small mb-0">
                                    Professional service and
                                    dependable order processing.
                                </p>

                            </div>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <div class="d-flex">

                            <div class="feature-icon me-3">
                                <i class="bi bi-headset"></i>
                            </div>

                            <div>

                                <h6 class="fw-bold">
                                    Customer Support
                                </h6>

                                <p class="text-muted small mb-0">
                                    We're here to help you select
                                    the right products for your needs.
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- Back to Shop -->

    <div class="text-center mt-5">

        <a
            href="<?= $baseUrl ?>/shop.php"
            class="btn btn-outline-primary"
        >

            <i class="bi bi-arrow-left me-2"></i>

            Continue Shopping

        </a>

    </div>

</div>


<style>

/*
|--------------------------------------------------------------------------
| Product Details Styling
|--------------------------------------------------------------------------
*/

.product-detail-image-wrapper {
    background: #ffffff;
    border: 1px solid #e8edf5;
    border-radius: 18px;
    min-height: 480px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 30px;
    overflow: hidden;
}

.product-detail-main-image {
    width: 100%;
    max-height: 480px;
    object-fit: contain;
    border-radius: 12px;
}

.product-detail-no-image {
    min-height: 420px;
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #adb5bd;
}

.product-detail-no-image i {
    font-size: 80px;
    margin-bottom: 15px;
}

.product-thumbnail-gallery {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.product-thumbnail {
    width: 85px;
    height: 85px;
    padding: 5px;
    border: 2px solid #e8edf5;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    cursor: pointer;
    transition: 0.2s ease;
}

.product-thumbnail:hover,
.product-thumbnail.active {
    border-color: #1D88E9;
}

.product-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 6px;
}

.product-detail-price {
    font-size: 2rem;
    font-weight: 800;
    color: #1D88E9;
}

.product-description {
    line-height: 1.8;
    font-size: 1rem;
}

.product-purchase-box {
    background: #f7faff;
    border: 1px solid #dcecff;
    border-radius: 15px;
    padding: 20px;
}

.product-meta-box {
    background: #f8f9fa;
    border-radius: 15px;
    padding: 20px;
}

.product-information-section {
    background: #ffffff;
    border: 1px solid #e8edf5;
    border-radius: 18px;
    padding: 30px;
}

.feature-icon {
    width: 42px;
    height: 42px;
    min-width: 42px;
    border-radius: 50%;
    background: #eaf4ff;
    color: #1D88E9;
    display: flex;
    align-items: center;
    justify-content: center;
}

@media (max-width: 767px) {

    .product-detail-image-wrapper {
        min-height: 320px;
        padding: 20px;
    }

    .product-detail-main-image {
        max-height: 320px;
    }

    .product-detail-price {
        font-size: 1.7rem;
    }

}

</style>


<script>

function changeProductImage(imagePath, button) {

    const mainImage = document.getElementById('mainProductImage');

    if (!mainImage) {
        return;
    }

    mainImage.src = imagePath;

    document
        .querySelectorAll('.product-thumbnail')
        .forEach(function(item) {

            item.classList.remove('active');

        });

    button.classList.add('active');
}

</script>


<?php

require_once __DIR__ . '/includes/footer.php';

?>