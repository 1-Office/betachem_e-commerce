<?php

/*
|--------------------------------------------------------------------------
| Betachem E-Commerce — Products
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

require_once __DIR__ . '/../../includes/functions.php';

$pageTitle = 'Products';
$baseUrl = '/betachem/betachem_e-commerce';

/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? '');
$categoryId = (int) ($_GET['category_id'] ?? 0);
$status = $_GET['status'] ?? '';

if (!in_array($status, ['active', 'inactive'], true)) {
    $status = '';
}

/*
|--------------------------------------------------------------------------
| Categories
|--------------------------------------------------------------------------
*/

$categories = [];

$categoryResult = $conn->query("
    SELECT id, name
    FROM categories
    ORDER BY name ASC
");

if ($categoryResult) {
    while ($category = $categoryResult->fetch_assoc()) {
        $categories[] = $category;
    }
}

/*
|--------------------------------------------------------------------------
| Product Statistics
|--------------------------------------------------------------------------
*/

$statsResult = $conn->query("
    SELECT
        COUNT(*) AS total_products,
        COALESCE(SUM(is_active = 1), 0) AS active_products,
        COALESCE(SUM(is_active = 0), 0) AS inactive_products,
        COALESCE(SUM(stock_quantity <= 0), 0) AS out_of_stock
    FROM products
");

$stats = $statsResult
    ? $statsResult->fetch_assoc()
    : [
        'total_products' => 0,
        'active_products' => 0,
        'inactive_products' => 0,
        'out_of_stock' => 0
    ];

/*
|--------------------------------------------------------------------------
| Fetch Products
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        p.*,
        c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE 1 = 1
";

$params = [];
$types = '';

if ($search !== '') {
    $sql .= "
        AND (
            p.name LIKE ?
            OR p.sku LIKE ?
            OR p.description LIKE ?
        )
    ";

    $searchTerm = '%' . $search . '%';

    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;

    $types .= 'sss';
}

if ($categoryId > 0) {
    $sql .= " AND p.category_id = ? ";
    $params[] = $categoryId;
    $types .= 'i';
}

if ($status === 'active') {
    $sql .= " AND p.is_active = 1 ";
} elseif ($status === 'inactive') {
    $sql .= " AND p.is_active = 0 ";
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die('Unable to prepare the products query.');
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$result = $stmt->get_result();

$products = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();

/*
|--------------------------------------------------------------------------
| Header
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/header.php';

?>

<style>
/* Betachem Products — page-specific styling */

.betachem-products {
    --bc-blue: #1D88E9;
    --bc-dark-blue: #1745CA;
    --bc-purple: #2D208A;
    --bc-border: #e7edf5;
    color: #243247;
}

.betachem-products .products-hero {
    padding: 28px 30px;
    overflow: hidden;
    border-radius: 18px;
    color: #fff;
    background: linear-gradient(
        115deg,
        var(--bc-purple),
        var(--bc-dark-blue),
        var(--bc-blue)
    );
    box-shadow: 0 8px 24px rgba(23, 69, 202, .15);
}

.betachem-products .products-hero h1 {
    font-size: 1.8rem;
    font-weight: 750;
    letter-spacing: -.5px;
}

.betachem-products .products-hero p {
    color: rgba(255,255,255,.82);
}

.betachem-products .hero-icon {
    width: 62px;
    height: 62px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 17px;
    background: rgba(255,255,255,.15);
    font-size: 30px;
}

.betachem-products .bc-card {
    height: 100%;
    border: 1px solid var(--bc-border);
    border-radius: 15px;
    background: #fff;
    box-shadow: 0 4px 16px rgba(25, 45, 75, .045);
    transition: transform .2s ease, box-shadow .2s ease;
}

.betachem-products .bc-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(25, 45, 75, .075);
}

.betachem-products .stat-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    font-size: 22px;
}

.betachem-products .stat-label {
    color: #718096;
    font-size: .87rem;
    font-weight: 600;
}

.betachem-products .stat-number {
    color: #1f2d40;
    font-size: 1.8rem;
    font-weight: 750;
}

.betachem-products .filter-card,
.betachem-products .table-card {
    overflow: hidden;
    border: 1px solid var(--bc-border);
    border-radius: 15px;
    background: #fff;
    box-shadow: 0 4px 16px rgba(25, 45, 75, .045);
}

.betachem-products .section-heading {
    color: #1f2d40;
    font-weight: 750;
}

.betachem-products .form-label {
    color: #34445b;
    font-size: .88rem;
    font-weight: 650;
}

