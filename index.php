<?php

require_once __DIR__ . '/includes/header.php';

/*
|--------------------------------------------------------------------------
| Betachem E-Commerce Homepage
|--------------------------------------------------------------------------
| Corporate + Premium + Compact
|--------------------------------------------------------------------------
*/

$baseUrl = '/betachem/betachem_e-commerce';


/*
|--------------------------------------------------------------------------
| Image Helpers
|--------------------------------------------------------------------------
*/

function homeProductImage($image)
{
    global $baseUrl;

    if (empty($image)) {
        return null;
    }

    $baseDir = __DIR__ . '/uploads/products/';
    $image = basename($image);

    $extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    foreach ($extensions as $extension) {

        /*
        |--------------------------------------------------------------------------
        | Image already has extension
        |--------------------------------------------------------------------------
        */

        if (
            strtolower(pathinfo($image, PATHINFO_EXTENSION))
            === $extension
        ) {

            $file = $baseDir . $image;

            if (file_exists($file)) {

                return $baseUrl
                    . '/uploads/products/'
                    . rawurlencode($image);
            }

        } else {

            /*
            |--------------------------------------------------------------------------
            | Image stored without extension
            |--------------------------------------------------------------------------
            */

            $fileWithExtension =
                $baseDir . $image . '.' . $extension;

            if (file_exists($fileWithExtension)) {

                return $baseUrl
                    . '/uploads/products/'
                    . rawurlencode($image . '.' . $extension);
            }
        }
    }

    return null;
}


function homeCategoryImage($image)
{
    global $baseUrl;

    if (empty($image)) {
        return null;
    }

    $baseDir = __DIR__ . '/uploads/categories/';
    $image = basename($image);

    $extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    foreach ($extensions as $extension) {

        /*
        |--------------------------------------------------------------------------
        | Image already has extension
        |--------------------------------------------------------------------------
        */

        if (
            strtolower(pathinfo($image, PATHINFO_EXTENSION))
            === $extension
        ) {

            $file = $baseDir . $image;

            if (file_exists($file)) {

                return $baseUrl
                    . '/uploads/categories/'
                    . rawurlencode($image);
            }

        } else {

            /*
            |--------------------------------------------------------------------------
            | Image stored without extension
            |--------------------------------------------------------------------------
            */

            $fileWithExtension =
                $baseDir . $image . '.' . $extension;

            if (file_exists($fileWithExtension)) {

                return $baseUrl
                    . '/uploads/categories/'
                    . rawurlencode($image . '.' . $extension);
            }
        }
    }

    return null;
}


/*
|--------------------------------------------------------------------------
| Load Categories
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
        c.image
    ORDER BY
        c.sort_order ASC,
        c.name ASC
");

while ($row = $categoryResult->fetch_assoc()) {
    $categories[] = $row;
}


/*
|--------------------------------------------------------------------------
| Featured Products
|--------------------------------------------------------------------------
*/

$featuredProducts = [];

$featuredResult = $conn->query("
    SELECT
        p.id,
        p.name,
        p.slug,
        p.sku,
        p.description,
        p.price,
        p.stock_quantity,
        p.main_image,
        c.name AS category_name
    FROM products p
    LEFT JOIN categories c
        ON c.id = p.category_id
    WHERE
        p.is_active = 1
        AND p.is_featured = 1
    ORDER BY
        p.created_at DESC,
        p.name ASC
    LIMIT 4
");

while ($row = $featuredResult->fetch_assoc()) {
    $featuredProducts[] = $row;
}


/*
|--------------------------------------------------------------------------
| Latest Products
|--------------------------------------------------------------------------
*/

$latestProducts = [];

$latestResult = $conn->query("
    SELECT
        p.id,
        p.name,
        p.slug,
        p.sku,
        p.description,
        p.price,
        p.stock_quantity,
        p.main_image,
        c.name AS category_name
    FROM products p
    LEFT JOIN categories c
        ON c.id = p.category_id
    WHERE p.is_active = 1
    ORDER BY p.created_at DESC
    LIMIT 4
");

while ($row = $latestResult->fetch_assoc()) {
    $latestProducts[] = $row;
}


/*
|--------------------------------------------------------------------------
| Display Categories
|--------------------------------------------------------------------------
*/

$mainCategories = array_slice($categories, 0, 6);

?>

<style>

/*
|--------------------------------------------------------------------------
| PAGE
|--------------------------------------------------------------------------
*/

.betachem-home {
    background: #ffffff;
    color: #172033;
}

.betachem-home .container {
    max-width: 1160px;
}


/*
|--------------------------------------------------------------------------
| HERO
|--------------------------------------------------------------------------
*/

.bchem-hero {
    padding: 72px 0 68px;
    background:
        linear-gradient(
            135deg,
            #f5f8fc 0%,
            #ffffff 58%,
            #edf4fb 100%
        );
    border-bottom: 1px solid #e7edf4;
}

.bchem-hero-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.08fr) minmax(360px, .92fr);
    gap: 55px;
    align-items: center;
}

