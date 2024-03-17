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
public function generateJwtToken($user_id) {
    $payload = array(
        "user_id" => $user_id,
        "exp" => time() + 3600, // Token expiration time (adjust as needed)
    );
    return JWT::encode($payload,$this->secretKey,'HS256');
}



// Function to verify and get user details from JWT token
function verifyAndGetUserDetails($token, $secret_key) {
    try {
        $decoded = JWT::decode($token, new Key("donem", 'HS512'));
        $user_id = $decoded->user_id;

        // Retrieve user details from the database based on user_id
        $user_details =$this->getUserDetailsFromDatabase($user_id);

        return $user_details;
    } catch(Exception $e) {
        // Token verification failed
        return null;
    }
}

// Function to retrieve user details from the database
function getUserDetailsFromDatabase($user_id) {
    // Implement logic to fetch user details based on user_id from your database
    // Replace this with your actual database query
    $user_details = [
        'user_id' => $user_id,
        'username' => 'example_user',
        // Add other user details as needed
    ];

    return $user_details;
}

/**
     * The function checks if a given string only contains valid letters (a-z, A-Z, hyphen, apostrophe,
     * and space).
     *
     * @param string $name The parameter `$name` is a string that represents a person's name.
     *
     * @return bool A boolean value. If the name contains only valid letters (a-z, A-Z, hyphen, apostrophe,
     * and space), it will return true. Otherwise, it will return false.
     */
    public function validLetters(string $name): bool
    {
        return (bool)preg_match("/^[a-zA-Z-' ]*$/", $name);
    }

    /**
     * The function redirects the user to a specified URL and terminates the script execution.
     *
     * @param string $url The URL where the user will be redirected to.
     */
    public function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit();
    }


      /**
     * The function "validate" in PHP trims, removes slashes, and converts special characters to HTML
     * entities in the input string.
     *
     * @param string $input The input parameter is a string that needs to be validated.
     *
     * @return string The validated and sanitized input.
     */
    public function validate(string $input): string
    {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input);
        return $input;
    }

    /**
     * The function "filteremail" checks if a given email address is valid or not.
     *
     * @param string $email The parameter `$email` is a string that represents an email address.
     *
     * @return bool A boolean value. If the email address passed as an argument is valid, the function will
     * return true. If the email address is invalid, the function will return false.
     */
    public function filteremail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * The function checks if the password and confirm password inputs match and returns true if they
     * do, and false if they don't.
     *
     * @param string $password The password that the user entered.
     * @param string $confirmpass The parameter `$confirmpass` is the password entered by the user to confirm
     * their password.
     *
     * @return bool A boolean value. If the password and confirm password match, it will return true. If they
     * do not match, it will return false.
     */
    public function matchpassword(string $password, string $confirmpass): bool
    {
        if($password === $confirmpass){
            return true;
        }else{
            return false;
        }
    }


    /**
     * The function checks if a password is at least 6 characters long and returns true if it is, and
     * false otherwise.
     *
     * @param string $password The parameter `$password` is a string that represents the password that needs to
     * be checked for its length.
     *
     * @return bool A boolean value. If the length of the password is less than 6, it will return false.
     * Otherwise, it will return true.
     */
    public function passwordlength(string $password): bool
    {
        return strlen($password) >= 6;
    }



// Example of using these functions for user signup and retrieving details
// $secret_key = 'your_secret_key'; // Replace with your actual secret key

// User Signup
// $newToken = signUpUser('new_user', 'password123', $secret_key);

// Verify Token and Get User Details
// $verifiedUser = verifyAndGetUserDetails($newToken, $secret_key);

// $verifiedUser will contain the user details if the token is valid, otherwise, it will be null

}
