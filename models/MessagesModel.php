<?php

require_once __DIR__ . '/../config/database.php';

class MessagesModel
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = getDBConnection();
    }

    public function getUnreadCountForReceiver(string $receiverRole, int $receiverId): int
    {
        $sql = 'SELECT COUNT(*) AS total FROM messages WHERE receiver_role = ? AND receiver_id = ? AND is_read = 0';
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return 0;
        }

        $stmt->bind_param('si', $receiverRole, $receiverId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        return (int) ($row['total'] ?? 0);
    }

    public function getRecentMessagesForReceiver(string $receiverRole, int $receiverId, int $limit = 5): array
    {
        $safeLimit = max(1, min($limit, 20));
        $sql = 'SELECT id, sender_role, sender_id, receiver_role, receiver_id, module_origin, message_text, is_read, created_at
                FROM messages
                WHERE (receiver_role = ? AND receiver_id = ?) OR (sender_role = ? AND sender_id = ?)
                ORDER BY created_at DESC
                LIMIT ' . $safeLimit;
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('sisi', $receiverRole, $receiverId, $receiverRole, $receiverId);
        $stmt->execute();
        $result = $stmt->get_result();
        $messages = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();

        return $messages;
    }

    public function getMessageThreadsForReceiver(string $receiverRole, int $receiverId, string $filter = 'all'): array
    {
        $where = 'WHERE ((receiver_role = ? AND receiver_id = ?) OR (sender_role = ? AND sender_id = ?))';
        if ($filter === 'customers') {
            $where .= " AND (sender_role = 'customer' OR receiver_role = 'customer')";
        } elseif ($filter === 'internal') {
            $where .= " AND (sender_role = 'staff' OR receiver_role = 'staff')";
        } elseif ($filter === 'unread') {
            $where .= ' AND receiver_role = ? AND receiver_id = ? AND is_read = 0';
        }

        $sql = 'SELECT id, sender_role, sender_id, receiver_role, receiver_id, module_origin, message_text, is_read, created_at
                FROM messages
                ' . $where . '
                ORDER BY created_at DESC';
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }

        if ($filter === 'unread') {
            $stmt->bind_param('sisisi', $receiverRole, $receiverId, $receiverRole, $receiverId, $receiverRole, $receiverId);
        } else {
            $stmt->bind_param('sisi', $receiverRole, $receiverId, $receiverRole, $receiverId);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $messages = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();

        return $this->buildThreads($messages, $receiverRole, $receiverId);
    }

    public function sendMessage(
        string $senderRole,
        int $senderId,
        string $receiverRole,
        int $receiverId,
        string $moduleOrigin,
        string $messageText
    ): bool {
        $sql = 'INSERT INTO messages (sender_role, sender_id, receiver_role, receiver_id, module_origin, message_text, is_read, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 0, NOW())';
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('sisiss', $senderRole, $senderId, $receiverRole, $receiverId, $moduleOrigin, $messageText);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function markMessageAsRead(int $messageId, string $receiverRole, int $receiverId): bool
    {
        $sql = 'UPDATE messages SET is_read = 1 WHERE id = ? AND receiver_role = ? AND receiver_id = ?';
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('isi', $messageId, $receiverRole, $receiverId);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function markAllAsReadForReceiver(string $receiverRole, int $receiverId): bool
    {
        $sql = 'UPDATE messages SET is_read = 1 WHERE receiver_role = ? AND receiver_id = ? AND is_read = 0';
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('si', $receiverRole, $receiverId);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    private function buildThreads(array $messages, string $receiverRole, int $receiverId): array
    {
        $threads = [];

        foreach ($messages as $message) {
            $senderRole = (string) ($message['sender_role'] ?? '');
            $senderId = (int) ($message['sender_id'] ?? 0);
            $messageReceiverRole = (string) ($message['receiver_role'] ?? '');
            $messageReceiverId = (int) ($message['receiver_id'] ?? 0);
            $participantRole = $senderRole;
            $participantId = $senderId;
            if ($senderRole === $receiverRole && $senderId === $receiverId) {
                $participantRole = $messageReceiverRole;
                $participantId = $messageReceiverId;
            }

            $key = $participantRole . ':' . $participantId;

            if (!isset($threads[$key])) {
                $threads[$key] = [
                    'thread_key' => $key,
                    'participant_role' => $participantRole,
                    'participant_id' => $participantId,
                    'module_origin' => (string) ($message['module_origin'] ?? ''),
                    'last_message' => (string) ($message['message_text'] ?? ''),
                    'last_message_at' => (string) ($message['created_at'] ?? ''),
                    'unread_count' => 0,
                    'messages' => []
                ];
            }

            $threads[$key]['messages'][] = $message;
            if ((int) ($message['is_read'] ?? 0) === 0
                && (string) ($message['receiver_role'] ?? '') === $receiverRole
                && (int) ($message['receiver_id'] ?? 0) === $receiverId
            ) {
                $threads[$key]['unread_count']++;
            }
        }

        return array_values($threads);
    }
}
