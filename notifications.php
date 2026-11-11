<?php

/*
|--------------------------------------------------------------------------
| Betachem E-Commerce
| Customer Notifications
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/notification_functions.php';

requireLogin();

$userId = (int) $_SESSION['user_id'];

$baseUrl = '/betachem/betachem_e-commerce';


/*
|--------------------------------------------------------------------------
| Handle Actions
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | Mark All As Read
    |--------------------------------------------------------------------------
    */

    if ($action === 'mark_all_read') {

        markAllNotificationsAsRead($userId);

        $_SESSION['notification_message'] =
            'All notifications have been marked as read.';

        redirect($baseUrl . '/notifications.php');
    }


    /*
    |--------------------------------------------------------------------------
    | Mark Single Notification As Read
    |--------------------------------------------------------------------------
    */

    if ($action === 'mark_read') {

        $notificationId = (int) ($_POST['notification_id'] ?? 0);

        if ($notificationId > 0) {

            markNotificationAsRead(
                $notificationId,
                $userId
            );
        }

        redirect($baseUrl . '/notifications.php');
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Notification
    |--------------------------------------------------------------------------
    */

    if ($action === 'delete') {

        $notificationId = (int) ($_POST['notification_id'] ?? 0);

        if ($notificationId > 0) {

            deleteNotification(
                $notificationId,
                $userId
            );
        }

        redirect($baseUrl . '/notifications.php');
    }
}


/*
|--------------------------------------------------------------------------
| Load Notifications
|--------------------------------------------------------------------------
*/

$notifications = getNotifications(
    $userId,
    100
);

$unreadCount = getUnreadNotificationCount(
    $userId
);


require_once __DIR__ . '/includes/header.php';

?>

