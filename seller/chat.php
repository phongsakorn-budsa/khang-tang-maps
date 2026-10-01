<?php 
require_once("../config/connect.php");
require_once("../layout/header.php");

// เช็คสิทธิ์
if (!isset($_SESSION['userid']) || $_SESSION['role'] != 'seller') {
    header("Location: ../index.php");
    exit;
}
?>

<style>
    .chat-container {
        height: 60vh;
        overflow-y: auto;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .msg-bubble {
        max-width: 75%;
        padding: 10px 15px;
        border-radius: 15px;
        font-size: 0.95rem;
    }
    .msg-admin {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        align-self: flex-start;
        border-bottom-left-radius: 2px;
    }
    .msg-me {
        background: #ffeb3b;
        color: #333;
        align-self: flex-end;
        border-bottom-right-radius: 2px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .msg-time {
        font-size: 0.7rem;
        color: #888;
        margin-top: 4px;
        text-align: right;
    }
    .chat-input-area {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 10px;
        margin-top: 15px;
    }
</style>

<div class="container my-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h3 class="fw-bold"><i class="bi bi-chat-dots-fill text-warning me-2"></i> แชทติดต่อแอดมิน</h3>
        <a href="myshop.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> กลับไปร้านค้าของฉัน</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            
            <div id="chatBox" class="chat-container">
                <div class="text-center text-muted my-auto" id="loadingMsg">
                    <div class="spinner-border spinner-border-sm me-2"></div> กำลังโหลดข้อความ...
                </div>
            </div>

            <div class="chat-input-area d-flex gap-2">
                <input type="text" id="msgInput" class="form-control bg-light border-0" placeholder="พิมพ์ข้อความของคุณ..." onkeypress="handleEnter(event)">
                <button class="btn btn-warning fw-bold px-4" onclick="sendMessage()" id="sendBtn">
                    ส่ง <i class="bi bi-send-fill ms-1"></i>
                </button>
            </div>

        </div>
    </div>
</div>

<script>
let adminId = 0;
let lastMsgCount = 0;
let isScrolling = false;

// 1. หา Admin ID ก่อน
fetch('../api/chat.php?action=get_admin_id')
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            adminId = data.admin_id;
            loadMessages();
            // Polling ทุกๆ 3 วินาที
            setInterval(loadMessages, 3000);
        } else {
            document.getElementById('chatBox').innerHTML = '<div class="text-center text-danger my-auto">ไม่พบแอดมินในระบบ</div>';
        }
    });

function loadMessages() {
    if(adminId == 0) return;
    
    fetch('../api/chat.php?action=get&other_id=' + adminId)
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                renderMessages(data.data, data.my_id);
            }
        });
}

function renderMessages(messages, myId) {
    const chatBox = document.getElementById('chatBox');
    
    if(messages.length === 0) {
        if(lastMsgCount === 0) {
            chatBox.innerHTML = '<div class="text-center text-muted my-auto"><i class="bi bi-info-circle display-4 opacity-25"></i><br>ยังไม่มีข้อความ<br>เริ่มพิมพ์ทักทายแอดมินเลย!</div>';
        }
        return;
    }
    
    // ถ้ามีข้อความใหม่ ค่อย re-render เพื่อไม่ให้กระตุก
    if(messages.length !== lastMsgCount) {
        lastMsgCount = messages.length;
        
        // เช็คว่า user สกอร์ดูข้อความเก่าอยู่หรือเปล่า
        const atBottom = chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight < 50;

        let html = '';
        messages.forEach(msg => {
            const isMe = (msg.sender_id == myId);
            const time = new Date(msg.created_at).toLocaleTimeString('th-TH', {hour: '2-digit', minute:'2-digit'});
            
            if(isMe) {
                html += `
                    <div class="msg-bubble msg-me">
                        <div>${escapeHtml(msg.message)}</div>
                        <div class="msg-time">${time} ${msg.is_read == 1 ? '<i class="bi bi-check2-all text-primary"></i>' : '<i class="bi bi-check2"></i>'}</div>
                    </div>
                `;
            } else {
                html += `
                    <div class="msg-bubble msg-admin shadow-sm">
                        <div class="fw-bold text-danger" style="font-size:0.75rem;"><i class="bi bi-shield-lock-fill"></i> แอดมิน</div>
                        <div>${escapeHtml(msg.message)}</div>
                        <div class="msg-time">${time}</div>
                    </div>
                `;
            }
        });
        
        chatBox.innerHTML = html;
        
        // เลื่อนลงล่างสุดถ้าแต่ก่อนอยู่ล่างสุด
        if(atBottom || chatBox.scrollTop === 0) {
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    }
}

function handleEnter(e) {
    if(e.key === 'Enter') {
        sendMessage();
    }
}

function sendMessage() {
    const input = document.getElementById('msgInput');
    const msg = input.value.trim();
    
    if(msg === '' || adminId == 0) return;
    
    input.value = '';
    
    const formData = new FormData();
    formData.append('action', 'send');
    formData.append('receiver_id', adminId);
    formData.append('message', msg);
    
    fetch('../api/chat.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            loadMessages(); // โหลดทันทีหลังส่ง
        } else {
            alert('ส่งข้อความไม่สำเร็จ');
        }
    });
}

function escapeHtml(unsafe) {
    return (unsafe || '').toString()
         .replace(/&/g, "&amp;")
         .replace(/</g, "&lt;")
         .replace(/>/g, "&gt;")
         .replace(/"/g, "&quot;")
         .replace(/'/g, "&#039;");
}
</script>

<?php require_once("../layout/footer.php"); ?>
