<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

requirePermission('contact_messages');

$pageTitle = 'View Contact Message';

$baseUrl = '/betachem/betachem_e-commerce';

$messageId = (int) ($_GET['id'] ?? 0);

if ($messageId <= 0) {

    header(
        'Location: ' .
        $baseUrl .
        '/admin/contact_messages/index.php'
    );

    exit;
}

$errors = [];
$success = '';


/*
|--------------------------------------------------------------------------
| Handle Update
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $newStatus = trim($_POST['status'] ?? '');
    $adminNotes = trim($_POST['admin_notes'] ?? '');

    $allowedStatuses = [
        'new',
        'read',
        'replied',
        'closed'
    ];

    if (!in_array($newStatus, $allowedStatuses, true)) {

        $errors[] = 'Please select a valid message status.';

    }

    if (empty($errors)) {

        $stmt = $conn->prepare("
            UPDATE contact_messages
            SET
                status = ?,
                admin_notes = ?
            WHERE id = ?
        ");

        $stmt->bind_param(
            'ssi',
            $newStatus,
            $adminNotes,
            $messageId
        );

        $stmt->execute();

        $stmt->close();

        $success = 'Contact message updated successfully.';
    }
}


/*
|--------------------------------------------------------------------------
| Get Message
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        id,
        user_id,
        first_name,
        last_name,
        email,
        phone,
        subject,
        message,
        status,
        admin_notes,
        created_at,
        updated_at
    FROM contact_messages
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param('i', $messageId);

$stmt->execute();

$result = $stmt->get_result();

$message = $result->fetch_assoc();

$stmt->close();


if (!$message) {

    header(
        'Location: ' .
        $baseUrl .
        '/admin/contact_messages/index.php'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| Automatically Mark New Message as Read
|--------------------------------------------------------------------------
*/