<div class="container py-5">

    <!-- PAGE HEADER -->

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">

        <div>

            <div
                class="text-uppercase fw-semibold small mb-2"
                style="color:#1769aa;letter-spacing:1.5px;"
            >
                CUSTOMER ACCOUNT
            </div>

            <h1
                class="fw-bold mb-2"
                style="color:#102b49;"
            >
                Notifications
            </h1>

            <p class="text-muted mb-0">
                Stay updated with your orders and account activity.
            </p>

        </div>


        <?php if ($unreadCount > 0): ?>

            <form method="POST" class="mt-3 mt-md-0">

                <input
                    type="hidden"
                    name="action"
                    value="mark_all_read"
                >

                <button
                    type="submit"
                    class="btn btn-outline-primary"
                >
                    <i class="bi bi-check2-all me-1"></i>
                    Mark All as Read
                </button>

            </form>

        <?php endif; ?>

    </div>


    <!-- FLASH MESSAGE -->

    <?php if (!empty($_SESSION['notification_message'])): ?>

        <div class="alert alert-success">

            <i class="bi bi-check-circle me-2"></i>

            <?= e($_SESSION['notification_message']) ?>

        </div>

        <?php unset($_SESSION['notification_message']); ?>

    <?php endif; ?>


    <!-- NOTIFICATION COUNT -->

    <?php if ($unreadCount > 0): ?>

        <div
            class="alert alert-primary d-flex align-items-center"
        >

            <i class="bi bi-bell-fill fs-5 me-3"></i>

            <div>

                You have
                <strong><?= $unreadCount ?></strong>
                unread notification<?= $unreadCount === 1 ? '' : 's' ?>.

            </div>

        </div>

    <?php endif; ?>


    <?php if (empty($notifications)): ?>

        <!-- EMPTY STATE -->

        <div
            class="text-center py-5 px-4 bg-white rounded-4 shadow-sm"
        >

            <div
                class="mx-auto mb-4 d-flex align-items-center justify-content-center rounded-circle"
                style="
                    width:80px;
                    height:80px;
                    background:#eef6fc;
                    color:#1769aa;
                "
            >

                <i class="bi bi-bell-slash fs-2"></i>

            </div>

            <h3
                class="fw-bold"
                style="color:#102b49;"
            >
                No notifications yet
            </h3>

            <p class="text-muted mb-4">
                Your order and account updates will appear here.
            </p>

            <a
                href="<?= $baseUrl; ?>/shop.php"
                class="btn btn-primary"
            >
                <i class="bi bi-shop me-2"></i>
                Continue Shopping
            </a>

        </div>

    <?php else: ?>


        <!-- NOTIFICATIONS -->

        <div class="d-flex flex-column gap-3">

            <?php foreach ($notifications as $notification): ?>

                <?php

                $type = $notification['type'];

                $icon = 'bi-bell';

                if ($type === 'order_placed') {
                    $icon = 'bi-bag-check';
                } elseif ($type === 'order_processing') {
                    $icon = 'bi-hourglass-split';
                } elseif ($type === 'order_ready') {
                    $icon = 'bi-box-seam';
                } elseif ($type === 'order_shipped') {
                    $icon = 'bi-truck';
                } elseif ($type === 'order_completed') {
                    $icon = 'bi-check-circle';
                } elseif ($type === 'order_cancelled') {
                    $icon = 'bi-x-circle';
                } elseif ($type === 'payment') {
                    $icon = 'bi-credit-card';
                }

                $isUnread = (int) $notification['is_read'] === 0;

                ?>

                <div
                    class="bg-white rounded-4 shadow-sm p-4"
                    style="
                        border-left:
                        4px solid
                        <?= $isUnread ? '#1769aa' : '#dfe7ee' ?>;
                    "
                >

                    <div class="d-flex gap-3">

                        <!-- ICON -->

                        <div
                            class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle"
                            style="
                                width:52px;
                                height:52px;
                                background:
                                <?= $isUnread ? '#eef6fc' : '#f4f6f8' ?>;
                                color:
                                <?= $isUnread ? '#1769aa' : '#7c8b99' ?>;
                            "
                        >

                            <i class="bi <?= e($icon) ?> fs-5"></i>

                        </div>


                        <!-- CONTENT -->

                        <div class="flex-grow-1">

                            <div
                                class="d-flex flex-column flex-md-row justify-content-between gap-2"
                            >

                                <div>

                                    <h5
                                        class="fw-bold mb-1"
                                        style="color:#102b49;"
                                    >

                                        <?= e($notification['title']) ?>

                                        <?php if ($isUnread): ?>

                                            <span
                                                class="badge bg-primary ms-2"
                                                style="font-size:9px;"
                                            >
                                                NEW
                                            </span>

                                        <?php endif; ?>

                                    </h5>

                                    <p class="text-muted mb-2">
                                        <?= e($notification['message']) ?>
                                    </p>

                                    <small class="text-muted">

                                        <i class="bi bi-clock me-1"></i>

                                        <?= date(
                                            'd M Y, H:i',
                                            strtotime($notification['created_at'])
                                        ) ?>

                                    </small>

                                </div>


                                <!-- ACTIONS -->

                                <div class="d-flex gap-2 align-items-start">

                                    <?php if (!empty($notification['order_id'])): ?>

                                        <a
                                            href="<?= $baseUrl; ?>/order_details.php?id=<?= (int) $notification['order_id'] ?>"
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            View Order
                                        </a>

                                    <?php endif; ?>


                                    <?php if ($isUnread): ?>

                                        <form method="POST">

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="mark_read"
                                            >

                                            <input
                                                type="hidden"
                                                name="notification_id"
                                                value="<?= (int) $notification['id'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-secondary"
                                            >
                                                Mark Read
                                            </button>

                                        </form>

                                    <?php endif; ?>


                                    <form method="POST">

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="delete"
                                        >

                                        <input
                                            type="hidden"
                                            name="notification_id"
                                            value="<?= (int) $notification['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Delete notification"
                                        >

                                            <i class="bi bi-trash"></i>

                                        </button>

                                    </form>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>


<?php

require_once __DIR__ . '/includes/footer.php';

?>