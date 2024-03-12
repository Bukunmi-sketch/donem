<?php

namespace App\Services;

class PushNotificationService {
    private $fcmApiKey;

    public function __construct() {
        $this->fcmApiKey = "";
    }

    public function sendNotification($registrationToken, $title, $body) {
        $url = 'https://fcm.googleapis.com/fcm/send';
        
        $headers = [
            'Authorization: key=' . $this->fcmApiKey,
            'Content-Type: application/json',
        ];

        $data = [
            'to' => $registrationToken,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
        ];

        $options = [
            'http' => [
                'header' => implode("\r\n", $headers),
                'method' => 'POST',
                'content' => json_encode($data),
            ],
        ];

        $context = stream_context_create($options);
        file_get_contents($url, false, $context);
    }
}
