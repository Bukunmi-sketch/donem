<?php


class Login
{
    private PDO $db;

    public function __construct(PDO $conn)
    {
        $this->db = $conn;
    }

   
    public function login(string $email, string $password)
    {
        try {
            $sql = "SELECT * FROM users WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $returned_row = $stmt->fetch(PDO::FETCH_ASSOC);

                if (password_verify($password, $returned_row['password'])) {

                    return $returned_row;
                    // return [
                    //     'id' => $returned_row['user_id'],
                    //     'fullname' => $returned_row['fullname'],
                    //     'email' => $returned_row['email'],
                    //     'reg_date' => $returned_row['reg_date'],
                    //     'password' => $returned_row['password']
                    // ];
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Updates the last activity details of a user in the database.
     *
     * @param int $sessionid The session ID of the user.
     * @param string $activitytime The time of the last activity.
     * @param string $activetime The time of the last activity.
     * @param string $activedate The date of the last activity.
     * @param string $datelastactivity The date of the last activity performed by the user.
     *
     * @return bool If the execution of the SQL statement is successful, it returns true.
     */
    public function lastActivity(int $sessionid, string $activitytime, string $activetime, string $activedate, string $datelastactivity): bool
    {
        $query = "UPDATE bucxai_users SET LastActivity = :activitytime, LastActiveTime = :activetime, LastActiveDate = :activedate, DateLastActivity = :datelastactivity WHERE user_id = :sessionid";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":sessionid", $sessionid);
        $stmt->bindParam(":activitytime", $activitytime);
        $stmt->bindParam(":activetime", $activetime);
        $stmt->bindParam(":activedate", $activedate);
        $stmt->bindParam(":datelastactivity", $datelastactivity);

        return $stmt->execute();
    }

    /**
     * Updates the IP address and browser type of a user in the database based on their session ID.
     *
     * @param int $sessionid The session ID of the user.
     * @param string $ipaddress The IP address of the user.
     * @param string $browsertype The type of browser that the user is using.
     *
     * @return bool If the execution of the SQL query is successful, it returns true.
     */
    public function usersLogData(int $sessionid, string $ipaddress, string $browsertype): bool
    {
        $query = "UPDATE bucxai_users SET ip_address = :ipaddress, browser_type = :browsertype WHERE user_id = :sessionid";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":sessionid", $sessionid);
        $stmt->bindParam(":ipaddress", $ipaddress);
        $stmt->bindParam(":browsertype", $browsertype);

        return $stmt->execute();
    }
}


?>
