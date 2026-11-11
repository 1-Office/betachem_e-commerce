<?php

/*
|--------------------------------------------------------------------------
| Betachem E-Commerce — Contact Messages
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/auth.php';

requireAdmin();
requirePermission('contact_messages');

$pageTitle = 'Contact Messages';
$baseUrl = '/betachem/betachem_e-commerce';

require_once __DIR__ . '/../../includes/functions.php';

/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

$status = trim($_GET['status'] ?? '');
$search = trim($_GET['search'] ?? '');

$allowedStatuses = ['new', 'read', 'replied', 'closed'];

if (!in_array($status, $allowedStatuses, true)) {
    $status = '';
}

/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

$stats = [
    'total' => 0,
    'new' => 0,
    'read' => 0,
    'replied' => 0,
    'closed' => 0,
];

$statsResult = $conn->query("
    SELECT
        COUNT(*) AS total,
        COALESCE(SUM(status = 'new'), 0) AS new_count,
        COALESCE(SUM(status = 'read'), 0) AS read_count,
        COALESCE(SUM(status = 'replied'), 0) AS replied_count,
        COALESCE(SUM(status = 'closed'), 0) AS closed_count
    FROM contact_messages
");

if ($statsResult) {
    $row = $statsResult->fetch_assoc();

    $stats['total'] = (int) ($row['total'] ?? 0);
    $stats['new'] = (int) ($row['new_count'] ?? 0);
    $stats['read'] = (int) ($row['read_count'] ?? 0);
    $stats['replied'] = (int) ($row['replied_count'] ?? 0);
    $stats['closed'] = (int) ($row['closed_count'] ?? 0);
}

/*
|--------------------------------------------------------------------------
| Fetch Messages
|--------------------------------------------------------------------------
*/

$sql = "
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
    WHERE 1 = 1
";

$params = [];
$types = '';

if ($status !== '') {
    $sql .= " AND status = ?";
    $params[] = $status;
    $types .= 's';
}

if ($search !== '') {
    $sql .= "
        AND (
            first_name LIKE ?
            OR last_name LIKE ?
            OR email LIKE ?
            OR subject LIKE ?
            OR message LIKE ?
        )
    ";

    $term = '%' . $search . '%';

    for ($i = 0; $i < 5; $i++) {
        $params[] = $term;
    }

    $types .= 'sssss';
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die('Unable to prepare the contact messages query.');
}

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

/*
|--------------------------------------------------------------------------
| Status Styling
|--------------------------------------------------------------------------
*/

function contactStatusBadge(string $status): string
{
    $classes = [
        'new' => 'bc-status-new',
        'read' => 'bc-status-read',
        'replied' => 'bc-status-replied',
        'closed' => 'bc-status-closed',
    ];

    $icons = [
        'new' => 'bi-envelope-fill',
        'read' => 'bi-envelope-open',
        'replied' => 'bi-reply-fill',
        'closed' => 'bi-check-circle',
    ];

    $class = $classes[$status] ?? 'bc-status-closed';
    $icon = $icons[$status] ?? 'bi-envelope';
    $label = ucfirst($status);

    return '<span class="bc-status ' . $class . '">'
        . '<i class="bi ' . $icon . '"></i> '
        . e($label)
        . '</span>';
}

require_once __DIR__ . '/../includes/header.php';

?>

<style>
/* =========================================================
   BETACHEM CONTACT MESSAGES
   Matching the Categories index design
   ========================================================= */

.betachem-messages {
    --bc-blue: #1D88E9;
    --bc-dark-blue: #1745CA;
    --bc-navy: #2D208A;
    --bc-text: #183153;
    --bc-muted: #68778D;
    --bc-border: #E3EAF3;

    padding: 8px 0 32px;
    color: var(--bc-text);
    font-family: Raleway, Arial, sans-serif;
}

.betachem-messages * {
    box-sizing: border-box;
}

.betachem-messages .bc-hero {
    background: linear-gradient(120deg, #2D208A, #1745CA, #1D88E9);
    border-radius: 18px;
    padding: 27px 30px;
    color: #fff;
    margin-bottom: 24px;
    box-shadow: 0 8px 24px rgba(23, 69, 202, .14);
}

.betachem-messages .bc-hero h1 {
    color: #fff;
    font-size: clamp(24px, 3vw, 32px);
    font-weight: 800;
    margin: 0 0 8px;
}

.betachem-messages .bc-hero p {
    color: rgba(255,255,255,.88);
    font-size: 13px;
    line-height: 1.7;
    margin: 0;
}

.betachem-messages .bc-hero-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 18px;
}