.bchem-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    color: #1769aa;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 18px;
}

.bchem-eyebrow::before {
    content: "";
    width: 32px;
    height: 2px;
    background: #1769aa;
}

.bchem-hero h1 {
    font-size: clamp(42px, 5vw, 64px);
    line-height: 1.02;
    letter-spacing: -2px;
    font-weight: 800;
    color: #102b49;
    margin: 0 0 22px;
}

.bchem-hero h1 span {
    color: #1769aa;
}

.bchem-hero-text {
    max-width: 600px;
    color: #64748b;
    font-size: 17px;
    line-height: 1.8;
    margin-bottom: 30px;
}

.bchem-hero-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.bchem-btn-primary {
    background: #1769aa;
    border: 1px solid #1769aa;
    color: #ffffff;
    padding: 13px 23px;
    border-radius: 5px;
    font-size: 14px;
    font-weight: 700;
    text-decoration: none;
    transition: all .25s ease;
}

.bchem-btn-primary:hover {
    background: #0f4f82;
    border-color: #0f4f82;
    color: #ffffff;
    transform: translateY(-2px);
}

.bchem-btn-outline {
    background: #ffffff;
    border: 1px solid #cbd7e3;
    color: #183b5d;
    padding: 13px 23px;
    border-radius: 5px;
    font-size: 14px;
    font-weight: 700;
    text-decoration: none;
    transition: all .25s ease;
}

.bchem-btn-outline:hover {
    border-color: #1769aa;
    color: #1769aa;
    transform: translateY(-2px);
}


/*
|--------------------------------------------------------------------------
| HERO PANEL
|--------------------------------------------------------------------------
*/

.bchem-hero-panel {
    position: relative;
    min-height: 370px;
    background:
        linear-gradient(
            145deg,
            #0d2945,
            #1769aa
        );
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 22px 55px rgba(15, 51, 82, .18);
}

.bchem-hero-panel::before {
    content: "";
    position: absolute;
    inset: 0;
    background:
        linear-gradient(
            90deg,
            rgba(255,255,255,.04) 1px,
            transparent 1px
        ),
        linear-gradient(
            rgba(255,255,255,.04) 1px,
            transparent 1px
        );
    background-size: 34px 34px;
}

.bchem-hero-panel::after {
    content: "";
    position: absolute;
    width: 270px;
    height: 270px;
    border: 1px solid rgba(255,255,255,.16);
    border-radius: 50%;
    right: -90px;
    top: -75px;
    box-shadow:
        0 0 0 35px rgba(255,255,255,.025),
        0 0 0 70px rgba(255,255,255,.02);
}

.bchem-panel-content {
    position: relative;
    z-index: 2;
    padding: 38px;
    height: 100%;
    min-height: 370px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.bchem-panel-top {
    color: rgba(255,255,255,.72);
    font-size: 11px;
    letter-spacing: 2px;
    font-weight: 800;
}

.bchem-panel-title {
    color: #ffffff;
    font-size: 32px;
    line-height: 1.15;
    font-weight: 800;
    max-width: 330px;
}

.bchem-panel-title span {
    color: #a9d5f5;
}

.bchem-panel-list {
    display: grid;
    gap: 10px;
    max-width: 330px;
}

.bchem-panel-list div {
    display: flex;
    align-items: center;
    gap: 11px;
    color: rgba(255,255,255,.9);
    font-size: 13px;
}

.bchem-panel-list i {
    color: #ffffff;
    font-size: 15px;
}


/*
|--------------------------------------------------------------------------
| TRUST BAR
|--------------------------------------------------------------------------
*/

.bchem-trust {
    background: #ffffff;
    border-bottom: 1px solid #e7edf4;
}

.bchem-trust-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
}

