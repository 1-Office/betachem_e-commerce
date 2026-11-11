<?php
/*
|--------------------------------------------------------------------------
| Betachem E-Commerce — Category Management
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$pageTitle = 'Categories';
$baseUrl = '/betachem/betachem_e-commerce';

require_once __DIR__ . '/../includes/header.php';

/*
|--------------------------------------------------------------------------
| Fetch Categories
|--------------------------------------------------------------------------
| Count products in each category without duplicating category records.
*/

$sql = "
    SELECT
        c.*,
        (
            SELECT COUNT(*)
            FROM products p
            WHERE p.category_id = c.id
        ) AS product_count
    FROM categories c
    ORDER BY c.sort_order ASC, c.name ASC
";

$result = $conn->query($sql);

$totalCategories = $result ? $result->num_rows : 0;
?>

<style>
/* =========================================================
   BETACHEM CATEGORY MANAGEMENT
========================================================= */

.betachem-categories {
    --bc-blue: #1769aa;
    --bc-dark: #102b49;
    --bc-light: #edf5fc;
    --bc-border: #e4ebf3;
    --bc-muted: #718096;

    color: var(--bc-dark);
    padding-bottom: 30px;
}

/* PAGE HERO */

.betachem-categories .category-hero {
    position: relative;
    overflow: hidden;
    background: linear-gradient(
        120deg,
        #102b49 0%,
        #1769aa 65%,
        #1d88e9 100%
    );
    color: #fff;
    border-radius: 18px;
    padding: 30px;
    margin-bottom: 26px;
    box-shadow: 0 10px 28px rgba(16, 43, 73, .12);
}

.betachem-categories .category-hero::after {
    content: "";
    position: absolute;
    width: 230px;
    height: 230px;
    right: -65px;
    top: -105px;
    border: 32px solid rgba(255, 255, 255, .08);
    transform: rotate(30deg);
    border-radius: 38px;
    pointer-events: none;
}

.betachem-categories .hero-content {
    position: relative;
    z-index: 1;
}

.betachem-categories .hero-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #d9ecff;
    font-size: .75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 10px;
}

.betachem-categories .hero-title {
    font-size: clamp(1.7rem, 3vw, 2.15rem);
    font-weight: 800;
    letter-spacing: -.7px;
    margin-bottom: 8px;
}

.betachem-categories .hero-description {
    color: rgba(255, 255, 255, .86);
    margin: 0;
    max-width: 560px;
    line-height: 1.65;
}

.betachem-categories .hero-count {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin-top: 20px;
    padding: 9px 14px;
    border: 1px solid rgba(255, 255, 255, .2);
    border-radius: 10px;
    background: rgba(255, 255, 255, .1);
    font-size: .9rem;
}

.betachem-categories .hero-count strong {
    font-size: 1.15rem;
}

.betachem-categories .btn-add-category {
    position: relative;
    z-index: 2;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    background: #fff;
    color: var(--bc-blue);
    border: 1px solid #fff;
    border-radius: 10px;
    padding: 12px 18px;
    font-weight: 700;
    text-decoration: none;
    transition: .2s ease;
    white-space: nowrap;
}

.betachem-categories .btn-add-category:hover {
    background: #edf5fc;
    border-color: #edf5fc;
    color: var(--bc-dark);
    transform: translateY(-1px);
}

/* MAIN PANEL */

.betachem-categories .category-panel {
    background: #fff;
    border: 1px solid var(--bc-border);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(16, 43, 73, .045);
}

.betachem-categories .panel-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    padding: 21px 24px;
    border-bottom: 1px solid var(--bc-border);
}

.betachem-categories .panel-heading h2 {
    font-size: 1.05rem;
    font-weight: 800;
    margin: 0 0 4px;
    color: var(--bc-dark);
}

.betachem-categories .panel-heading p {
    margin: 0;
    color: var(--bc-muted);
    font-size: .84rem;
}

