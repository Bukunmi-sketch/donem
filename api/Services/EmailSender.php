<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailSender {
    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);

        // Additional configuration settings
        $this->mailer->CharSet = 'UTF-8'; // Set the character set
        $this->mailer->Encoding = 'base64'; // Set the encoding type
        $this->mailer->SMTPAutoTLS = false; // Disable automatic TLS connection
        $this->mailer->SMTPSecure = false; // Set to false for non-TLS, or 'tls' or 'ssl' for TLS/SSL
        $this->mailer->Port =2525; // Adjust the port based on your mail server configuration
        $this->mailer->isHTML(true); // Set email format to HTML

        // Additional settings for debugging (remove in production)
        $this->mailer->SMTPDebug = 2; // Enable verbose debug output
        $this->mailer->Debugoutput = function ($str, $level) {
            // You can log or echo debug information here
            // For example: error_log("SMTPDebug: $str");
        };

        // Set your SMTP server credentials
        $this->mailer->isSMTP();
        $this->mailer->Host ='sandbox.smtp.mailtrap.io'; // Your SMTP server host
        $this->mailer->SMTPAuth = true; // Enable SMTP authentication
        $this->mailer->Username ='fa3f994793ce8a'; // Your SMTP username
        $this->mailer->Password = 'c528e02b12e62b'; // Your SMTP password
    }

    public function sendLoginNotification($recipientEmail, $username) {
       
        try {
            $username="bucuzzi";
            // Recipients
            $this->mailer->setFrom('your_email@example.com', 'Your Name');
            $this->mailer->addAddress($recipientEmail);
    
            // Content
            $this->mailer->Subject = 'Login Notification';
    
            // Load the HTML template
            // $templatePath = __DIR__ . '/templates/login_notification_template.html';
            $templatePath ='./Views/emails/login_notification_template.html';
            $htmlContent = file_get_contents($templatePath);
    
            // Replace placeholders in the template
            $htmlContent = str_replace('{USERNAME}', $username, $htmlContent);
    
            // Embed image in the email
            // $imagePath = __DIR__ . '/images/your_logo.png'; // Adjust the path to your image
            // $imageData = file_get_contents($imagePath);
            // $imageEncoded = base64_encode($imageData);
            // $this->mailer->AddEmbeddedImage("data:image/png;base64,$imageEncoded", 'logo', 'your_logo.png');
    
            // Set HTML content
            $this->mailer->msgHTML($htmlContent);
    
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function sendOTPNotification($recipientEmail, $otp) {
        try {
            // Recipients
            $this->mailer->setFrom('donemcargo@support.com', 'Donem');
            $this->mailer->addAddress($recipientEmail);

            // Content
            $this->mailer->Subject = 'Donem OTP Notification';
            $this->mailer->Body = "Your OTP is: $otp";

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }


    public function sendShipmentNotification($recipientEmail, $username, $trackingId) {
        try {
            // Recipients
            $this->mailer->setFrom('your_email@example.com', 'Your Name');
            $this->mailer->addAddress($recipientEmail);
    
            // Content
            $this->mailer->Subject = 'Shipment Notification';
    
            // Load the HTML template
            $templatePath = __DIR__ . '/templates/shipment_notification_template.html';
            $htmlContent = file_get_contents($templatePath);
    
            // Replace placeholders in the template
            $htmlContent = str_replace('{USERNAME}', $username, $htmlContent);
            $htmlContent = str_replace('{TRACKING_ID}', $trackingId, $htmlContent);
    
            // Embed image in the email
            $imagePath = __DIR__ . '/images/shipment_image.png'; // Adjust the path to your image
            $imageData = file_get_contents($imagePath);
            $imageEncoded = base64_encode($imageData);
            $this->mailer->AddEmbeddedImage("data:image/png;base64,$imageEncoded", 'shipment_image', 'shipment_image.png');
    
            // Set HTML content
            $this->mailer->msgHTML($htmlContent);
    
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    
    
}




// Example usage:
$emailSender = new EmailSender();
// $loginNotificationSent = $emailSender->sendLoginNotification('user@example.com', 'John Doe');
// $otpNotificationSent = $emailSender->sendOTPNotification('user@example.com', '123456');

// if ($loginNotificationSent) {
//     echo 'Login notification sent successfully.';
// } else {
//     echo 'Failed to send login notification.';
// }

// if ($otpNotificationSent) {
//     echo 'OTP notification sent successfully.';
// } else {
//     echo 'Failed to send OTP notification.';
// }
