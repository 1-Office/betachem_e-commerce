<?php

/*
|--------------------------------------------------------------------------
| Betachem E-Commerce
| Notification Functions
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';


/*
|--------------------------------------------------------------------------
| Create Notification
|--------------------------------------------------------------------------
*/

function createNotification(
    int $userId,
    string $type,
    string $title,
    string $message,
    ?int $orderId = null
): bool {

    global $conn;

    $stmt = $conn->prepare("
        INSERT INTO notifications
        (
            user_id,
            order_id,
            type,
            title,
            message
        )
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "iisss",
        $userId,
        $orderId,
        $type,
        $title,
        $message
    );

    return $stmt->execute();
}


/*
|--------------------------------------------------------------------------
| Get Notifications
|--------------------------------------------------------------------------
*/

function getNotifications(int $userId, int $limit = 10): array
{
    global $conn;

    $limit = max(1, min($limit, 100));

    $stmt = $conn->prepare("
        SELECT
            id,
            order_id,
            type,
            title,
            message,
            is_read,
            created_at
        FROM notifications
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT ?
    ");

    $stmt->bind_param(
        "ii",
        $userId,
        $limit
    );

    $stmt->execute();

    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}


/*
|--------------------------------------------------------------------------
| Get Unread Notification Count
|--------------------------------------------------------------------------
*/

function getUnreadNotificationCount(int $userId): int
{
    global $conn;

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM notifications
        WHERE user_id = ?
        AND is_read = 0
    ");

    $stmt->bind_param(
        "i",
        $userId
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $row = $result->fetch_assoc();

    return (int) ($row['total'] ?? 0);
}


/*
|--------------------------------------------------------------------------
| Mark Notification As Read
|--------------------------------------------------------------------------
*/

function markNotificationAsRead(
    int $notificationId,
    int $userId
): bool {

    global $conn;

    $stmt = $conn->prepare("
        UPDATE notifications
        SET is_read = 1
        WHERE id = ?
        AND user_id = ?
    ");

    $stmt->bind_param(
        "ii",
        $notificationId,
        $userId
    );

    return $stmt->execute();
}


/*
|--------------------------------------------------------------------------
| Mark All Notifications As Read
|--------------------------------------------------------------------------
*/

function markAllNotificationsAsRead(int $userId): bool
{
    global $conn;

    $stmt = $conn->prepare("
        UPDATE notifications
        SET is_read = 1
        WHERE user_id = ?
        AND is_read = 0
    ");

    $stmt->bind_param(
        "i",
        $userId
    );

    return $stmt->execute();
}


/*
|--------------------------------------------------------------------------
| Delete Notification
|--------------------------------------------------------------------------
*/

function deleteNotification(
    int $notificationId,
    int $userId
): bool {

    global $conn;

    $stmt = $conn->prepare("
        DELETE FROM notifications
        WHERE id = ?
        AND user_id = ?
    ");

    $stmt->bind_param(
        "ii",
        $notificationId,
        $userId
    );

    return $stmt->execute();
}