<?php
require_once("../config/connect.php");
require_once("../layout/header.php");

// เช็คสิทธิ์
if (!isset($_SESSION['userid']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}
?>

<style>
    .chat-wrapper {
        height: calc(100vh - 130px);
        border: 1px solid #dee2e6;
        border-radius: 8px;
        background: #fff;
        overflow: hidden;
    }
    .contact-list {
        height: 100%;
        overflow-y: auto;
        border-right: 1px solid #dee2e6;
        background: #f8f9fa;
    }
    .contact-item {
        padding: 15px;
        border-bottom: 1px solid #dee2e6;
        cursor: pointer;
        transition: 0.2s;
    }
    .contact-item:hover, .contact-item.active {
        background: #fff3cd;
    }
    .chat-container {
        flex-grow: 1;
        overflow-y: auto;
        padding: 15px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        background: #fdfdfd;
    }
    .chat-input-area {
        height: 70px;
        flex-shrink: 0;
        border-top: 1px solid #dee2e6;
        padding: 15px;
        background: #fff;
    }
    .msg-bubble {
        max-width: 75%;
        padding: 10px 15px;
        border-radius: 15px;
        font-size: 0.95rem;
    }
    .msg-seller {
        background: #f1f3f5;
        border: 1px solid #e0e0e0;
        align-self: flex-start;
        border-bottom-left-radius: 2px;
    }
    .msg-me {
        background: #dc3545;
        color: #fff;
        align-self: flex-end;
        border-bottom-right-radius: 2px;
    }
    .msg-time {
        font-size: 0.7rem;
        color: #adb5bd;
        margin-top: 4px;
        text-align: right;
    }
    .msg-me .msg-time {
        color: rgba(255,255,255,0.8);
    }
    .empty-chat {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #6c757d;
        flex-direction: column;
    }
</style>

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-12 d-flex align-items-center gap-3">
            <a href="dashbord.php" class="btn btn-light shadow-sm border rounded-circle d-flex align-items-center justify-content-center text-dark" style="width: 42px; height: 42px;" title="กลับไปหน้า Dashboard">
                <i class="bi bi-arrow-left fw-bold fs-5"></i>
            </a>
            <h3 class="fw-bold mb-0"><i class="bi bi-chat-left-text-fill text-danger me-2"></i> แชทกับร้านค้า</h3>
        </div>
    </div>

    <div class="row chat-wrapper mx-0 shadow-sm">
        <!-- ฝั่งซ้าย: รายชื่อคนคุย -->
        <div class="col-md-4 col-lg-3 p-0 contact-list position-relative d-flex flex-column" style="height: 100%;">
            <div class="p-2 border-bottom sticky-top bg-white z-1">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0 rounded-start-pill"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="contactSearch" class="form-control bg-light border-start-0 rounded-end-pill px-0" placeholder="ค้นหาร้านค้า..." oninput="filterContacts()">
                </div>
            </div>
            <div id="contactList" class="flex-grow-1 overflow-auto">
                <div class="p-4 text-center text-muted">
                    <div class="spinner-border spinner-border-sm"></div> กำลังโหลด...
                </div>
            </div>
            
            <div class="mt-auto p-3 border-top bg-light">
                <a href="/wayside_edit/logout.php" class="btn btn-danger text-white w-100 fw-bold shadow-sm" style="border-radius: 10px;">
                    <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
                </a>
            </div>
        </div>

        <!-- ฝั่งขวา: ห้องแชท -->
        <div class="col-md-8 col-lg-9 p-0 d-flex flex-column position-relative" style="height: 100%;">
            <!-- Header สำหรับห้องแชท -->
            <div class="d-flex justify-content-between align-items-center p-3 border-bottom bg-white shadow-sm z-1" id="chatHeader" style="display:none !important;">
                <h6 class="mb-0 fw-bold text-dark" id="currentChatName"><i class="bi bi-shop text-warning me-2"></i>ร้านค้า</h6>
                <button class="btn btn-outline-danger btn-sm rounded-pill fw-bold" onclick="deleteChat()" title="ล้างข้อความแชททั้งหมด">
                    <i class="bi bi-trash-fill"></i> ลบแชท
                </button>
            </div>

            <div id="chatBox" class="chat-container">
                <div class="empty-chat">
                    <i class="bi bi-chat-square-dots display-3 mb-3 opacity-25"></i>
                    <h5>เลือกร้านค้าเพื่อเริ่มแชท</h5>
                </div>
            </div>

            <div class="chat-input-area d-flex gap-2" id="inputArea" style="display:none !important;">
                <input type="text" id="msgInput" class="form-control bg-light border-0" placeholder="พิมพ์ข้อความตอบกลับ..." onkeypress="handleEnter(event)">
                <button class="btn btn-danger fw-bold px-4" onclick="sendMessage()" id="sendBtn">
                    ส่ง <i class="bi bi-send-fill ms-1"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentSellerId = 0;
let lastMsgCount = 0;
let allContacts = []; // เก็บรายชื่อทั้งหมดไว้ทำ Search

// โหลดรายชื่อผู้ติดต่อ
function loadContacts() {
    fetch('../api/chat.php?action=get_contacts')
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                allContacts = data.data;
                filterContacts();
            }
        });
}

