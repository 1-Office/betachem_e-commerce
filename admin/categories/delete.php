<?php

require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$baseUrl = '/betachem/betachem_e-commerce';

$id = (int) ($_GET['id'] ?? 0);


if ($id <= 0) {

    header(
        'Location: ' .
        $baseUrl .
        '/admin/categories/index.php'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| Check Whether Category Has Products
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM products
    WHERE category_id = ?
");

$stmt->bind_param("i", $id);

$stmt->execute();

$result = $stmt->get_result();

$row = $result->fetch_assoc();

$stmt->close();


if ((int) $row['total'] > 0) {

    die("
        <div style='
            font-family:Arial;
            max-width:600px;
            margin:100px auto;
            text-align:center;
        '>

            <h2>Category Cannot Be Deleted</h2>

            <p>
                This category contains products.
                Remove or move those products first.
            </p>

            <a href='{$baseUrl}/admin/categories/index.php'>
                Back to Categories
            </a>

        </div>
    ");
}


/*
|--------------------------------------------------------------------------
| Get Image
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT image
    FROM categories
    WHERE id = ?
");

$stmt->bind_param("i", $id);

$stmt->execute();

$category = $stmt->get_result()->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| Delete Category
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    DELETE FROM categories
    WHERE id = ?
");

$stmt->bind_param("i", $id);

$stmt->execute();

$stmt->close();


/*
|--------------------------------------------------------------------------
| Delete Image
|--------------------------------------------------------------------------
*/

if (
    $category &&
    !empty($category['image'])
) {

    $imagePath =
        __DIR__ .
        '/../../uploads/categories/' .
        $category['image'];

    if (file_exists($imagePath)) {
        unlink($imagePath);
    }
}


/*
|--------------------------------------------------------------------------
| Redirect Back to Categories
|--------------------------------------------------------------------------
*/

header(
    'Location: ' .
    $baseUrl .
    '/admin/categories/index.php'
);

exit;