<?php

require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$pageTitle = "Add Category";

$baseUrl = '/betachem/betachem_e-commerce';

$error = "";
$success = "";


/*
|--------------------------------------------------------------------------
| Process Form
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $sort_order = (int) ($_POST['sort_order'] ?? 0);

    $is_active = isset($_POST['is_active']) ? 1 : 0;


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($name === '') {

        $error = "Category name is required.";

    } else {

        $slug = createSlug($name);


        /*
        |--------------------------------------------------------------------------
        | Check Duplicate Name
        |--------------------------------------------------------------------------
        */

        $check = $conn->prepare("
            SELECT id
            FROM categories
            WHERE name = ?
               OR slug = ?
            LIMIT 1
        ");

        $check->bind_param(
            "ss",
            $name,
            $slug
        );

        $check->execute();

        $existing = $check->get_result()->fetch_assoc();

        $check->close();


        if ($existing) {

            $error = "A category with this name already exists.";

        } else {


            /*
            |--------------------------------------------------------------------------
            | Image Upload
            |--------------------------------------------------------------------------
            */

            $imageName = null;

            if (
                isset($_FILES['image']) &&
                $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
            ) {

                if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {

                    $error = "There was a problem uploading the image.";

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

                        $imageName =
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


                        $uploadPath =
                            $uploadDirectory . $imageName;


                        if (
                            !move_uploaded_file(
                                $_FILES['image']['tmp_name'],
                                $uploadPath
                            )
                        ) {

                            $error =
                                "Unable to save the uploaded image.";

                            $imageName = null;
                        }
                    }
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Insert Category
            |--------------------------------------------------------------------------
            */

            if ($error === '') {

                $stmt = $conn->prepare("
                    INSERT INTO categories (
                        name,
                        slug,
                        description,
                        image,
                        is_active,
                        sort_order
                    )
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                $stmt->bind_param(
                    "ssssii",
                    $name,
                    $slug,
                    $description,
                    $imageName,
                    $is_active,
                    $sort_order
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
            Add Category
        </h1>

        <p class="text-muted mb-0">
            Create a new product category.
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
                    action="<?= $baseUrl; ?>/admin/categories/add.php"
                    enctype="multipart/form-data"
                >


                    <!-- NAME -->

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
                            placeholder="e.g. Laboratory Chemicals"
                            value="<?= e($_POST['name'] ?? '') ?>"
                            required
                        >

                        <div class="form-text">
                            The URL-friendly slug will be generated automatically.
                        </div>

                    </div>


                    <!-- DESCRIPTION -->

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
                            placeholder="Describe this category..."
                        ><?= e($_POST['description'] ?? '') ?></textarea>

                    </div>


                    <!-- IMAGE -->

                    <div class="mb-4">

                        <label
                            for="image"
                            class="form-label fw-semibold"
                        >
                            Category Image
                        </label>

                        <input
                            type="file"
                            class="form-control"
                            id="image"
                            name="image"
                            accept=".jpg,.jpeg,.png,.webp"
                        >

                        <div class="form-text">
                            JPG, PNG or WEBP. Maximum 5MB.
                        </div>

                    </div>


                    <!-- SORT ORDER -->

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
                            value="<?= e($_POST['sort_order'] ?? '0') ?>"
                            min="0"
                        >

                        <div class="form-text">
                            Lower numbers appear first.
                        </div>

                    </div>


                    <!-- ACTIVE -->

                    <div class="form-check form-switch mb-4">

                        <input
                            class="form-check-input"
                            type="checkbox"
                            role="switch"
                            id="is_active"
                            name="is_active"
                            <?= !isset($_POST['is_active']) || isset($_POST['is_active']) ? 'checked' : '' ?>
                        >

                        <label
                            class="form-check-label fw-semibold"
                            for="is_active"
                        >
                            Category Active
                        </label>

                    </div>


                    <!-- BUTTONS -->

                    <div class="d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >

                            <i class="bi bi-check-circle me-2"></i>

                            Save Category

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