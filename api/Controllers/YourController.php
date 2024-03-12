<?php

use YourApp\Models\UserModel;
use YourApp\Services\AuthHandler;
use YourApp\Services\PushNotificationService;

class YourController {
    private $authHandler;
    private $userModel;
    private $pushNotificationService;

    public function __construct(AuthHandler $authHandler, UserModel $userModel, PushNotificationService $pushNotificationService) {
        $this->authHandler = $authHandler;
        $this->userModel = $userModel;
        $this->pushNotificationService = $pushNotificationService;
    }

    public function register() {


          // Retrieve the raw JSON request body
    $jsonBody = file_get_contents("php://input");

    // Decode the JSON into an associative array
    $requestData = json_decode($jsonBody, true);

    // Ensure the required data is present
    if (!isset($requestData['username']) || !isset($requestData['password'])) {
        return ['error' => 'Invalid request data'];
    }

    
        // Logic for user registration
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Perform any necessary validation

        // Call UserModel to create the user
        if ($this->userModel->createUser($username, $password)) {
            // If user creation is successful, generate a token
            $token = $this->authHandler->registerUser($username, $password);

            // Send push notification to user
            $registrationToken = $this->userModel->getUserByUsername($username)['fcm_registration_token'];
            $this->pushNotificationService->sendNotification($registrationToken, 'Registration Successful', 'Welcome to the app!');

            return json_encode(['token' => $token]);
        } else {
            return json_encode(['error' => 'User registration failed']);
        }
    }


    public function login() {
        // Logic for user login
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Perform any necessary validation

        // Call AuthHandler to authenticate user and get a token
        $token = $this->authHandler->login($username, $password);

        // Return the token or handle as needed
        return json_encode(['token' => $token]);
    }

    public function getUserDetails() {
        // Logic to get user details from a protected resource
        // You may use the token for authentication and authorization
        $token = $_POST['token'];

        // Call AuthHandler to verify the token and get user details
        $userDetails = $this->authHandler->verifyToken($token);

        // Return the user details or handle unauthorized access
        if ($userDetails) {
            return json_encode(['user' => $userDetails]);
        } else {
            return json_encode(['error' => 'Unauthorized']);
        }
    }

    // Other methods remain unchanged
}