function filterContacts() {
    const keyword = document.getElementById('contactSearch').value.toLowerCase().trim();
    const filtered = allContacts.filter(c => {
        const name = (c.shop_name ? c.shop_name : c.first_name + ' ' + c.last_name).toLowerCase();
        return name.includes(keyword);
    });
    renderContacts(filtered);
}

function renderContacts(contacts) {
    const list = document.getElementById('contactList');
    if(contacts.length === 0) {
        list.innerHTML = '<div class="p-4 text-center text-muted">ยังไม่มีร้านค้าในระบบ</div>';
        return;
    }

    let html = '';
    contacts.forEach(c => {
        const activeClass = (c.user_id == currentSellerId) ? 'active' : '';
        const badge = c.unread_count > 0 ? `<span class="badge bg-danger rounded-pill float-end">${c.unread_count}</span>` : '';
        const lastMsg = c.last_msg ? `<div class="text-muted small text-truncate mt-1">${escapeHtml(c.last_msg)}</div>` : '';
        const displayName = c.shop_name ? escapeHtml(c.shop_name) : escapeHtml(c.first_name + ' ' + c.last_name);
        
        html += `
            <div class="contact-item ${activeClass}" onclick="selectSeller(${c.user_id}, '${displayName.replace(/'/g, "\\'")}')">
                <div class="fw-bold text-dark">
                    <i class="bi bi-shop text-warning me-1"></i> ${displayName}
                    ${badge}
                </div>
                ${lastMsg}
            </div>
        `;
    });
    list.innerHTML = html;
}

function selectSeller(sellerId, sellerName) {
    currentSellerId = sellerId;
    lastMsgCount = 0;
    
    // โชว์ Header และกล่องพิมพ์
    document.getElementById('chatHeader').style.setProperty('display', 'flex', 'important');
    document.getElementById('currentChatName').innerHTML = `<i class="bi bi-shop text-warning me-2"></i>${sellerName}`;
    document.getElementById('inputArea').style.setProperty('display', 'flex', 'important');
    document.getElementById('chatBox').innerHTML = '<div class="text-center text-muted my-auto"><div class="spinner-border spinner-border-sm"></div> กำลังโหลด...</div>';
    
    // โหลดรายชื่อใหม่เพื่อลบ badge
    loadContacts();
    
    // โหลดข้อความ
    loadMessages();
}

function deleteChat() {
    if(currentSellerId == 0) return;
    if(!confirm("คุณต้องการลบข้อความแชททั้งหมดกับร้านค้านี้ใช่หรือไม่?\n(เมื่อลบแล้วจะไม่สามารถกู้คืนได้)")) return;
    
    const formData = new FormData();
    formData.append('action', 'delete_chat');
    formData.append('other_id', currentSellerId);
    
    fetch('../api/chat.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            lastMsgCount = 0;
            document.getElementById('chatBox').innerHTML = '<div class="text-center text-muted my-auto">ลบข้อความแชทเรียบร้อยแล้ว</div>';
            loadContacts();
        } else {
            alert('เกิดข้อผิดพลาดในการลบแชท');
        }
    });
}

function loadMessages() {
    if(currentSellerId == 0) return;
    
    fetch('../api/chat.php?action=get&other_id=' + currentSellerId)
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
            chatBox.innerHTML = '<div class="text-center text-muted my-auto">เริ่มคุยกับร้านค้านี้</div>';
        }
        return;
    }
    
    if(messages.length !== lastMsgCount) {
        lastMsgCount = messages.length;
        const atBottom = chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight < 50;

        let html = '';
        messages.forEach(msg => {
            const isMe = (msg.sender_id == myId);
            const time = new Date(msg.created_at).toLocaleTimeString('th-TH', {hour: '2-digit', minute:'2-digit'});
            
            if(isMe) {
                html += `
                    <div class="msg-bubble msg-me shadow-sm">
                        <div>${escapeHtml(msg.message)}</div>
                        <div class="msg-time">${time} ${msg.is_read == 1 ? '<i class="bi bi-check2-all text-white"></i>' : '<i class="bi bi-check2"></i>'}</div>
                    </div>
                `;
            } else {
                html += `
                    <div class="msg-bubble msg-seller shadow-sm">
                        <div class="fw-bold text-dark mb-1" style="font-size:0.8rem;"><i class="bi bi-shop"></i> ร้านค้า</div>
                        <div>${escapeHtml(msg.message)}</div>
                        <div class="msg-time">${time}</div>
                    </div>
                `;
            }
        });
        
        chatBox.innerHTML = html;
        
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
    
    if(msg === '' || currentSellerId == 0) return;
    
    input.value = '';
    
    const formData = new FormData();
    formData.append('action', 'send');
    formData.append('receiver_id', currentSellerId);
    formData.append('message', msg);
    
    fetch('../api/chat.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            loadMessages();
            loadContacts();
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

// Initial Load
loadContacts();

// Polling
setInterval(() => {
    loadContacts();
    if(currentSellerId != 0) {
        loadMessages();
    }
}, 3000);
</script>

<?php require_once("../layout/footer.php"); ?>