.betachem-categories .panel-label {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 7px 11px;
    border-radius: 8px;
    background: var(--bc-light);
    color: var(--bc-blue);
    font-size: .78rem;
    font-weight: 700;
    white-space: nowrap;
}

/* TABLE */

.betachem-categories .table {
    margin: 0;
    min-width: 1000px;
}

.betachem-categories .table thead th {
    background: #f7faff;
    color: #63758a;
    font-size: .72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .65px;
    padding: 16px 17px;
    border-bottom: 1px solid var(--bc-border);
    white-space: nowrap;
}

.betachem-categories .table tbody td {
    padding: 16px 17px;
    border-bottom: 1px solid #edf1f6;
    vertical-align: middle;
    font-size: .88rem;
}

.betachem-categories .table tbody tr:last-child td {
    border-bottom: 0;
}

.betachem-categories .table tbody tr {
    transition: background .15s ease;
}

.betachem-categories .table tbody tr:hover {
    background: #f8fbff;
}

/* CATEGORY IMAGE */

.betachem-categories .category-image,
.betachem-categories .category-image-placeholder {
    width: 54px;
    height: 54px;
    border-radius: 12px;
    flex-shrink: 0;
}

.betachem-categories .category-image {
    display: block;
    object-fit: cover;
    border: 1px solid var(--bc-border);
    background: #f7faff;
}

.betachem-categories .category-image-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bc-light);
    color: #7aa9d6;
    border: 1px solid #dfebf8;
}

.betachem-categories .category-image-placeholder i {
    font-size: 1.35rem;
}

/* CATEGORY DETAILS */

.betachem-categories .category-name {
    display: block;
    color: var(--bc-dark);
    font-weight: 750;
    margin-bottom: 4px;
}

.betachem-categories .category-slug {
    display: block;
    color: var(--bc-muted);
    font-size: .76rem;
    overflow-wrap: anywhere;
}

.betachem-categories .category-description {
    color: #65758a;
    line-height: 1.55;
    min-width: 140px;
    max-width: 250px;
    overflow-wrap: anywhere;
}

/* PRODUCT COUNT */

.betachem-categories .product-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    padding: 7px 10px;
    border-radius: 8px;
    background: #eaf3ff;
    color: #1769aa;
    font-weight: 800;
    font-size: .82rem;
}

/* STATUS */

.betachem-categories .status-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border-radius: 20px;
    font-size: .75rem;
    font-weight: 750;
    white-space: nowrap;
}

.betachem-categories .status-pill::before {
    content: "";
    width: 6px;
    height: 6px;
    display: inline-block;
    border-radius: 50%;
    background: currentColor;
}

.betachem-categories .status-active {
    background: #e8f7ef;
    color: #198754;
}

.betachem-categories .status-inactive {
    background: #eef1f5;
    color: #667085;
}

/* SORT ORDER */

.betachem-categories .sort-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 34px;
    min-height: 34px;
    border: 1px solid var(--bc-border);
    border-radius: 8px;
    color: #52657b;
    font-weight: 700;
    font-size: .82rem;
}

/* ACTION BUTTONS */

.betachem-categories .action-group {
    display: inline-flex;
    align-items: center;
    justify-content: flex-end;
    flex-wrap: wrap;
    gap: 8px;
}

.betachem-categories .action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-height: 38px;
    padding: 8px 11px;
    border-radius: 9px;
    text-decoration: none !important;
    border: 1px solid transparent;
    font-size: .8rem;
    font-weight: 700;
    line-height: 1.2;
    opacity: 1;
    visibility: visible;
    box-shadow: 0 2px 6px rgba(16, 43, 73, .10);
    transition: all .2s ease;
}

.betachem-categories .action-btn i {
    font-size: .95rem;
}

/* EDIT */

.betachem-categories a.action-edit,
.betachem-categories a.action-edit:visited {
    background: #1769aa;
    border-color: #1769aa;
    color: #fff;
}