.betachem-products .form-control,
.betachem-products .form-select {
    min-height: 44px;
    border: 1px solid #dfe6ef;
    border-radius: 9px;
    box-shadow: none;
}

.betachem-products .form-control:focus,
.betachem-products .form-select:focus {
    border-color: var(--bc-blue);
    box-shadow: 0 0 0 3px rgba(29, 136, 233, .12);
}

.betachem-products .btn-primary {
    border-color: var(--bc-dark-blue);
    background: var(--bc-dark-blue);
}

.betachem-products .btn-primary:hover {
    border-color: var(--bc-purple);
    background: var(--bc-purple);
}

.betachem-products .table {
    --bs-table-hover-bg: #f7faff;
}

.betachem-products .table thead th {
    padding-top: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e5ebf3;
    background: #f6f8fc;
    color: #68768a;
    font-size: .76rem;
    font-weight: 750;
    letter-spacing: .4px;
    text-transform: uppercase;
    white-space: nowrap;
}

.betachem-products .table tbody td {
    padding-top: 15px;
    padding-bottom: 15px;
    border-color: #edf1f6;
    font-size: .88rem;
    vertical-align: middle;
}

.betachem-products .product-image {
    width: 58px;
    height: 58px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border: 1px solid #e4eaf2;
    border-radius: 11px;
    background: #f6f8fc;
    color: #9aabc0;
    font-size: 24px;
}

.betachem-products .product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.betachem-products .product-name {
    color: #243247;
    font-weight: 700;
}

.betachem-products .product-sku {
    display: inline-block;
    padding: 4px 7px;
    border-radius: 6px;
    background: #f1f4f9;
    color: #526176;
    font-size: .8rem;
}

.betachem-products .product-status,
.betachem-products .stock-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 9px;
    border-radius: 20px;
    font-size: .74rem;
    font-weight: 700;
    white-space: nowrap;
}

.betachem-products .product-status::before,
.betachem-products .stock-status::before {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
    content: "";
}

.betachem-products .status-active,
.betachem-products .stock-good {
    background: #e5f7ed;
    color: #16804a;
}

.betachem-products .status-inactive {
    background: #edf0f5;
    color: #667085;
}

.betachem-products .stock-low {
    background: #fff3d6;
    color: #956000;
}

.betachem-products .stock-empty {
    background: #fdebec;
    color: #c93643;
}

.betachem-products .featured-label {
    display: inline-block;
    margin-top: 5px;
    padding: 4px 7px;
    border-radius: 6px;
    background: #e9e7ff;
    color: #5141b7;
    font-size: .72rem;
    font-weight: 700;
}

.betachem-products .action-buttons {
    display: inline-flex;
    gap: 5px;
}

.betachem-products .action-buttons .btn {
    width: 34px;
    height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
}

.betachem-products .product-count {
    display: inline-block;
    padding: 6px 10px;
    border-radius: 8px;
    background: #edf4ff;
    color: var(--bc-dark-blue);
    font-weight: 700;
}

.betachem-products .empty-state {
    padding: 55px 20px;
}

.betachem-products .empty-state-icon {
    color: #a8b9d0;
    font-size: 46px;
}

@media (max-width: 767.98px) {
    .betachem-products .products-hero {
        padding: 23px 20px;
    }

    .betachem-products .products-hero h1 {
        font-size: 1.5rem;
    }

    .betachem-products .hero-icon {
        display: none;
    }

    .betachem-products .stat-number {
        font-size: 1.5rem;
    }
}
</style>

