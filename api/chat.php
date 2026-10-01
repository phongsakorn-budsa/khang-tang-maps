<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION["userid"])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once("../config/connect.php");
require_once("../config/chat_controller.php");

$chatCtrl = new ChatController($pdo);
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$my_id = $_SESSION["userid"];

if ($action == 'send') {
    $receiver_id = $_POST['receiver_id'] ?? 0;
    $message = trim($_POST['message'] ?? '');
    
    if (empty($message) || $receiver_id == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
        exit;
    }
    
    if ($chatCtrl->sendMessage($my_id, $receiver_id, $message)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'DB error']);
    }
    
} elseif ($action == 'get') {
    $other_id = $_GET['other_id'] ?? 0;
    
    if ($other_id == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid other_id']);
        exit;
    }
    
    // Mark as read
    $chatCtrl->markAsRead($other_id, $my_id);
    
    $messages = $chatCtrl->getMessages($my_id, $other_id);
    echo json_encode(['status' => 'success', 'data' => $messages, 'my_id' => $my_id]);
    
} elseif ($action == 'get_contacts') {
    // Only for admin
    if ($_SESSION["role"] != 'admin') {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    
    $contacts = $chatCtrl->getChatContacts();
    echo json_encode(['status' => 'success', 'data' => $contacts]);
    
} elseif ($action == 'get_admin_id') {
    $admin_id = $chatCtrl->getAdminId();
    if ($admin_id) {
        echo json_encode(['status' => 'success', 'admin_id' => $admin_id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Admin not found']);
    }
} elseif ($action == 'delete_chat') {
    $other_id = $_POST['other_id'] ?? 0;
    
    if ($other_id == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid other_id']);
        exit;
    }
    
    if ($chatCtrl->deleteChatMessages($my_id, $other_id)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete chat']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>
