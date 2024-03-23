<?php


require "./db/Database.php";
$db = new Database();
$conn = $db->getConnection();


// require __DIR__ . '/vendor/autoload.php';

// $options = array(
//   'cluster' => 'mt1',
//   'useTLS' => true
// );
// $pusher = new Pusher\Pusher(
//   'e266cd70d468b00faf2d',
//   '311e4a3f18f05aa78e62',
//   '1776280',
//   $options
// );

// $data['message'] = 'hello world';
// $pusher->trigger('my-channel', 'my-event', $data);

class Shipment
{

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function createShipment($user_id, $trackingid, $pickupAddress, $destination, $shipmentDate, $recipient_fname, $recipient_lname, $recipient_email, $recipient_phone, $packages)
    {
        try {
            $this->db->beginTransaction();
            $sqlcreateshipment = "INSERT INTO shipments (user_id, tracking_id, pickup_address, destination_address, date, recipient_fname, recipient_lname, recipient_email, recipient_phone, current_status) VALUES (:user_id, :tracking_id, :pickup_address, :destination_address, :date, :recipient_fname, :recipient_lname, :recipient_email, :recipient_phone, :current_status)";
            $stmt = $this->db->prepare($sqlcreateshipment);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':tracking_id', $trackingid);
            $stmt->bindParam(':pickup_address', $pickupAddress);
            $stmt->bindParam(':destination_address', $destination);
            $stmt->bindParam(':date', $shipmentDate);
            $stmt->bindParam(':recipient_fname', $recipient_lname);
            $stmt->bindParam(':recipient_lname', $recipient_fname);
            $stmt->bindParam(':recipient_email', $recipient_email);
            $stmt->bindParam(':recipient_phone', $recipient_phone);
            $status = "Pending"; // Set initial status
            $stmt->bindParam(':current_status', $status);
            $stmt->execute();

            $shipmentId = $this->db->lastInsertId();

            foreach ($packages as $package) {
                $description = $package['description'];
                $size = $package['size'];
                $weight = $package['weight'];
                $valueUSD = $package['value_usd'];
                $this->insertPackages($shipmentId, $description, $size, $weight, $valueUSD);

                // Get the last inserted package ID
                $packageId = $this->db->lastInsertId();

                // Insert package images into 'package_images' table
                foreach ($package['images'] as $imageUrl) {
                    $this->insertPackageImage($packageId, $imageUrl);
                }
            }
            $this->db->commit();
            return true; // Shipment creation successful
        } catch (PDOException $e) {
            $this->db->rollback();
            echo $e->getMessage();        // return false; // Shipment creation failed
        }
    }

    public function insertPackages($shipmentId, $package_description, $size, $weight, $valueUSD)
    {

        $sqlcreatepackage = "INSERT INTO packages (shipment_id,package_description, size, weight, value_usd) VALUES (:shipment_id,:package_description, :size, :weight, :value_usd)";
        $stmt = $this->db->prepare($sqlcreatepackage);
        $stmt->bindParam(':shipment_id', $shipmentId);
        $stmt->bindParam(':package_description', $package_description);
        $stmt->bindParam(':size', $size);
        $stmt->bindParam(':weight', $weight);
        $stmt->bindParam(':value_usd', $valueUSD);
        $stmt->execute();
    }

    public function insertPackageImage($packageId, $imageUrl)
    {
        $sql = "INSERT INTO package_images (package_id, image_url) VALUES (:package_id, :image_url)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':package_id', $packageId);
        $stmt->bindParam(':image_url', $imageUrl);
        $stmt->execute();
    }




    public function getAllShipments()
    {
        $sql = "SELECT firstname FROM bucxai_users";
        $stmt = $this->db->query($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $firstNames = [];
        foreach ($data as $row) {
            $firstNames[] = $row['firstname'];
        }
        return $firstNames;
    }


    public function getUserShipment($user_id)
    {
        $sql = "SELECT s.*, GROUP_CONCAT(CONCAT(p.package_id, ':', p.size, ':', p.weight, ':', p.value_usd)) AS packages
            FROM shipments s
            INNER JOIN packages p ON s.shipment_id = p.shipment_id
            WHERE  s.user_id = :user_id
            GROUP BY s.shipment_id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($shipments) {
                // Process packages into arrays
                foreach ($shipments as &$shipment) {
                    $packages = explode(',', $shipment['packages']);
                    $shipment['packages'] = [];
                    foreach ($packages as $package) {
                        list($package_id, $size, $weight, $value_usd) = explode(':', $package);
                        $shipment['packages'][] = [
                            'package_id' => $package_id,
                            'size' => $size,
                            'weight' => $weight,
                            'value_usd' => $value_usd
                        ];
                    }
                }
                return $shipments;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }


    public function getShipmentDetails($shipmentid)
    {
        $sql = "SELECT s.*, GROUP_CONCAT(CONCAT(p.package_id, ':', p.size, ':', p.weight, ':', p.value_usd)) AS packages
        FROM shipments s
        INNER JOIN packages p ON s.shipment_id = p.shipment_id
        WHERE  s.shipment_id = :shipmentid
        GROUP BY s.shipment_id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':shipmentid', $shipmentid);
            $stmt->execute();
            $shipments = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($shipments) {
                return $shipments;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }




    // function getAddressCoordinates($address)
    // {
    //     $apiKey = 'YOUR_API_KEY';
    //     $address = urlencode($address);
    //     $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key={$apiKey}";


    //     $response = file_get_contents($url);
    //     $json = json_decode($response, true);

    //     if ($json['status'] == 'OK') {
    //         $latitude = $json['results'][0]['geometry']['location']['lat'];
    //         $longitude = $json['results'][0]['geometry']['location']['lng'];
    //         return ['latitude' => $latitude, 'longitude' => $longitude];
    //     } else {
    //         // If the request failed, return null
    //         return null;
    //     }
    // }

   public function getGeolocationUsingCurl($address)
    {
        $apiKey = "AIzaSyDp32iWaShk9E_wTNtJbAkNXqdishmZnE8";
        $apiUrl = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=" . $apiKey;
    
        // Initialize cURL session
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $apiUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            $error = curl_error($curl);
            curl_close($curl);
            return "Error: " . $error;
        }
        curl_close($curl);
        $data = json_decode($response, true);

        if (isset($data['results']) && !empty($data['results'])) {
            $location = $data['results'][0]['geometry']['location'];
            $latitude = $location['lat'];
            $longitude = $location['lng'];

           $result= [
            'latitude' => $latitude,
            'longitude' => $longitude
           ];
          return $result;
        } else {
            return "No results found for the given address.";
        }
    }
    
    public function storeCargoLocation($shipmentid, $latitude, $longitude)
    {
        try {
            $sqlCheckCargo = "SELECT COUNT(*) AS count FROM package_locations WHERE shipment_id = :shipment_id";
            $stmtCheckCargo = $this->db->prepare($sqlCheckCargo);
            $stmtCheckCargo->bindParam(':shipment_id', $shipmentid);
            $stmtCheckCargo->execute();
            $cargoCount = $stmtCheckCargo->fetchColumn();

            if ($cargoCount > 0) {
                $sqlUpdateLocation = "UPDATE package_locations SET latitude = :latitude, longitude = :longitude WHERE shipment_id = :shipment_id";
                $stmtUpdateLocation = $this->db->prepare($sqlUpdateLocation);
                $stmtUpdateLocation->bindParam(':latitude', $latitude);
                $stmtUpdateLocation->bindParam(':longitude', $longitude);
                $stmtUpdateLocation->bindParam(':shipment_id', $shipmentid);
                $stmtUpdateLocation->execute();
            } else {
                $sqlInsertCargo = "INSERT INTO package_locations (shipment_id, latitude, longitude) VALUES (:shipment_id, :latitude, :longitude)";
                $stmtInsertCargo = $this->db->prepare($sqlInsertCargo);
                $stmtInsertCargo->bindParam(':shipment_id', $shipmentid);
                $stmtInsertCargo->bindParam(':latitude', $latitude);
                $stmtInsertCargo->bindParam(':longitude', $longitude);
                $stmtInsertCargo->execute();
            }

            return true; // Success
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false; // Failure
        }
    }

    public function getShipmentLocation($shipmentid)
    {
        try {
            $sql = "SELECT * FROM package_locations WHERE shipment_id = :shipmentid";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":shipmentid", $shipmentid);
            // $stmt->bindParam(":userid", $userid);
            $stmt->execute();
            $returned_row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($returned_row) {
                return $returned_row;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    
    }


   public function updateCargoLocation($userid,$shipmentId, $address)
    {
        // Convert address to latitude and longitude coordinates
        $coordinates = $this->getGeolocationUsingCurl($address);

        if ($coordinates) {
            $latitude = $coordinates['latitude'];
            $longitude = $coordinates['longitude'];

            // Store cargo location with latitude and longitude
            $this->storeCargoLocation($shipmentId, $latitude, $longitude);
            // Get cargo location
             $location = $this->getShipmentLocation($shipmentId, $userid);
            $data = [
                'shipment_id' => $shipmentId,
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude']
            ];
            return $data;
            // $pusher->trigger('cargo-location', 'update', $data);
        } else {
            // Handle error if address conversion fails
            echo "Error: Unable to convert address to coordinates.";
        }
    }

    public function searchShipmentByTrackingId($trackingId)
    {
        $sql = "SELECT s.*, 
    GROUP_CONCAT(CONCAT(p.package_id, ':', p.package_description, ':',  p.size, ':', p.weight, ':', p.value_usd)) AS packages,
    GROUP_CONCAT(CONCAT(pl.latitude, ':', pl.longitude)) AS package_locations
    FROM shipments s
    INNER JOIN packages p ON s.shipment_id = p.shipment_id
    LEFT JOIN package_locations pl ON p.shipment_id = pl.shipment_id
    WHERE  s.tracking_id= :trackingid
    GROUP BY s.shipment_id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':trackingid', $trackingId);
            $stmt->execute();
            $shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($shipments) {
                foreach ($shipments as &$shipment) {
                    $packages = explode(',', $shipment['packages']);
                    $shipment['packages'] = [];
                    foreach ($packages as $package) {
                        list($package_id, $package_description, $size, $weight, $value_usd) = explode(':', $package);
                        $shipment['packages'][] = [
                            'package_id' => $package_id,
                            'package_description' => $package_description,
                            'size' => $size,
                            'weight' => $weight,
                            'value_usd' => $value_usd
                        ];
                    }

                    //  Process package locations
                    $packageLocations = explode(',', $shipment['package_locations']);
                    $shipment['package_locations'] = [];
                    foreach ($packageLocations as $location) {
                        $locationParts = explode(':', $location);
                        if (count($locationParts) >= 2) {
                            list($latitude, $longitude) = $locationParts;
                            $shipment['package_locations'][] = [
                                'latitude' => $latitude,
                                'longitude' => $longitude
                            ];
                        }
                    }

                    // $shipment['image_urls'] = explode(',', $shipment['image_urls']);
                }
                return $shipments;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }


    // public function searchShipmentByTrackingId($trackingId)
    // {
    //     try {
    //         $sql = "SELECT s.*, p.*, pl.latitude, pl.longitude
    //             FROM shipments s
    //             LEFT JOIN packages p ON s.shipment_id = p.shipment_id
    //             LEFT JOIN package_locations pl ON p.shipment_id = pl.shipment_id
    //             WHERE s.tracking_id = :tracking_id";

    //         $stmt = $this->db->prepare($sql);
    //         $stmt->bindParam(':tracking_id', $trackingId);
    //         $stmt->execute();

    //         $shipmentData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //         if ($shipmentData) {
    //             return $shipmentData;
    //         } else {
    //             return false;
    //         }
    //     } catch (PDOException $e) {
    //         echo "Error: " . $e->getMessage();
    //         return false;
    //     }
    // }


    public function sendRepackageRequest($userid, $shipmentId)
    {
    }

    public function userDeleteShipment($userid, $shipmentid)
    {
        try {
            $sqldelete = 'DELETE FROM shipments WHERE user_id = :userid AND shipment_id = :shipmentid';
            $stmt = $this->db->prepare($sqldelete);
            $stmt->bindParam(":userid", $userid);
            $stmt->bindParam(":shipmentid", $shipmentid);
            $stmt->execute();
            $affectedRows = $stmt->rowCount();
            if ($affectedRows > 0) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false; // Error occurred during execution
        }
    }


    public function userAcceptRepackage()
    {
    }

    public function userRejectRepackage()
    {
    }


    public function getShipMentNotification()
    {
    }



    public function degreesToRadians($degrees)
    {
        return $degrees * pi() / 180.0;
    }

    public function radiansToDegrees($radians)
    {
        return $radians * 180.0 / pi();
    }

    public function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Radius of the earth in kilometers

        $dLat = $this->degreesToRadians($lat2 - $lat1);
        $dLon = $this->degreesToRadians($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos($this->degreesToRadians($lat1)) * cos($this->degreesToRadians($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c; // Distance in kilometers

        return $distance;
    }

}
