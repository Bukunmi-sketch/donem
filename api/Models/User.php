<?php

// require "./db/Database.php";
// $db = new Database();
// $conn = $db->getConnection();

class User{
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function createUser($firstname,$lastname, $phone, $address,$email) {
        // Hash the password before storing in the database
        // $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert user into the 'users' table
        $role = 'user';
        try {
            $sql="INSERT INTO users(firstname,lastname, phone, address,email) VALUES (:firstname, :lastname, :phone, :address, :email)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ":firstname" => $firstname,
                ":lastname" => $lastname,
                ":phone" => $phone,
                ":address" => $address,
                ":email" => $email
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

    public function updateProfile(string $firstname,string $lastname, string $email, int $phone, int $userid) {
      
        try {
            $sql="UPDATE users SET firstname= :firstname, lastname - :lastname, email = :email, phone -:phone  WHERE user_id = :userid";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ":firstname" => $firstname,
                ":lastname" => $lastname,
                ":email" => $email,
                ":phone" => $phone,
                ":userid" => $userid
            ]);

            $stmt->execute();
            $affectedRows = $stmt->rowCount();
            if ($affectedRows > 0) {
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

            if($stmt->rowCount() == 1){
                return true;
            }else{
                return false;
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function createPassword($password,$userid){
        try {
            // Hash the password
            $user_hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users(userid, password) VALUES (:userid, :password)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ":userid" => $userid,
                ":password" => $user_hashed_password,
            ]);

            return $result;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function getUserInfo($userid)
    {
        try {
            // $sql = "SELECT *
            // FROM bucxai_users
            // INNER JOIN bucxai_profiles ON bucxai_users.user_id = bucxai_profiles.user_id
            // WHERE bucxai_users.user_id = :userid;";
            $sql='SELECT * FROM users WHERE user_id=:userid';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":userid", $userid);
            $stmt->execute();
            $returned_row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($returned_row) {
                return $returned_row;
            } else {
               // return $userid;
                return false;
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    




    public function verifyEmail(int $userId, int $mailotp): bool
    {
        try {
            $sql = "SELECT * FROM bucxai_users WHERE user_id = :userId";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();

            // if ($stmt->rowCount() > 0) {
                $returnedRow = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($mailotp === $returnedRow['mailotp']){
                    return true;
                }
            // }

            return false;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function sendOtp(string $email, string $mailotp): bool
    {
        $sql = "UPDATE bucxai_users SET mailotp = :mailotp WHERE email = :email";
        $stmtUpdate = $this->db->prepare($sql);
        $stmtUpdate->bindParam(":mailotp", $mailotp);
        $stmtUpdate->bindParam(":email", $email);

        return $stmtUpdate->execute();
    }


    public function updateRegStep(int $userId, string $regStep): bool
    {
        $status = 'verified';
        $sql = "UPDATE bucxai_users SET reg_step = :regStep, account_status = :status WHERE user_id = :userId";
        $stmtUpdate = $this->db->prepare($sql);
        $stmtUpdate->bindParam(":userId", $userId);
        $stmtUpdate->bindParam(":regStep", $regStep);
        $stmtUpdate->bindParam(":status", $status);

        return $stmtUpdate->execute();
    }


    public function getUserByEmail($email) {
        try {
            $sql = "SELECT * FROM users WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                unset($result['password']);
                unset($result['otp']);
                return $result;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function ifEmailVerified(string $email): bool
    {
        try {
            $sql = "SELECT emailVerified FROM users WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $returnedRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($returnedRow['emailVerified'] == 1){
                return true;
            }else{
                return false;
            }

        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function changePassword(int $sessionid, string $newpassword)
    {
        $user_hashed_newpassword = password_hash($newpassword, PASSWORD_DEFAULT);

        $query = "UPDATE bucxai_users SET password = :newpassword WHERE user_id = :sessionid";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":newpassword", $user_hashed_newpassword);

        return $stmt->execute();
    }

    public function deleteAccount(int $userid)
    {
        $sqldelete = 'DELETE FROM bucxai_users WHERE user_id = :userid';
        $stmt = $this->db->prepare($sqldelete);
        $stmt->bindParam(":userid", $userid);
        $stmt->execute();
        return $stmt;
    }

    
}
