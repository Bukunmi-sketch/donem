<?php

// require_once 'path/to/firebase/php-jwt/JWT.php'; // Adjust the path accordingly
// require './Services/AuthHandler.php';
// require './Services/ResponseHandler.php';
// require './Services/EmailSender.php';
// require "./Models/User.php";
// require "./Models/Login.php";
// require "./Models/Register.php";


class userController
{
    private $database;
    private $conn;
    private $userModel;
    private $auth;
    private $response;
    private $emailService;
    private $registerModel;
    private $loginModel;

    public function __construct()
    {
        $this->database = new Database();
        $this->conn = $this->database->getConnection();
        $this->auth = new AuthHandler('donem');
        $this->userModel = new User($this->conn);
        $this->response = new Response();
        $this->emailService = new EmailSender();
        $this->registerModel = new Register($this->conn);
        $this->loginModel = new Login($this->conn);
    }


   
 



    public function fetchUserdetails($userid)
    {
        if ($userid) {
            $userdetails=$this->userModel->getUserInfo($userid);
            return json_encode(['user' => $userdetails]);
        } else {
            return json_encode(['error' => 'unauthorized access']);
        }
    }

    public function updateProfile($userid)
    {

        $requestBody = json_decode(file_get_contents("php://input"), true);
        $requiredFields = ['firstname', 'lastname', 'email','phone'];

        foreach ($requiredFields as $field) {
            if (!isset($requestBody[$field])) {
                return $this->response->sendError('error', ucfirst($field) . ' field is required');
            }
        }

        $firstname = $this->auth->validate($requestBody['firstname']);
        $lastname = $this->auth->validate($requestBody['lastname']);
        $phone = $this->auth->validate($requestBody['phone']);
        $email = $this->auth->validate($requestBody['email']);


        
        if (empty($firstname) || empty($lastname) || empty($phone) || empty($email)) {
            return $this->response->sendError('error', 'all fields are required to be filled');
        }
        if (!$this->auth->validLetters($firstname)) {
            return $this->response->sendError('error', 'Only valid letters are allowed for firstname');
        }

        if (!$this->auth->validLetters($lastname)) {
            return $this->response->sendError('error', 'Only valid letters are allowed for lastname');
        }
      
        if (!$this->auth->filteremail($email)) {
            return $this->response->sendError('error', 'Invalid Email Address');
        } else {
            if ($this->userModel->ifEmailExist($email)) {
                return $this->response->sendError('error', 'this email already exist');
            } else {
                $otp = random_int(100000, 999990);
                $registerUser = $this->registerModel->createUser($firstname, $lastname, $phone, $address,$email,$otp);
                if ($registerUser) {
                    // $otpNotificationSent = $this->emailService->sendOTPNotification($email, $otp);
                    $otpNotificationSent =true;
                    if ($otpNotificationSent) {
                        return $this->response->sendResponse('success', 'otp sent successfully');
                    } else {
                        return $this->response->sendError('error', 'an error occured while sending otp');
                    }
                   
                }
            }
        }
    }



}



?>