.betachem-messages .bc-hero .bc-refresh {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 15px;
    border-radius: 9px;
    border: 1px solid rgba(255,255,255,.6);
    color: #fff;
    text-decoration: none;
    font-size: 12px;
    font-weight: 800;
    background: rgba(255,255,255,.08);
}

.betachem-messages .bc-hero .bc-refresh:hover {
    background: rgba(255,255,255,.18);
}

.betachem-messages .bc-eyebrow {
    text-transform: uppercase;
    letter-spacing: 1.5px;
    font-size: 10px;
    font-weight: 800;
    opacity: .8;
    margin-bottom: 8px;
}

.betachem-messages .bc-stats {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 24px;
}

.betachem-messages .bc-stat {
    background: #fff;
    border: 1px solid var(--bc-border);
    border-radius: 14px;
    padding: 18px;
    box-shadow: 0 3px 12px rgba(24,49,83,.035);
    min-width: 0;
}

.betachem-messages .bc-stat-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.betachem-messages .bc-stat-label {
    color: var(--bc-muted);
    font-size: 11px;
    font-weight: 700;
}

.betachem-messages .bc-stat-value {
    font-size: 28px;
    font-weight: 800;
    line-height: 1.3;
    margin-top: 12px;
    color: var(--bc-text);
}

.betachem-messages .bc-stat-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 39px;
    height: 39px;
    flex-shrink: 0;
    border-radius: 11px;
    font-size: 17px;
}

.betachem-messages .bc-icon-blue {
    background: #E8F3FF;
    color: #1769AA;
}

.betachem-messages .bc-icon-red {
    background: #FFE9E9;
    color: #C03939;
}

.betachem-messages .bc-icon-green {
    background: #E7F8EF;
    color: #16834A;
}

.betachem-messages .bc-icon-purple {
    background: #F0ECFF;
    color: #5D42C3;
}

.betachem-messages .bc-icon-grey {
    background: #EEF2F7;
    color: #63748B;
}

.betachem-messages .bc-panel {
    background: #fff;
    border: 1px solid var(--bc-border);
    border-radius: 15px;
    overflow: hidden;
    margin-bottom: 22px;
    box-shadow: 0 3px 12px rgba(24,49,83,.035);
}

.betachem-messages .bc-panel-header {
    padding: 18px 21px;
    border-bottom: 1px solid var(--bc-border);
}

.betachem-messages .bc-panel-header h2 {
    font-size: 16px;
    font-weight: 800;
    margin: 0;
    color: var(--bc-text);
}

.betachem-messages .bc-panel-header p {
    color: var(--bc-muted);
    font-size: 12px;
    margin: 5px 0 0;
}

.betachem-messages .bc-filter-body {
    padding: 20px;
}

.betachem-messages .form-label {
    font-size: 12px;
    font-weight: 800;
    color: var(--bc-text);
    margin-bottom: 7px;
}

.betachem-messages .form-control,
.betachem-messages .form-select,
.betachem-messages .input-group-text {
    border-color: #DCE5F0;
    font-size: 13px;
    min-height: 43px;
}

.betachem-messages .form-control:focus,
.betachem-messages .form-select:focus {
    border-color: var(--bc-blue);
    box-shadow: 0 0 0 .2rem rgba(29,136,233,.12);
}

.betachem-messages .bc-btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    min-height: 40px;
    padding: 9px 15px;
    background: var(--bc-dark-blue);
    border: 1px solid var(--bc-dark-blue);
    border-radius: 8px;
    color: #fff;
    font-size: 12px;
    font-weight: 800;
    text-decoration: none;
}

.betachem-messages .bc-btn-primary:hover {
    background: var(--bc-blue);
    border-color: var(--bc-blue);
    color: #fff;
}

.betachem-messages .bc-btn-outline {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    min-height: 36px;
    padding: 8px 11px;
    background: #fff;
    border: 1px solid #C8DDF5;
    border-radius: 8px;
    color: #175EA8;
    font-size: 11px;
    font-weight: 800;
    text-decoration: none;
    white-space: nowrap;
}

.betachem-messages .bc-btn-outline:hover {
    background: #175EA8;
    color: #fff;
}

.betachem-messages .bc-table-wrap {
    width: 100%;
    overflow-x: auto;
}

.betachem-messages .bc-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
    font-size: 12px;
}

.betachem-messages .bc-table thead th {
    background: #F6F9FD;
    color: #63748B;
    text-transform: uppercase;
    letter-spacing: .6px;
    font-size: 10px;
    font-weight: 800;
    padding: 14px 16px;
    white-space: nowrap;
    border-bottom: 1px solid var(--bc-border);
    text-align: left;
}

.betachem-messages .bc-table tbody td {
    padding: 15px 16px;
    border-bottom: 1px solid #EDF1F6;
    vertical-align: middle;
}

