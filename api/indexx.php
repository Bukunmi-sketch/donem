<?php

// Set necessary headers
// Set necessary headers
header('Content-Type: application/json');

// Include necessary libraries and classes
require_once 'vendor/autoload.php';
require_once './Services/AuthHandler.php';
require_once 'Controllers/AuthController.php';
require_once 'Models/User.php';
require_once 'Services/PushNotificationService.php';
require_once 'Controllers/ShipmentController.php';
require_once 'Models/Shipment.php';
require_once 'Controllers/PaymentController.php';
require_once 'Controllers/Controller.php';
require_once 'Models/Payment.php';
require_once 'config.php';

// Instantiate AuthHandler with your secret key
// $authHandler = new AuthHandler($_ENV['JWT_SECRET']);

// Connect to MySQL database (PDO)
$dbHost = $_ENV['DB_HOST'];
$dbName = $_ENV['DB_NAME'];
$dbUser = $_ENV['DB_USER'];
$dbPassword = $_ENV['DB_PASSWORD'];

$db = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);

// Instantiate UserModel with the database connection
$userModel = new UserModel($db);

// // Instantiate ShipmentModel with the database connection
// $shipmentModel = new ShipmentModel($db);

// // Instantiate PaymentModel with the database connection
// $paymentModel = new PaymentModel($db);

// // Instantiate PushNotificationService with FCM API key
// $fcmApiKey = $_ENV['FCM_API_KEY'];
// $pushNotificationService = new PushNotificationService($fcmApiKey);

// // Instantiate controllers with necessary dependencies
// $authController = new AuthController($userModel, $authHandler);
// $shipmentController = new ShipmentController($shipmentModel, $authHandler, $pushNotificationService);
// $paymentController = new PaymentController($paymentModel, $authHandler);


// Include necessary libraries and classes
// require_once './vendor/firebase/php-jwt/JWT.php'; // Adjust the path accordingly
require_once 'Controllers/YourController.php';
// $authHandler = new AuthHandler();

// Explicitly define controller and method based on the incoming request
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = strtok($_SERVER['REQUEST_URI'], '?'); // Remove query parameters

$controller = new YourController($authHandler);

// Extract URL parameters
$parts = explode('/', $endpoint);
$param1 = $parts[1] ?? null; // Extract first parameter, adjust as needed

// Manually define controllers and methods based on the endpoint
if ($method === 'POST') {
    switch ($endpoint) {
        case '/register':
            $response = $controller->register();
            break;
        case '/login':
            $response = $controller->login();
            break;
        case '/getUserDetails':
            $response = $controller->getUserDetails();
            break;
        // case '/createShipment':
        //     $response = $controller->createShipment();
        //     break;
        // case '/getShipmentDetails':
        //     if ($param1) {
        //         $response = $controller->getShipmentDetails($param1);
        //     } else {
        //         // Handle missing parameter
        //         http_response_code(400);
        //         $response = json_encode(['error' => 'Missing parameter']);
        //     }
        //     break;
        // case '/createPayment':
        //     $response = $controller->createPayment();
        //     break;
        default:
            // Handle 404 - Endpoint Not Found
            http_response_code(404);
            $response = json_encode(['error' => 'Endpoint Not Found']);
            break;
    }

    // Echo the JSON response
    echo $response;
} else {
    // Handle unsupported methods
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
}


















?>