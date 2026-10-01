# วิธีการติดตั้งและรันโปรเจกต์ (Installation Setup)

เพื่อให้สามารถทดสอบโปรเจกต์นี้ในเครื่องคอมพิวเตอร์ของคุณ (Localhost) โปรดทำตามขั้นตอนดังนี้:

### 1. เตรียมความพร้อม:
ติดตั้ง XAMPP หรือโปรแกรมจำลอง Server อื่นๆ ที่รองรับ PHP และ MySQL

### 2. คัดลอกโปรเจกต์:
Clone หรือ ดาวน์โหลดโปรเจกต์นี้ไปไว้ในโฟลเดอร์ `htdocs` (สำหรับ XAMPP) เปลี่ยนชื่อโฟลเดอร์เป็น `wayside_edit` (หรือชื่อตามต้องการ) 
ตัวอย่าง path: `C:\xampp\htdocs\wayside_edit`

### 3. นำเข้าฐานข้อมูล (Database):
1. เปิด phpMyAdmin (ปกติจะอยู่ที่ http://localhost/phpmyadmin)
2. สร้างฐานข้อมูลใหม่ (ตัวอย่าง: `wayside_edit` หรือตั้งชื่อตามที่คุณใช้งาน)
3. กดที่แท็บ Import และเลือกไฟล์ SQL ของระบบ (เช่น `wayside.sql` หรือไฟล์ฐานข้อมูลของคุณ)

### 4. การตั้งค่า (Configuration):

**ตั้งค่าฐานข้อมูล (Database):**
เปลี่ยนชื่อไฟล์ `connect.example.php` เป็น `connect.php` และไฟล์ `config/connect.example.php` เป็น `config/connect.php` จากนั้นเข้าไปแก้ไขข้อมูลเชื่อมต่อฐานข้อมูลในทั้ง 2 ไฟล์:
```php
$host = "localhost";
$username = "root";
$password = "";
$db = "ชื่อฐานข้อมูลของคุณ";
```

**ตั้งค่าการส่งอีเมล (OTP สำหรับลืมรหัสผ่าน):**
เปลี่ยนชื่อไฟล์ `config/mail.example.php` เป็น `config/mail.php` แล้วเข้าไปกรอกอีเมลและ App Password ของคุณ (รหัสผ่านนี้จะไม่ถูกนำขึ้น Git):
```php
define('SMTP_USERNAME', 'your_email@gmail.com');
define('SMTP_PASSWORD', 'your_app_password_here');
```

### 5. เข้าใช้งานระบบ:
เปิดเว็บบราวเซอร์แล้วเข้าไปที่ http://localhost/wayside_edit


---

## 📁 โครงสร้างโปรเจกต์เบื้องต้น

```text
wayside_edit/
├── admin/              # ระบบจัดการสำหรับผู้ดูแลระบบ (Admin)
├── api/                # API สำหรับจัดการข้อมูลต่างๆ (เช่น Chat)
├── asset/              # ไฟล์ CSS, JS (Bootstrap) และ PHPMailer
├── config/             # ไฟล์ตั้งค่าและเชื่อมต่อ DB, Mail (เช่น connect.php, mail.php, Controller)
├── layout/             # ไฟล์ส่วนประกอบหน้าเว็บ (Header, Footer, Session)
├── seller/             # ระบบจัดการสำหรับร้านค้า (Seller)
├── uploads/            # โฟลเดอร์เก็บไฟล์รูปภาพอัปโหลด
├── index.php           # หน้าแรกของเว็บไซต์ (แสดงแผนที่ร้านค้า)
├── login.php           # หน้าเข้าสู่ระบบ
├── register.php        # หน้าสมัครสมาชิกใหม่
├── forgot_password.php # หน้าลืมรหัสผ่าน
├── send_otp.php        # ไฟล์ระบบส่งรหัสผ่าน OTP
├── view_shop.php       # หน้าแสดงรายละเอียดของร้านค้านั้นๆ
```
