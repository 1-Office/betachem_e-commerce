
<?php
/*
|--------------------------------------------------------------------------
| BETACHEM E-COMMERCE
| Secure Shop Single Sign-On Receiver
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed.');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';

$token = $_POST['token'] ?? '';

if (
    !is_string($token) ||
    !preg_match('/^[a-f0-9]{64}$/', $token)
) {
    http_response_code(400);
    exit('Invalid sign-in request. Return to the official website and try again.');
}

$tokenHash = hash('sha256', $token);

try {
    $conn->begin_transaction();

    /*
     * Find and lock the valid, unused token.
     */
    $stmt = $conn->prepare(
        "SELECT
            s.id AS token_id,
            u.id AS user_id,
            u.first_name,
            u.last_name,
            u.email,
            u.role,
            u.status
         FROM sso_tokens s
         INNER JOIN users u ON u.id = s.user_id
         WHERE s.token_hash = ?
           AND s.used_at IS NULL
           AND s.expires_at > NOW()
           AND u.status = 'active'
         LIMIT 1
         FOR UPDATE"
    );

    $stmt->bind_param('s', $tokenHash);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        $conn->rollback();

        http_response_code(401);
        exit(
            'This sign-in link has expired or has already been used. ' .
            'Return to the official website and click Shop again.'
        );
    }

    /*
     * Consume the one-time token.
     */
    $tokenId = (int) $user['token_id'];

    $update = $conn->prepare(
        "UPDATE sso_tokens
         SET used_at = NOW(), updated_at = NOW()
         WHERE id = ?
           AND used_at IS NULL
           AND expires_at > NOW()"
    );

    $update->bind_param('i', $tokenId);
    $update->execute();

    if ($update->affected_rows !== 1) {
        $update->close();
        $conn->rollback();

        http_response_code(401);
        exit('This sign-in link is no longer valid. Please click Shop again.');
    }

    $update->close();
    $conn->commit();

    /*
     * Preserve the existing guest cart before changing sessions.
     */
    $cart = $_SESSION['cart'] ?? [];

    session_regenerate_id(true);

    $_SESSION['cart'] = $cart;
    $_SESSION['user_id'] = (int) $user['user_id'];
    $_SESSION['user_name'] = trim(
        ($user['first_name'] ?? '') . ' ' .
        ($user['last_name'] ?? '')
    );
    $_SESSION['user_first_name'] = $user['first_name'] ?? '';
    $_SESSION['user_last_name'] = $user['last_name'] ?? '';
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = strtolower(
        trim((string) ($user['role'] ?? 'customer'))
    );

    /*
     * All active, authorised users enter the public Shop.
     * Admin Portal access remains protected by requireAdmin().
     */
    header('Location: /betachem/betachem_e-commerce/shop.php');
    exit;

} catch (Throwable $e) {
    try {
        $conn->rollback();
    } catch (Throwable $ignored) {
        // The transaction may already have been closed.
    }

    error_log('Betachem Shop SSO error: ' . $e->getMessage());

    http_response_code(500);
    exit('We could not sign you into the Shop. Please try again.');
}