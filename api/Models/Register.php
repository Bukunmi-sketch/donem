<?php

// require "./db/Database.php";
// $db = new Database();
// $conn = $db->getConnection();

class Register{
    private $db;
    

    public function __construct($db) {
        $this->db = $db;
    }

    public function createUser($firstname,$lastname, $phone, $address,$email,$otp) {
        $role = 'user';
        try {
            $sql="INSERT INTO users(firstname,lastname, phone, address,email,otp) VALUES (:firstname, :lastname, :phone, :address,:email,:otp)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ":firstname" => $firstname,
                ":lastname" => $lastname,
                ":phone" => $phone,
                ":address" => $address,
                ":email" => $email,
                ":otp" => $otp
            ]);

            if ($result) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function verifyEmail(string $email, int $mailotp): bool
    {
        try {
            $sql = "SELECT * FROM users WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            // if ($stmt->rowCount() > 0) {
                $returnedRow = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($mailotp == $returnedRow['otp']){
                    return true;
                }else{
                    return false;
                }
            

            
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function updateEmailVerified(string $email, int $emailVerified) {
       
        try {
            $sql="UPDATE users SET emailVerified = :emailVerified WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ":email" => $email,
                ":emailVerified" => $emailVerified
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

  

    public function createPassword($password,$email,$phone){
        try {
            // Hash the password
            $user_hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql="UPDATE users SET password = :password WHERE email = :email AND phone = :phone ";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ":password"=>$user_hashed_password,
                ":email" => $email,
                ":phone" => $phone
            ]);
            return $result;
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

}


?>