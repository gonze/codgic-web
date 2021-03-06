<?php
/**
* Codgic Mailing Configuration File
* ===============================
* This is the email configuration file of Codgic. Codgic uses PHPMailer to achieve sending emails.
* Set up an administrator email account here so that users could recieve emails from you.
* Moreover, you can customize various email templates here.
*
* 1. Account
* ----------------
* Please set up your email account inside the postmail() function.
* You may need to check out PHPMailer document.
*/

//Basic Connection Info

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

function postmail($to,$subject = '',$body = ''){
    //error_reporting(E_STRICT);
    date_default_timezone_set('Asia/Shanghai');
    $mail = new PHPMailer(); 
    $mail->CharSet ="UTF-8";
    $mail->Encoding ="base64";
    $mail->IsSMTP();
    //$mail->SMTPDebug  = 0;                     // 2 - DISABLE
    $mail->SMTPAuth   = true;               
    $mail->SMTPSecure = "ssl";         
    $mail->Host       = SMTP_SERVER;      //SMTP Server Address
    $mail->Port       = SMTP_PORT;     //SMTP Service Port             
    $mail->Username   = SMTP_USER;  //Input your admin email
    $mail->Password   = SMTP_PASSWORD;  //Input your email password.     
    $mail->SetFrom(SMTP_USER, SMTP_DISPLAY);
    $mail->AddReplyTo(SMTP_USER, SMTP_DISPLAY);
    $mail->Subject    = $subject;
    $mail->WordWrap = 60;
    //$mail->AltBody    = 'To view the message, please use an HTML compatible email viewer!'; // optional, comment out and test
    $mail->MsgHTML($body);
    $address = $to;
    $mail->AddAddress($address, '');
    $mail->IsHTML(true); 
    if(!$mail->Send()) {
        return $mail->ErrorInfo;
    } else {
        return 'success';
    }
}

/**
* 2. Functions
* ----------------
* Here are various email generating functions you may would like to customize.
*/

//Generate Password Reset Email.
function resetpwd_mail(){
    require __DIR__.'/ojsettings.php';
    if(!isset($_SESSION['resetpwd_user']) || !isset($_SESSION['resetpwd_email']) || !isset($_SESSION['resetpwd_code'])) 
        return 'timeout';
    $user = $_SESSION['resetpwd_user'];
    $email = $_SESSION['resetpwd_email'];
    $code = $_SESSION['resetpwd_code'];
    $subject = "$oj_name 密码重置验证";
    require __DIR__.'/userinfo.php';
    $ip = get_ip();
    $nowtime = date("Y/m/d H:i:s");
    $content = "<div>亲爱的 $user ,<br><p>我们收到了您在 {$constant('OJ_NAME')} 重置密码的请求并发送了验证码来确认您的身份。</p><b><p>请求时间: $nowtime (UTC+08:00)</p><p>IP地址: $ip</p><p>验证码: $code</p></b><p>如果您没有在 {$constant('OJ_NAME')} 有过重置密码的请求，您只需忽略这封邮件并不要把验证码告诉任何人。<br>如有任何问题，请回复该邮件来与管理员取得联系。</p><br>谢谢！<p>{$constant('OJ_COPYRIGHT')}</p></div>";
    return postmail($email,$subject,$content);
}