.betachem-messages .bc-table tbody tr:last-child td {
    border-bottom: 0;
}

.betachem-messages .bc-table tbody tr:hover {
    background: #FAFCFF;
}

.betachem-messages .bc-main-text {
    font-size: 12px;
    font-weight: 800;
    color: var(--bc-text);
}

.betachem-messages .bc-sub-text {
    color: var(--bc-muted);
    font-size: 11px;
    line-height: 1.5;
    margin-top: 4px;
}

.betachem-messages .bc-message-preview {
    display: block;
    max-width: 320px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.betachem-messages .bc-status {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 9px;
    border-radius: 30px;
    font-size: 10px;
    font-weight: 800;
    white-space: nowrap;
}

.betachem-messages .bc-status-new {
    background: #FFE8E8;
    color: #B52F37;
}

.betachem-messages .bc-status-read {
    background: #E6F2FF;
    color: #175EA8;
}

.betachem-messages .bc-status-replied {
    background: #E4F7EC;
    color: #187744;
}

.betachem-messages .bc-status-closed {
    background: #EEF2F7;
    color: #526176;
}

.betachem-messages .bc-empty {
    text-align: center;
    padding: 45px 20px;
}

.betachem-messages .bc-empty-icon {
    display: inline-flex;
    width: 60px;
    height: 60px;
    border-radius: 16px;
    align-items: center;
    justify-content: center;
    background: #EAF3FF;
    color: var(--bc-dark-blue);
    font-size: 25px;
    margin-bottom: 14px;
}

.betachem-messages .bc-empty h3 {
    color: var(--bc-text);
    font-size: 16px;
    font-weight: 800;
}

.betachem-messages .bc-empty p {
    color: var(--bc-muted);
    font-size: 12px;
    margin-bottom: 0;
}

.betachem-messages .bc-result-count {
    color: var(--bc-muted);
    font-size: 11px;
    font-weight: 700;
}

@media (max-width: 1200px) {
    .betachem-messages .bc-stats {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 768px) {
    .betachem-messages .bc-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .betachem-messages .bc-hero {
        padding: 22px 19px;
        border-radius: 14px;
    }

    .betachem-messages .bc-stat {
        padding: 15px;
    }

    .betachem-messages .bc-stat-value {
        font-size: 24px;
    }

    .betachem-messages .bc-panel-header,
    .betachem-messages .bc-filter-body {
        padding: 16px;
    }
}

@media (max-width: 420px) {
    .betachem-messages .bc-stats {
        grid-template-columns: 1fr 1fr;
    }

    .betachem-messages .bc-stat-icon {
        width: 32px;
        height: 32px;
        font-size: 14px;
    }
}
</style>

<div class="container-fluid betachem-messages">

    <!-- Page Header -->
    <section class="bc-hero">
        <div class="bc-hero-top">
            <div>
                <div class="bc-eyebrow">Customer Support</div>
                <h1>
                    <i class="bi bi-envelope-paper me-2"></i>
                    Contact Messages
                </h1>
                <p>
                    Manage customer enquiries, review messages, and track replies.
                </p>
            </div>

            <a class="bc-refresh"
               href="<?= $baseUrl ?>/admin/contact_messages/index.php">
                <i class="bi bi-arrow-clockwise"></i>
                Refresh
            </a>
        </div>
    </section>

    <!-- Statistics -->
    <div class="bc-stats">

        <div class="bc-stat">
            <div class="bc-stat-top">
                <div class="bc-stat-label">Total Messages</div>
                <div class="bc-stat-icon bc-icon-blue">
                    <i class="bi bi-envelope"></i>
                </div>
            </div>
            <div class="bc-stat-value"><?= number_format($stats['total']) ?></div>
        </div>

        <div class="bc-stat">
            <div class="bc-stat-top">
                <div class="bc-stat-label">New</div>
                <div class="bc-stat-icon bc-icon-red">
                    <i class="bi bi-envelope-fill"></i>
                </div>
            </div>
            <div class="bc-stat-value"><?= number_format($stats['new']) ?></div>
        </div>

        <div class="bc-stat">
            <div class="bc-stat-top">
                <div class="bc-stat-label">Read</div>
                <div class="bc-stat-icon bc-icon-blue">
                    <i class="bi bi-envelope-open"></i>
                </div>
            </div>
            <div class="bc-stat-value"><?= number_format($stats['read']) ?></div>
        </div>

        <div class="bc-stat">
            <div class="bc-stat-top">
                <div class="bc-stat-label">Replied</div>
                <div class="bc-stat-icon bc-icon-green">
                    <i class="bi bi-reply-fill"></i>
                </div>
            </div>
            <div class="bc-stat-value"><?= number_format($stats['replied']) ?></div>
        </div>

        <div class="bc-stat">
            <div class="bc-stat-top">
                <div class="bc-stat-label">Closed</div>
                <div class="bc-stat-icon bc-icon-grey">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
            <div class="bc-stat-value"><?= number_format($stats['closed']) ?></div>
        </div>

    </div>

    <!-- Filters -->
    <section class="bc-panel">
        <div class="bc-panel-header">
            <h2>
                <i class="bi bi-funnel me-2"></i>
                Search and Filter
            </h2>
            <p>Find enquiries by customer details, subject, message, or status.</p>
        </div>

        <div class="bc-filter-body">
            <form method="GET"
                  action="<?= $baseUrl ?>/admin/contact_messages/index.php">

                <div class="row g-3 align-items-end">

                    <div class="col-lg-6">
                        <label for="search" class="form-label">Search Messages</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-search"></i>
                            </span>
                            <input
                                id="search"
                                type="search"
                                name="search"
                                class="form-control"
                                placeholder="Name, email, subject or message..."
                                value="<?= e($search) ?>"
                            >
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <label for="status" class="form-label">Message Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="new" <?= $status === 'new' ? 'selected' : '' ?>>New</option>
                            <option value="read" <?= $status === 'read' ? 'selected' : '' ?>>Read</option>
                            <option value="replied" <?= $status === 'replied' ? 'selected' : '' ?>>Replied</option>
                            <option value="closed" <?= $status === 'closed' ? 'selected' : '' ?>>Closed</option>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex gap-2">
                            <button type="submit" class="bc-btn-primary flex-grow-1">
                                <i class="bi bi-search"></i>
                                Search
                            </button>

                            <a class="bc-btn-outline"
                               href="<?= $baseUrl ?>/admin/contact_messages/index.php"
                               title="Clear filters"
                               aria-label="Clear filters">
                                <i class="bi bi-x-lg"></i>
                                Clear
                            </a>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </section>

    <!-- Messages Table -->
    <section class="bc-panel">

        <div class="bc-panel-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h2>
                    <i class="bi bi-chat-left-text me-2"></i>
                    Customer Enquiries
                </h2>
                <p>Review incoming customer contact requests.</p>
            </div>

            <span class="bc-result-count">
                <?= number_format($result->num_rows) ?> message(s)
            </span>
        </div>

        <?php if ($result->num_rows > 0): ?>

            <div class="bc-table-wrap">
                <table class="bc-table">
                    <thead>
                        <tr>
                            <th>Date Received</th>
                            <th>Customer</th>
                            <th>Subject and Message</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php while ($message = $result->fetch_assoc()): ?>

                        <tr>
                            <td>
                                <div class="bc-main-text">
                                    <?= !empty($message['created_at'])
                                        ? e(date('d M Y', strtotime($message['created_at'])))
                                        : '—' ?>
                                </div>
                                <div class="bc-sub-text">
                                    <?= !empty($message['created_at'])
                                        ? e(date('H:i', strtotime($message['created_at'])))
                                        : '' ?>
                                </div>
                            </td>

                            <td>
                                <div class="bc-main-text">
                                    <?= e(trim(
                                        ($message['first_name'] ?? '') . ' ' .
                                        ($message['last_name'] ?? '')
                                    )) ?>
                                </div>
                                <div class="bc-sub-text">
                                    <?= e($message['email'] ?? '') ?>
                                </div>
                                <?php if (!empty($message['phone'])): ?>
                                    <div class="bc-sub-text">
                                        <i class="bi bi-telephone me-1"></i>
                                        <?= e($message['phone']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="bc-main-text">
                                    <?= e($message['subject'] ?? '(No subject)') ?>
                                </div>
                                <div class="bc-sub-text bc-message-preview">
                                    <?= e($message['message'] ?? '') ?>
                                </div>
                            </td>

                            <td>
                                <?= contactStatusBadge((string) ($message['status'] ?? 'new')) ?>
                            </td>

                            <td class="text-end">
                                <a
                                    class="bc-btn-outline"
                                    href="<?= $baseUrl ?>/admin/contact_messages/view.php?id=<?= (int) $message['id'] ?>"
                                    title="View Message"
                                    aria-label="View Message"
                                >
                                    <i class="bi bi-eye"></i>
                                    View
                                </a>
                            </td>
                        </tr>

                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>

            <div class="bc-empty">
                <div class="bc-empty-icon">
                    <i class="bi bi-envelope-open"></i>
                </div>
                <h3>No Contact Messages Found</h3>
                <p>There are no enquiries matching your current filters.</p>
            </div>

        <?php endif; ?>

    </section>

</div>

<?php
$stmt->close();
require_once __DIR__ . '/../includes/footer.php';
?>