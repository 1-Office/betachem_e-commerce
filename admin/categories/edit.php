<?php

require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$pageTitle = "Edit Category";

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
| Get Category
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT *
    FROM categories
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $id);

$stmt->execute();

$category = $stmt->get_result()->fetch_assoc();

$stmt->close();


if (!$category) {

    header(
        'Location: ' .
        $baseUrl .
        '/admin/categories/index.php'
    );

    exit;
}


$error = "";


/*
|--------------------------------------------------------------------------
| Process Update
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $sort_order = (int) ($_POST['sort_order'] ?? 0);

    $is_active = isset($_POST['is_active']) ? 1 : 0;


    if ($name === '') {

        $error = "Category name is required.";

    } else {

        $slug = createSlug($name);


        /*
        |--------------------------------------------------------------------------
        | Check Duplicate
        |--------------------------------------------------------------------------
        */

        $check = $conn->prepare("
            SELECT id
            FROM categories
            WHERE (name = ? OR slug = ?)
            AND id != ?
            LIMIT 1
        ");

        $check->bind_param(
            "ssi",
            $name,
            $slug,
            $id
        );

        $check->execute();

        $existing =
            $check->get_result()->fetch_assoc();

        $check->close();


        if ($existing) {

            $error =
                "Another category already uses this name.";

        } else {


            /*
            |--------------------------------------------------------------------------
            | Existing Image
            |--------------------------------------------------------------------------
            */

            $imageName = $category['image'];


            /*
            |--------------------------------------------------------------------------
            | New Image
            |--------------------------------------------------------------------------
            */

            if (
                isset($_FILES['image']) &&
                $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
            ) {

                if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {

                    $error =
                        "There was a problem uploading the image.";

                } else {

                    $allowedTypes = [
                        'image/jpeg',
                        'image/png',
                        'image/webp'
                    ];

                    $fileType =
                        mime_content_type(
                            $_FILES['image']['tmp_name']
                        );


                    if (!in_array($fileType, $allowedTypes, true)) {

                        $error =
                            "Only JPG, PNG and WEBP images are allowed.";

                    } elseif (
                        $_FILES['image']['size'] > 5 * 1024 * 1024
                    ) {

                        $error =
                            "The image must be smaller than 5MB.";

                    } else {

                        $extension =
                            strtolower(
                                pathinfo(
                                    $_FILES['image']['name'],
                                    PATHINFO_EXTENSION
                                )
                            );

                        $newImage =
                            'category_' .
                            time() .
                            '_' .
                            bin2hex(random_bytes(4)) .
                            '.' .
                            $extension;


                        $uploadDirectory =
                            __DIR__ .
                            '/../../uploads/categories/';


                        if (!is_dir($uploadDirectory)) {

                            mkdir(
                                $uploadDirectory,
                                0755,
                                true
                            );

                        }


                        if (
                            move_uploaded_file(
                                $_FILES['image']['tmp_name'],
                                $uploadDirectory . $newImage
                            )
                        ) {

                            if (
                                !empty($category['image']) &&
                                file_exists(
                                    $uploadDirectory .
                                    $category['image']
                                )
                            ) {

                                unlink(
                                    $uploadDirectory .
                                    $category['image']
                                );
                            }

                            $imageName = $newImage;

                        } else {

                            $error =
                                "Unable to save the uploaded image.";

                        }
                    }
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Update
            |--------------------------------------------------------------------------
            */

            if ($error === '') {

                $stmt = $conn->prepare("
                    UPDATE categories
                    SET
                        name = ?,
                        slug = ?,
                        description = ?,
                        image = ?,
                        is_active = ?,
                        sort_order = ?
                    WHERE id = ?
                ");

                $stmt->bind_param(
                    "ssssiii",
                    $name,
                    $slug,
                    $description,
                    $imageName,
                    $is_active,
                    $sort_order,
                    $id
                );

                $stmt->execute();

                $stmt->close();


                header(
                    'Location: ' .
                    $baseUrl .
                    '/admin/categories/index.php'
                );

                exit;
            }
        }
    }
}


require_once __DIR__ . '/../includes/header.php';

?>


<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h1 class="fw-bold mb-1">
            Edit Category
        </h1>

        <p class="text-muted mb-0">
            Update category information.
        </p>

    </div>

    <a
        href="<?= $baseUrl; ?>/admin/categories/index.php"
        class="btn btn-outline-primary"
    >

        <i class="bi bi-arrow-left me-2"></i>

        Back to Categories

    </a>

</div>


<div class="row justify-content-center">

    <div class="col-lg-8">

        <div class="card border-0 shadow-sm">

            <div class="card-body p-4">


                <?php if ($error): ?>

                    <div class="alert alert-danger">

                        <i class="bi bi-exclamation-circle me-2"></i>

                        <?= e($error) ?>

                    </div>

                <?php endif; ?>


                <form
                    method="POST"
                    action="<?= $baseUrl; ?>/admin/categories/edit.php?id=<?= $id ?>"
                    enctype="multipart/form-data"
                >


                    <div class="mb-4">

                        <label
                            for="name"
                            class="form-label fw-semibold"
                        >
                            Category Name
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="name"
                            name="name"
                            value="<?= e($category['name']) ?>"
                            required
                        >

                    </div>


                    <div class="mb-4">

                        <label
                            for="description"
                            class="form-label fw-semibold"
                        >
                            Description
                        </label>

                        <textarea
                            class="form-control"
                            id="description"
                            name="description"
                            rows="4"
                        ><?= e($category['description'] ?? '') ?></textarea>

                    </div>


                    <?php if (!empty($category['image'])): ?>

                        <div class="mb-4">

                            <label class="form-label fw-semibold">
                                Current Image
                            </label>

                            <div>

                                <img
                                    src="<?= $baseUrl; ?>/uploads/categories/<?= rawurlencode($category['image']) ?>"
                                    alt="<?= e($category['name']) ?>"
                                    style="
                                        width:150px;
                                        height:150px;
                                        object-fit:cover;
                                        border-radius:12px;
                                    "
                                >

                            </div>

                        </div>

                    <?php endif; ?>


                    <div class="mb-4">

                        <label
                            for="image"
                            class="form-label fw-semibold"
                        >
                            Replace Image
                        </label>

                        <input
                            type="file"
                            class="form-control"
                            id="image"
                            name="image"
                            accept=".jpg,.jpeg,.png,.webp"
                        >

                        <div class="form-text">
                            Leave empty to keep the current image.
                        </div>

                    </div>


                    <div class="mb-4">

                        <label
                            for="sort_order"
                            class="form-label fw-semibold"
                        >
                            Sort Order
                        </label>

                        <input
                            type="number"
                            class="form-control"
                            id="sort_order"
                            name="sort_order"
                            value="<?= (int) $category['sort_order'] ?>"
                            min="0"
                        >

                    </div>


                    <div class="form-check form-switch mb-4">

                        <input
                            class="form-check-input"
                            type="checkbox"
                            role="switch"
                            id="is_active"
                            name="is_active"
                            <?= $category['is_active'] ? 'checked' : '' ?>
                        >

                        <label
                            class="form-check-label fw-semibold"
                            for="is_active"
                        >
                            Category Active
                        </label>

                    </div>


                    <div class="d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >

                            <i class="bi bi-save me-2"></i>

                            Save Changes

                        </button>


                        <a
                            href="<?= $baseUrl; ?>/admin/categories/index.php"
                            class="btn btn-outline-secondary"
                        >
                            Cancel
                        </a>

                    </div>


                </form>

            </div>

        </div>

    </div>

</div>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>