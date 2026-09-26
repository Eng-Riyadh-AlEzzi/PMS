<?php

class NotificationRepository {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function createNotification(int $userId, string $type, string $title, string $message, ?string $link): bool {
        $stmt = $this->db->prepare("INSERT INTO notifications (user_id, type, title, message, link, is_read, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())");
        return $stmt->execute([$userId, $type, $title, $message, $link]);
    }

    public function getUnreadCount(int $userId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public function getLatestNotifications(int $userId, int $limit = 5): array {
        $stmt = $this->db->prepare("SELECT id, title, message, link, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->bindParam(1, $userId, PDO::PARAM_INT);
        $stmt->bindParam(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markAsRead(int $notificationId): bool {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
        return $stmt->execute([$notificationId]);
    }
}