.betachem-categories a.action-edit:hover,
.betachem-categories a.action-edit:focus {
    background: #102b49;
    border-color: #102b49;
    color: #fff;
    transform: translateY(-2px);
}

/* DELETE */

.betachem-categories a.action-delete,
.betachem-categories a.action-delete:visited {
    background: #dc3545;
    border-color: #dc3545;
    color: #fff;
}

.betachem-categories a.action-delete:hover,
.betachem-categories a.action-delete:focus {
    background: #b02a37;
    border-color: #b02a37;
    color: #fff;
    transform: translateY(-2px);
}

/* EMPTY STATE */

.betachem-categories .empty-state {
    padding: 65px 24px;
    text-align: center;
}

.betachem-categories .empty-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 76px;
    height: 76px;
    margin: 0 auto 20px;
    border-radius: 22px;
    background: var(--bc-light);
    color: var(--bc-blue);
    font-size: 2rem;
}

.betachem-categories .empty-state h3 {
    color: var(--bc-dark);
    font-size: 1.2rem;
    font-weight: 800;
    margin-bottom: 8px;
}

.betachem-categories .empty-state p {
    color: var(--bc-muted);
    max-width: 390px;
    margin: 0 auto 22px;
    line-height: 1.7;
}

.betachem-categories .empty-add {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 11px 16px;
    background: var(--bc-blue);
    color: #fff;
    text-decoration: none;
    border-radius: 9px;
    font-weight: 700;
    transition: .2s ease;
}

.betachem-categories .empty-add:hover {
    background: var(--bc-dark);
    color: #fff;
}

/* RESPONSIVE */

@media (max-width: 767.98px) {
    .betachem-categories .category-hero {
        padding: 23px 20px;
        border-radius: 14px;
    }

    .betachem-categories .hero-actions {
        margin-top: 22px;
    }

    .betachem-categories .btn-add-category {
        width: 100%;
    }

    .betachem-categories .panel-heading {
        padding: 18px;
        align-items: flex-start;
    }

    .betachem-categories .panel-label {
        display: none;
    }

    .betachem-categories .action-group {
        flex-direction: column;
        align-items: stretch;
    }

    .betachem-categories .action-btn {
        justify-content: center;
    }
}
</style>

