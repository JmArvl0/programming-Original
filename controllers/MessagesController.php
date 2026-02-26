<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/MessagesModel.php';
require_once __DIR__ . '/../models/NotificationsModel.php';
require_once __DIR__ . '/../includes/auth.php';

class MessagesController extends BaseController
{
    private ?MessagesModel $messagesModel = null;
    private ?NotificationsModel $notificationsModel = null;

    public function __construct()
    {
        try {
            $this->messagesModel = new MessagesModel();
        } catch (Throwable $exception) {
            $this->messagesModel = null;
        }
        try {
            $this->notificationsModel = new NotificationsModel();
        } catch (Throwable $exception) {
            $this->notificationsModel = null;
        }
    }

    public function index(): void
    {
        if (isset($_GET['ajax']) && $_GET['ajax'] !== '') {
            $this->handleAjax((string) $_GET['ajax']);
            return;
        }

        $receiverRole = 'ae';
        $receiverId = $this->currentAeId();
        $filter = strtolower(trim((string) ($_GET['filter'] ?? 'all')));
        $allowedFilters = ['all', 'customers', 'internal', 'unread'];
        if (!in_array($filter, $allowedFilters, true)) {
            $filter = 'all';
        }

        $threads = $this->messagesModel instanceof MessagesModel
            ? $this->messagesModel->getMessageThreadsForReceiver($receiverRole, $receiverId, $filter)
            : [];
        $unreadCount = $this->messagesModel instanceof MessagesModel
            ? $this->messagesModel->getUnreadCountForReceiver($receiverRole, $receiverId)
            : 0;

        $this->render('messages/index', [
            'pageTitle' => 'Messages',
            'pageSubtitle' => 'Customer and Internal Communication Center',
            'threads' => $threads,
            'selectedFilter' => $filter,
            'unreadCount' => $unreadCount
        ], [
            'styles' => ['css/account-executive.css']
        ]);
    }

    private function handleAjax(string $action): void
    {
        $receiverRole = 'ae';
        $receiverId = $this->currentAeId();
        $payload = $this->readJsonBody();

        switch ($action) {
            case 'header-summary':
                $this->jsonResponse([
                    'ok' => true,
                    'messages_unread' => $this->messagesModel instanceof MessagesModel
                        ? $this->messagesModel->getUnreadCountForReceiver($receiverRole, $receiverId)
                        : 0,
                    'notifications_unread' => $this->notificationsModel instanceof NotificationsModel
                        ? $this->notificationsModel->getUnreadCount()
                        : 0
                ]);
                return;

            case 'recent-messages':
                $items = $this->messagesModel instanceof MessagesModel
                    ? $this->messagesModel->getRecentMessagesForReceiver($receiverRole, $receiverId, 5)
                    : [];
                $this->jsonResponse([
                    'ok' => true,
                    'items' => $items
                ]);
                return;

            case 'recent-notifications':
                $items = $this->notificationsModel instanceof NotificationsModel
                    ? $this->notificationsModel->getRecentNotificationsGrouped(5)
                    : [];
                $this->jsonResponse([
                    'ok' => true,
                    'items' => $items
                ]);
                return;

            case 'mark-message-read':
                $messageId = (int) ($payload['message_id'] ?? 0);
                $ok = $messageId > 0
                    && $this->messagesModel instanceof MessagesModel
                    && $this->messagesModel->markMessageAsRead($messageId, $receiverRole, $receiverId);
                $this->jsonResponse([
                    'ok' => $ok,
                    'message' => $ok ? 'Message marked as read.' : 'Unable to mark message as read.'
                ], $ok ? 200 : 400);
                return;

            case 'mark-all-messages-read':
                $ok = $this->messagesModel instanceof MessagesModel
                    && $this->messagesModel->markAllAsReadForReceiver($receiverRole, $receiverId);
                $this->jsonResponse([
                    'ok' => $ok,
                    'message' => $ok ? 'All messages marked as read.' : 'Unable to mark all messages as read.'
                ], $ok ? 200 : 400);
                return;

            case 'mark-all-notifications-read':
                $ok = $this->notificationsModel instanceof NotificationsModel
                    && $this->notificationsModel->markAllAsRead();
                $this->jsonResponse([
                    'ok' => $ok,
                    'message' => $ok ? 'All notifications marked as read.' : 'Unable to mark all notifications as read.'
                ], $ok ? 200 : 400);
                return;

            case 'send-message':
                $senderRole = strtolower(trim((string) ($payload['sender_role'] ?? 'ae')));
                $senderId = (int) ($payload['sender_id'] ?? $receiverId);
                $targetType = strtolower(trim((string) ($payload['target_type'] ?? 'customer')));
                $receiverRoleForMessage = $targetType === 'internal' ? 'staff' : 'customer';
                $receiverIdForMessage = (int) ($payload['receiver_id'] ?? 0);
                $moduleOrigin = trim((string) ($payload['module_origin'] ?? 'CRM'));
                $messageText = trim((string) ($payload['message_text'] ?? ''));

                if ($receiverIdForMessage <= 0 || $messageText === '') {
                    $this->jsonResponse([
                        'ok' => false,
                        'message' => 'Receiver and message text are required.'
                    ], 400);
                    return;
                }

                $ok = $this->messagesModel instanceof MessagesModel
                    && $this->messagesModel->sendMessage(
                        $senderRole,
                        $senderId,
                        $receiverRoleForMessage,
                        $receiverIdForMessage,
                        $moduleOrigin,
                        $messageText
                    );
                $this->jsonResponse([
                    'ok' => $ok,
                    'message' => $ok ? 'Message sent successfully.' : 'Unable to send message.'
                ], $ok ? 200 : 400);
                return;

            default:
                $this->jsonResponse([
                    'ok' => false,
                    'message' => 'Unsupported AJAX action.'
                ], 400);
                return;
        }
    }

    private function readJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function currentAeId(): int
    {
        $sessionUser = $_SESSION['user'] ?? null;
        if (is_array($sessionUser) && isset($sessionUser['id'])) {
            return max(1, (int) $sessionUser['id']);
        }

        return 1;
    }
}
