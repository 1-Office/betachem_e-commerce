<?php
/*
|--------------------------------------------------------------------------
| BETACHEM E-COMMERCE — PREMIUM SHOP
|--------------------------------------------------------------------------
*/

// Start the session before ANY HTML, whitespace, or included output.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Shop';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/cart_functions.php';

$baseUrl = '/betachem/betachem_e-commerce';

/*
|--------------------------------------------------------------------------
| FILTERS
|--------------------------------------------------------------------------
*/

$categorySlug = trim($_GET['category'] ?? '');
$search       = trim($_GET['search'] ?? '');
$priceRange   = trim($_GET['price_range'] ?? '');
$inStock      = isset($_GET['in_stock']) ? 1 : 0;
$featured     = isset($_GET['featured']) ? 1 : 0;
$sort         = trim($_GET['sort'] ?? 'newest');
$page         = max(1, (int) ($_GET['page'] ?? 1));

$perPage = 4;

$allowedSorts = [
    'newest',
    'popular',
    'price_low',
    'price_high',
    'name_asc',
    'name_desc'
];

if (!in_array($sort, $allowedSorts, true)) {
    $sort = 'newest';
}

$priceRanges = [
    '' => [
        'label' => 'All Prices',
        'min' => null,
        'max' => null
    ],
    'under_200' => [
        'label' => 'Under R200',
        'min' => null,
        'max' => 200
    ],
    '200_299' => [
        'label' => 'R200 – R299',
        'min' => 200,
        'max' => 300
    ],
    '300_499' => [
        'label' => 'R300 – R499',
        'min' => 300,
        'max' => 500
    ],
    '500_999' => [
        'label' => 'R500 – R999',
        'min' => 500,
        'max' => 1000
    ],
    '1000_2499' => [
        'label' => 'R1,000 – R2,499',
        'min' => 1000,
        'max' => 2500
    ],
    '2500_4999' => [
        'label' => 'R2,500 – R4,999',
        'min' => 2500,
        'max' => 5000
    ],
    '5000_plus' => [
        'label' => 'R5,000+',
        'min' => 5000,
        'max' => null
    ]
];

if (!array_key_exists($priceRange, $priceRanges)) {
    $priceRange = '';
}

/*
|--------------------------------------------------------------------------
| URL HELPERS
|--------------------------------------------------------------------------
*/

function shopFilterUrl(
    string $category = '',
    string $search = '',
    string $priceRange = '',
    int $inStock = 0,
    int $featured = 0,
    string $sort = 'newest'
): string {
    global $baseUrl;

    $query = [];

    if ($category !== '') {
        $query['category'] = $category;
    }

    if ($search !== '') {
        $query['search'] = $search;
    }

    if ($priceRange !== '') {
        $query['price_range'] = $priceRange;
    }

    if ($inStock) {
        $query['in_stock'] = 1;
    }

    if ($featured) {
        $query['featured'] = 1;
    }

    if ($sort !== 'newest') {
        $query['sort'] = $sort;
    }

    return $baseUrl . '/shop.php' .
        ($query ? '?' . http_build_query($query) : '');
}

function shopPaginationUrl(int $pageNumber): string
{
    $query = $_GET;
    $query['page'] = $pageNumber;

    return '?' . http_build_query($query);
}

/*
|--------------------------------------------------------------------------
| PRODUCT IMAGE
|--------------------------------------------------------------------------
*/

function shopProductImage(?string $image): string
{
    global $baseUrl;

    if (!empty($image)) {
        // Prevent directory traversal through image filenames.
        $safeImage = str_replace('\\', '/', $image);
        $parts = explode('/', $safeImage);

        foreach ($parts as $part) {
            if ($part === '..' || $part === '.') {
                return $baseUrl . '/assets/images/product-placeholder.png';
            }
        }

        $imagePath = __DIR__ . '/uploads/products/' . $safeImage;

        if (is_file($imagePath)) {
            return $baseUrl . '/uploads/products/' .
                implode('/', array_map('rawurlencode', $parts));
        }
    }

    return $baseUrl . '/assets/images/product-placeholder.png';
}

/*
|--------------------------------------------------------------------------
| CATEGORIES
|--------------------------------------------------------------------------
*/

$categories = [];

