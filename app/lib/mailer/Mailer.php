<?php
use PHPMailer\PHPMailer\PHPMailer;
class Mailer {

    private $phpmailer_instance;
    private $host;

    private static $instance;

    public static function getInstance(){
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->host = Config::get('smtp_server');
        //SMTP needs accurate times, and the PHP time zone MUST be set
        //This should be done in your php.ini, but this is how to do it if you don't have access to that
        date_default_timezone_set('Etc/UTC');
        require ROOTDIR.'/vendor/autoload.php';
        //Create a new PHPMailer instance
        $mail = new PHPMailer;
        //Tell PHPMailer to use SMTP
        $mail->isSMTP();
        //Enable SMTP debugging
        // 0 = off (for production use)
        // 1 = client messages
        // 2 = client and server messages
        $mail->SMTPDebug = SMTP_DEBUG;
        //Set the hostname of the mail server
        $mail->Host = $this->host;
        //Set the SMTP port number - likely to be 25, 465 or 587
        $mail->Port = 25;
        $mail->SMTPAuth = false;
        $mail->SMTPSecure = false;
        $mail->SMTPAutoTLS = false;
        //Set who the message is to be sent from
        $mail->setFrom(SMTP_FROM.'@'.$this->host, SMTP_NAME);
        $this->phpmailer_instance = $mail;
    }

    public function send($to, $subject,$content) {
        $mail = $this->phpmailer_instance;
        //Set who the message is to be sent to
        $mail->addAddress($to);
        //Set the subject line
        $mail->Subject = $subject;
        //Read an HTML message body from an external file, convert referenced images to embedded,
        //convert HTML into a basic plain-text alternative body
        $mail->msgHTML($content);
        //send the message, check for errors
        if (!$mail->send()) {
            return false;
        } else {
            return true;
        }
    }

}