<div class="betachem-categories">

    <!-- PAGE HERO -->
    <section class="category-hero">
        <div class="row align-items-center">
            <div class="col-lg-8 hero-content">

                <div class="hero-eyebrow">
                    <i class="bi bi-grid-3x3-gap-fill"></i>
                    Store Management
                </div>

                <h1 class="hero-title">Product Categories</h1>

                <p class="hero-description">
                    Organise your product catalogue, manage category visibility,
                    and keep your online store structured and easy to navigate.
                </p>

                <div class="hero-count">
                    <i class="bi bi-collection"></i>
                    <span>
                        <strong><?= (int) $totalCategories ?></strong>
                        <?= $totalCategories === 1 ? 'category' : 'categories' ?>
                        in your catalogue
                    </span>
                </div>

            </div>

            <div class="col-lg-4 hero-actions text-lg-end">
                <a
                    href="<?= $baseUrl ?>/admin/categories/add.php"
                    class="btn-add-category"
                >
                    <i class="bi bi-plus-circle-fill"></i>
                    Add New Category
                </a>
            </div>
        </div>
    </section>

    <!-- CATEGORY TABLE -->
    <section class="category-panel">

        <div class="panel-heading">
            <div>
                <h2>
                    <i class="bi bi-list-ul me-2"></i>
                    All Categories
                </h2>
                <p>View and manage your store's product categories.</p>
            </div>

            <div class="panel-label">
                <i class="bi bi-shield-check"></i>
                Catalogue Management
            </div>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>

            <div class="table-responsive">
                <table class="table align-middle">

                    <thead>
                        <tr>
                            <th class="ps-4">Image</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th>Sort Order</th>
                            <th class="text-end pe-4" style="min-width: 180px;">
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php while ($category = $result->fetch_assoc()): ?>

                        <?php
                        $description = trim($category['description'] ?? '');

                        if (strlen($description) > 70) {
                            $description = substr($description, 0, 70) . '...';
                        }

                        $categoryImage = trim($category['image'] ?? '');
                        $categoryName = $category['name'] ?? 'Unnamed category';
                        $categoryId = (int) $category['id'];
                        ?>

                        <tr>

                            <!-- IMAGE -->
                            <td class="ps-4">
                                <?php if ($categoryImage !== ''): ?>

                                    <img
                                        src="<?= $baseUrl ?>/uploads/categories/<?= rawurlencode($categoryImage) ?>"
                                        alt="<?= e($categoryName) ?>"
                                        class="category-image"
                                        loading="lazy"
                                    >

                                <?php else: ?>

                                    <div
                                        class="category-image-placeholder"
                                        aria-label="No category image"
                                    >
                                        <i class="bi bi-image"></i>
                                    </div>

                                <?php endif; ?>
                            </td>

                            <!-- CATEGORY -->
                            <td>
                                <span class="category-name">
                                    <?= e($categoryName) ?>
                                </span>

                                <span class="category-slug">
                                    <?= e($category['slug'] ?? '') ?>
                                </span>
                            </td>

                            <!-- DESCRIPTION -->
                            <td>
                                <div class="category-description">
                                    <?= e(
                                        $description !== ''
                                            ? $description
                                            : 'No description provided'
                                    ) ?>
                                </div>
                            </td>

                            <!-- PRODUCT COUNT -->
                            <td>
                                <span
                                    class="product-count"
                                    title="Products in this category"
                                >
                                    <i class="bi bi-box-seam me-1"></i>
                                    <?= (int) $category['product_count'] ?>
                                </span>
                            </td>

                            <!-- STATUS -->
                            <td>
                                <?php if ((int) ($category['is_active'] ?? 0) === 1): ?>

                                    <span class="status-pill status-active">
                                        Active
                                    </span>

                                <?php else: ?>

                                    <span class="status-pill status-inactive">
                                        Inactive
                                    </span>

                                <?php endif; ?>
                            </td>

                            <!-- SORT ORDER -->
                            <td>
                                <span class="sort-number">
                                    <?= (int) ($category['sort_order'] ?? 0) ?>
                                </span>
                            </td>

                            <!-- ACTIONS -->
                            <td class="text-end pe-4">
                                <div class="action-group">

                                    <a
                                        href="<?= $baseUrl ?>/admin/categories/edit.php?id=<?= $categoryId ?>"
                                        class="action-btn action-edit"
                                        title="Edit this category"
                                        aria-label="Edit <?= e($categoryName) ?>"
                                    >
                                        <i class="bi bi-pencil-square"></i>
                                        <span>Edit</span>
                                    </a>

                                    <a
                                        href="<?= $baseUrl ?>/admin/categories/delete.php?id=<?= $categoryId ?>"
                                        class="action-btn action-delete"
                                        title="Delete this category"
                                        aria-label="Delete <?= e($categoryName) ?>"
                                        onclick="return confirm('Are you sure you want to delete this category?');"
                                    >
                                        <i class="bi bi-trash3"></i>
                                        <span>Delete</span>
                                    </a>

                                </div>
                            </td>

                        </tr>

                    <?php endwhile; ?>
                    </tbody>

                </table>
            </div>

        <?php else: ?>

            <div class="empty-state">

                <div class="empty-icon">
                    <i class="bi bi-grid-1x2"></i>
                </div>

                <h3>No Categories Yet</h3>

                <p>
                    Your catalogue doesn't have any categories yet.
                    Create your first category to start organising your products.
                </p>

                <a
                    href="<?= $baseUrl ?>/admin/categories/add.php"
                    class="empty-add"
                >
                    <i class="bi bi-plus-circle"></i>
                    Create First Category
                </a>

            </div>

        <?php endif; ?>

    </section>

</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>