<?php

// Set necessary headers
header('Content-Type: application/json');

// Include necessary libraries and classes
require_once 'vendor/autoload.php';
// require_once 'Controllers/AuthController.php';
require_once 'Controllers/AuthController.php';
require_once 'Controllers/ShipmentController.php';
require_once 'Models/Payment.php';
require_once 'config.php';

// Instantiate auth with your secret key
// $auth = new \App\Services\auth("shit");
// $auth = new auth("shit");

// Create a simple router class
class Router {

    public function route($method, $endpoint) {
        $registercontroller = new authController();
        $shipmentcontroller = new ShipmentController();

        switch ($method) {
            case 'POST':
                return $this->handlePostRequest($registercontroller, $shipmentcontroller, $endpoint);
            case 'GET':
                return $this->handleGetRequest($registercontroller,  $shipmentcontroller, $endpoint);
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
            default:
                // Handle 404 - Endpoint Not Found
                http_response_code(404);
                return json_encode(['error' => 'Endpoint Not Found']);
        }
    }

    private function handleGetRequest($authcontroller, $endpoint) {
        switch ($endpoint) {
            case '/getUsersDetails':
                return $authcontroller::register;
            case '/login':
                return $authcontroller->login();
            case '/updateusers':
                return $authcontroller->updateUsers(); // Adjust the method name accordingly
            // Add more routes as needed
            default:
                // Handle 404 - Endpoint Not Found
                http_response_code(404);
                return json_encode(['error' => 'Endpoint Not Found']);
        }
    }


    private function handlePutRequest($authcontroller, $endpoint) {
        switch ($endpoint) {
            case '/getUsersDetails':
                return $authcontroller::register;
            case '/login':
                return $authcontroller->login();
            case '/updateusers':
                return $authcontroller->updateUsers(); // Adjust the method name accordingly
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

// Route the request
$response = $router->route($method, $endpoint);

// Echo the JSON response
echo $response;
