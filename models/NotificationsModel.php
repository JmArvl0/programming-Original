<?php

require_once __DIR__ . '/../config/database.php';

class NotificationsModel
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = getDBConnection();
    }

    public function createNotification(string $type, string $moduleSource, int $relatedId, string $message): bool
    {
        $sql = 'INSERT INTO notifications (type, module_source, related_id, message, is_read, created_at)
                VALUES (?, ?, ?, ?, 0, NOW())';
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('ssis', $type, $moduleSource, $relatedId, $message);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function getUnreadCount(): int
    {
        $result = $this->db->query('SELECT COUNT(*) AS total FROM notifications WHERE is_read = 0');
        if (!$result) {
            return 0;
        }

        $row = $result->fetch_assoc();
        return (int) ($row['total'] ?? 0);
    }

    public function getRecentNotificationsGrouped(int $limit = 5): array
    {
        $safeLimit = max(1, min($limit, 20));
        $result = $this->db->query(
            'SELECT id, type, module_source, related_id, message, is_read, created_at
             FROM notifications
             ORDER BY created_at DESC
             LIMIT ' . $safeLimit
        );

        if (!$result) {
            return [];
        }

        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $grouped = [];
        foreach ($rows as $row) {
            $module = (string) ($row['module_source'] ?? 'General');
            if (!isset($grouped[$module])) {
                $grouped[$module] = [];
            }
            $grouped[$module][] = $row;
        }

        return $grouped;
    }

    public function markAllAsRead(): bool
    {
        return (bool) $this->db->query('UPDATE notifications SET is_read = 1 WHERE is_read = 0');
    }

    public function moduleLink(string $moduleSource): string
    {
        $module = strtolower(trim($moduleSource));
        return match ($module) {
            'crm' => 'crm.php',
            'passport', 'passport visa', 'passport&visa', 'passport_visa' => 'passport_visa.php',
            'schedule', 'schedule rates' => 'schedule_rates.php',
            'facilities' => 'facilities.php',
            'financial' => 'account_executive.php',
            default => 'index.php',
        };
    }
}
