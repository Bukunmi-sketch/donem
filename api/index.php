<?php

header('Content-Type: application/json');

// Include necessary libraries and classes
require_once 'vendor/autoload.php';
// require_once 'Controllers/AuthController.php';
require_once 'Controllers/AuthController.php';
require_once 'Controllers/ShipmentController.php';
require_once 'Controllers/userController.php';
require_once 'Models/Payment.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Router
{

    private function handleProtectedEndpoints($endpoint) {
        $protectedEndpoints = ['get-user', 'updateusers', 'create-shipment', 'update-profile','getusers-shipments','shipment-details','delete-shipment'];
        // Check if the requested endpoint is protected
        if (in_array($endpoint, $protectedEndpoints)) {
            // Check if JWT token is present in the request headers
            $authorizationHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
            if (!$authorizationHeader) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized: JWT token missing, you are not signed in yet ']);
                exit();
            }
            try {
                $token = str_replace('Bearer ', '', $authorizationHeader);
                $decoded = JWT::decode($token,  new Key("donem", 'HS256'));
                return $decoded;
                // $user_id = $decoded->data->userid;
                // echo json_encode(['user' => $user_id]); 
                // if ($decoded == "") {
                //     http_response_code(401);
                //     echo json_encode(['error' => 'Unauthorized: Token has expired']);
                //     exit();
                // }

            } catch (Exception $e) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized: Invalid JWT token']);
                exit();
            }
            return true;
        }
    }


    public function route($method, $param, $endpoint)
    {
        $registercontroller = new authController();
        $shipmentcontroller = new ShipmentController();
        $usercontroller = new userController();

        $token = $this->handleProtectedEndpoints($endpoint);
        switch ($method) {
            case 'POST':
                return $this->handlePostRequest($registercontroller, $token, $shipmentcontroller, $usercontroller, $param, $endpoint);
            case 'GET':
                return $this->handleGetRequest($registercontroller, $token, $shipmentcontroller, $usercontroller, $param, $endpoint);
            case 'PUT':
                return $this->handlePutRequest($registercontroller, $token, $shipmentcontroller, $usercontroller, $param, $endpoint);
            case 'DELETE':
                return $this->handleDeleteRequest($registercontroller, $token, $shipmentcontroller, $usercontroller, $param, $endpoint);
            default:
                http_response_code(405);
                return json_encode(['error' => 'Method Not Allowed']);
        }
    }


    private function handlePostRequest($authcontroller, $token, $shipcontroller, $usercontroller, $param, $endpoint)
    {
        switch ($endpoint) {
            case 'register':
                return $authcontroller->register();
            case 'login':
                return $authcontroller->login();
            case 'verify-email':
                return $authcontroller->confirmOtp();
            case 'create-password':
                return $authcontroller->createPassword();
            case 'forget':
                return $authcontroller->forgetPassword();
            case 'create-shipment':
                return $shipcontroller->createShipment($token->data->userid);
                break;
            case 'pay-shipment': //id=packageid
            default:
                // Handle 404 - Endpoint Not Found
                http_response_code(404);
                return json_encode(['error' => 'Endpoint Not Found in Post Request']);
        }
    }

    private function handleGetRequest($authcontroller, $token, $shipcontroller, $usercontroller, $param, $endpoint){
        switch ($endpoint) {
            case 'get-user':
                return $usercontroller->fetchUserdetails($token->data->userid); //userid
                break;
            case 'userdetails':
                return $authcontroller->getAllUsers();
                break;
            case 'getusers-shipments':
                return  $shipcontroller->getUserShipment($token->data->userid); //userid
                break;
            case 'shipment-details':
                return $param ?  $shipcontroller->getShipmentDetails($param) : json_encode(['error' => 'Missing parameter']); //shipmentid
            default:
                http_response_code(404);
                return json_encode(['error' => 'Endpoint Not Found']);
        }
    }


    private function handlePutRequest($authcontroller, $token, $shipcontroller, $usercontroller, $param, $endpoint){
        switch ($endpoint) {
            case 'update-profile':
                return $authcontroller->updateProfile();
                break;
            case '/mark-delivered': 
            default:
                http_response_code(404);
                return json_encode(['error' => 'Endpoint Not Found']);
        }
    }


    private function handleDeleteRequest($authcontroller, $token, $shipcontroller, $usercontroller, $param, $endpoint){
        switch ($endpoint) {
            case 'delete-shipment':
                 return $param ?  $shipcontroller->DeleteShipment($token->data->userid,$param) : json_encode(['error' => 'Missing parameter']);
                break;
            default:
                http_response_code(404);
                return json_encode(['error' => 'Endpoint Not Found']);
        }
    }
}

$router = new Router();

$method = $_SERVER['REQUEST_METHOD'];
$endpoint = strtok($_SERVER['REQUEST_URI'], '?'); // Remove query parameters

$parts = explode('/', $endpoint);
$endpoint = $parts[1];
$param = $parts[2] ?? null;

$response = $router->route($method, $param, $endpoint);

echo $response;