<div class="container-fluid py-4 betachem-products">

    <!-- PAGE HEADER -->

    <div class="products-hero mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

            <div>
                <div class="small fw-semibold mb-2">
                    BETACHEM E-COMMERCE / CATALOGUE MANAGEMENT
                </div>

                <h1 class="mb-2">
                    <i class="bi bi-box-seam me-2"></i>Products
                </h1>

                <p class="mb-0">
                    Manage your product catalogue, pricing and stock levels.
                </p>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="hero-icon">
                    <i class="bi bi-boxes"></i>
                </div>

                <a
                    href="<?= $baseUrl ?>/admin/products/add.php"
                    class="btn btn-light fw-semibold"
                >
                    <i class="bi bi-plus-circle me-1"></i>
                    Add Product
                </a>
            </div>

        </div>
    </div>

    <!-- SUCCESS MESSAGE -->

    <?php if (!empty($_SESSION['product_message'])): ?>

        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm"
             role="alert">

            <i class="bi bi-check-circle me-2"></i>
            <?= e($_SESSION['product_message']) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close"
            ></button>

        </div>

        <?php unset($_SESSION['product_message']); ?>

    <?php endif; ?>

    <!-- STATISTICS -->

    <div class="row g-3 mb-4">

        <div class="col-6 col-xl-3">
            <div class="bc-card p-3 p-lg-4">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <div class="stat-label mb-2">Total Products</div>
                        <div class="stat-number">
                            <?= (int) ($stats['total_products'] ?? 0) ?>
                        </div>
                    </div>

                    <div class="stat-icon" style="background:#e7f1ff;color:#1745CA;">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="bc-card p-3 p-lg-4">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <div class="stat-label mb-2">Active</div>
                        <div class="stat-number">
                            <?= (int) ($stats['active_products'] ?? 0) ?>
                        </div>
                    </div>

                    <div class="stat-icon" style="background:#e5f7ed;color:#16804a;">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="bc-card p-3 p-lg-4">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <div class="stat-label mb-2">Inactive</div>
                        <div class="stat-number">
                            <?= (int) ($stats['inactive_products'] ?? 0) ?>
                        </div>
                    </div>

                    <div class="stat-icon" style="background:#edf0f5;color:#667085;">
                        <i class="bi bi-eye-slash"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="bc-card p-3 p-lg-4">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <div class="stat-label mb-2">Out of Stock</div>
                        <div class="stat-number">
                            <?= (int) ($stats['out_of_stock'] ?? 0) ?>
                        </div>
                    </div>

                    <div class="stat-icon" style="background:#fdebec;color:#c93643;">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- SEARCH AND FILTERS -->

    <div class="filter-card mb-4">
        <div class="p-3 p-lg-4">

            <div class="section-heading mb-3">
                <i class="bi bi-funnel me-2 text-primary"></i>
                Search and Filter Products
            </div>

            <form method="GET" action="<?= $baseUrl ?>/admin/products/index.php">

                <div class="row g-3 align-items-end">

                    <div class="col-lg-5">
                        <label for="product-search" class="form-label">
                            Search
                        </label>

                        <input
                            type="text"
                            id="product-search"
                            name="search"
                            class="form-control"
                            placeholder="Product name, SKU or description"
                            value="<?= e($search) ?>"
                        >
                    </div>

                    <div class="col-lg-3">
                        <label for="product-category" class="form-label">
                            Category
                        </label>

                        <select
                            id="product-category"
                            name="category_id"
                            class="form-select"
                        >
                            <option value="0">All Categories</option>

                            <?php foreach ($categories as $category): ?>
                                <option
                                    value="<?= (int) $category['id'] ?>"
                                    <?= $categoryId === (int) $category['id'] ? 'selected' : '' ?>
                                >
                                    <?= e($category['name']) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>
                    </div>

                    <div class="col-lg-2">
                        <label for="product-status" class="form-label">
                            Status
                        </label>

                        <select
                            id="product-status"
                            name="status"
                            class="form-select"
                        >
                            <option value="">All Statuses</option>
                            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>
                                Active
                            </option>
                            <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>
                                Inactive
                            </option>
                        </select>
                    </div>

                    <div class="col-lg-2">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-1"></i> Filter
                            </button>

                            <a
                                href="<?= $baseUrl ?>/admin/products/index.php"
                                class="btn btn-outline-secondary"
                            >
                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                Clear Filters
                            </a>
                        </div>
                    </div>

                </div>

            </form>
        </div>
    </div>

    <!-- PRODUCTS TABLE -->

    <div class="table-card">

        <div class="p-3 p-lg-4 border-bottom">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">

                <div>
                    <h5 class="section-heading mb-1">Product Catalogue</h5>
                    <div class="text-muted small">
                        Manage product details, availability, pricing and inventory.
                    </div>
                </div>

                <span class="product-count">
                    <?= count($products) ?>
                    <?= count($products) === 1 ? 'product' : 'products' ?>
                </span>

            </div>
        </div>

        <?php if (empty($products)): ?>

            <div class="empty-state text-center">

                <div class="empty-state-icon mb-3">
                    <i class="bi bi-box-seam"></i>
                </div>

                <h5 class="fw-bold">No Products Found</h5>

                <p class="text-muted mb-3">
                    No products match your current filters.
                </p>

                <?php if ($search !== '' || $categoryId > 0 || $status !== ''): ?>

                    <a
                        href="<?= $baseUrl ?>/admin/products/index.php"
                        class="btn btn-outline-primary me-2"
                    >
                        Clear Filters
                    </a>

                <?php endif; ?>

                <a
                    href="<?= $baseUrl ?>/admin/products/add.php"
                    class="btn btn-primary"
                >
                    <i class="bi bi-plus-circle me-1"></i>
                    Add Product
                </a>

            </div>

        <?php else: ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead>
                        <tr>
                            <th class="ps-3">Image</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>SKU</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($products as $product): ?>

                            <?php
                            $stock = (int) ($product['stock_quantity'] ?? 0);
                            $minimumStock = (int) ($product['minimum_stock'] ?? 0);

                            if ($stock <= 0) {
                                $stockClass = 'stock-empty';
                                $stockLabel = 'Out of Stock';
                            } elseif ($stock <= $minimumStock) {
                                $stockClass = 'stock-low';
                                $stockLabel = 'Low Stock';
                            } else {
                                $stockClass = 'stock-good';
                                $stockLabel = 'In Stock';
                            }

                            $isActive = (int) ($product['is_active'] ?? 0) === 1;
                            $isFeatured = (int) ($product['is_featured'] ?? 0) === 1;

                            /*
                             * Keep product image URLs relative to the app's
                             * public uploads directory.
                             */
                            $imageName = basename(
                                (string) ($product['main_image'] ?? '')
                            );

                            $imageUrl = $baseUrl . '/uploads/products/' .
                                rawurlencode($imageName);
                            ?>

                            <tr>

                                <!-- IMAGE -->

                                <td class="ps-3">
                                    <div class="product-image">

                                        <?php if ($imageName !== ''): ?>

                                            <img
                                                src="<?= e($imageUrl) ?>"
                                                alt="<?= e($product['name']) ?>"
                                                loading="lazy"
                                                onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                                            >

                                            <span style="display:none;align-items:center;justify-content:center;width:100%;height:100%;">
                                                <i class="bi bi-image"></i>
                                            </span>

                                        <?php else: ?>

                                            <i class="bi bi-image"></i>

                                        <?php endif; ?>

                                    </div>
                                </td>

                                <!-- PRODUCT -->

                                <td>
                                    <div class="product-name">
                                        <?= e($product['name']) ?>
                                    </div>

                                    <?php if ($isFeatured): ?>
                                        <span class="featured-label">
                                            <i class="bi bi-star-fill me-1"></i>
                                            Featured
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- CATEGORY -->

                                <td>
                                    <?= e($product['category_name'] ?? 'Uncategorised') ?>
                                </td>

                                <!-- SKU -->

                                <td>
                                    <span class="product-sku">
                                        <?= e($product['sku'] ?? '—') ?>
                                    </span>
                                </td>

                                <!-- PRICE -->

                                <td>
                                    <span class="fw-bold">
                                        <?= money((float) $product['price']) ?>
                                    </span>
                                </td>

                                <!-- STOCK -->

                                <td>
                                    <div class="fw-bold mb-1">
                                        <?= number_format($stock) ?>
                                    </div>

                                    <span class="stock-status <?= e($stockClass) ?>">
                                        <?= e($stockLabel) ?>
                                    </span>
                                </td>

                                <!-- STATUS -->

                                <td>
                                    <?php if ($isActive): ?>
                                        <span class="product-status status-active">
                                            Active
                                        </span>
                                    <?php else: ?>
                                        <span class="product-status status-inactive">
                                            Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- FEATURED -->

                                <td>
                                    <?php if ($isFeatured): ?>
                                        <i
                                            class="bi bi-star-fill text-warning fs-5"
                                            title="Featured"
                                            aria-label="Featured"
                                        ></i>
                                    <?php else: ?>
                                        <i
                                            class="bi bi-star text-muted fs-5"
                                            title="Not featured"
                                            aria-label="Not featured"
                                        ></i>
                                    <?php endif; ?>
                                </td>

                                <!-- ACTIONS -->

                                <td class="text-end pe-3">
                                    <div class="action-buttons">

                                        <a
                                            href="<?= $baseUrl ?>/admin/products/edit.php?id=<?= (int) $product['id'] ?>"
                                            class="btn btn-sm btn-outline-primary"
                                            title="Edit product"
                                            aria-label="Edit product"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <a
                                            href="<?= $baseUrl ?>/admin/products/images.php?id=<?= (int) $product['id'] ?>"
                                            class="btn btn-sm btn-outline-secondary"
                                            title="Manage product images"
                                            aria-label="Manage product images"
                                        >
                                            <i class="bi bi-images"></i>
                                        </a>

                                        <a
                                            href="<?= $baseUrl ?>/admin/products/delete.php?id=<?= (int) $product['id'] ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Delete product"
                                            aria-label="Delete product"
                                            onclick="return confirm('Are you sure you want to delete this product?');"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </a>

                                    </div>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>
            </div>

        <?php endif; ?>

    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>