<?php
class ChatController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // หา ID ของแอดมินคนแรก
    public function getAdminId() {
        try {
            $stmt = $this->db->prepare("SELECT user_id FROM users WHERE role = 'admin' LIMIT 1");
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return false;
        }
    }

    // ส่งข้อความ
    public function sendMessage($sender_id, $receiver_id, $message) {
        try {
            $sql = "INSERT INTO chat_messages (sender_id, receiver_id, message) VALUES (:sender, :receiver, :msg)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':sender' => $sender_id,
                ':receiver' => $receiver_id,
                ':msg' => $message
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // ดึงข้อความระหว่าง 2 คน
    public function getMessages($user1_id, $user2_id) {
        try {
            $sql = "SELECT m.*, u.first_name, u.last_name 
                    FROM chat_messages m
                    LEFT JOIN users u ON m.sender_id = u.user_id
                    WHERE (m.sender_id = :u1 AND m.receiver_id = :u2)
                       OR (m.sender_id = :u2 AND m.receiver_id = :u1)
                    ORDER BY m.created_at ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':u1' => $user1_id, ':u2' => $user2_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // ควรอ่านข้อความเมื่อมีการโหลดแชท
    public function markAsRead($sender_id, $receiver_id) {
        try {
            // ควรอ่านข้อความที่คนอื่นส่งมาให้เรา (เราเป็น receiver)
            $sql = "UPDATE chat_messages SET is_read = 1 
                    WHERE sender_id = :sender AND receiver_id = :receiver AND is_read = 0";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':sender' => $sender_id, ':receiver' => $receiver_id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getChatContacts() {
        try {
            // ดึงเฉพาะร้านค้าที่มีอยู่จริง (ใช้ INNER JOIN)
            $sql = "SELECT u.user_id, u.first_name, u.last_name, u.username, s.shop_name,
                    (SELECT COUNT(*) FROM chat_messages m WHERE m.sender_id = u.user_id AND m.is_read = 0) as unread_count,
                    (SELECT message FROM chat_messages m WHERE (m.sender_id = u.user_id OR m.receiver_id = u.user_id) ORDER BY m.created_at DESC LIMIT 1) as last_msg
                    FROM users u
                    INNER JOIN shops s ON u.user_id = s.owner_id
                    WHERE u.role = 'seller' AND (s.is_deleted = 0 OR s.is_deleted IS NULL)
                    ORDER BY unread_count DESC, u.first_name ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    // ลบข้อความแชทระหว่าง 2 คน
    public function deleteChatMessages($user1_id, $user2_id) {
        try {
            $sql = "DELETE FROM chat_messages 
                    WHERE (sender_id = :u1 AND receiver_id = :u2)
                       OR (sender_id = :u2 AND receiver_id = :u1)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':u1' => $user1_id, ':u2' => $user2_id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
