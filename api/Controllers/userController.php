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
}



?>