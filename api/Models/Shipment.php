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

    public function createShipment($user_id,$pickupAddress, $destination, $shipmentDate, $recipientDetails, $packages)
    {
        try {
            // Start a transaction
            $this->db->beginTransaction();
            $sqlcreateshipment = "INSERT INTO shipments (pickup_address, destination, shipment_date, recipient_details) VALUES (:pickup_address, :destination, :shipment_date, :recipient_details)";
            $stmt = $this->db->prepare($sqlcreateshipment);
            $stmt->bindParam(':pickup_address', $pickupAddress);
            $stmt->bindParam(':destination', $destination);
            $stmt->bindParam(':shipment_date', $shipmentDate);
            $stmt->bindParam(':recipient_details', $recipientDetails);
            $stmt->execute();

            // Get the last inserted shipment ID
            $shipmentId = $this->db->lastInsertId();

            // Insert package details into 'packages' table
            foreach ($packages as $package) {
                $size = $package['size'];
                $weight = $package['weight'];
                $valueUSD = $package['value_usd'];
                $this->insertPackages($shipmentId, $size, $weight, $valueUSD);

                // $stmt =$this->db->prepare("INSERT INTO packages (shipment_id, size, weight, value_usd) 
                //                         VALUES (:shipment_id, :size, :weight, :value_usd)");
                // $stmt->bindParam(':shipment_id', $shipmentId);
                // $stmt->bindParam(':size', $size);
                // $stmt->bindParam(':weight', $weight);
                // $stmt->bindParam(':value_usd', $valueUSD);
                // $stmt->execute();

                // Get the last inserted package ID
                $packageId = $this->db->lastInsertId();

                // Insert package images into 'package_images' table
                foreach ($package['images'] as $imageUrl) {
                    // $stmt =$this->db->prepare("INSERT INTO package_images (package_id, image_url) 
                    //                         VALUES (:package_id, :image_url)");
                    // $stmt->bindParam(':package_id', $packageId);
                    // $stmt->bindParam(':image_url', $imageUrl);
                    // $stmt->execute();
                    $this->insertPackageImage($packageId, $imageUrl);
                }
            }

            // Commit the transaction
            $this->db->commit();

            return true; // Shipment creation successful
        } catch (PDOException $e) {
            // Rollback the transaction if an error occurred
            $this->db->rollback();
            return false; // Shipment creation failed
        }
    }

    public function insertPackages($shipmentId, $size, $weight, $valueUSD)
    {

        $sqlcreatepackage = "INSERT INTO packages (shipment_id, size, weight, value_usd) VALUES (:shipment_id, :size, :weight, :value_usd)";
        $stmt = $this->db->prepare($sqlcreatepackage);
        $stmt->bindParam(':shipment_id', $shipmentId);
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



    public function getUserShipment()
    {
        // User ID for whom you want to retrieve the shipment details
        $user_id = 123; // Replace with the actual user ID

        // Query to retrieve shipment details along with associated packages and package images
        $sql = "SELECT s.*, p.*, pi.image_url
        FROM shipments s
        JOIN packages p ON s.shipment_id = p.shipment_id
        LEFT JOIN package_images pi ON p.package_id = pi.package_id
        WHERE s.user_id = :user_id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            // Fetch all rows
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Process the results
            foreach ($results as $row) {
                // Access shipment details
                $shipment_id = $row['shipment_id'];
                $pickup_address = $row['pickup_address'];
                $destination = $row['destination'];
                $date = $row['date'];
                $recipient_details = $row['recipient_details'];

                // Access package details
                $package_id = $row['package_id'];
                $size = $row['size'];
                $weight = $row['weight'];
                $value_usd = $row['value_usd'];

                // Access package image URL
                $image_url = $row['image_url'];

                // Output or process the retrieved data as needed
                echo "Shipment ID: $shipment_id, Pickup Address: $pickup_address, Destination: $destination, Date: $date, Recipient Details: $recipient_details <br>";
                echo "Package ID: $package_id, Size: $size, Weight: $weight, Value in USD: $value_usd <br>";
                echo "Package Image URL: $image_url <br><br>";
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }




    function getAddressCoordinates($address) {
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
    
    function getGeolocationUsingCurl($address) {
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
        if(curl_errno($curl)) {
            $error = curl_error($curl);
            curl_close($curl);
            return "Error: " . $error;
        }
    
        // Close cURL session
        curl_close($curl);
    
        // Decode JSON response
        $data = json_decode($response, true);
    
        // Check if response contains results
        if(isset($data['results']) && !empty($data['results'])) {
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
public function storeCargoLocation($cargoId, $latitude, $longitude) {
    // Store cargo location in the database or any other storage mechanism
    // Update the location of the cargo with the given cargoId
}

// Function to get cargo location
public function getCargoLocation($cargoId,$userid) {
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


function updateCargoLocation($cargoId, $address,$userid) {
    // Convert address to latitude and longitude coordinates
    $coordinates =$this->getAddressCoordinates($address);

    if ($coordinates) {
        $latitude = $coordinates['latitude'];
        $longitude = $coordinates['longitude'];
        
        // Store cargo location with latitude and longitude
        $this->storeCargoLocation($cargoId, $latitude, $longitude);

        // Get cargo location
        $location =$this->getCargoLocation($cargoId,$userid);
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




public function degreesToRadians($degrees) {
    return $degrees * pi() / 180.0;
}

public function radiansToDegrees($radians) {
    return $radians * 180.0 / pi();
}

public function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Radius of the earth in kilometers

    $dLat = $this->degreesToRadians($lat2 - $lat1);
    $dLon =$this->degreesToRadians($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos($this->degreesToRadians($lat1)) * cos($this->degreesToRadians($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    $distance = $earthRadius * $c; // Distance in kilometers

    return $distance;
}

public function sendRepackageRequest($userid,$shipmentId){

}

public function userDeleteShipment($userid,$shipmentId,$trackingid){
    $sqldelete = 'DELETE FROM bucxai_users WHERE user_id = :userid';
    $stmt = $this->db->prepare($sqldelete);
    $stmt->bindParam(":userid", $userid);
    $stmt->execute();
    return $stmt;
}

public function userAcceptRepackage(){

}

public function userRejectRepackage(){

}


public function getShipMentNotification(){

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