$categoryResult = $conn->query("
    SELECT
        c.id,
        c.name,
        c.slug,
        c.description,
        c.image,
        COUNT(p.id) AS product_count
    FROM categories c
    LEFT JOIN products p
        ON p.category_id = c.id
        AND p.is_active = 1
    WHERE c.is_active = 1
    GROUP BY
        c.id,
        c.name,
        c.slug,
        c.description,
        c.image,
        c.sort_order
    ORDER BY c.sort_order ASC, c.name ASC
");

if ($categoryResult) {
    while ($row = $categoryResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

/*
|--------------------------------------------------------------------------
| SELECTED CATEGORY
|--------------------------------------------------------------------------
*/

$selectedCategory = null;

if ($categorySlug !== '') {
    $categoryStmt = $conn->prepare("
        SELECT id, name, slug, description
        FROM categories
        WHERE slug = ?
          AND is_active = 1
        LIMIT 1
    ");

    if ($categoryStmt) {
        $categoryStmt->bind_param('s', $categorySlug);
        $categoryStmt->execute();

        $selectedCategory = $categoryStmt
            ->get_result()
            ->fetch_assoc();

        $categoryStmt->close();
    }
}

/*
|--------------------------------------------------------------------------
| PRODUCT FILTERS
|--------------------------------------------------------------------------
*/

$where = ['p.is_active = 1'];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = "(
        p.name LIKE ?
        OR p.sku LIKE ?
        OR p.description LIKE ?
    )";

    $searchTerm = '%' . $search . '%';

    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;

    $types .= 'sss';
}

if ($selectedCategory) {
    $where[] = 'p.category_id = ?';
    $params[] = (int) $selectedCategory['id'];
    $types .= 'i';
}

$currentPrice = $priceRanges[$priceRange];

if ($currentPrice['min'] !== null) {
    $where[] = 'p.price >= ?';
    $params[] = (float) $currentPrice['min'];
    $types .= 'd';
}

if ($currentPrice['max'] !== null) {
    $where[] = 'p.price < ?';
    $params[] = (float) $currentPrice['max'];
    $types .= 'd';
}

if ($inStock) {
    $where[] = 'p.stock_quantity > 0';
}

if ($featured) {
    $where[] = 'p.is_featured = 1';
}

$whereSql = implode(' AND ', $where);

/*
|--------------------------------------------------------------------------
| SORTING
|--------------------------------------------------------------------------
*/

switch ($sort) {
    case 'price_low':
        $orderSql = 'p.price ASC, p.name ASC';
        break;

    case 'price_high':
        $orderSql = 'p.price DESC, p.name ASC';
        break;

    case 'name_asc':
        $orderSql = 'p.name ASC';
        break;

    case 'name_desc':
        $orderSql = 'p.name DESC';
        break;

    case 'popular':
        $orderSql = 'p.is_featured DESC, p.created_at DESC';
        break;

    default:
        $orderSql = 'p.created_at DESC';
        break;
}

/*
|--------------------------------------------------------------------------
| TOTAL COUNT
|--------------------------------------------------------------------------
*/

$totalProducts = 0;

$countStmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM products p
    WHERE {$whereSql}
");

if ($countStmt) {
    if ($params) {
        $countStmt->bind_param($types, ...$params);
    }

    $countStmt->execute();

    $countRow = $countStmt
        ->get_result()
        ->fetch_assoc();

    $totalProducts = (int) ($countRow['total'] ?? 0);

    $countStmt->close();
}

$totalPages = max(1, (int) ceil($totalProducts / $perPage));

if ($page > $totalPages) {
    $page = $totalPages;
}

$offset = ($page - 1) * $perPage;

/*
|--------------------------------------------------------------------------
| PRODUCTS
|--------------------------------------------------------------------------
*/

$products = [];

$productSql = "
    SELECT
        p.id,
        p.category_id,
        p.name,
        p.slug,
        p.sku,
        p.description,
        p.specifications,
        p.price,
        p.stock_quantity,
        p.minimum_stock,
        p.main_image,
        p.is_featured,
        c.name AS category_name,
        c.slug AS category_slug
    FROM products p
    LEFT JOIN categories c
        ON c.id = p.category_id
    WHERE {$whereSql}
    ORDER BY {$orderSql}
    LIMIT ? OFFSET ?
";

$productStmt = $conn->prepare($productSql);

if ($productStmt) {
    $productParams = $params;
    $productTypes = $types . 'ii';

    $productParams[] = $perPage;
    $productParams[] = $offset;

    $productStmt->bind_param(
        $productTypes,
        ...$productParams
    );

    $productStmt->execute();

    $productResult = $productStmt->get_result();

    while ($row = $productResult->fetch_assoc()) {
        $products[] = $row;
    }

    $productStmt->close();
}

/*
|--------------------------------------------------------------------------
| FEATURED PRODUCTS
|--------------------------------------------------------------------------
*/

$featuredProducts = [];

$featuredResult = $conn->query("
    SELECT
        p.id,
        p.name,
        p.slug,
        p.sku,
        p.price,
        p.stock_quantity,
        p.main_image,
        c.name AS category_name
    FROM products p
    LEFT JOIN categories c
        ON c.id = p.category_id
    WHERE p.is_active = 1
      AND p.is_featured = 1
    ORDER BY p.created_at DESC
    LIMIT 4
");

if ($featuredResult) {
    while ($row = $featuredResult->fetch_assoc()) {
        $featuredProducts[] = $row;
    }
}

/*
|--------------------------------------------------------------------------
| ACTIVE FILTER COUNT
|--------------------------------------------------------------------------
*/

$activeFilters =
    (int) ($categorySlug !== '') +
    (int) ($search !== '') +
    (int) ($priceRange !== '') +
    $inStock +
    $featured;
?>

<style>
.shop-page {
    --shop-blue: #1745ca;
    --shop-blue-dark: #10369e;
    --shop-navy: #13264a;
    --shop-text: #29374d;
    --shop-muted: #78849a;
    --shop-border: #e5eaf2;
    --shop-bg: #f4f6fa;

    width: 100%;
    min-height: 100vh;
    padding-bottom: 48px;
    background: var(--shop-bg);
    color: var(--shop-text);
}

.shop-page *,
.shop-page *::before,
.shop-page *::after {
    box-sizing: border-box;
}

.shop-container {
    width: 100%;
    max-width: none !important;
    margin: 0;
    padding-right: clamp(14px, 2.5vw, 40px);
    padding-left: clamp(14px, 2.5vw, 40px);
}

.shop-page a,
.shop-page button {
    transition: color .2s ease, background .2s ease,
        border-color .2s ease, transform .2s ease,
        box-shadow .2s ease;
}

/* HERO */

.shop-hero {
    position: relative;
    isolation: isolate;
    display: flex;
    align-items: center;
    min-height: 290px;
    overflow: hidden;
    padding: clamp(28px, 4vw, 55px);
    border-radius: 17px;
    background: linear-gradient(
        112deg,
        #102454 0%,
        #1745ca 60%,
        #2679e8 100%
    );
    color: #fff;
    box-shadow: 0 12px 30px rgba(23,69,202,.15);
}

.shop-hero-content {
    position: relative;
    z-index: 2;
    max-width: 680px;
}

.shop-hero-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    margin-bottom: 16px;
    color: #dce8ff;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1.8px;
}

.shop-hero h1 {
    margin: 0 0 14px;
    color: #fff;
    font-size: clamp(30px, 3.4vw, 49px);
    font-weight: 850;
    letter-spacing: -1.5px;
    line-height: 1.12;
}

.shop-hero p {
    max-width: 570px;
    margin: 0 0 25px;
    color: #e0e9ff;
    font-size: 15px;
    line-height: 1.8;
}

.shop-hero-button {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    padding: 14px 20px;
    border-radius: 7px;
    background: white;
    color: var(--shop-blue);
    font-size: 13px;
    font-weight: 800;
    text-decoration: none;
}

.shop-hero-button:hover {
    transform: translateY(-2px);
    color: var(--shop-blue-dark);
    box-shadow: 0 8px 22px rgba(0,0,0,.16);
}

.shop-hero-decoration {
    position: absolute;
    inset: 0 0 0 auto;
    width: 38%;
    pointer-events: none;
}

.shop-hero-circle {
    position: absolute;
    border: 1px solid rgba(255,255,255,.16);
    border-radius: 50%;
}

.shop-hero .circle-one {
    width: 370px;
    height: 370px;
    top: -55px;
    right: -45px;
}

.shop-hero .circle-two {
    width: 265px;
    height: 265px;
    top: 0;
    right: 8px;
}

.shop-hero-icon {
    position: absolute;
    top: 50%;
    right: 105px;
    display: grid;
    place-items: center;
    width: 130px;
    height: 130px;
    border: 1px solid rgba(255,255,255,.28);
    border-radius: 28px;
    background: rgba(255,255,255,.12);
    color: white;
    font-size: 65px;
    transform: translateY(-50%) rotate(8deg);
}

.shop-hero-floating {
    position: absolute;
    right: 24px;
    bottom: 28px;
    padding: 12px 15px;
    border: 1px solid rgba(255,255,255,.2);
    border-radius: 8px;
    background: rgba(14,35,86,.8);
    color: white;
    font-size: 12px;
    font-weight: 700;
}

.shop-hero-floating i {
    margin-right: 6px;
    color: #8df0c0;
}

/* SEARCH */

.shop-search {
    padding: 20px;
    margin-bottom: 23px;
    border: 1px solid var(--shop-border);
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 4px 14px rgba(25,45,80,.035);
}

.shop-search .form-control {
    height: 49px;
    border-color: #dce3ed;
    font-size: 14px;
}

.shop-search .form-control:focus {
    border-color: var(--shop-blue);
    box-shadow: 0 0 0 3px rgba(23,69,202,.09);
}

.shop-search .btn {
    min-height: 49px;
    padding-right: 22px;
    padding-left: 22px;
    background: var(--shop-blue);
    border-color: var(--shop-blue);
    font-weight: 700;
}

/* CATEGORIES */

.category-strip {
    margin-bottom: 24px;
    overflow: hidden;
    border: 1px solid var(--shop-border);
    border-radius: 12px;
    background: white;
}

.category-strip-title {
    padding: 17px 20px;
    border-bottom: 1px solid var(--shop-border);
    color: var(--shop-navy);
    font-size: 15px;
    font-weight: 800;
}

.category-strip-title i {
    color: var(--shop-blue);
}

.category-list {
    display: flex;
    flex-wrap: wrap;
    padding: 8px;
}

.category-link {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin: 3px;
    padding: 10px 13px;
    border: 1px solid transparent;
    border-radius: 7px;
    color: #4c5a70;
    font-size: 12px;
    font-weight: 650;
    text-decoration: none;
}

.category-link:hover {
    border-color: #dce6ff;
    background: #f3f7ff;
    color: var(--shop-blue);
}

.category-link.active {
    background: var(--shop-blue);
    color: white;
}

/* FEATURED PRODUCTS */

.featured-section {
    padding: 24px;
    margin-bottom: 25px;
    border: 1px solid var(--shop-border);
    border-radius: 13px;
    background: white;
}

.featured-title {
    color: var(--shop-navy);
    font-size: 21px;
    font-weight: 850;
    letter-spacing: -.4px;
}

.featured-product {
    height: 100%;
    overflow: hidden;
    border: 1px solid var(--shop-border);
    border-radius: 11px;
    background: white;
}

.featured-product:hover {
    transform: translateY(-4px);
    border-color: #cbd9fa;
    box-shadow: 0 12px 26px rgba(25,49,92,.09);
}

.featured-image {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 175px;
    padding: 20px;
    background: linear-gradient(180deg, #fff, #f7f9fe);
    border-bottom: 1px solid #eef1f6;
}

.featured-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.featured-body {
    padding: 15px;
}

.featured-category,
.product-category {
    margin-bottom: 7px;
    color: var(--shop-muted);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .7px;
    text-transform: uppercase;
}

.featured-name {
    min-height: 40px;
    margin: 6px 0 12px;
    color: var(--shop-navy);
    font-size: 13px;
    font-weight: 750;
    line-height: 1.5;
}

.featured-name a {
    color: inherit;
    text-decoration: none;
}

.featured-name a:hover {
    color: var(--shop-blue);
}

.featured-price {
    color: var(--shop-blue);
    font-size: 19px;
    font-weight: 850;
}

/* FILTERS */

.shop-main-row {
    --bs-gutter-x: 1.35rem;
}

.filter-card {
    position: sticky;
    top: 90px;
    overflow: hidden;
    border: 1px solid var(--shop-border);
    border-radius: 12px;
    background: white;
    box-shadow: 0 4px 14px rgba(25,45,80,.035);
}

.filter-header {
    padding: 18px;
    border-bottom: 1px solid var(--shop-border);
}

.filter-header strong {
    color: var(--shop-navy);
    font-size: 15px;
}

.filter-section {
    padding: 18px;
    border-bottom: 1px solid #edf0f5;
}

.filter-section:last-child {
    border-bottom: 0;
}

.filter-title {
    margin-bottom: 13px;
    color: var(--shop-navy);
    font-size: 12px;
    font-weight: 850;
}

.filter-category-list {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.filter-category-item {
    margin: 0;
}

.filter-category-item a {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 9px 10px;
    border-radius: 6px;
    color: #59667b;
    font-size: 12px;
    text-decoration: none;
}

.filter-category-item a:hover,
.filter-category-item.active a {
    background: #eff4ff;
    color: var(--shop-blue);
}

.filter-category-item.active a {
    font-weight: 800;
}

.filter-count {
    color: #8792a5;
    font-size: 11px;
}

.price-range-select,
.filter-section .form-select {
    min-height: 43px;
    border-color: #dfe5ee;
    border-radius: 7px;
    font-size: 12px;
}

.price-range-select:focus,
.filter-section .form-select:focus {
    border-color: var(--shop-blue);
    box-shadow: 0 0 0 3px rgba(23,69,202,.08);
}

.filter-check-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 6px 0;
    color: #59667b;
    font-size: 12px;
    text-decoration: none;
}

.filter-check-link:hover,
.filter-check-link.active {
    color: var(--shop-blue);
}

.filter-check-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    border: 1px solid #cbd4e1;
    border-radius: 5px;
    background: white;
    font-size: 10px;
}

.filter-check-link.active .filter-check-icon {
    border-color: var(--shop-blue);
    background: var(--shop-blue);
    color: white;
}

/* RESULTS TOOLBAR */

.results-header {
    padding: 16px 18px;
    margin-bottom: 18px !important;
    border: 1px solid var(--shop-border);
    border-radius: 11px;
    background: white;
    box-shadow: 0 3px 12px rgba(24,42,75,.025);
}

.results-count {
    color: var(--shop-muted);
    font-size: 12px;
    line-height: 1.8;
}

.results-count strong {
    color: var(--shop-navy);
}

.results-header .form-select {
    min-height: 38px;
    border-color: #dce3ed;
    border-radius: 6px;
    color: var(--shop-text);
    font-size: 12px;
}

/* PRODUCT CARDS */

.product-card {
    position: relative;
    display: flex;
    flex-direction: column;
    height: 100%;
    overflow: hidden;
    border: 1px solid var(--shop-border);
    border-radius: 12px;
    background: white;
    transition: transform .23s, box-shadow .23s, border-color .23s;
}

.product-card:hover {
    transform: translateY(-5px);
    border-color: #ccd9fa;
    box-shadow: 0 15px 34px rgba(21,43,86,.10);
}

.product-image-wrap {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    height: clamp(190px, 17vw, 270px);
    padding: 24px;
    overflow: hidden;
    background: linear-gradient(180deg, #fff, #f8faff);
    border-bottom: 1px solid #edf0f5;
}

.product-image-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    transition: transform .35s;
}

.product-card:hover .product-image {
    transform: scale(1.055);
}

.product-featured {
    position: absolute;
    z-index: 2;
    top: 12px;
    left: 12px;
    padding: 6px 9px;
    border-radius: 5px;
    background: #fff0c2;
    color: #795400;
    font-size: 9px;
    font-weight: 850;
    letter-spacing: .3px;
    text-transform: uppercase;
}

.product-stock {
    position: absolute;
    z-index: 2;
    top: 12px;
    right: 12px;
    padding: 6px 8px;
    border-radius: 5px;
    font-size: 9px;
    font-weight: 850;
}

.stock-available {
    background: #e7f7ee;
    color: #14794f;
}

.stock-low {
    background: #fff2d4;
    color: #8a5b00;
}

.stock-out {
    background: #ffebed;
    color: #b42332;
}

.product-body {
    display: flex;
    flex: 1;
    flex-direction: column;
    padding: 17px;
}

.product-category {
    margin-bottom: 8px;
}

.product-name {
    min-height: 43px;
    margin: 0 0 7px;
    color: var(--shop-navy);
    font-size: 14px;
    font-weight: 800;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

.product-name a {
    color: inherit;
    text-decoration: none;
}

.product-name a:hover {
    color: var(--shop-blue);
}

.product-sku {
    margin-bottom: 10px;
    color: #929bad;
    font-size: 10px;
}

.product-description {
    min-height: 38px;
    margin-bottom: 12px;
    color: #788399;
    font-size: 11px;
    line-height: 1.65;
}

.product-price {
    margin-top: auto;
    margin-bottom: 15px;
    color: var(--shop-navy);
    font-size: clamp(18px, 1.45vw, 23px);
    font-weight: 850;
    letter-spacing: -.5px;
}

/* ACTION BUTTONS */

.product-actions {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    align-items: stretch;
    gap: 9px;
    margin-top: auto;
}

.product-actions > a,
.product-actions > form,
.product-actions > button {
    width: 100%;
    min-width: 0;
}

.product-actions .btn,
.add-to-cart-form button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 0;
    min-height: 42px;
    padding: 8px 6px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 800;
    white-space: nowrap;
}

.product-actions .btn-primary,
.add-to-cart-form button {
    border-color: var(--shop-blue);
    background: var(--shop-blue);
    color: #fff;
}

.product-actions .btn-primary:hover,
.add-to-cart-form button:hover {
    border-color: var(--shop-blue-dark);
    background: var(--shop-blue-dark);
    color: #fff;
}

.product-actions .btn-outline-primary {
    border-color: #d7e1fa;
    background: #f6f8ff;
    color: var(--shop-blue);
}

.product-actions .btn-outline-primary:hover {
    border-color: var(--shop-blue);
    background: var(--shop-blue);
    color: white;
}

.add-to-cart-form {
    display: flex;
    width: 100%;
    min-width: 0;
    margin: 0;
}

.add-to-cart-form button {
    width: 100%;
}

.product-actions button:disabled {
    cursor: not-allowed;
    opacity: .65;
}

/* EMPTY RESULTS */

.empty-shop {
    padding: 70px 24px;
    border: 1px solid var(--shop-border);
    border-radius: 13px;
    background: white;
    text-align: center;
}

.empty-shop > i {
    color: #a6b3c8;
    font-size: 46px;
}

.empty-shop h4 {
    margin-top: 18px;
    color: var(--shop-navy);
    font-weight: 800;
}

/* PAGINATION */

.shop-pagination .page-link {
    display: grid;
    place-items: center;
    min-width: 39px;
    height: 39px;
    margin: 0 3px;
    border: 1px solid #e0e6ef;
    border-radius: 7px !important;
    background: white;
    color: var(--shop-blue);
    font-size: 12px;
    font-weight: 700;
    box-shadow: none;
}

.shop-pagination .page-link:hover {
    border-color: var(--shop-blue);
    background: #eff4ff;
}

.shop-pagination .page-item.active .page-link {
    border-color: var(--shop-blue);
    background: var(--shop-blue);
    color: white;
}

.shop-pagination .page-item.disabled .page-link {
    background: #f4f6f9;
    color: #aab3c1;
}

.shop-page a:focus-visible,
.shop-page button:focus-visible,
.shop-page select:focus-visible,
.shop-page input:focus-visible {
    outline: 3px solid rgba(23,69,202,.28);
    outline-offset: 3px;
}

/* RESPONSIVE */

@media (min-width: 1500px) {
    .shop-main-row {
        --bs-gutter-x: 1.6rem;
    }

    .product-body {
        padding: 20px;
    }
}

@media (max-width: 991.98px) {
    .filter-card {
        position: static;
    }

    .shop-hero {
        min-height: 260px;
    }

    .shop-hero-decoration {
        opacity: .5;
    }

    .product-image-wrap {
        height: 220px;
    }
}

@media (max-width: 767.98px) {
    .shop-hero {
        min-height: 280px;
        padding: 26px 23px;
        border-radius: 12px;
    }

    .shop-hero-content {
        max-width: 100%;
    }

    .shop-hero h1 {
        max-width: 420px;
        font-size: 32px;
    }

    .shop-hero p {
        max-width: 390px;
        font-size: 13px;
    }

    .shop-hero-decoration {
        width: 100%;
        opacity: .17;
    }

    .shop-hero-icon {
        right: 10px;
    }

    .shop-hero-floating {
        display: none;
    }

    .featured-section {
        padding: 15px;
    }

    .featured-image {
        height: 145px;
        padding: 14px;
    }

    .product-image-wrap {
        height: 185px;
        padding: 15px;
    }

    .product-body {
        padding: 12px;
    }

    .product-name {
        font-size: 12px;
    }

    .product-price {
        font-size: 18px;
    }

    .product-actions {
        grid-template-columns: 1fr;
    }

    .product-actions .btn,
    .add-to-cart-form button {
        min-height: 40px;
    }

    .results-header form {
        flex-wrap: wrap;
    }
}

@media (max-width: 380px) {
    .shop-hero h1 {
        font-size: 27px;
    }

    .product-image-wrap {
        height: 155px;
    }

    .product-featured,
    .product-stock {
        top: 7px;
        font-size: 8px;
    }

    .product-featured {
        left: 7px;
    }

    .product-stock {
        right: 7px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .shop-page *,
    .shop-page *::before,
    .shop-page *::after {
        transition-duration: .01ms !important;
        animation-duration: .01ms !important;
    }
}
</style>

<div class="shop-page">
    <div class="container-fluid shop-container py-4">

        <!-- HERO -->

        <section class="shop-hero mb-4">
            <div class="shop-hero-content">
                <span class="shop-hero-eyebrow">
                    <i class="fa fa-check-circle"></i>
                    BETACHEM ONLINE STORE
                </span>

                <h1>Everything your business needs.</h1>

                <p>
                    Discover our range of chemical products and
                    industrial solutions. Find the products you need
                    in one convenient online shopping destination.
                </p>

                <a href="#shop-products" class="shop-hero-button">
                    Explore products
                    <i class="fa fa-arrow-right"></i>
                </a>
            </div>

            <div class="shop-hero-decoration" aria-hidden="true">
                <div class="shop-hero-circle circle-one"></div>
                <div class="shop-hero-circle circle-two"></div>

                <div class="shop-hero-icon">
                    <i class="fa fa-flask"></i>
                </div>

                <div class="shop-hero-floating">
                    <i class="fa fa-check-circle"></i>
                    Quality solutions
                </div>
            </div>
        </section>

        <!-- SEARCH -->

        <div class="shop-search">
            <form method="GET" action="<?= e($baseUrl . '/shop.php'); ?>">

                <?php if ($categorySlug !== ''): ?>
                    <input type="hidden" name="category"
                           value="<?= e($categorySlug); ?>">
                <?php endif; ?>

                <?php if ($priceRange !== ''): ?>
                    <input type="hidden" name="price_range"
                           value="<?= e($priceRange); ?>">
                <?php endif; ?>

                <?php if ($inStock): ?>
                    <input type="hidden" name="in_stock" value="1">
                <?php endif; ?>

                <?php if ($featured): ?>
                    <input type="hidden" name="featured" value="1">
                <?php endif; ?>

                <?php if ($sort !== 'newest'): ?>
                    <input type="hidden" name="sort"
                           value="<?= e($sort); ?>">
                <?php endif; ?>

                <label for="productSearch"
                       class="form-label fw-semibold"
                       style="color:#13264a;">
                    <i class="fa fa-search me-1"></i>
                    What are you looking for?
                </label>

                <div class="input-group">
                    <input
                        type="text"
                        name="search"
                        id="productSearch"
                        class="form-control"
                        placeholder="Search products by name, SKU or description..."
                        value="<?= e($search); ?>"
                        autocomplete="off">

                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search me-1"></i>
                        Search
                    </button>

                    <?php if ($search !== ''): ?>
                        <a
                            href="<?= e(shopFilterUrl(
                                $categorySlug,
                                '',
                                $priceRange,
                                $inStock,
                                $featured,
                                $sort
                            )); ?>"
                            class="btn btn-outline-secondary"
                            aria-label="Clear search">
                            <i class="fa fa-times"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- SHOP BY CATEGORY -->

        <?php if (!empty($categories)): ?>
            <div class="category-strip mb-4">
                <div class="category-strip-title">
                    <i class="fa fa-th me-2"></i>
                    Shop by Category
                </div>

                <div class="category-list">
                    <a
                        href="<?= e(shopFilterUrl()); ?>"
                        class="category-link <?= $categorySlug === '' ? 'active' : ''; ?>">
                        All Products
                    </a>

                    <?php foreach ($categories as $category): ?>
                        <a
                            href="<?= e(shopFilterUrl(
                                $category['slug'],
                                '',
                                '',
                                0,
                                0,
                                'newest'
                            )); ?>"
                            class="category-link <?= $categorySlug === $category['slug'] ? 'active' : ''; ?>">

                            <?= e($category['name']); ?>

                            <small class="opacity-75">
                                (<?= (int) $category['product_count']; ?>)
                            </small>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- FEATURED PRODUCTS -->

        <?php if (
            !empty($featuredProducts) &&
            !$selectedCategory &&
            $search === '' &&
            $priceRange === '' &&
            !$inStock &&
            !$featured
        ): ?>
            <section class="featured-section mb-4">
                <div class="d-flex flex-wrap justify-content-between
                            align-items-center gap-3 mb-4">
                    <div>
                        <div class="featured-title">Featured Products</div>
                        <div class="small text-muted mt-1">
                            Explore selected products from our store
                        </div>
                    </div>

                    <a
                        href="<?= e(shopFilterUrl(
                            '',
                            '',
                            '',
                            0,
                            1,
                            'newest'
                        )); ?>"
                        class="btn btn-outline-primary btn-sm">
                        View All Featured
                        <i class="fa fa-arrow-right ms-1"></i>
                    </a>
                </div>

                <div class="row g-3">
                    <?php foreach ($featuredProducts as $featuredProduct): ?>
                        <div class="col-6 col-md-3">
                            <article class="featured-product">
                                <div class="featured-image">
                                    <a
                                        href="<?= e($baseUrl); ?>/product.php?slug=<?= urlencode($featuredProduct['slug']); ?>"
                                        class="w-100 h-100 d-flex align-items-center justify-content-center">

                                        <img
                                            src="<?= e(shopProductImage(
                                                $featuredProduct['main_image']
                                            )); ?>"
                                            alt="<?= e($featuredProduct['name']); ?>"
                                            loading="lazy">
                                    </a>
                                </div>

                                <div class="featured-body">
                                    <div class="featured-category">
                                        <?= e($featuredProduct['category_name'] ?? 'Product'); ?>
                                    </div>

                                    <div class="featured-name">
                                        <a href="<?= e($baseUrl); ?>/product.php?slug=<?= urlencode($featuredProduct['slug']); ?>">
                                            <?= e($featuredProduct['name']); ?>
                                        </a>
                                    </div>

                                    <div class="featured-price">
                                        <?= money($featuredProduct['price']); ?>
                                    </div>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- MAIN SHOP AREA -->

        <div class="row g-4 shop-main-row">

            <!-- FILTER SIDEBAR -->

            <aside class="col-lg-3 col-xl-2">
                <div class="filter-card">

                    <div class="filter-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>
                                <i class="fa fa-sliders me-2"></i>
                                Refine Products
                            </strong>

                            <?php if ($activeFilters > 0): ?>
                                <span class="badge bg-primary">
                                    <?= $activeFilters; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="filter-section">
                        <div class="filter-title">Category</div>

                        <div class="filter-category-list">
                            <div class="filter-category-item <?= $categorySlug === '' ? 'active' : ''; ?>">
                                <a href="<?= e(shopFilterUrl(
                                    '',
                                    $search,
                                    $priceRange,
                                    $inStock,
                                    $featured,
                                    $sort
                                )); ?>">
                                    <span>All Categories</span>
                                </a>
                            </div>

                            <?php foreach ($categories as $category): ?>
                                <div class="filter-category-item <?= $categorySlug === $category['slug'] ? 'active' : ''; ?>">
                                    <a href="<?= e(shopFilterUrl(
                                        $category['slug'],
                                        $search,
                                        $priceRange,
                                        $inStock,
                                        $featured,
                                        $sort
                                    )); ?>">
                                        <span><?= e($category['name']); ?></span>
                                        <span class="filter-count">
                                            <?= (int) $category['product_count']; ?>
                                        </span>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="filter-section">
                        <div class="filter-title">Price Range</div>

                        <select
                            class="form-select price-range-select"
                            aria-label="Filter by price range"
                            onchange="window.location.href = this.value;">

                            <?php foreach ($priceRanges as $rangeKey => $range): ?>
                                <?php
                                $rangeUrl = shopFilterUrl(
                                    $categorySlug,
                                    $search,
                                    $rangeKey,
                                    $inStock,
                                    $featured,
                                    $sort
                                );
                                ?>

                                <option
                                    value="<?= e($rangeUrl); ?>"
                                    <?= $priceRange === $rangeKey ? 'selected' : ''; ?>>
                                    <?= e($range['label']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-section">
                        <div class="filter-title">Availability</div>

                        <a
                            href="<?= e(shopFilterUrl(
                                $categorySlug,
                                $search,
                                $priceRange,
                                $inStock ? 0 : 1,
                                $featured,
                                $sort
                            )); ?>"
                            class="filter-check-link <?= $inStock ? 'active' : ''; ?>">

                            <span class="filter-check-icon">
                                <?php if ($inStock): ?>
                                    <i class="fa fa-check"></i>
                                <?php endif; ?>
                            </span>

                            In Stock Only
                        </a>
                    </div>

                    <div class="filter-section">
                        <div class="filter-title">Product Type</div>

                        <a
                            href="<?= e(shopFilterUrl(
                                $categorySlug,
                                $search,
                                $priceRange,
                                $inStock,
                                $featured ? 0 : 1,
                                $sort
                            )); ?>"
                            class="filter-check-link <?= $featured ? 'active' : ''; ?>">

                            <span class="filter-check-icon">
                                <?php if ($featured): ?>
                                    <i class="fa fa-check"></i>
                                <?php endif; ?>
                            </span>

                            Featured Products
                        </a>
                    </div>

                    <?php if ($activeFilters > 0): ?>
                        <div class="filter-section">
                            <a
                                href="<?= e($baseUrl); ?>/shop.php"
                                class="btn btn-outline-secondary w-100">
                                <i class="fa fa-times-circle me-1"></i>
                                Clear All Filters
                            </a>
                        </div>
                    <?php endif; ?>

                </div>
            </aside>

            <!-- PRODUCT RESULTS -->

            <main class="col-lg-9 col-xl-10" id="shop-products">

                <div class="results-header">
                    <div class="row align-items-center g-3">

                        <div class="col-md">
                            <h2 class="h5 mb-1"
                                style="color:#13264a;font-weight:800;">
                                <?= $selectedCategory
                                    ? e($selectedCategory['name'])
                                    : 'Discover Our Products'; ?>
                            </h2>

                            <div class="results-count">
                                <?php if ($search !== ''): ?>
                                    Results for
                                    <strong>"<?= e($search); ?>"</strong>
                                    <br>
                                <?php endif; ?>

                                Showing
                                <strong><?= $totalProducts > 0 ? $offset + 1 : 0; ?></strong>
                                –
                                <strong><?= min($offset + $perPage, $totalProducts); ?></strong>
                                of
                                <strong><?= $totalProducts; ?></strong>
                                products
                            </div>
                        </div>

                        <div class="col-md-auto">
                            <form
                                method="GET"
                                action="<?= e($baseUrl); ?>/shop.php"
                                class="d-flex align-items-center gap-2">

                                <?php if ($categorySlug !== ''): ?>
                                    <input type="hidden" name="category"
                                           value="<?= e($categorySlug); ?>">
                                <?php endif; ?>

                                <?php if ($search !== ''): ?>
                                    <input type="hidden" name="search"
                                           value="<?= e($search); ?>">
                                <?php endif; ?>

                                <?php if ($priceRange !== ''): ?>
                                    <input type="hidden" name="price_range"
                                           value="<?= e($priceRange); ?>">
                                <?php endif; ?>

                                <?php if ($inStock): ?>
                                    <input type="hidden" name="in_stock" value="1">
                                <?php endif; ?>

                                <?php if ($featured): ?>
                                    <input type="hidden" name="featured" value="1">
                                <?php endif; ?>

                                <label for="shopSort"
                                       class="small text-muted text-nowrap">
                                    Sort by
                                </label>

                                <select
                                    id="shopSort"
                                    name="sort"
                                    class="form-select form-select-sm"
                                    onchange="this.form.submit()">

                                    <option value="newest" <?= $sort === 'newest' ? 'selected' : ''; ?>>
                                        Newest
                                    </option>

                                    <option value="popular" <?= $sort === 'popular' ? 'selected' : ''; ?>>
                                        Featured
                                    </option>

                                    <option value="price_low" <?= $sort === 'price_low' ? 'selected' : ''; ?>>
                                        Price: Low to High
                                    </option>

                                    <option value="price_high" <?= $sort === 'price_high' ? 'selected' : ''; ?>>
                                        Price: High to Low
                                    </option>

                                    <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : ''; ?>>
                                        Name: A–Z
                                    </option>

                                    <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : ''; ?>>
                                        Name: Z–A
                                    </option>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- PRODUCT GRID -->

                <?php if (!empty($products)): ?>

                    <div class="row g-3">

                        <?php foreach ($products as $product): ?>

                            <?php
                            $stock = (int) $product['stock_quantity'];
                            $minimumStock = (int) $product['minimum_stock'];

                            if ($stock <= 0) {
                                $stockClass = 'stock-out';
                                $stockText = 'Out of Stock';
                            } elseif (
                                $minimumStock > 0 &&
                                $stock <= $minimumStock
                            ) {
                                $stockClass = 'stock-low';
                                $stockText = 'Low Stock';
                            } else {
                                $stockClass = 'stock-available';
                                $stockText = 'In Stock';
                            }

                            $description = trim(
                                (string) ($product['description'] ?? '')
                            );

                            if (strlen($description) > 90) {
                                $description = substr($description, 0, 87) . '...';
                            }

                            $productUrl = $baseUrl . '/product.php?slug=' .
                                urlencode($product['slug']);
                            ?>

                            <div class="col-6 col-md-4 col-xl-3">
                                <article class="product-card">

                                    <!-- PRODUCT IMAGE -->

                                    <div class="product-image-wrap">

                                        <?php if ((int) $product['is_featured'] === 1): ?>
                                            <span class="product-featured">
                                                <i class="fa fa-star me-1"></i>
                                                Featured
                                            </span>
                                        <?php endif; ?>

                                        <span class="product-stock <?= e($stockClass); ?>">
                                            <?= e($stockText); ?>
                                        </span>

                                        <a
                                            href="<?= e($productUrl); ?>"
                                            class="product-image-link"
                                            aria-label="View <?= e($product['name']); ?>">

                                            <img
                                                src="<?= e(shopProductImage(
                                                    $product['main_image']
                                                )); ?>"
                                                alt="<?= e($product['name']); ?>"
                                                class="product-image"
                                                loading="lazy">
                                        </a>
                                    </div>

                                    <!-- PRODUCT DETAILS -->

                                    <div class="product-body">

                                        <div class="product-category">
                                            <?= e($product['category_name'] ?? 'Product'); ?>
                                        </div>

                                        <h3 class="product-name">
                                            <a href="<?= e($productUrl); ?>">
                                                <?= e($product['name']); ?>
                                            </a>
                                        </h3>

                                        <div class="product-sku">
                                            Product code:
                                            <?= e($product['sku'] ?? 'N/A'); ?>
                                        </div>

                                        <div class="product-description">
                                            <?= $description !== ''
                                                ? e($description)
                                                : 'Quality products for your business.'; ?>
                                        </div>

                                        <div class="product-price">
                                            <?= money($product['price']); ?>
                                        </div>

                                        <!-- PRODUCT ACTIONS -->

                                        <div class="product-actions">

                                            <a
                                                href="<?= e($productUrl); ?>"
                                                class="btn btn-outline-primary">
                                                <i class="fa fa-eye me-1"></i>
                                                View Details
                                            </a>

                                            <?php if ($stock > 0): ?>

                                                <form
                                                    method="POST"
                                                    action="<?= e($baseUrl); ?>/cart.php"
                                                    class="add-to-cart-form">

                                                    <input
                                                        type="hidden"
                                                        name="action"
                                                        value="add">

                                                    <input
                                                        type="hidden"
                                                        name="product_id"
                                                        value="<?= (int) $product['id']; ?>">

                                                    <input
                                                        type="hidden"
                                                        name="quantity"
                                                        value="1">

                                                    <button
                                                        type="submit"
                                                        class="btn btn-primary">
                                                        <i class="fa fa-shopping-cart me-1"></i>
                                                        Add to Cart
                                                    </button>

                                                </form>

                                            <?php else: ?>

                                                <button
                                                    type="button"
                                                    class="btn btn-secondary"
                                                    disabled>
                                                    <i class="fa fa-ban me-1"></i>
                                                    Unavailable
                                                </button>

                                            <?php endif; ?>

                                        </div>
                                    </div>
                                </article>
                            </div>

                        <?php endforeach; ?>

                    </div>

                    <!-- PAGINATION -->

                    <?php if ($totalPages > 1): ?>
                        <nav
                            class="shop-pagination mt-4"
                            aria-label="Product pages">

                            <ul class="pagination justify-content-center">

                                <li class="page-item <?= $page <= 1 ? 'disabled' : ''; ?>">
                                    <a
                                        class="page-link"
                                        href="<?= $page > 1 ? e(shopPaginationUrl($page - 1)) : '#'; ?>"
                                        aria-label="Previous page">
                                        <i class="fa fa-chevron-left"></i>
                                    </a>
                                </li>

                                <?php
                                $startPage = max(1, $page - 2);
                                $endPage = min($totalPages, $page + 2);
                                ?>

                                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : ''; ?>">
                                        <a
                                            class="page-link"
                                            href="<?= e(shopPaginationUrl($i)); ?>">
                                            <?= $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <li class="page-item <?= $page >= $totalPages ? 'disabled' : ''; ?>">
                                    <a
                                        class="page-link"
                                        href="<?= $page < $totalPages ? e(shopPaginationUrl($page + 1)) : '#'; ?>"
                                        aria-label="Next page">
                                        <i class="fa fa-chevron-right"></i>
                                    </a>
                                </li>

                            </ul>
                        </nav>
                    <?php endif; ?>

                <?php else: ?>

                    <!-- EMPTY RESULTS -->

                    <div class="empty-shop">
                        <i class="fa fa-search"></i>

                        <h4>No products found</h4>

                        <?php if ($search !== ''): ?>
                            <p class="text-muted mb-4">
                                We couldn't find products matching
                                <strong>"<?= e($search); ?>"</strong>.
                                Try a different search.
                            </p>
                        <?php else: ?>
                            <p class="text-muted mb-4">
                                We couldn't find products matching your
                                selected filters.
                            </p>
                        <?php endif; ?>

                        <a
                            href="<?= e($baseUrl); ?>/shop.php"
                            class="btn btn-primary">
                            <i class="fa fa-th-large me-1"></i>
                            View All Products
                        </a>
                    </div>

                <?php endif; ?>

            </main>
        </div>
    </div>
