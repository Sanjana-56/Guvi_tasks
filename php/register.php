<?php

require 'C:/xampp/htdocs/task1/vendor/autoload.php';
use MongoDB\Client;
use Predis\Client as RedisClient;

$redis = new RedisClient([
    'scheme' => 'tcp',
    'host'   => '127.0.0.1',
    'port'   => 6379,
]);

try {
    if (!$redis->ping()) {
        throw new Exception('Could not connect to Redis');
    }
} catch (Exception $e) {
    echo 'Redis connection error: ' . $e->getMessage();
    exit;
}

$host = "localhost";
$username = "root";
$password = "";
$dbname = "phpmyadmin";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}

$response = array('success' => false);

if (isset($_POST['checkUsername'])) {
    $username = $_POST['checkUsername'];
    $stmt = $conn->prepare("SELECT COUNT(*) FROM task1 WHERE user = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $count = $stmt->fetchColumn();
    $response['success'] = $count == 0;
    echo json_encode($response);
    exit;
}

if (isset($_POST['checkEmail'])) {
    $email = $_POST['checkEmail'];
    $stmt = $conn->prepare("SELECT COUNT(*) FROM task1 WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $count = $stmt->fetchColumn();
    $response['success'] = $count == 0;
    echo json_encode($response);
    exit;
}

if (isset($_POST['name']) && !empty($_POST['name']) && isset($_POST['password']) && !empty($_POST['password']) && isset($_POST['email']) && !empty($_POST['email'])) {
    $name = $_POST['name'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    try {
        $emailKey = "submission_attempts:" . $email;
        $attempts = $redis->incr($emailKey);
        $redis->expire($emailKey, 3600);

        if ($attempts > 5) {
            $response['message'] = "Too many submission attempts. Please try again later.";
        } else {
            $stmt = $conn->prepare("INSERT INTO task1 (user, pass, email) VALUES (:name, :password, :email)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':email', $email);

            if ($stmt->execute()) {
                // MongoDB integration
                $mongoClient = new Client("mongodb://localhost:27017");
                $mongoDbName = "intern"; // Replace with your MongoDB database name
                $mongoCollectionName = "profiles"; // Collection to store registered emails
                
                $db = $mongoClient->$mongoDbName;
                $collection = $db->$mongoCollectionName;

                $insertResult = $collection->insertOne(['email' => $email]);

                if ($insertResult->getInsertedCount() > 0) {
                    $response['success'] = true;
                    $redis->del($emailKey); // Clear attempts in Redis
                } else {
                    $response['message'] = "Error inserting email into MongoDB.";
                }
            } else {
                $response['message'] = "Error inserting data into MySQL.";
            }
        }
    } catch (PDOException $e) {
        $response['message'] = "Error: " . $e->getMessage();
    }
} else {
    $response["message"] = "Please provide required details.";
}

echo json_encode($response);
?>