<?php
require "./Models/Shipment.php";
require "./Services/PushNotificationService.php";

use App\Services\PushNotificationService;
// use Shipment\Shipment;

class ShipmentController {
    private $shipmentModel;
    private $database;
    private $conn;
    private $authHandler;
    private $pushNotificationService;

    public function __construct() {
        $this->database = new Database();
        $this->conn = $this->database->getConnection();
        $this->shipmentModel = new Shipment($this->conn);
        $this->authHandler =  new AuthHandler('your_secret_key'); 
        $this->pushNotificationService = new PushNotificationService();
    }

    public function createShipment($userId, $trackingId) {
        // Logic for creating a shipment
        if ($this->shipmentModel->createShipment($userId, $trackingId)) {
            // Send push notification to user
            $this->sendShipmentNotification($userId, $trackingId);
            return json_encode(['message' => 'Shipment created successfully']);
        } else {
            return json_encode(['error' => 'Failed to create shipment']);
        }
    }

    public function addPackage(){
        
    }

    public function getShipmentByTrackingId($trackingId) {
        // Logic for retrieving shipment by tracking ID
        $shipment = $this->shipmentModel->getShipmentByTrackingId($trackingId);

        if ($shipment) {
            return json_encode(['shipment' => $shipment]);
        } else {
            return json_encode(['error' => 'Shipment not found']);
        }
    }

    private function sendShipmentNotification($userId, $trackingId) {
        // Retrieve FCM registration token for the user (assuming it's stored in the 'users' table)
        $registrationToken = $this->authHandler->getFcmRegistrationToken($userId);

        if ($registrationToken) {
            // Send push notification
            $title = 'Shipment Update';
            $body = "Your shipment with tracking ID $trackingId has been updated.";
            $this->pushNotificationService->sendNotification($registrationToken, $title, $body);
        }
    }

    // Add other shipment-related methods as needed
}