.bchem-trust-item {
    padding: 25px 20px;
    border-right: 1px solid #e7edf4;
    display: flex;
    align-items: center;
    gap: 13px;
}

.bchem-trust-item:first-child {
    border-left: 1px solid #e7edf4;
}

.bchem-trust-icon {
    width: 40px;
    height: 40px;
    background: #edf5fc;
    color: #1769aa;
    border-radius: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.bchem-trust-item strong {
    display: block;
    font-size: 13px;
    color: #183b5d;
}

.bchem-trust-item span {
    display: block;
    font-size: 11px;
    color: #8996a6;
    margin-top: 2px;
}


/*
|--------------------------------------------------------------------------
| SECTION
|--------------------------------------------------------------------------
*/

.bchem-section {
    padding: 72px 0;
}

.bchem-section-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 30px;
}

.bchem-section-label {
    color: #1769aa;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 8px;
}

.bchem-section-title {
    margin: 0;
    font-size: 34px;
    letter-spacing: -.8px;
    font-weight: 800;
    color: #102b49;
}

.bchem-section-description {
    max-width: 520px;
    margin: 10px 0 0;
    color: #718096;
    line-height: 1.7;
    font-size: 14px;
}

.bchem-view-all {
    color: #1769aa;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
}

.bchem-view-all:hover {
    color: #0f4f82;
}


/*
|--------------------------------------------------------------------------
| CATEGORY CARDS
|--------------------------------------------------------------------------
*/

.bchem-category-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
}

.bchem-category-card {
    position: relative;
    min-height: 245px;
    border: 1px solid #e1e8ef;
    border-radius: 7px;
    overflow: hidden;
    background: #ffffff;
    text-decoration: none;
    transition: all .3s ease;
}

.bchem-category-card:hover {
    transform: translateY(-5px);
    border-color: #b9d2e7;
    box-shadow: 0 18px 40px rgba(25, 61, 92, .11);
}

.bchem-category-image {
    height: 145px;
    background: #edf3f8;
    overflow: hidden;
}

.bchem-category-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform .45s ease;
}

.bchem-category-card:hover .bchem-category-image img {
    transform: scale(1.05);
}

.bchem-category-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #8aa2b7;
    font-size: 40px;
}

.bchem-category-body {
    padding: 18px 19px;
}

.bchem-category-body h3 {
    color: #183b5d;
    font-size: 17px;
    font-weight: 750;
    margin: 0 0 6px;
}

.bchem-category-body p {
    color: #7b8998;
    font-size: 12px;
    line-height: 1.55;
    margin: 0;
}

.bchem-category-count {
    margin-top: 11px;
    color: #1769aa;
    font-size: 11px;
    font-weight: 700;
}


/*
|--------------------------------------------------------------------------
| PRODUCT DIVISIONS
|--------------------------------------------------------------------------
*/

.bchem-divisions {
    background: #f5f8fb;
    border-top: 1px solid #e6edf3;
    border-bottom: 1px solid #e6edf3;
}

.bchem-division-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
}

.bchem-division {
    background: #ffffff;
    border: 1px solid #dfe7ee;
    border-radius: 7px;
    overflow: hidden;
    transition: all .3s ease;
}

.bchem-division:hover {
    transform: translateY(-4px);
    box-shadow: 0 18px 35px rgba(20, 55, 85, .1);
}

.bchem-division-heading {
    background: #1769aa;
    color: #ffffff;
    padding: 17px 19px;
    font-size: 14px;
    font-weight: 800;
    letter-spacing: .3px;
}

.bchem-division:nth-child(2) .bchem-division-heading {
    background: #234e73;
}

.bchem-division:nth-child(3) .bchem-division-heading {
    background: #102b49;
}

.bchem-division-body {
    padding: 22px;
}

.bchem-division-body p {
    color: #6d7c8b;
    font-size: 13px;
    line-height: 1.7;
    margin-bottom: 18px;
}

.bchem-division-link {
    color: #1769aa;
    text-decoration: none;
    font-size: 12px;
    font-weight: 800;
}

.bchem-division-link:hover {
    color: #0f4f82;
}


/*
|--------------------------------------------------------------------------
| PRODUCTS
|--------------------------------------------------------------------------
*/

.bchem-product-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
}

.bchem-product-card {
    background: #ffffff;
    border: 1px solid #e0e7ee;
    border-radius: 7px;
    overflow: hidden;
    transition: all .3s ease;
}

