<?php

/* The Auth class provides various methods for authentication and validation in PHP. */

class Auth
{
    /**
     * The function initializes a private variable called `db` with the value of the `$conn` parameter.
     *
     * @param PDO $conn The parameter `$conn` is likely a PDO database connection object. It is used
     * to establish a connection to a database and interact with it. The `__construct` method is a
     * constructor that is called when an object of this class is created. It takes the `$conn`
     * parameter and assigns it.
     */
    private PDO $db;
    private $secret_key = "buckaikeys";

    public function __construct(PDO $conn)
    {
        $this->db = $conn;
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
    public function escapeString(string $biotext): string
    {
        $biotext = $this->db->quote($biotext);
        return $biotext;
    }

    /**
     * The function checks if the user is logged in by checking the value of the 'loggedin' key in the
     * session variable.
     *
     * @return bool true if the value of `$_SESSION['loggedin']` is true.
     */
    public function isloggedin(): bool
    {
        return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
    }

    /**
     * The function custom_trim trims whitespace from a string, or returns an empty string if the input
     * is null.
     *
     * @param string|null $value The parameter `$value` is a nullable string.
     *
     * @return string The trimmed value of the input string, or an empty string if the input is null.
     */
    public function custom_trim(?string $value): string
    {
        return trim($value ?? '');
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
     * The function "logout" destroys the current session and unsets the session ID.
     *
     * @param string $sessionid The session ID is a unique identifier that is assigned to each user's session
     * when they log in. It is used to track and manage the user's session data.
     */
    public function logout(mixed $sessionid): void
    {
        session_destroy();
        unset($_SESSION[$sessionid]);
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
        return $password === $confirmpass;
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

    /**
     * The authPassword function checks if the entered password matches the hashed password stored in
     * the database for a given user.
     *
     * @param string $sessionid The `$sessionid` parameter is the unique identifier for the user session. It is
     * used to retrieve the user's information from the database.
     * @param string $password The `$password` parameter is the password entered by the user for authentication.
     *
     * @return bool A boolean value. It returns true if the entered password matches the hashed password
     * stored in the database for the given user ID, and false otherwise.
     */
    public function authPassword(int $sessionid, string $password): bool
    {
        try {
            $sql = "SELECT * FROM bucxai_users WHERE user_id = :userid";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':userid', $sessionid);
            $stmt->execute();
            // Check if row is actually returned
            if ($stmt->rowCount() > 0) {
                // Return row as an array indexed by both column name
                $returned_row = $stmt->fetch(PDO::FETCH_ASSOC);
                // Verify hashed password against entered password
                return password_verify($password, $returned_row['password']);
            } else {
                return false;
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function generateToken($user_id) {
        $token = bin2hex(random_bytes(32)); // Generate a random token
        $expirationDate = date('Y-m-d H:i:s', strtotime('+1 day')); // Set expiration to 1 day from now

        // Store the token in the database with user_id and expiration_date
        $query = "INSERT INTO tokens (user_id, token, expiration_date) VALUES (:user_id, :token, :expiration_date)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":token", $token);
        $stmt->bindParam(":expiration_date", $expirationDate);

        if ($stmt->execute()) {
            return $token;
        } else {
            return null;
        }
    }

    public function validateToken($token) {
        if (!empty($token)) {
            // Check if the token exists in the database
            $query = "SELECT * FROM tokens WHERE token = :token";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":token", $token);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                // Token exists, validate it against expiration date or any other criteria
                $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
                $currentTimestamp = time();
                $tokenExpirationTimestamp = strtotime($tokenData['expiration_date']);

                if ($currentTimestamp <= $tokenExpirationTimestamp) {
                    // Token is valid
                    return true;
                } else {
                    // Token has expired
                    return false;
                }
            } else {
                // Token not found in the database
                return false;
            }
        } else {
            // Token is empty
            return false;
        }
    }

    public function generateUserBearerToken($userId)
    {
        // Unique current time as part of the token payload
        $currentTime = time();

        // Combine user ID and current time in a payload array
        $payload = array(
            'u' => $userId,
            't' => $currentTime
        );

        // Convert payload to JSON and base64 encode
        $encodedPayload = base64_encode(json_encode($payload));

        // Generate a signature using a secret key (replace 'your_secret_key' with your actual secret key)
        $signature = hash_hmac('sha256', $encodedPayload, $this->secret_key);

        // Combine encoded payload and signature to form the final token
        // $shortBearerToken = substr($encodedPayload . '.' . $signature, 0, 20);
        //  return $shortBearerToken;

        $bearerToken = $encodedPayload . '.' . $signature;

        return $bearerToken;
    }



    public function generateBearerToken($userId, $chatboId)
    {
        // Unique current time as part of the token payload
        $currentTime = time();

        // Combine user ID and current time in a payload array
        $payload = array(
            'user_id' => $userId,
            'timestamp' => $currentTime,
            'chatbot_id' => $chatboId
        );

        // Convert payload to JSON and base64 encode
        $encodedPayload = base64_encode(json_encode($payload));

        // Generate a signature using a secret key (replace 'your_secret_key' with your actual secret key)
        $signature = hash_hmac('sha256', $encodedPayload, $this->secret_key);

        // Combine encoded payload and signature to form the final token
        $bearerToken = $encodedPayload . '.' . $signature;

        return $bearerToken;
    }



    public function decodeBearerToken($token) {
        // Split the token into encoded payload and signature
        list($encodedPayload, $signature) = explode('.', $token);
    
        // Decode the payload from base64
        $decodedPayload = base64_decode($encodedPayload);
    
        // Verify the signature
        $expectedSignature = hash_hmac('sha256', $encodedPayload, $this->secret_key);
    
        if (hash_equals($expectedSignature, $signature)) {
            // Signature is valid, decode the JSON payload
            $payload = json_decode($decodedPayload, true);
    
            // Verify if the token is not expired (you may add additional checks here)
    
            return $payload;
        } else {
            // Invalid signature
            return false;
        }
    }


    public function encryptId($id) {
        $cipher = 'aes-256-cbc';
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
        $encrypted = openssl_encrypt($id, $cipher, $this->secret_key, 0, $iv);
        $encoded_id = base64_encode($iv . $encrypted);
    
        return $encoded_id;
    }

    function decryptId($encoded_id) {
        $cipher = 'aes-256-cbc';
        $data = base64_decode($encoded_id);
        $iv = substr($data, 0, openssl_cipher_iv_length($cipher));
        $encrypted = substr($data, openssl_cipher_iv_length($cipher));
        $decrypted_id = openssl_decrypt($encrypted, $cipher, $this->secret_key, 0, $iv);
    
        return $decrypted_id;
    }
    

}
// Example usage with user ID (replace 'user123' with the actual user ID)
// $userId = 'user123';
// $accessToken = generateBearerToken($userId);
// echo $accessToken;
?>