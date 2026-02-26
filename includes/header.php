<?php
require_once __DIR__ . '/../models/MessagesModel.php';
require_once __DIR__ . '/../models/NotificationsModel.php';

$messagesModel = null;
$notificationsModel = null;
$headerRecentMessages = [];
$headerRecentNotifications = [];
$headerMessageUnreadCount = 0;
$headerNotificationUnreadCount = 0;
$headerAeId = 1;
$sessionUser = $_SESSION['user'] ?? null;
if (is_array($sessionUser) && isset($sessionUser['id'])) {
    $headerAeId = max(1, (int) $sessionUser['id']);
}

try {
    $messagesModel = new MessagesModel();
    $notificationsModel = new NotificationsModel();
    $headerMessageUnreadCount = $messagesModel->getUnreadCountForReceiver('ae', $headerAeId);
    $headerNotificationUnreadCount = $notificationsModel->getUnreadCount();
    $headerRecentMessages = $messagesModel->getRecentMessagesForReceiver('ae', $headerAeId, 5);
    $headerRecentNotifications = $notificationsModel->getRecentNotificationsGrouped(5);
} catch (Throwable $exception) {
    $headerRecentMessages = [];
    $headerRecentNotifications = [];
    $headerMessageUnreadCount = 0;
    $headerNotificationUnreadCount = 0;
}
?>

<header class="header d-flex justify-content-between align-items-center p-3 bg-light border-bottom position-sticky" style="top: 0; z-index: 99; transition: margin-left 0.3s;">
    <div class="header-left d-flex align-items-center gap-3">
        <span id="sidebarHamburgerHeader" class="hamburger-btn" role="button" tabindex="0" data-bs-toggle="offcanvas" data-bs-target="#appSidebar" aria-controls="appSidebar" aria-label="Toggle navigation">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16" aria-hidden="true">
                <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"/>
            </svg>
            <span class="visually-hidden">Menu</span>
        </span>
        <div>
            <h1 class="page-title h4 mb-1"><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></h1>
            <p class="page-subtitle text-muted small mb-0"><?php echo isset($pageSubtitle) ? $pageSubtitle : 'Overall Overview of Operational Status'; ?></p>
        </div>
    </div>

    <div class="header-right d-flex align-items-center gap-3">
        <div class="dropdown notification-icon position-relative">
            <button class="btn btn-link p-0 text-dark icon-trigger" type="button" id="messagesDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Open messages">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-chat-dots-fill" viewBox="0 0 16 16" aria-hidden="true">
                    <path d="M16 8c0 3.866-3.582 7-8 7a9 9 0 0 1-2.347-.306c-.584.296-1.925.864-4.181 1.234-.2.032-.352-.176-.273-.362.354-.836.674-1.95.77-2.966C.744 11.37 0 9.76 0 8c0-3.866 3.582-7 8-7s8 3.134 8 7M5 8a1 1 0 1 0-2 0 1 1 0 0 0 2 0m4 0a1 1 0 1 0-2 0 1 1 0 0 0 2 0m3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
                </svg>
            </button>
            <span id="headerMessagesBadge" class="notification-badge badge bg-danger position-absolute top-0 start-100 translate-middle <?= $headerMessageUnreadCount > 0 ? '' : 'd-none' ?>">
                <?= (int) $headerMessageUnreadCount ?>
            </span>
            <div class="dropdown-menu dropdown-menu-end p-2" style="width: 360px; max-height: 420px; overflow-y: auto;" aria-labelledby="messagesDropdownBtn">
                <div class="d-flex justify-content-between align-items-center px-2 py-1">
                    <strong>Messages</strong>
                    <small class="text-muted"><span id="headerMessagesCountLabel"><?= (int) $headerMessageUnreadCount ?></span> unread</small>
                </div>
                <div id="headerMessagesList">
                    <?php if (!empty($headerRecentMessages)): ?>
                        <?php foreach ($headerRecentMessages as $message): ?>
                            <div class="dropdown-item-text border-bottom py-2">
                                <div class="d-flex justify-content-between">
                                    <small class="fw-semibold text-uppercase"><?= htmlspecialchars((string) ($message['sender_role'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small>
                                    <?php if ((int) ($message['is_read'] ?? 0) === 0): ?>
                                        <small class="badge bg-danger">New</small>
                                    <?php endif; ?>
                                </div>
                                <div class="small text-muted"><?= htmlspecialchars((string) ($message['module_origin'] ?? 'General'), ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="small"><?= htmlspecialchars((string) ($message['message_text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="dropdown-item-text text-muted small">No recent messages.</div>
                    <?php endif; ?>
                </div>
                <div class="d-flex justify-content-between align-items-center px-2 pt-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="markAllMessagesReadHeaderBtn">Mark all as read</button>
                    <a href="messages.php" class="btn btn-sm btn-primary">View All Messages</a>
                </div>
            </div>
        </div>

        <div class="dropdown notification-icon position-relative">
            <button class="btn btn-link p-0 text-dark icon-trigger" type="button" id="notificationsDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Open notifications">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-bell-fill" viewBox="0 0 16 16" aria-hidden="true">
                    <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2m.995-14.901a1 1 0 1 0-1.99 0A5 5 0 0 0 3 6c0 1.098-.5 6-2 7h14c-1.5-1-2-5.902-2-7 0-2.42-1.72-4.44-4.005-4.901"/>
                </svg>
            </button>
            <span id="headerNotificationsBadge" class="notification-badge badge bg-danger position-absolute top-0 start-100 translate-middle <?= $headerNotificationUnreadCount > 0 ? '' : 'd-none' ?>">
                <?= (int) $headerNotificationUnreadCount ?>
            </span>
            <div class="dropdown-menu dropdown-menu-end p-2" style="width: 380px; max-height: 420px; overflow-y: auto;" aria-labelledby="notificationsDropdownBtn">
                <div class="d-flex justify-content-between align-items-center px-2 py-1">
                    <strong>Notifications</strong>
                    <small class="text-muted"><span id="headerNotificationsCountLabel"><?= (int) $headerNotificationUnreadCount ?></span> unread</small>
                </div>
                <div id="headerNotificationsList">
                    <?php if (!empty($headerRecentNotifications)): ?>
                        <?php foreach ($headerRecentNotifications as $moduleName => $items): ?>
                            <div class="dropdown-item-text border-bottom py-2">
                                <div class="small fw-semibold mb-1"><?= htmlspecialchars((string) $moduleName, ENT_QUOTES, 'UTF-8') ?></div>
                                <?php foreach ($items as $item): ?>
                                    <?php $link = $notificationsModel ? $notificationsModel->moduleLink((string) ($item['module_source'] ?? '')) : 'index.php'; ?>
                                    <a href="<?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>" class="d-block small text-decoration-none mb-1">
                                        <?= htmlspecialchars((string) ($item['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="dropdown-item-text text-muted small">No recent notifications.</div>
                    <?php endif; ?>
                </div>
                <div class="d-flex justify-content-end px-2 pt-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="markAllNotificationsReadBtn">Mark all as read</button>
                </div>
            </div>
        </div>

        <div class="user-profile d-flex align-items-center gap-2">
            <span>Account Executive</span>
            <div class="profile-pic bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <span class="text-white">A</span>
            </div>
        </div>
    </div>
</header>