.bchem-product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 18px 38px rgba(24, 59, 93, .1);
}

.bchem-product-image {
    height: 205px;
    background: #f3f6f9;
    position: relative;
    overflow: hidden;
}

.bchem-product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform .45s ease;
}

.bchem-product-card:hover .bchem-product-image img {
    transform: scale(1.04);
}

.bchem-product-placeholder {
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #a2b0bd;
    font-size: 45px;
}

.bchem-featured-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: #1769aa;
    color: #ffffff;
    padding: 5px 9px;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .8px;
    border-radius: 3px;
}

.bchem-product-body {
    padding: 17px;
}

.bchem-product-category {
    color: #1769aa;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 7px;
}

.bchem-product-name {
    color: #183b5d;
    font-size: 15px;
    line-height: 1.35;
    font-weight: 750;
    min-height: 40px;
    margin-bottom: 6px;
}

.bchem-product-sku {
    color: #9aa7b4;
    font-size: 10px;
    margin-bottom: 14px;
}

.bchem-product-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.bchem-product-price {
    color: #102b49;
    font-size: 17px;
    font-weight: 800;
}

.bchem-product-link {
    width: 34px;
    height: 34px;
    border: 1px solid #d4e0ea;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #1769aa;
    text-decoration: none;
    transition: all .2s ease;
}

.bchem-product-link:hover {
    background: #1769aa;
    border-color: #1769aa;
    color: #ffffff;
}


/*
|--------------------------------------------------------------------------
| CORPORATE SECTION
|--------------------------------------------------------------------------
*/

.bchem-corporate {
    background: #102b49;
    color: #ffffff;
}

.bchem-corporate-grid {
    display: grid;
    grid-template-columns: 1.05fr .95fr;
    gap: 60px;
    align-items: center;
}

.bchem-corporate-label {
    color: #8fc5ec;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 12px;
}

.bchem-corporate h2 {
    color: #ffffff;
    font-size: 35px;
    line-height: 1.15;
    font-weight: 800;
    letter-spacing: -.8px;
    margin-bottom: 18px;
}

.bchem-corporate p {
    color: rgba(255,255,255,.7);
    font-size: 14px;
    line-height: 1.8;
    margin-bottom: 25px;
}

.bchem-corporate-points {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.bchem-corporate-point {
    border-top: 1px solid rgba(255,255,255,.14);
    padding-top: 14px;
}

.bchem-corporate-point strong {
    display: block;
    color: #ffffff;
    font-size: 13px;
    margin-bottom: 4px;
}

.bchem-corporate-point span {
    color: rgba(255,255,255,.58);
    font-size: 11px;
}

.bchem-corporate-panel {
    border: 1px solid rgba(255,255,255,.13);
    padding: 28px;
    border-radius: 6px;
    background: rgba(255,255,255,.035);
}

.bchem-corporate-panel-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 25px;
}

.bchem-corporate-panel-top strong {
    color: #ffffff;
    font-size: 13px;
}

.bchem-corporate-panel-top span {
    color: #8fc5ec;
    font-size: 10px;
    letter-spacing: 1px;
}

