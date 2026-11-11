<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

/*
|--------------------------------------------------------------------------
| Get Active Categories
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        c.id,
        c.name,
        c.slug,
        c.description,
        c.image,
        c.sort_order,
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
    ORDER BY
        c.sort_order ASC,
        c.name ASC
");

$stmt->execute();

$categories = $stmt->get_result();

$stmt->close();

require_once __DIR__ . '/includes/header.php';

?>

<!-- =========================
     PAGE HEADER
========================= -->

<section class="py-5 bg-light">

    <div class="container">

        <div class="text-center">

            <span class="text-primary fw-semibold">
                BETACHEM CATEGORIES
            </span>

            <h1 class="display-5 fw-bold mt-2 mb-3">
                Shop by Category
            </h1>

            <p class="text-muted mx-auto mb-0" style="max-width: 700px;">

                Explore our range of laboratory, industrial,
                safety and chemical products.

            </p>

        </div>

    </div>

</section>


<!-- =========================
     CATEGORIES
========================= -->

<div class="container py-5">

    <?php if ($categories->num_rows > 0): ?>

        <div class="row g-4">

            <?php while ($category = $categories->fetch_assoc()): ?>

                <div class="col-sm-6 col-lg-4 col-xl-3">

                    <div class="card border-0 shadow-sm h-100 category-card">

                        <!-- CATEGORY IMAGE -->

                        <div class="category-image-wrapper">

                            <?php

                            $categoryImage = '';

                            if (!empty($category['image'])) {

                                $categoryImage = $category['image'];

                                /*
                                |--------------------------------------------------------------------------
                                | Handle stored image path
                                |--------------------------------------------------------------------------
                                */

                                if (
                                    !str_starts_with(
                                        $categoryImage,
                                        'http://'
                                    ) &&
                                    !str_starts_with(
                                        $categoryImage,
                                        'https://'
                                    )
                                ) {

                                    if (
                                        !str_starts_with(
                                            $categoryImage,
                                            'uploads/'
                                        )
                                    ) {

                                        $categoryImage =
                                            'uploads/categories/' .
                                            basename($categoryImage);

                                    }

                                }

                            }

                            ?>

                            <?php if ($categoryImage !== ''): ?>

                                <img
                                    src="/betachem_e-commerce/<?= e($categoryImage) ?>"
                                    alt="<?= e($category['name']) ?>"
                                    class="category-image"
                                >

                            <?php else: ?>

                                <div class="category-placeholder">

                                    <i class="bi bi-box-seam"></i>

                                </div>

                            <?php endif; ?>

                        </div>


                        <!-- CATEGORY CONTENT -->

                        <div class="card-body p-4 d-flex flex-column">

                            <h4 class="fw-bold mb-2">

                                <?= e($category['name']) ?>

                            </h4>


                            <?php if (!empty($category['description'])): ?>

                                <p class="text-muted small mb-3">

                                    <?= e(
                                        mb_strimwidth(
                                            $category['description'],
                                            0,
                                            110,
                                            '...'
                                        )
                                    ) ?>

                                </p>

                            <?php else: ?>

                                <p class="text-muted small mb-3">

                                    Browse products in this category.

                                </p>

                            <?php endif; ?>


                            <div class="mt-auto">

                                <div class="d-flex justify-content-between align-items-center mb-3">

                                    <span class="text-muted small">

                                        <?= (int) $category['product_count'] ?>

                                        <?= (int) $category['product_count'] === 1
                                            ? 'Product'
                                            : 'Products'
                                        ?>

                                    </span>

                                    <span class="text-primary">

                                        <i class="bi bi-arrow-right"></i>

                                    </span>

                                </div>


                                <a
                                    href="shop.php?category=<?= urlencode($category['slug']) ?>"
                                    class="btn btn-outline-primary w-100"
                                >

                                    View Products

                                </a>

                            </div>

                        </div>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <!-- EMPTY STATE -->

        <div class="text-center py-5">

            <div
                class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                style="width: 80px; height: 80px;"
            >

                <i class="bi bi-grid fs-2"></i>

            </div>

            <h3 class="fw-bold">
                No Categories Available
            </h3>

            <p class="text-muted">
                There are currently no active product categories.
            </p>

            <a
                href="shop.php"
                class="btn btn-primary"
            >
                Browse Shop
            </a>

        </div>

    <?php endif; ?>

</div>


<!-- =========================
     CATEGORY CARD STYLING
========================= -->

<style>

.category-card {
    transition:
        transform 0.25s ease,
        box-shadow 0.25s ease;
    overflow: hidden;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.10) !important;
}

.category-image-wrapper {
    height: 190px;
    overflow: hidden;
    background: #f5f8fc;
}

.category-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.3s ease;
}

.category-card:hover .category-image {
    transform: scale(1.04);
}

.category-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(
        135deg,
        #eef6ff,
        #f8fbff
    );
    color: #1D88E9;
    font-size: 3.5rem;
}

</style>


<?php require_once __DIR__ . '/includes/footer.php'; ?>