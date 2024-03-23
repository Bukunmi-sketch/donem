<?php

// require_once 'path/to/firebase/php-jwt/JWT.php'; // Adjust the path accordingly
require './Services/AuthHandler.php';
require './Services/ResponseHandler.php';
require './Services/EmailSender.php';
require "./Models/User.php";
require "./Models/Login.php";
require "./Models/Register.php";


class authController
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


    public function register()
    {
        // Receive the request body
        $requestBody = json_decode(file_get_contents("php://input"), true);
        $requiredFields = ['firstname', 'lastname', 'phone', 'address','email'];

        foreach ($requiredFields as $field) {
            if (!isset($requestBody[$field])) {
                return $this->response->sendError('error', ucfirst($field) . ' field is required');
            }
        }

        $firstname = $this->auth->validate($requestBody['firstname']);
        $lastname = $this->auth->validate($requestBody['lastname']);
        $phone = $this->auth->validate($requestBody['phone']);
        $address = $this->auth->validate($requestBody['address']);
        $email = $this->auth->validate($requestBody['email']);

        if (empty($firstname) || empty($lastname) || empty($phone) || empty($address)) {
            return $this->response->sendError('error', 'all fields are required to be filled');
        }
        if (!$this->auth->validLetters($firstname)) {
            return $this->response->sendError('error', 'Only valid letters are allowed for firstname');
        }

        if (!$this->auth->validLetters($lastname)) {
            return $this->response->sendError('error', 'Only valid letters are allowed for lastname');
        }
        if (empty($email)) {
            return $this->response->sendError('error', 'email address is required');
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

    



    public function confirmOtp()
    {

        $requestBody = json_decode(file_get_contents("php://input"), true);
        $requiredFields = ['email', 'otp'];

        foreach ($requiredFields as $field) {
            if (!isset($requestBody[$field])) {
                return $this->response->sendError('error', ucfirst($field) . ' field is required');
            }
        }

        $email = $this->auth->validate($requestBody['email']);
        $otp = $this->auth->validate($requestBody['otp']);

        if (empty($email)) {
            return $this->response->sendError('error', 'email address is required');
        }
        if (empty($otp)) {
            return $this->response->sendError('error', 'otp code is required');
        }

        if (!$this->auth->filteremail($email)) {
            return $this->response->sendError('error', 'Invalid Email Address');
        } else {
            if ($this->registerModel->verifyEmail($email,$otp)) {
                $emailVerified=1;
                $this->registerModel->updateEmailVerified($email,$emailVerified);
                return $this->response->sendResponse('success', 'email verified successfully');
                //update email has verified
            } else {
                return $this->response->sendError('error', 'incorrect otp ');
            }
        }
        return $this->response->sendError('error', 'Failed to add email');
    }


    // public function registerEmail()
    // {

    //     $requestBody = json_decode(file_get_contents("php://input"), true);
    //     $requiredFields = ['email'];

    //     foreach ($requiredFields as $field) {
    //         if (!isset($requestBody[$field])) {
    //             return $this->response->sendError('error', ucfirst($field) . ' field is required');
    //         }
    //     }

    //     $email = $this->auth->validate($requestBody['email']);

    //     if (empty($email)) {
    //         return $this->response->sendError('error', 'email address is required');
    //     }


    //     if (!$this->auth->filteremail($email)) {
    //         return $this->response->sendError('error', 'Invalid Email Address');
    //     } else {
    //         if ($this->userModel->ifEmailExist($email)) {
    //             return $this->response->sendError('error', 'this email already exist');
    //         } else {
    //             $code = random_int(100000, 999990);
    //             $otpNotificationSent = $this->emailService->sendOTPNotification($email, $code);

    //             if ($otpNotificationSent) {
    //                 $registerEmail = $this->userModel->addEmail($email, "6");
    //                 return $this->response->sendResponse('success', 'otp sent successfully');
    //             } else {
    //                 return $this->response->sendError('error', 'an error occured while sending otp');
    //             }
    //         }
    //     }
    //     return $this->response->sendError('error', 'Failed to add email');
    // }


    public function createPassword()
    {

        $requestBody = json_decode(file_get_contents("php://input"), true);
        $requiredFields = ['password', 'confirmpassword','email','phone'];

        foreach ($requiredFields as $field) {
            if (!isset($requestBody[$field])) {
                return $this->response->sendError('error', ucfirst($field) . ' field is required');
            }
        }
        $password = $this->auth->validate($requestBody['password']);
        $confirmpassword = $this->auth->validate($requestBody['confirmpassword']);
        $email = $this->auth->validate($requestBody['email']);
        $phone = $this->auth->validate($requestBody['phone']);

        if (empty($email) || empty($phone)) {
            return $this->response->sendError('error', 'no specified user for password creation');
        }

        if (empty($password) && empty($confirmpassword)) {
            return $this->response->sendError('error', 'all fields must be filled');
        } else {
            if (!$this->auth->passwordlength($password)) {
                return $this->response->sendError('error', 'password length must be greater than 6');
            } else {
                if (!$this->auth->matchpassword($password, $confirmpassword)) {
                    return $this->response->sendError('error', 'password does not match');
                } else {
                    if ($this->registerModel->createPassword($password,$email,$phone)) {
                        $result = $this->userModel->getUserByEmail($email);
                        $userid= $result['user_id']; 
                        $firstname =$result['firstname'];
                        $lastname =$result['lastname'];
                        $useremail =$result['email'];
                        $userphone =$result['phone'];


                        $jwtToken = $this->auth->generateJwtToken($userid,$firstname,$lastname,$useremail,$userphone);
                        return $this->response->sendResponse('success', $result, 200, $jwtToken);
                
                    } else {
                        return $this->response->sendError('error', 'password does not match');
                    }
                }
            }
        }


        // return $this->response->sendError('error', 'Failed to add email');
    }


    public function login()
    {

        $requestBody = json_decode(file_get_contents("php://input"), true);
        $requiredFields = ['email', 'password'];

        foreach ($requiredFields as $field) {
            if (!isset($requestBody[$field])) {
                return $this->response->sendError('error', ucfirst($field) . ' field is required');
            }
        }

        $email = $this->auth->validate($requestBody['email']);
        $password = $this->auth->validate($requestBody['password']);

        if (empty($email)) {
            return $this->response->sendError('error', 'email address is required');
        }
        if (empty($password)) {
            return $this->response->sendError('error', 'password is required');
        }
        $userid = 1;

        if (!$this->auth->filteremail($email)) {
            return $this->response->sendError('error', 'Invalid Email Address');
        } else {
            if ($this->loginModel->login($email, $password)) {
                      if($this->userModel->ifEmailVerified($email)){
                        $result=$this->loginModel->login($email, $password);
                        $userid= $result['user_id']; 
                        $firstname =$result['firstname'];
                        $lastname =$result['lastname'];
                        $useremail =$result['email'];
                        $userphone =$result['phone'];
                    
                    //cheeck if email is verified
                    $jwtToken = $this->auth->generateJwtToken($userid,$firstname,$lastname,$useremail,$userphone);
                    return $this->response->sendResponse('success', $result, 200,$jwtToken);
                      }else{
                        return $this->response->sendError('error', "email isn't verified");
                      }
                  
            } else {
                return $this->response->sendError('error', 'wrong email or password');
            }
        }
        return $this->response->sendError('error', 'Failed to add email');
    }


    public function forgetPassword()
    {
        // Receive the request body
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
                $otp = random_int(100000, 999990);
             //   $createOtp = $this->registerModel->createUser($firstname, $lastname, $phone, $address,$email,$otp);
                    // $otpNotificationSent = $this->emailService->sendOTPNotification($email, $otp);
                    $otpNotificationSent =true;
                    if ($otpNotificationSent) {
                        return $this->response->sendResponse('success', 'otp sent successfully');
                    } else {
                        return $this->response->sendError('error', 'an error occured while sending otp');
                    }
            } else {
                return $this->response->sendError('error', 'this email does not exist');
            }
        }
    }

}

?>
