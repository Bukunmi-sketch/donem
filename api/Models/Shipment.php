<?php


require "./db/Database.php";
$db = new Database();
$conn = $db->getConnection();


// require __DIR__ . '/vendor/autoload.php';

// // Use the appropriate namespaces
// use Pusher\Pusher;

// Initialize Pusher
// $options = array(
//     'cluster' => 'YOUR_PUSHER_CLUSTER',
//     'useTLS' => true
// );
// $pusher = new Pusher(
//     'YOUR_PUSHER_KEY',
//     'YOUR_PUSHER_SECRET',
//     'YOUR_PUSHER_APP_ID',
//     $options
// );

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
            // Start a transaction
            $this->db->beginTransaction();

            // Insert shipment details into 'shipments' table
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

            // Get the last inserted shipment ID
            $shipmentId = $this->db->lastInsertId();

            // Insert package details into 'packages' table
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

            // Commit the transaction
            $this->db->commit();

            return true; // Shipment creation successful
        } catch (PDOException $e) {
            // Rollback the transaction if an error occurred
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

            // Check if any shipments were found
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
            // Handle the error gracefully, log it, or return a specific error response
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
                // Process packages into arrays
                // foreach ($shipments as &$shipment) {
                //     $packages = explode(',', $shipment['packages']);
                //     $shipment['packages'] = [];
                //     foreach ($packages as $package) {
                //         list($package_id, $size, $weight, $value_usd) = explode(':', $package);
                //         $shipment['packages'][] = [
                //             'package_id' => $package_id,
                //             'size' => $size,
                //             'weight' => $weight,
                //             'value_usd' => $value_usd
                //         ];
                //     }
                // }
                return $shipments;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            // Handle the error gracefully, log it, or return a specific error response
            echo "Error: " . $e->getMessage();
            return false;
        }
    }




    function getAddressCoordinates($address)
    {
        // Replace 'YOUR_API_KEY' with your actual Google Maps API key
        $apiKey = 'YOUR_API_KEY';
        $address = urlencode($address);
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key={$apiKey}";

        // Send request to Google Maps Geocoding API
        $response = file_get_contents($url);
        $json = json_decode($response, true);

        // Check if the request was successful
        if ($json['status'] == 'OK') {
            // Extract latitude and longitude from the response
            $latitude = $json['results'][0]['geometry']['location']['lat'];
            $longitude = $json['results'][0]['geometry']['location']['lng'];
            return ['latitude' => $latitude, 'longitude' => $longitude];
        } else {
            // If the request failed, return null
            return null;
        }
    }

    function getGeolocationUsingCurl($address)
    {
        // API endpoint for Google Maps Geocoding API
        $apiUrl = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address);

        // Initialize cURL session
        $curl = curl_init();

        // Set cURL options
        curl_setopt($curl, CURLOPT_URL, $apiUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // Execute cURL request
        $response = curl_exec($curl);

        // Check for errors
        if (curl_errno($curl)) {
            $error = curl_error($curl);
            curl_close($curl);
            return "Error: " . $error;
        }

        // Close cURL session
        curl_close($curl);

        // Decode JSON response
        $data = json_decode($response, true);

        // Check if response contains results
        if (isset($data['results']) && !empty($data['results'])) {
            // Extract latitude and longitude from the first result
            $location = $data['results'][0]['geometry']['location'];
            $latitude = $location['lat'];
            $longitude = $location['lng'];

            return "Latitude: $latitude, Longitude: $longitude";
        } else {
            return "No results found for the given address.";
        }
    }

    // Test the function with an address
    // $address = "1600 Amphitheatre Parkway, Mountain View, CA";
    // echo getGeolocationUsingCurl($address);



    // Function to store cargo location
    public function storeCargoLocation($cargoId, $latitude, $longitude)
    {
        // Store cargo location in the database or any other storage mechanism
        // Update the location of the cargo with the given cargoId
    }

    // Function to get cargo location
    public function getCargoLocation($cargoId, $userid)
    {
        try {
            $sql = "SELECT *
        FROM bucxai_users
        INNER JOIN bucxai_profiles ON bucxai_users.user_id = bucxai_profiles.user_id
        WHERE bucxai_users.user_id = :userid;";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":userid", $userid);
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
        // Retrieve cargo location from the database or any other storage mechanism
        // Return the cargo location as an array ['latitude' => $latitude, 'longitude' => $longitude]
    }


    function updateCargoLocation($cargoId, $address, $userid)
    {
        // Convert address to latitude and longitude coordinates
        $coordinates = $this->getAddressCoordinates($address);

        if ($coordinates) {
            $latitude = $coordinates['latitude'];
            $longitude = $coordinates['longitude'];

            // Store cargo location with latitude and longitude
            $this->storeCargoLocation($cargoId, $latitude, $longitude);

            // Get cargo location
            $location = $this->getCargoLocation($cargoId, $userid);
            $data = [
                'cargo_id' => $cargoId,
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude']
            ];

            // Trigger a Pusher event to notify clients about the new location
            $pusher->trigger('cargo-location', 'update', $data);
        } else {
            // Handle error if address conversion fails
            echo "Error: Unable to convert address to coordinates.";
        }
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





    // Example usage:
    // $pickupAddress = "123 Main St";
    // $destination = "456 Oak St";
    // $shipmentDate = "2024-03-16";
    // $recipientDetails = "John Doe";
    // $packages = [
    //     [
    //         'size' => 'Small',
    //         'weight' => 2.0,
    //         'value_usd' => 50.00,
    //         'images' => ['http://example.com/package1_image1.jpg', 'http://example.com/package1_image2.jpg']
    //     ],
    //     [
    //         'size' => 'Large',
    //         'weight' => 5.0,
    //         'value_usd' => 100.00,
    //         'images' => ['http://example.com/package2_image1.jpg', 'http://example.com/package2_image2.jpg']
    //     ]
    // ];


}
