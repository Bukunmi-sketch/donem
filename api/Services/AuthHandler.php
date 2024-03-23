<?php

// namespace App\Services;
require_once('./vendor/autoload.php');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// require_once './db/Database.php';

class AuthHandler{
    private $secretKey;

    private $database;
    private $db;
    // private $secret_key = "buckaikeys";


    public function __construct($secretKey) {
        $this->secretKey = $secretKey;
        // $this->database = new Database();
        // $this->db = $this->database->getConnection();
    }

   
// Function to generate JWT token
public function generateJwtToken($user_id,$firstname,$lastname,$email,$phone) {
    $userdata= ["userid" => $user_id, "firstname" => $firstname, "lastname" => $lastname, "email"=>$email, "phone"=>$phone ];

    $payload = array(
        $iss = "localhost",
        "data" => $userdata,
        "exp" => time() + 3600, // Token expiration time (adjust as needed)
    );
    return JWT::encode($payload,$this->secretKey,'HS256');
}



// // Function to verify and get user details from JWT token
// function verifyAndGetUserDetails($token, $secret_key) {
//     try {
//         $decoded = JWT::decode($token, new Key("donem", 'HS512'));
//         $user_id = $decoded->user_id;

//         // Retrieve user details from the database based on user_id
//         $user_details =$this->getUserDetailsFromDatabase($user_id);

//         return $user_details;
//     } catch(Exception $e) {
//         // Token verification failed
//         return null;
//     }
// }


    public function validLetters(string $name): bool
    {
        return (bool)preg_match("/^[a-zA-Z-' ]*$/", $name);
    }


    public function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit();
    }

    public function validate($input){
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $input[$key] = $this->applyValidation($value);
            }
        } else {
            $input = $this->applyValidation($input);
        }
        return $input;
    }
    
    private function applyValidation($value){
        $value = trim($value);
        $value = stripslashes($value);
        $value = htmlspecialchars($value);
        return $value;
    }
    

    // public function validate($input): string
    // {
    //     $input = trim($input);
    //     $input = stripslashes($input);
    //     $input = htmlspecialchars($input);
    //     return $input;
    // }


    public function filteremail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

   
    public function matchpassword(string $password, string $confirmpass): bool
    {
        if($password === $confirmpass){
            return true;
        }else{
            return false;
        }
    }

    public function passwordlength(string $password): bool
    {
        return strlen($password) >= 6;
    }

}
