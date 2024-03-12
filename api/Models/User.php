<?php

require "./db/Database.php";
$db = new Database();
$conn = $db->getConnection();

class User{
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function createUser($firstname,$lastname, $phone, $address) {
        // Hash the password before storing in the database
        // $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert user into the 'users' table
        $role = 'user';
        try {
            $sql="INSERT INTO users(firstname,lastname, phone, address) VALUES (:firstname, :lastname, :phone, :address)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ":firstname" => $firstname,
                ":lastname" => $lastname,
                ":phone" => $phone,
                ":address" => $address,
            ]);

            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function addEmail(string $email, int $userid) {
        $role = 'user';
        try {
            $sql="UPDATE users SET email = :email WHERE user_id = :userid";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ":email" => $email,
                ":userid" => $userid
            ]);

            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }


    public function ifEmailExist(string $email): bool
    {
        try {
            $sql = "SELECT * FROM users WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            return $stmt->rowCount() === 0;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function getUserByUsername($username) {
        // Retrieve user from the 'users' table by username
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->bindParam(1, $username);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
}