.bchem-stat {
    border-top: 1px solid rgba(255,255,255,.12);
    padding: 18px 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.bchem-stat:last-child {
    border-bottom: 1px solid rgba(255,255,255,.12);
}

.bchem-stat strong {
    color: #ffffff;
    font-size: 15px;
}

.bchem-stat span {
    color: rgba(255,255,255,.55);
    font-size: 11px;
}


/*
|--------------------------------------------------------------------------
| CTA
|--------------------------------------------------------------------------
*/

.bchem-cta {
    padding: 68px 0;
    background: #f4f7fa;
}

.bchem-cta-box {
    background:
        linear-gradient(
            120deg,
            #1769aa,
            #0e4d7e
        );
    border-radius: 7px;
    padding: 42px 48px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 30px;
    box-shadow: 0 18px 40px rgba(23,105,170,.17);
}

.bchem-cta h2 {
    color: #ffffff;
    font-size: 27px;
    font-weight: 800;
    margin: 0 0 8px;
}

.bchem-cta p {
    color: rgba(255,255,255,.72);
    font-size: 13px;
    margin: 0;
}

.bchem-cta-button {
    background: #ffffff;
    color: #1769aa;
    border: 0;
    padding: 13px 22px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 800;
    text-decoration: none;
    white-space: nowrap;
    transition: all .25s ease;
}

.bchem-cta-button:hover {
    background: #eef6fc;
    color: #0f4f82;
    transform: translateY(-2px);
}


/*
|--------------------------------------------------------------------------
| RESPONSIVE
|--------------------------------------------------------------------------
*/

@media (max-width: 992px) {

    .bchem-hero-grid,
    .bchem-corporate-grid {
        grid-template-columns: 1fr;
    }

    .bchem-hero-panel {
        min-height: 320px;
    }

    .bchem-panel-content {
        min-height: 320px;
    }

    .bchem-category-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .bchem-division-grid {
        grid-template-columns: 1fr;
    }

    .bchem-product-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .bchem-trust-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .bchem-trust-item:nth-child(3) {
        border-left: 1px solid #e7edf4;
    }

    .bchem-cta-box {
        align-items: flex-start;
        flex-direction: column;
    }
}


@media (max-width: 640px) {

    .bchem-hero {
        padding: 50px 0;
    }

    .bchem-hero h1 {
        font-size: 42px;
        letter-spacing: -1.5px;
    }

    .bchem-hero-text {
        font-size: 15px;
    }

    .bchem-section {
        padding: 55px 0;
    }

    .bchem-section-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .bchem-section-title {
        font-size: 29px;
    }

    .bchem-category-grid,
    .bchem-product-grid,
    .bchem-trust-grid {
        grid-template-columns: 1fr;
    }

    .bchem-trust-item,
    .bchem-trust-item:first-child {
        border-left: 1px solid #e7edf4;
    }

    .bchem-corporate-points {
        grid-template-columns: 1fr;
    }

    .bchem-cta-box {
        padding: 32px 25px;
    }

    .bchem-cta h2 {
        font-size: 24px;
    }
}

</style>


<div class="betachem-home">


    <!-- ==============================================================
         HERO
    ============================================================== -->

    <section class="bchem-hero">

        <div class="container">

            <div class="bchem-hero-grid">

                <div>

                    <div class="bchem-eyebrow">
                        BETACHEM E-COMMERCE
                    </div>

                    <h1>
                        Chemical solutions
                        <span>for industry.</span>
                    </h1>

                    <p class="bchem-hero-text">
                        Explore Betachem's online catalogue of laboratory,
                        industrial and specialty chemical products.
                        Find the products you need and order with confidence.
                    </p>

                    <div class="bchem-hero-buttons">

                        <a
                            href="<?= $baseUrl; ?>/shop.php"
                            class="bchem-btn-primary"
                        >
                            <i class="bi bi-grid-3x3-gap me-2"></i>
                            Shop Products
                        </a>

                        <a
                            href="<?= $baseUrl; ?>/category.php"
                            class="bchem-btn-outline"
                        >
                            Browse Categories
                            <i class="bi bi-arrow-right ms-2"></i>
                        </a>

                    </div>

                </div>


                <!-- HERO PANEL -->

                <div class="bchem-hero-panel">

                    <div class="bchem-panel-content">

                        <div class="bchem-panel-top">
                            BETACHEM PRODUCT CATALOGUE
                        </div>

                        <div>

                            <div class="bchem-panel-title">
                                Products built around
                                <span>quality & reliability.</span>
                            </div>

                        </div>

                        <div class="bchem-panel-list">

                            <div>
                                <i class="bi bi-check-circle-fill"></i>
                                Laboratory chemicals & reagents
                            </div>

                            <div>
                                <i class="bi bi-check-circle-fill"></i>
                                Industrial chemical solutions
                            </div>

                            <div>
                                <i class="bi bi-check-circle-fill"></i>
                                Laboratory equipment & consumables
                            </div>

                            <div>
                                <i class="bi bi-check-circle-fill"></i>
                                Professional product support
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>


    <!-- ==============================================================
         TRUST BAR
    ============================================================== -->

    <section class="bchem-trust">

        <div class="container">

            <div class="bchem-trust-grid">

                <div class="bchem-trust-item">

                    <div class="bchem-trust-icon">
                        <i class="bi bi-patch-check"></i>
                    </div>

                    <div>
                        <strong>Quality Products</strong>
                        <span>Professional product selection</span>
                    </div>

                </div>


                <div class="bchem-trust-item">

                    <div class="bchem-trust-icon">
                        <i class="bi bi-box-seam"></i>
                    </div>

                    <div>
                        <strong>Reliable Supply</strong>
                        <span>Products ready to order</span>
                    </div>

                </div>


                <div class="bchem-trust-item">

                    <div class="bchem-trust-icon">
                        <i class="bi bi-diagram-3"></i>
                    </div>

                    <div>
                        <strong>Industry Focus</strong>
                        <span>Solutions for demanding applications</span>
                    </div>

                </div>


                <div class="bchem-trust-item">

                    <div class="bchem-trust-icon">
                        <i class="bi bi-headset"></i>
                    </div>

                    <div>
                        <strong>Customer Support</strong>
                        <span>Professional assistance</span>
                    </div>

                </div>

            </div>

        </div>

    </section>


    <!-- ==============================================================
         CATEGORIES
    ============================================================== -->

    <section class="bchem-section">

        <div class="container">

            <div class="bchem-section-header">

                <div>

                    <div class="bchem-section-label">
                        Product Catalogue
                    </div>

                    <h2 class="bchem-section-title">
                        Shop by category
                    </h2>

                    <p class="bchem-section-description">
                        Browse our product categories to quickly find
                        chemicals, laboratory products, equipment and
                        essential consumables.
                    </p>

                </div>

                <a
                    href="<?= $baseUrl; ?>/category.php"
                    class="bchem-view-all"
                >
                    View All Categories
                    <i class="bi bi-arrow-right ms-1"></i>
                </a>

            </div>


            <?php if (!empty($mainCategories)): ?>

                <div class="bchem-category-grid">

                    <?php foreach ($mainCategories as $category): ?>

                        <?php
                        $categoryImage = homeCategoryImage(
                            $category['image'] ?? null
                        );
                        ?>

                        <a
                            href="<?= $baseUrl; ?>/shop.php?category=<?= urlencode($category['slug']) ?>"
                            class="bchem-category-card"
                        >

                            <div class="bchem-category-image">

                                <?php if ($categoryImage): ?>

                                    <img
                                        src="<?= e($categoryImage) ?>"
                                        alt="<?= e($category['name']) ?>"
                                    >

                                <?php else: ?>

                                    <div class="bchem-category-placeholder">
                                        <i class="bi bi-box-seam"></i>
                                    </div>

                                <?php endif; ?>

                            </div>


                            <div class="bchem-category-body">

                                <h3>
                                    <?= e($category['name']) ?>
                                </h3>

                                <?php if (!empty($category['description'])): ?>

                                    <p>
                                        <?= e(
                                            mb_strimwidth(
                                                $category['description'],
                                                0,
                                                90,
                                                '...'
                                            )
                                        ) ?>
                                    </p>

                                <?php else: ?>

                                    <p>
                                        Explore products in this category.
                                    </p>

                                <?php endif; ?>

                                <div class="bchem-category-count">

                                    <?= (int) $category['product_count'] ?>

                                    Products

                                    <i class="bi bi-arrow-right ms-1"></i>

                                </div>

                            </div>

                        </a>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="text-center py-5 text-muted">
                    No categories are currently available.
                </div>

            <?php endif; ?>

        </div>

    </section>


    <!-- ==============================================================
         BETACHEM PRODUCT DIVISIONS
    ============================================================== -->

    <section class="bchem-section bchem-divisions">

        <div class="container">

            <div class="bchem-section-header">

                <div>

                    <div class="bchem-section-label">
                        Betachem
                    </div>

                    <h2 class="bchem-section-title">
                        Our product divisions
                    </h2>

                    <p class="bchem-section-description">
                        A focused range of chemical products and solutions
                        serving laboratory, industrial and specialised
                        applications.
                    </p>

                </div>

            </div>


            <div class="bchem-division-grid">


                <!-- BETA RANGE -->

                <div class="bchem-division">

                    <div class="bchem-division-heading">
                        BETA RANGE
                    </div>

                    <div class="bchem-division-body">

                        <p>
                            Explore Betachem's range of specialised
                            chemical products developed for demanding
                            industrial and technical applications.
                        </p>

                        <a
                            href="<?= $baseUrl; ?>/shop.php"
                            class="bchem-division-link"
                        >
                            Explore Products
                            <i class="bi bi-arrow-right ms-1"></i>
                        </a>

                    </div>

                </div>


                <!-- AGENCY -->

                <div class="bchem-division">

                    <div class="bchem-division-heading">
                        AGENCY CHEMICALS &amp; PROCESSES
                    </div>

                    <div class="bchem-division-body">

                        <p>
                            Access selected chemical products and
                            process solutions from trusted industry
                            suppliers and partners.
                        </p>

                        <a
                            href="<?= $baseUrl; ?>/shop.php"
                            class="bchem-division-link"
                        >
                            Explore Products
                            <i class="bi bi-arrow-right ms-1"></i>
                        </a>

                    </div>

                </div>


                <!-- COMMODITIES -->

                <div class="bchem-division">

                    <div class="bchem-division-heading">
                        COMMODITIES
                    </div>

                    <div class="bchem-division-body">

                        <p>
                            Practical chemical commodities and
                            essential products for industrial,
                            commercial and operational requirements.
                        </p>

                        <a
                            href="<?= $baseUrl; ?>/shop.php"
                            class="bchem-division-link"
                        >
                            Explore Products
                            <i class="bi bi-arrow-right ms-1"></i>
                        </a>

                    </div>

                </div>


            </div>

        </div>

    </section>


    <!-- ==============================================================
         FEATURED PRODUCTS
    ============================================================== -->

    <?php if (!empty($featuredProducts)): ?>

        <section class="bchem-section">

            <div class="container">

                <div class="bchem-section-header">

                    <div>

                        <div class="bchem-section-label">
                            Featured
                        </div>

                        <h2 class="bchem-section-title">
                            Featured products
                        </h2>

                        <p class="bchem-section-description">
                            A selection of products currently highlighted
                            in our online catalogue.
                        </p>

                    </div>

                    <a
                        href="<?= $baseUrl; ?>/shop.php"
                        class="bchem-view-all"
                    >
                        View All Products
                        <i class="bi bi-arrow-right ms-1"></i>
                    </a>

                </div>


                <div class="bchem-product-grid">

                    <?php foreach ($featuredProducts as $product): ?>

                        <?php
                        $productImage = homeProductImage(
                            $product['main_image'] ?? null
                        );
                        ?>

                        <div class="bchem-product-card">

                            <div class="bchem-product-image">

                                <?php if ($productImage): ?>

                                    <img
                                        src="<?= e($productImage) ?>"
                                        alt="<?= e($product['name']) ?>"
                                    >

                                <?php else: ?>

                                    <div class="bchem-product-placeholder">
                                        <i class="bi bi-droplet"></i>
                                    </div>

                                <?php endif; ?>


                                <div class="bchem-featured-badge">
                                    FEATURED
                                </div>

                            </div>


                            <div class="bchem-product-body">

                                <div class="bchem-product-category">

                                    <?= e(
                                        $product['category_name']
                                        ?: 'Betachem Product'
                                    ) ?>

                                </div>

                                <div class="bchem-product-name">
                                    <?= e($product['name']) ?>
                                </div>

                                <div class="bchem-product-sku">

                                    SKU:
                                    <?= e($product['sku']) ?>

                                </div>


                                <div class="bchem-product-footer">

                                    <div class="bchem-product-price">
                                        <?= money($product['price']) ?>
                                    </div>

                                    <a
                                        href="<?= $baseUrl; ?>/product.php?slug=<?= urlencode($product['slug']) ?>"
                                        class="bchem-product-link"
                                        aria-label="View <?= e($product['name']) ?>"
                                    >
                                        <i class="bi bi-arrow-up-right"></i>
                                    </a>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

        </section>

    <?php endif; ?>


    <!-- ==============================================================
         CORPORATE SECTION
    ============================================================== -->

    <section class="bchem-section bchem-corporate">

        <div class="container">

            <div class="bchem-corporate-grid">


                <div>

                    <div class="bchem-corporate-label">
                        Why Betachem
                    </div>

                    <h2>
                        More than products.
                        <br>
                        Practical chemical solutions.
                    </h2>

                    <p>
                        Betachem brings together chemical products,
                        technical knowledge and industry-focused
                        solutions. Our online catalogue makes it easier
                        to find the products required for your
                        laboratory and operational needs.
                    </p>


                    <div class="bchem-corporate-points">

                        <div class="bchem-corporate-point">

                            <strong>
                                Quality Focus
                            </strong>

                            <span>
                                Products selected for professional use.
                            </span>

                        </div>


                        <div class="bchem-corporate-point">

                            <strong>
                                Industry Experience
                            </strong>

                            <span>
                                Solutions built around real applications.
                            </span>

                        </div>


                        <div class="bchem-corporate-point">

                            <strong>
                                Reliable Supply
                            </strong>

                            <span>
                                Convenient online product ordering.
                            </span>

                        </div>


                        <div class="bchem-corporate-point">

                            <strong>
                                Technical Approach
                            </strong>

                            <span>
                                Professional support when you need it.
                            </span>

                        </div>

                    </div>

                </div>


                <div class="bchem-corporate-panel">

                    <div class="bchem-corporate-panel-top">

                        <strong>
                            BETACHEM CATALOGUE
                        </strong>

                        <span>
                            ONLINE
                        </span>

                    </div>


                    <div class="bchem-stat">

                        <strong>
                            Chemicals
                        </strong>

                        <span>
                            Industrial &amp; Laboratory
                        </span>

                    </div>


                    <div class="bchem-stat">

                        <strong>
                            Laboratory Products
                        </strong>

                        <span>
                            Equipment &amp; Consumables
                        </span>

                    </div>


                    <div class="bchem-stat">

                        <strong>
                            Technical Solutions
                        </strong>

                        <span>
                            Industry Focused
                        </span>

                    </div>


                    <div class="bchem-stat">

                        <strong>
                            Online Ordering
                        </strong>

                        <span>
                            Simple &amp; Convenient
                        </span>

                    </div>

                </div>

            </div>

        </div>

    </section>


    <!-- ==============================================================
         LATEST PRODUCTS
    ============================================================== -->

    <?php if (!empty($latestProducts)): ?>

        <section class="bchem-section">

            <div class="container">

                <div class="bchem-section-header">

                    <div>

                        <div class="bchem-section-label">
                            Catalogue
                        </div>

                        <h2 class="bchem-section-title">
                            Latest products
                        </h2>

                    </div>

                    <a
                        href="<?= $baseUrl; ?>/shop.php"
                        class="bchem-view-all"
                    >
                        Browse Catalogue
                        <i class="bi bi-arrow-right ms-1"></i>
                    </a>

                </div>


                <div class="bchem-product-grid">

                    <?php foreach ($latestProducts as $product): ?>

                        <?php
                        $productImage = homeProductImage(
                            $product['main_image'] ?? null
                        );
                        ?>

                        <div class="bchem-product-card">

                            <div class="bchem-product-image">

                                <?php if ($productImage): ?>

                                    <img
                                        src="<?= e($productImage) ?>"
                                        alt="<?= e($product['name']) ?>"
                                    >

                                <?php else: ?>

                                    <div class="bchem-product-placeholder">
                                        <i class="bi bi-box-seam"></i>
                                    </div>

                                <?php endif; ?>

                            </div>


                            <div class="bchem-product-body">

                                <div class="bchem-product-category">

                                    <?= e(
                                        $product['category_name']
                                        ?: 'Betachem Product'
                                    ) ?>

                                </div>

                                <div class="bchem-product-name">
                                    <?= e($product['name']) ?>
                                </div>

                                <div class="bchem-product-sku">

                                    SKU:
                                    <?= e($product['sku']) ?>

                                </div>


                                <div class="bchem-product-footer">

                                    <div class="bchem-product-price">
                                        <?= money($product['price']) ?>
                                    </div>

                                    <a
                                        href="<?= $baseUrl; ?>/product.php?slug=<?= urlencode($product['slug']) ?>"
                                        class="bchem-product-link"
                                    >
                                        <i class="bi bi-arrow-up-right"></i>
                                    </a>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

        </section>

    <?php endif; ?>


    <!-- ==============================================================
         CTA
    ============================================================== -->

    <section class="bchem-cta">

        <div class="container">

            <div class="bchem-cta-box">

                <div>

                    <h2>
                        Looking for a specific product?
                    </h2>

                    <p>
                        Browse the Betachem catalogue or start your
                        search for the product you need.
                    </p>

                </div>

                <a
                    href="<?= $baseUrl; ?>/shop.php"
                    class="bchem-cta-button"
                >
                    Explore Catalogue
                    <i class="bi bi-arrow-right ms-2"></i>
                </a>

            </div>

        </div>

    </section>


</div>


<?php

require_once __DIR__ . '/includes/footer.php';

?>