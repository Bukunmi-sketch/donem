<?php

// require './Services/AuthHandler.php';
require "./Models/Shipment.php";
// require './Services/ResponseHandler.php';
require "./Services/PushNotificationService.php";

// use App\Services\PushNotificationService;
// use Shipment\Shipment;

class ShipmentController
{
    private $shipmentModel;
    private $database;
    private $conn;
    private $response;
    private $auth;
    private $pushNotificationService;

    public function __construct()
    {
        $this->database = new Database();
        $this->conn = $this->database->getConnection();
        $this->shipmentModel = new Shipment($this->conn);
        $this->response = new Response();
        $this->auth = new AuthHandler('donem');
        // $this->pushNotificationService = new PushNotificationService();
    }

   public function generateTrackingId() {
        $character = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $trackingId = '';
        for ($i = 0; $i < 10; $i++) {
            $index = rand(0, strlen($character) - 1);
            $trackingId .= $character[$index];
        }
        return $trackingId;
    }
    

    public function createShipment($userId)
    {
        $trackingId =$this->generateTrackingId();

        $requestBody = json_decode(file_get_contents("php://input"), true);
        $requiredFields = ['pickupAddress', 'destination', 'shipmentDate', 'recipient_fname', 'recipient_lname', 'recipient_email', 'recipient_phone', 'packages'];

        $validatedValues = [];
        foreach ($requiredFields as $field) {
            if (!isset($requestBody[$field])) {
                return $this->response->sendError('error', ucfirst($field) . ' field is required');
            }
            // $validatedValues[$field] = $this->auth->validate($requestBody[$field]);
            $validatedValues[$field] = $requestBody[$field];
        }
        
        extract($validatedValues);

        // Check if any of the required fields are empty
        if (empty($pickupAddress) || empty($destination) || empty($shipmentDate) || empty($recipient_fname) || empty($recipient_lname) || empty($recipient_email) || empty($recipient_phone) || empty($packages)) {
            return $this->response->sendError('error', 'All fields are required to be filled');
        }

        if ($this->shipmentModel->createShipment($userId, $trackingId, $pickupAddress, $destination, $shipmentDate, $recipient_fname, $recipient_lname, $recipient_email, $recipient_phone, $packages)) {
            // Send push notification to user
            // $this->sendShipmentNotification($userId, $trackingId);
            return $this->response->sendResponse('success', 'Shipment created successfully');
           // return json_encode(['message' => 'Shipment created successfully']);
        } else {
            return $this->response->sendError('error', "failed to create shipments");
        }
    }

    public function getUserShipment($userid){
          if($userid){
           $result =$this->shipmentModel->getUserShipment($userid);
           return $this->response->sendResponse('success', $result);
          }else{
            return $this->response->sendError('error', "invalid user");
          }
    }


    public function getShipmentDetails($shipmentid)
    {
        if($shipmentid){
            $result =$this->shipmentModel->getShipmentDetails($shipmentid);
            if($result){
                return $this->response->sendResponse('success', $result);
            }else{
                return $this->response->sendError('error', "no shipment found");
            }      
        }
    }

    public function DeleteShipment($userid,$shipmentid)
    {
        if($shipmentid){
            $result =$this->shipmentModel->userDeleteShipment($userid,$shipmentid);
            if($result){
                return $this->response->sendResponse('success', "shipment deleted successfully");
            }else{
                return $this->response->sendError('error', "no shipment found");
            }      
        }
    }

    public function addPackage()
    {
    }

    public function searchShipment($trackingId)
    {
        // Logic for retrieving shipment by tracking ID
        $shipment = $this->shipmentModel->searchShipmentByTrackingId($trackingId);

        if ($shipment) {
            return json_encode(['shipment' => $shipment]);
        } else {
            return json_encode(['error' => 'Shipment not found']);
        }
    }

    public function updateShipmentLocation($userid, $shipmentid)
    {
        $requestBody = json_decode(file_get_contents("php://input"), true);
        $requiredFields = ['address'];

        foreach ($requiredFields as $field) {
            if (!isset($requestBody[$field])) {
                return $this->response->sendError('error', ucfirst($field) . ' field is required');
            }
        }

        $address = $this->auth->validate($requestBody['address']);
        if (empty($address)) {
            return $this->response->sendError('error', 'address cannot be empty');
        }
       $shipment = $this->shipmentModel->updateCargoLocation($userid,$shipmentid,$address);
        // $shipment=$this->shipmentModel->getGeolocationUsingCurl($address);

        if ($shipment) {
            return json_encode(['shipment' => $shipment]);
        } else {
            return json_encode(['error' => 'Shipment not found']);
        }
    }

    public function getShipmentLocation($shipmentid){
        $shipment = $this->shipmentModel->getShipmentLocation($shipmentid);

        if ($shipment) {
            return json_encode(['shipment' => $shipment]);
        } else {
            return json_encode(['error' => 'Shipment not found']);
        }
    }

    // private function sendShipmentNotification($userId, $trackingId)
    // {
    //     // Retrieve FCM registration token for the user (assuming it's stored in the 'users' table)
    //     $registrationToken = $this->authHandler->getFcmRegistrationToken($userId);

    //     if ($registrationToken) {
    //         // Send push notification
    //         $title = 'Shipment Update';
    //         $body = "Your shipment with tracking ID $trackingId has been updated.";
    //         $this->pushNotificationService->sendNotification($registrationToken, $title, $body);
    //     }
    // }

    // Add other shipment-related methods as needed
}


?>