if (
    $message['status'] === 'new' &&
    $_SERVER['REQUEST_METHOD'] !== 'POST'
) {

    $updateRead = $conn->prepare("
        UPDATE contact_messages
        SET status = 'read'
        WHERE id = ?
        AND status = 'new'
    ");

    $updateRead->bind_param('i', $messageId);

    $updateRead->execute();

    $updateRead->close();

    $message['status'] = 'read';
}


/*
|--------------------------------------------------------------------------
| Status Badge
|--------------------------------------------------------------------------
*/

function contactMessageStatusBadge(string $status): string
{
    switch ($status) {

        case 'new':

            return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2">
                        <i class="bi bi-envelope-fill me-1"></i>
                        New
                    </span>';

        case 'read':

            return '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
                        <i class="bi bi-envelope-open me-1"></i>
                        Read
                    </span>';

        case 'replied':

            return '<span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                        <i class="bi bi-reply-fill me-1"></i>
                        Replied
                    </span>';

        case 'closed':

            return '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2">
                        <i class="bi bi-check-circle me-1"></i>
                        Closed
                    </span>';

        default:

            return '<span class="badge bg-light text-dark border px-3 py-2">
                        ' . e(ucfirst($status)) . '
                    </span>';
    }
}

?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>


<div class="container-fluid px-3 px-lg-4 py-4">


    <!-- Page Header -->

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">

        <div>

            <div class="mb-2">

                <a
                    href="<?= $baseUrl; ?>/admin/contact_messages/index.php"
                    class="text-decoration-none text-muted"
                >

                    <i class="bi bi-arrow-left me-1"></i>

                    Contact Messages

                </a>

            </div>


            <h2 class="fw-bold mb-1">

                <i class="bi bi-envelope-paper me-2 text-primary"></i>

                Contact Message

            </h2>


            <p class="text-muted mb-0">
                View and manage this customer enquiry.
            </p>

        </div>


        <div>

            <?= contactMessageStatusBadge($message['status']); ?>

        </div>

    </div>


    <?php if (!empty($success)): ?>

        <div
            class="alert alert-success alert-dismissible fade show shadow-sm"
            role="alert"
        >

            <i class="bi bi-check-circle-fill me-2"></i>

            <?= e($success); ?>


            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>


    <?php if (!empty($errors)): ?>

        <div
            class="alert alert-danger shadow-sm"
            role="alert"
        >

            <div class="fw-semibold mb-2">
                Please correct the following:
            </div>


            <ul class="mb-0">

                <?php foreach ($errors as $error): ?>

                    <li>
                        <?= e($error); ?>
                    </li>

                <?php endforeach; ?>

            </ul>

        </div>

    <?php endif; ?>


    <div class="row g-4">


        <!-- Message -->

        <div class="col-lg-8">


            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white border-bottom py-3">

                    <div class="d-flex justify-content-between align-items-center">

                        <h5 class="fw-bold mb-0">
                            <?= e($message['subject']); ?>
                        </h5>

                        <small class="text-muted">
                            #<?= (int) $message['id']; ?>
                        </small>

                    </div>

                </div>


                <div class="card-body">


                    <div class="mb-4">

                        <small class="text-muted d-block mb-1">
                            Message
                        </small>


                        <div
                            class="border rounded p-3 bg-light"
                            style="white-space: pre-wrap;"
                        ><?= e($message['message']); ?></div>

                    </div>


                    <div class="row g-3">


                        <div class="col-md-6">

                            <div class="border rounded p-3 h-100">

                                <small class="text-muted d-block mb-1">
                                    Received
                                </small>


                                <div class="fw-semibold">
                                    <?= e(
                                        date(
                                            'd F Y',
                                            strtotime($message['created_at'])
                                        )
                                    ); ?>
                                </div>


                                <small class="text-muted">
                                    <?= e(
                                        date(
                                            'H:i',
                                            strtotime($message['created_at'])
                                        )
                                    ); ?>
                                </small>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="border rounded p-3 h-100">

                                <small class="text-muted d-block mb-1">
                                    Last Updated
                                </small>


                                <div class="fw-semibold">
                                    <?= e(
                                        date(
                                            'd F Y',
                                            strtotime($message['updated_at'])
                                        )
                                    ); ?>
                                </div>


                                <small class="text-muted">
                                    <?= e(
                                        date(
                                            'H:i',
                                            strtotime($message['updated_at'])
                                        )
                                    ); ?>
                                </small>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- Customer Details -->

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white border-bottom py-3">

                    <h5 class="fw-bold mb-0">

                        <i class="bi bi-person me-2 text-primary"></i>

                        Customer Details

                    </h5>

                </div>


                <div class="card-body">

                    <div class="row g-4">


                        <div class="col-md-6">

                            <label class="text-muted small d-block">
                                Full Name
                            </label>

                            <div class="fw-semibold">

                                <?= e(
                                    $message['first_name'] .
                                    ' ' .
                                    $message['last_name']
                                ); ?>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <label class="text-muted small d-block">
                                Email Address
                            </label>


                            <div class="fw-semibold">

                                <a
                                    href="mailto:<?= e($message['email']); ?>"
                                    class="text-decoration-none"
                                >
                                    <?= e($message['email']); ?>
                                </a>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <label class="text-muted small d-block">
                                Phone
                            </label>


                            <div class="fw-semibold">

                                <?php if (!empty($message['phone'])): ?>

                                    <a
                                        href="tel:<?= e($message['phone']); ?>"
                                        class="text-decoration-none"
                                    >
                                        <?= e($message['phone']); ?>
                                    </a>

                                <?php else: ?>

                                    <span class="text-muted">
                                        Not provided
                                    </span>

                                <?php endif; ?>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <label class="text-muted small d-block">
                                Account
                            </label>


                            <div class="fw-semibold">

                                <?php if (!empty($message['user_id'])): ?>

                                    <span class="text-success">

                                        <i class="bi bi-person-check me-1"></i>

                                        Registered Customer

                                    </span>

                                <?php else: ?>

                                    <span class="text-muted">
                                        Guest Customer
                                    </span>

                                <?php endif; ?>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- Right Side -->

        <div class="col-lg-4">


            <!-- Update Status -->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-white border-bottom py-3">

                    <h5 class="fw-bold mb-0">

                        <i class="bi bi-sliders me-2 text-primary"></i>

                        Manage Message

                    </h5>

                </div>


                <div class="card-body">

                    <form
                        method="POST"
                        action="<?= $baseUrl; ?>/admin/contact_messages/view.php?id=<?= (int) $message['id']; ?>"
                    >


                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Status
                            </label>


                            <select
                                name="status"
                                class="form-select"
                                required
                            >

                                <option
                                    value="new"
                                    <?= $message['status'] === 'new' ? 'selected' : ''; ?>
                                >
                                    New
                                </option>


                                <option
                                    value="read"
                                    <?= $message['status'] === 'read' ? 'selected' : ''; ?>
                                >
                                    Read
                                </option>


                                <option
                                    value="replied"
                                    <?= $message['status'] === 'replied' ? 'selected' : ''; ?>
                                >
                                    Replied
                                </option>


                                <option
                                    value="closed"
                                    <?= $message['status'] === 'closed' ? 'selected' : ''; ?>
                                >
                                    Closed
                                </option>

                            </select>

                        </div>


                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Admin Notes
                            </label>


                            <textarea
                                name="admin_notes"
                                class="form-control"
                                rows="6"
                                placeholder="Add internal notes about this enquiry..."
                            ><?= e($message['admin_notes'] ?? ''); ?></textarea>


                            <div class="form-text">
                                These notes are for administrators and are not shown to the customer.
                            </div>

                        </div>


                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >

                            <i class="bi bi-save me-1"></i>

                            Save Changes

                        </button>

                    </form>

                </div>

            </div>


            <!-- Quick Actions -->

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white border-bottom py-3">

                    <h5 class="fw-bold mb-0">

                        <i class="bi bi-lightning-charge me-2 text-primary"></i>

                        Quick Actions

                    </h5>

                </div>


                <div class="card-body">

                    <div class="d-grid gap-2">


                        <a
                            href="mailto:<?= e($message['email']); ?>?subject=Re: <?= rawurlencode($message['subject']); ?>"
                            class="btn btn-outline-primary"
                        >

                            <i class="bi bi-envelope me-1"></i>

                            Reply by Email

                        </a>


                        <?php if (!empty($message['phone'])): ?>

                            <a
                                href="tel:<?= e($message['phone']); ?>"
                                class="btn btn-outline-success"
                            >

                                <i class="bi bi-telephone me-1"></i>

                                Call Customer

                            </a>

                        <?php endif; ?>


                        <a
                            href="<?= $baseUrl; ?>/admin/contact_messages/index.php"
                            class="btn btn-outline-secondary"
                        >

                            <i class="bi bi-arrow-left me-1"></i>

                            Back to Messages

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>