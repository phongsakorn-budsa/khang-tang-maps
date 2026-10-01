<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'asset/PHPMailer/Exception.php';
require 'asset/PHPMailer/PHPMailer.php';
require 'asset/PHPMailer/SMTP.php';

function sendOTP($email, $otp_code) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();                                            
        $mail->Host       = 'smtp.gmail.com';                     
        $mail->SMTPAuth   = true;                                   
        
        // ดึงค่า Config ของ Email (เพื่อไม่ให้รหัสผ่านหลุดขึ้น GitHub)
        if (file_exists('config/mail.php')) {
            require_once 'config/mail.php';
        }

        // ==========================================
        $mail->Username   = defined('SMTP_USERNAME') ? SMTP_USERNAME : 'your_email@gmail.com';                 
        $mail->Password   = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : 'your_app_password_here';                    
        // ==========================================
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            
        $mail->Port       = 465;                                    

        // Recipients
        $from_email = defined('SMTP_USERNAME') ? SMTP_USERNAME : 'your_email@gmail.com';
        $mail->setFrom($from_email, 'Wayside Support');
        $mail->addAddress($email);     

        // Content
        $mail->isHTML(true);                                  
        $mail->Subject = 'รหัส OTP สำหรับรีเซ็ตรหัสผ่าน - Wayside';
        $mail->Body    = '
        <div style="font-family: Arial, sans-serif; text-align: center; padding: 20px;">
            <h2>ระบบลืมรหัสผ่าน Wayside</h2>
            <p>รหัส OTP สำหรับตั้งรหัสผ่านใหม่ของคุณคือ:</p>
            <h1 style="color: #ff9800; font-size: 36px; letter-spacing: 5px;">' . $otp_code . '</h1>
            <p style="color: #666; font-size: 12px;">รหัสนี้มีอายุ 5 นาที หากคุณไม่ได้ทำรายการนี้ กรุณาเพิกเฉยต่ออีเมลฉบับนี้</p>
        </div>';

        $mail->send();
        return true;
    } catch (Exception $e) {
        // บันทึก Error ไว้สำหรับการ Debug
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>
