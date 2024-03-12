<?php

// require_once 'path/to/firebase/php-jwt/JWT.php'; // Adjust the path accordingly
require './Services/AuthHandler.php';
require './Services/ResponseHandler.php';
require './Services/EmailSender.php';
require "./Models/User.php";

// use App\Services\AuthHandler;
require_once './db/Database.php';

// Create an instance of the Database class


// Get the database connection


// Instantiate AuthHandler with your secret key
// $authHandler = new AuthHandler($_ENV['JWT_SECRET']); // Replace with your actual secret key
$auth = new AuthHandler('shit');


class authController
{
    private $database;
    private $conn;
    private $userModel;
    private $auth;
    private $response;
    private $emailService;

    public function __construct()
    {
        $this->database = new Database();
        $this->conn = $this->database->getConnection();
        $this->auth = new AuthHandler('your_secret_key');
        $this->userModel = new User($this->conn);
        $this->response = new Response();
        $this->emailService = new EmailSender();
    }



    public function register()
    {
        // Receive the request body
        $requestBody = json_decode(file_get_contents("php://input"), true);
        $requiredFields = ['firstname', 'lastname', 'phone', 'address'];

        foreach ($requiredFields as $field) {
            if (!isset($requestBody[$field])) {
                return $this->response->sendError('error', ucfirst($field) . ' field is required');
            }
        }

        $firstname = $this->auth->validate($requestBody['firstname']);
        $lastname = $this->auth->validate($requestBody['lastname']);
        $phone = $this->auth->validate($requestBody['phone']);
        $address = $this->auth->validate($requestBody['address']);

        if (empty($firstname) || empty($lastname) || empty($phone) || empty($address)) {
            return $this->response->sendError('error', 'all fields are required to be filled');
        }
        if (!$this->auth->validLetters($firstname)) {
            return $this->response->sendError('error', 'Only valid letters are allowed for firstname');
        }

        if (!$this->auth->validLetters($lastname)) {
            return $this->response->sendError('error', 'Only valid letters are allowed for lastname');
        }
        // Call auth to sign up the user and get a JWT token
        $registerUser = $this->userModel->createUser($firstname, $lastname, $phone, $address);

        if ($registerUser) {
            //  $jwtToken=$this->auth->generateJwtToken($user_id, $secret_key);
            // return json_encode(['token' => $jwtToken]);
            return $this->response->sendResponse('success', 'Created Successfully');
        }

        // Return error response if registration fails
        return $this->response->sendError('error', 'Failed to create user');
    }


    public function registerEmail()
    {

        $requestBody = json_decode(file_get_contents("php://input"), true);
        $requiredFields = ['email'];

        foreach ($requiredFields as $field) {
            if (!isset($requestBody[$field])) {
                return $this->response->sendError('error', ucfirst($field) . ' field is required');
            }
        }

        $email = $this->auth->validate($requestBody['email']);

        if (empty($email)) {
            return $this->response->sendError('error', 'email address is required');
        }


        if (!$this->auth->filteremail($email)) {
            return $this->response->sendError('error', 'Invalid Email Address');
        } else {
            if ($this->userModel->ifEmailExist($email)) {
                return $this->response->sendError('error', 'this email already exist');
            } else {
                $code = random_int(100000, 999990);
                $otpNotificationSent = $this->emailService->sendOTPNotification($email, $code);

                if ($otpNotificationSent) {
                    $registerEmail = $this->userModel->addEmail($email, "6");
                    return $this->response->sendResponse('success', 'otp sent successfully');
                } else {
                    return $this->response->sendError('error', 'an error occured while sending otp');
                }
                // if ($registerEmail) {
                //     return $this->response->sendResponse('success','email added succesfully');

                // }

            }
        }






        return $this->response->sendError('error', 'Failed to add email');
    }

    public function getUserDetailsAction()
    {
        // Receive the request body
        $requestBody = json_decode(file_get_contents("php://input"), true);

        // Extract necessary parameters
        $token = $requestBody['token'];

        // Call auth to verify the token and get user details
        // $userDetails = $this->auth->verifyAndGetUserDetails($token);
        $userDetails = "";
        // Return the user details or handle unauthorized access
        if ($userDetails) {
            return json_encode(['user' => $userDetails]);
        } else {
            // Handle unauthorized access, e.g., return an error response
            return json_encode(['error' => 'Unauthorized']);
            // return $this->sendResponse($data, 'User Addresses');
        }


        /**
         * The function "escapeString" in PHP takes a string as input and returns the string with special
         * characters escaped.
         *
         * @param string $biotext The parameter `$biotext` is a string that represents the text that needs to be
         * escaped.
         *
         * @return string The escaped string.
         */
        // public function escapeString(string $biotext): string
        // {
        //     $biotext = $this->db->quote($biotext);
        //     return $biotext;
        // }
    }
}

// Example usage:
// $controller = new YourController($auth);
// $signupResponse = $controller->register('new_user', 'password123');
// $userDetailsResponse = $controller->getUserDetailsAction($signupResponse['token']);
