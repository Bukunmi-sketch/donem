<?php

// Set necessary headers
// header('Content-Type: application/json');

// Include necessary libraries and classes
require_once 'vendor/autoload.php';
// require_once 'Controllers/AuthController.php';
require_once 'Controllers/AuthController.php';
require_once 'Controllers/ShipmentController.php';
require_once 'Models/Payment.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Router {



    private function handleProtectedEndpoints($endpoint) {
        // Define endpoints that require JWT authentication
        $protectedEndpoints = ['/getUsersDetails', '/updateusers','/create-shipments','/login'];

        // Check if the requested endpoint is protected
        if (in_array($endpoint, $protectedEndpoints)) {
            // Check if JWT token is present in the request headers
            $authorizationHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
            if (!$authorizationHeader) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized: JWT token missing']);
                exit();
            }            
            try {
                // $decodedToken = $jwt->decode($token, 'your_secret_key', array('HS256'));
                $token = str_replace('Bearer ', '', $authorizationHeader);
                $decoded = JWT::decode($token,  new Key("donem", 'HS256'));
                $user_id = $decoded->user_id;
                // echo json_encode(['error' => $decoded]); 
        
            } catch (Exception $e) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized: Invalid JWT token']);
                exit();
            }
            // Proceed with the request as the JWT token is valid
            return true;
        }
    }


    public function route($method, $param, $endpoint) {
        $registercontroller = new authController();
        $shipmentcontroller = new ShipmentController();

        $this->handleProtectedEndpoints($endpoint);
        
        switch ($method) {
            case 'POST':
                return $this->handlePostRequest($registercontroller, $shipmentcontroller, $endpoint);
            case 'GET':
                return $this->handleGetRequest($registercontroller,  $shipmentcontroller, $param, $endpoint);
            case 'PUT':
                return $this->handlePutRequest($registercontroller,  $shipmentcontroller, $endpoint);
            default:
                http_response_code(405);
                return json_encode(['error' => 'Method Not Allowed']);
        }
    }

    private function handlePostRequest($authcontroller, $shipcontroller,  $endpoint) {
        switch ($endpoint) {
            case '/register':
                return $authcontroller->register();
            case '/login':
                return $authcontroller->login();
            case '/register-email':
                return $authcontroller->registerEmail();
            case '/verify-email':
                return $authcontroller->verifyEmail();   
            case '/register-password':
                return $authcontroller->createPassword();  
            case '/create-shipment':
                return $shipcontroller->createShipment();   
            case '/pay-shipment': //id=packageid
            default:
                // Handle 404 - Endpoint Not Found
                http_response_code(404);
                return json_encode(['error' => 'Endpoint Not Found']);
        }
    }

    private function handleGetRequest($authcontroller, $shipcontroller, $param, $endpoint) {
        switch ($endpoint) {
            case '/getUsersDetails':
                return $authcontroller->register;
                break;
            case '/login':
                return $authcontroller->login();
            case '/userdetails':
                return $authcontroller->getAllUsers();
                break;
            case 'getusers-shipments':
            return $response = $param ? json_encode(['error' => $param]) : json_encode(['error' => 'Missing parameter']);
            http_response_code($param ? 200 : 400);
                // if ($param) {
                //    return $response = json_encode(['error' => $param]);
                //   return $authcontroller->getShipmentDetails($param);
                //     } else {
                //         http_response_code(400);
                //        return $response = json_encode(['error' => 'Missing parameter']);
                //     }
                break;
            case '/getusers-details':   
            if ($param) {
                return $authcontroller->getShipmentDetails($param);
                    } else {
                        http_response_code(400);
                        $response = json_encode(['error' => 'Missing parameter']);
                    }
                break;
            case '/getshipment-details': 
              return $authcontroller->get;    
            default:
                http_response_code(404);
                return json_encode(['error' => 'Endpoint Not Found']);
        }
    }


    private function handlePutRequest($authcontroller,  $endpoint) {
        switch ($endpoint) {
            case '/getUsersDetails':
                return $authcontroller::register;
            case '/login':
                return $authcontroller->login();
            case '/updateusers':
                return $authcontroller->updateUsers(); 
            case '/mark-delivered': //id=packageid
            // Add more routes as needed
            default:
                // Handle 404 - Endpoint Not Found
                http_response_code(404);
                return json_encode(['error' => 'Endpoint Not Found']);
        }
    }
}

$router = new Router();

// Explicitly define controller and method based on the incoming request
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = strtok($_SERVER['REQUEST_URI'], '?'); // Remove query parameters

$parts = explode('/', $endpoint);
 $endpoint = $parts[1]; 
// echo "<br>";
 $param = $parts[2] ?? null;

// Route the request
$response = $router->route($method, $param, $endpoint);

echo $response;
