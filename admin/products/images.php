<?php
/**
 * =========================================================
 * BETACHEM E-COMMERCE
 * ADMIN - PRODUCT IMAGES
 * =========================================================
 */

require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$pageTitle = "Product Images";

$baseUrl = '/betachem/betachem_e-commerce';

$productId = (int)($_GET['id'] ?? $_POST['product_id'] ?? 0);

if ($productId <= 0) {
    redirect($baseUrl . '/admin/products/index.php');
}


/*
|--------------------------------------------------------------------------
| Load Product
|--------------------------------------------------------------------------
*/

$productStmt = $conn->prepare("
    SELECT
        id,
        name,
        sku,
        main_image
    FROM products
    WHERE id = ?
    LIMIT 1
");

$productStmt->bind_param(
    "i",
    $productId
);

$productStmt->execute();

$product = $productStmt
    ->get_result()
    ->fetch_assoc();

$productStmt->close();

if (!$product) {
    redirect($baseUrl . '/admin/products/index.php');
}


/*
|--------------------------------------------------------------------------
| Messages
|--------------------------------------------------------------------------
*/

$errors = [];

$successMessage = $_SESSION['product_image_message'] ?? '';

unset($_SESSION['product_image_message']);


/*
|--------------------------------------------------------------------------
| Upload Images
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        !isset($_FILES['images']) ||
        empty($_FILES['images']['name'][0])
    ) {

        $errors[] = "Please select at least one image.";

    } else {

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


        $allowedMimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp'
        ];


        $uploadedCount = 0;


        foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {

            $error =
                $_FILES['images']['error'][$index] ?? UPLOAD_ERR_NO_FILE;

            $fileName =
                $_FILES['images']['name'][$index] ?? '';

            $fileSize =
                $_FILES['images']['size'][$index] ?? 0;


            if ($error === UPLOAD_ERR_NO_FILE) {
                continue;
            }


            if ($error !== UPLOAD_ERR_OK) {

                $errors[] =
                    "There was a problem uploading: " .
                    $fileName;

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | File Size
            |--------------------------------------------------------------------------
            */

            if ($fileSize > 5 * 1024 * 1024) {

                $errors[] =
                    $fileName .
                    " is larger than 5MB.";

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | MIME Type
            |--------------------------------------------------------------------------
            */

            $fileInfo =
                finfo_open(FILEINFO_MIME_TYPE);

            $mimeType =
                finfo_file(
                    $fileInfo,
                    $tmpName
                );

            finfo_close($fileInfo);


            if (!isset($allowedMimeTypes[$mimeType])) {

                $errors[] =
                    $fileName .
                    " is not a valid image.";

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Generate Filename
            |--------------------------------------------------------------------------
            */

            $extension =
                $allowedMimeTypes[$mimeType];

            $newFileName =
                'product_' .
                date('Ymd_His') .
                '_' .
                bin2hex(random_bytes(5)) .
                '.' .
                $extension;


            $destination =
                $uploadDirectory .
                $newFileName;


            /*
            |--------------------------------------------------------------------------
            | Move File
            |--------------------------------------------------------------------------
            */

            if (
                !move_uploaded_file(
                    $tmpName,
                    $destination
                )
            ) {

                $errors[] =
                    "Could not save " .
                    $fileName;

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Determine Primary Image
            |--------------------------------------------------------------------------
            */

            $primaryCheckStmt = $conn->prepare("
                SELECT COUNT(*) AS total
                FROM product_images
                WHERE product_id = ?
                  AND is_primary = 1
            ");

            $primaryCheckStmt->bind_param(
                "i",
                $productId
            );

            $primaryCheckStmt->execute();

            $primaryCheck =
                $primaryCheckStmt
                    ->get_result()
                    ->fetch_assoc();

            $primaryCheckStmt->close();


            $isPrimary =
                ((int)$primaryCheck['total'] === 0)
                    ? 1
                    : 0;


            /*
            |--------------------------------------------------------------------------
            | Sort Order
            |--------------------------------------------------------------------------
            */

            $sortStmt = $conn->prepare("
                SELECT COALESCE(MAX(sort_order), -1) + 1 AS next_order
                FROM product_images
                WHERE product_id = ?
            ");

            $sortStmt->bind_param(
                "i",
                $productId
            );

            $sortStmt->execute();

            $sortData =
                $sortStmt
                    ->get_result()
                    ->fetch_assoc();

            $sortStmt->close();

            $sortOrder =
                (int)$sortData['next_order'];


            /*
            |--------------------------------------------------------------------------
            | Insert Image
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
                    ?, ?, ?, ?, ?, NOW(), NOW()
                )
            ");

            $imageStmt->bind_param(
                "issii",
                $productId,
                $newFileName,
                $product['name'],
                $sortOrder,
                $isPrimary
            );

            $imageStmt->execute();

            $imageStmt->close();


            /*
            |--------------------------------------------------------------------------
            | Set Main Image
            |--------------------------------------------------------------------------
            */

            if ($isPrimary) {

                $updateMainImage = $conn->prepare("
                    UPDATE products
                    SET
                        main_image = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");

                $updateMainImage->bind_param(
                    "si",
                    $newFileName,
                    $productId
                );

                $updateMainImage->execute();

                $updateMainImage->close();
            }


            $uploadedCount++;
        }


        if ($uploadedCount > 0) {

            $_SESSION['product_image_message'] =
                $uploadedCount .
                " image(s) uploaded successfully.";

            redirect(
                $baseUrl .
                '/admin/products/images.php?id=' .
                $productId
            );
        }
    }
}


/*
|--------------------------------------------------------------------------
| Delete Image
|--------------------------------------------------------------------------
*/

if (
    isset($_GET['delete_image']) &&
    (int)$_GET['delete_image'] > 0
) {

    $imageId =
        (int)$_GET['delete_image'];


    $imageStmt = $conn->prepare("
        SELECT
            id,
            image_path,
            is_primary
        FROM product_images
        WHERE id = ?
          AND product_id = ?
        LIMIT 1
    ");

    $imageStmt->bind_param(
        "ii",
        $imageId,
        $productId
    );

    $imageStmt->execute();

    $image =
        $imageStmt
            ->get_result()
            ->fetch_assoc();

    $imageStmt->close();


    if ($image) {

        /*
        |--------------------------------------------------------------------------
        | Count Images
        |--------------------------------------------------------------------------
        */

        $countStmt = $conn->prepare("
            SELECT COUNT(*) AS total
            FROM product_images
            WHERE product_id = ?
        ");

        $countStmt->bind_param(
            "i",
            $productId
        );

        $countStmt->execute();

        $countData =
            $countStmt
                ->get_result()
                ->fetch_assoc();

        $countStmt->close();

        $imageCount =
            (int)$countData['total'];


        /*
        |--------------------------------------------------------------------------
        | Delete Image
        |--------------------------------------------------------------------------
        */

        $deleteStmt = $conn->prepare("
            DELETE FROM product_images
            WHERE id = ?
              AND product_id = ?
        ");

        $deleteStmt->bind_param(
            "ii",
            $imageId,
            $productId
        );

        $deleteStmt->execute();

        $deleteStmt->close();


        /*
        |--------------------------------------------------------------------------
        | Remove File
        |--------------------------------------------------------------------------
        */

        $imagePath =
            __DIR__ .
            '/../../uploads/products/' .
            basename($image['image_path']);

        if (is_file($imagePath)) {
            unlink($imagePath);
        }


        /*
        |--------------------------------------------------------------------------
        | If Primary Was Deleted
        |--------------------------------------------------------------------------
        */

        if ((int)$image['is_primary'] === 1) {

            $nextStmt = $conn->prepare("
                SELECT id, image_path
                FROM product_images
                WHERE product_id = ?
                ORDER BY sort_order ASC, id ASC
                LIMIT 1
            ");

            $nextStmt->bind_param(
                "i",
                $productId
            );

            $nextStmt->execute();

            $nextImage =
                $nextStmt
                    ->get_result()
                    ->fetch_assoc();

            $nextStmt->close();


            if ($nextImage) {

                $makePrimaryStmt = $conn->prepare("
                    UPDATE product_images
                    SET
                        is_primary = 1,
                        updated_at = NOW()
                    WHERE id = ?
                ");

                $makePrimaryStmt->bind_param(
                    "i",
                    $nextImage['id']
                );

                $makePrimaryStmt->execute();

                $makePrimaryStmt->close();


                $updateProductStmt = $conn->prepare("
                    UPDATE products
                    SET
                        main_image = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");

                $updateProductStmt->bind_param(
                    "si",
                    $nextImage['image_path'],
                    $productId
                );

                $updateProductStmt->execute();

                $updateProductStmt->close();

            } else {

                $updateProductStmt = $conn->prepare("
                    UPDATE products
                    SET
                        main_image = NULL,
                        updated_at = NOW()
                    WHERE id = ?
                ");

                $updateProductStmt->bind_param(
                    "i",
                    $productId
                );

                $updateProductStmt->execute();

                $updateProductStmt->close();
            }
        }


        $_SESSION['product_image_message'] =
            "Image deleted successfully.";

        redirect(
            $baseUrl .
            '/admin/products/images.php?id=' .
            $productId
        );
    }
}


/*
|--------------------------------------------------------------------------
| Set Primary Image
|--------------------------------------------------------------------------
*/

if (
    isset($_GET['primary']) &&
    (int)$_GET['primary'] > 0
) {

    $imageId =
        (int)$_GET['primary'];


    /*
    |--------------------------------------------------------------------------
    | Remove Existing Primary
    |--------------------------------------------------------------------------
    */

    $resetStmt = $conn->prepare("
        UPDATE product_images
        SET
            is_primary = 0,
            updated_at = NOW()
        WHERE product_id = ?
    ");

    $resetStmt->bind_param(
        "i",
        $productId
    );

    $resetStmt->execute();

    $resetStmt->close();


    /*
    |--------------------------------------------------------------------------
    | Set New Primary
    |--------------------------------------------------------------------------
    */

    $primaryStmt = $conn->prepare("
        SELECT
            id,
            image_path
        FROM product_images
        WHERE id = ?
          AND product_id = ?
        LIMIT 1
    ");

    $primaryStmt->bind_param(
        "ii",
        $imageId,
        $productId
    );

    $primaryStmt->execute();

    $primaryImage =
        $primaryStmt
            ->get_result()
            ->fetch_assoc();

    $primaryStmt->close();


    if ($primaryImage) {

        $makePrimaryStmt = $conn->prepare("
            UPDATE product_images
            SET
                is_primary = 1,
                updated_at = NOW()
            WHERE id = ?
        ");

        $makePrimaryStmt->bind_param(
            "i",
            $imageId
        );

        $makePrimaryStmt->execute();

        $makePrimaryStmt->close();


        /*
        |--------------------------------------------------------------------------
        | Update Product Main Image
        |--------------------------------------------------------------------------
        */

        $updateProductStmt = $conn->prepare("
            UPDATE products
            SET
                main_image = ?,
                updated_at = NOW()
            WHERE id = ?
        ");

        $updateProductStmt->bind_param(
            "si",
            $primaryImage['image_path'],
            $productId
        );

        $updateProductStmt->execute();

        $updateProductStmt->close();


        $_SESSION['product_image_message'] =
            "Primary image updated successfully.";
    }


    redirect(
        $baseUrl .
        '/admin/products/images.php?id=' .
        $productId
    );
}


/*
|--------------------------------------------------------------------------
| Load Images
|--------------------------------------------------------------------------
*/

$imagesStmt = $conn->prepare("
    SELECT *
    FROM product_images
    WHERE product_id = ?
    ORDER BY is_primary DESC, sort_order ASC, id ASC
");

$imagesStmt->bind_param(
    "i",
    $productId
);

$imagesStmt->execute();

$imagesResult =
    $imagesStmt->get_result();


include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>

            <h1 class="fw-bold mb-1">
                Product Images
            </h1>

            <p class="text-muted mb-0">
                Manage images for
                <strong><?= e($product['name']) ?></strong>
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


    <!-- Messages -->
    <?php if ($successMessage): ?>

        <div class="alert alert-success alert-dismissible fade show">

            <i class="bi bi-check-circle me-1"></i>

            <?= e($successMessage) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>


    <!-- Errors -->
    <?php if (!empty($errors)): ?>

        <div class="alert alert-danger">

            <ul class="mb-0">

                <?php foreach ($errors as $error): ?>

                    <li>
                        <?= e($error) ?>
                    </li>

                <?php endforeach; ?>

            </ul>

        </div>

    <?php endif; ?>


    <!-- Product Info -->
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <div class="row align-items-center">

                <div class="col-md-2">

                    <?php if (!empty($product['main_image'])): ?>

                        <img
                            src="<?= $baseUrl; ?>/uploads/products/<?= rawurlencode(basename($product['main_image'])) ?>"
                            alt="<?= e($product['name']) ?>"
                            class="img-fluid rounded border"
                            style="
                                width:120px;
                                height:120px;
                                object-fit:cover;
                            "
                        >

                    <?php else: ?>

                        <div
                            class="bg-light rounded d-flex align-items-center justify-content-center"
                            style="
                                width:120px;
                                height:120px;
                            "
                        >
                            <i class="bi bi-image fs-1 text-muted"></i>
                        </div>

                    <?php endif; ?>

                </div>

                <div class="col-md-10">

                    <h4 class="fw-bold mb-1">
                        <?= e($product['name']) ?>
                    </h4>

                    <div class="text-muted">
                        SKU:
                        <code><?= e($product['sku']) ?></code>
                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- Upload -->
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3">

            <h5 class="mb-0 fw-bold">
                Upload Images
            </h5>

        </div>

        <div class="card-body">

            <form
                method="POST"
                action="<?= $baseUrl; ?>/admin/products/images.php?id=<?= $productId ?>"
                enctype="multipart/form-data"
            >

                <input
                    type="hidden"
                    name="product_id"
                    value="<?= $productId ?>"
                >

                <div class="row g-3 align-items-end">

                    <div class="col-lg-9">

                        <label class="form-label fw-semibold">
                            Select Images
                        </label>

                        <input
                            type="file"
                            name="images[]"
                            class="form-control"
                            accept=".jpg,.jpeg,.png,.webp"
                            multiple
                            required
                        >

                        <small class="text-muted">
                            You can select multiple JPG, PNG or WEBP images.
                            Maximum 5MB per image.
                        </small>

                    </div>

                    <div class="col-lg-3">

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            <i class="bi bi-cloud-upload me-1"></i>
                            Upload Images
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- Gallery -->
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white py-3">

            <h5 class="mb-0 fw-bold">
                Product Gallery
            </h5>

        </div>

        <div class="card-body">

            <?php if ($imagesResult->num_rows > 0): ?>

                <div class="row g-4">

                    <?php while ($image = $imagesResult->fetch_assoc()): ?>

                        <div class="col-sm-6 col-md-4 col-lg-3">

                            <div class="card h-100 border">

                                <div
                                    class="position-relative bg-light"
                                    style="height:220px;"
                                >

                                    <img
                                        src="<?= $baseUrl; ?>/uploads/products/<?= rawurlencode(basename($image['image_path'])) ?>"
                                        alt="<?= e($image['alt_text']) ?>"
                                        class="w-100 h-100"
                                        style="object-fit:cover;"
                                    >

                                    <?php if ($image['is_primary']): ?>

                                        <span
                                            class="badge bg-primary position-absolute top-0 start-0 m-2"
                                        >
                                            <i class="bi bi-star-fill me-1"></i>
                                            Primary
                                        </span>

                                    <?php endif; ?>

                                </div>


                                <div class="card-body">

                                    <div class="small text-muted mb-3">

                                        <?= e($image['image_path']) ?>

                                    </div>


                                    <div class="d-grid gap-2">

                                        <?php if (!$image['is_primary']): ?>

                                            <a
                                                href="<?= $baseUrl; ?>/admin/products/images.php?id=<?= $productId ?>&primary=<?= (int)$image['id'] ?>"
                                                class="btn btn-sm btn-outline-primary"
                                            >
                                                <i class="bi bi-star me-1"></i>
                                                Make Primary
                                            </a>

                                        <?php endif; ?>


                                        <a
                                            href="<?= $baseUrl; ?>/admin/products/images.php?id=<?= $productId ?>&delete_image=<?= (int)$image['id'] ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Are you sure you want to delete this image?');"
                                        >
                                            <i class="bi bi-trash me-1"></i>
                                            Delete Image
                                        </a>

                                    </div>

                                </div>

                            </div>

                        </div>

                    <?php endwhile; ?>

                </div>

            <?php else: ?>

                <div class="text-center py-5">

                    <i class="bi bi-images fs-1 text-muted"></i>

                    <h5 class="mt-3">
                        No Product Images
                    </h5>

                    <p class="text-muted mb-0">
                        Upload images above to create the product gallery.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<?php
include __DIR__ . '/../includes/footer.php';
?>