</div>


<script>
(function () {
    'use strict';

    // Prevent duplicate event handlers if this script is initialized again.
    if (window.betachemShopPaginationReady) {
        return;
    }
    window.betachemShopPaginationReady = true;

    const resultsSelector = '#shop-products';
    let requestController = null;

    async function loadShopPage(url, updateHistory = true) {
        const currentResults = document.querySelector(resultsSelector);

        if (!currentResults) {
            window.location.href = url;
            return;
        }

        // Cancel an older pagination request if the customer clicks quickly.
        if (requestController) {
            requestController.abort();
        }

        requestController = new AbortController();

        currentResults.setAttribute('aria-busy', 'true');
        currentResults.style.opacity = '0.6';

        try {
            const response = await fetch(url, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: requestController.signal
            });

            if (!response.ok) {
                throw new Error('Could not load the requested products.');
            }

            const html = await response.text();
            const parsedDocument = new DOMParser().parseFromString(
                html,
                'text/html'
            );

            const newResults = parsedDocument.querySelector(
                resultsSelector
            );

            if (!newResults) {
                throw new Error('The product results were not found.');
            }

            // Replace only the results area, not the entire page.
            currentResults.innerHTML = newResults.innerHTML;

            // Update the browser URL while preserving normal navigation.
            if (updateHistory) {
                window.history.pushState(
                    { shopPagination: true },
                    '',
                    response.url || url
                );
            }

            // Keep keyboard and screen-reader users informed.
            currentResults.setAttribute('aria-busy', 'false');

        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            // Fall back to normal PHP navigation if AJAX fails.
            window.location.href = url;

        } finally {
            const results = document.querySelector(resultsSelector);

            if (results) {
                results.style.opacity = '1';
                results.setAttribute('aria-busy', 'false');
            }

            requestController = null;
        }
    }

    // Intercept pagination links only.
    document.addEventListener('click', function (event) {
        const link = event.target.closest(
            '.shop-pagination a.page-link'
        );

        if (!link) {
            return;
        }

        const pageItem = link.closest('.page-item');

        if (
            link.getAttribute('href') === '#' ||
            (pageItem && pageItem.classList.contains('disabled'))
        ) {
            event.preventDefault();
            return;
        }

        // Allow new-tab, download, and modified clicks to work normally.
        if (
            event.ctrlKey ||
            event.metaKey ||
            event.shiftKey ||
            event.altKey ||
            event.button !== 0
        ) {
            return;
        }

        event.preventDefault();

        loadShopPage(link.href);
    });

    // Support browser Back and Forward buttons.
    window.addEventListener('popstate', function () {
        loadShopPage(window.location.href, false);
    });
})();
</script>


<?php require_once __DIR__ . '/includes/footer.php'; ?>
