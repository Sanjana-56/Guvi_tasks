<?php
require 'C:/xampp/htdocs/task1/vendor/autoload.php'; 

use MongoDB\Client;
use MongoDB\Exception\Exception as MongoDBException;

// MongoDB configuration
$mongoHost = "localhost";
$mongoPort = 27017;
$mongoDbName = "intern";
$mongoCollectionName = "profiles";

try {
    // Connect to MongoDB
    $mongoClient = new Client("mongodb://$mongoHost:$mongoPort");
    $db = $mongoClient->$mongoDbName;
    $collection = $db->$mongoCollectionName;
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Sanitize and validate inputs (adjust as per your specific needs)
        $name = htmlspecialchars($_POST['name'] ?? '');
        $surname = htmlspecialchars($_POST['surname'] ?? '');
        $age = intval($_POST['age'] ?? 0); 
        $dob = htmlspecialchars($_POST['dob'] ?? '');
        $mobileNumber = htmlspecialchars($_POST['mobileNumber'] ?? '');
        $address = htmlspecialchars($_POST['address'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $country = htmlspecialchars($_POST['country'] ?? '');
        $state = htmlspecialchars($_POST['state'] ?? '');

        // Update document in MongoDB
        $filter = ['email' => $email]; // Assuming email is the unique identifier
        $update = [
            '$set' => [
                'name' => $name,
                'surname' => $surname,
                'age' => $age,
                'dob' => $dob,
                'mobileNumber' => $mobileNumber,
                'address' => $address,
                'country' => $country,
                'state' => $state
            ]
        ];

        $updateResult = $collection->updateOne($filter, $update);

        if ($updateResult->getModifiedCount() > 0) {
            echo "Profile updated successfully.";
        } else {
            echo "Error updating profile.";
        }
    } elseif ($_SERVER["REQUEST_METHOD"] == "GET") {
        // Fetch profile details based on session email (or any authentication method)
        session_start();
        if (isset($_SESSION['loggedInEmail'])) {
            $loggedInEmail = $_SESSION['loggedInEmail'];

            $filter = ['email' => $loggedInEmail];
            $profile = $collection->findOne($filter);

            if ($profile) {
                echo json_encode($profile);
            } else {
                echo "Profile not found.";
            }
        } else {
            echo "User not logged in.";
        }
    }
} catch (MongoDBException $e) { 
    echo "MongoDB Error: ";
} catch (Exception $e) { 
    echo "General Error: " . $e->getMessage();
}
?>
