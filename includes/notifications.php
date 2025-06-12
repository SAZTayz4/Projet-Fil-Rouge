<?php
session_start();

class NotificationSystem {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Ajouter une notification
    public function addNotification($utilisateur_id, $type, $message, $link = null) {
        $sql = "INSERT INTO notifications (utilisateur_id, type, message, link, created_at, is_read) 
                VALUES (?, ?, ?, ?, NOW(), 0)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$utilisateur_id, $type, $message, $link]);
    }
    
    // Récupérer les notifications non lues
    public function getUnreadNotifications($utilisateur_id) {
        $sql = "SELECT * FROM notifications 
                WHERE utilisateur_id = ? AND is_read = 0 
                ORDER BY created_at DESC 
                LIMIT 10";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$utilisateur_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Marquer une notification comme lue
    public function markAsRead($notificationId) {
        $sql = "UPDATE notifications SET is_read = 1 WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$notificationId]);
    }
    
    // Marquer toutes les notifications comme lues
    public function markAllAsRead($utilisateur_id) {
        $sql = "UPDATE notifications SET is_read = 1 WHERE utilisateur_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$utilisateur_id]);
    }
    
    // Compter les notifications non lues
    public function countUnreadNotifications($utilisateur_id) {
        $sql = "SELECT COUNT(*) FROM notifications WHERE utilisateur_id = ? AND is_read = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$utilisateur_id]);
        return $stmt->fetchColumn();
    }
}

// Types de notifications
define('NOTIF_TYPE_DROP', 'drop');
define('NOTIF_TYPE_REMINDER', 'reminder');
define('NOTIF_TYPE_PRICE', 'price');
define('NOTIF_TYPE_STOCK', 'stock');
?> 