<?php
require 'C:/xampp/htdocs/task1/vendor/autoload.php';

use Predis\Client;

$redis = new Client([
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

try {
    $conn = new PDO("mysql:host=$host;dbname=phpmyadmin", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}

$response = array('success' => false);

if (isset($_POST['action']) && $_POST['action'] == 'check_email') {
    $email = $_POST['email'];
    
    $sql = "SELECT * FROM task1 WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $response['success'] = true;
    } else {
        $response['success'] = false;
        $response['message'] = "Invalid email.";
    }
    
    echo json_encode($response);
    exit;
}

if (isset($_POST['action']) && $_POST['action'] == 'check_password') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM task1 WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if ($user['pass'] === $password) {
            $response['success'] = true;
        } else {
            $response['success'] = false;
            $response['message'] = "Invalid password.";
        }
    } else {
        $response['success'] = false;
        $response['message'] = "Invalid email.";
    }

    echo json_encode($response);
    exit;
}

if (isset($_POST['email']) && $_POST['email'] != '' && isset($_POST['password']) && $_POST['password'] != '') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $rateKey = "login_attempts:" . $email;
    $attempts = $redis->incr($rateKey);
    $redis->expire($rateKey, 3600);

    if ($attempts > 5) {
        $response['message'] = "Too many login attempts. Please try again later.";
    } else {
        $userKey = "user:" . $email;
        $cachedUser = $redis->get($userKey);

        if ($cachedUser) {
            $user = json_decode($cachedUser, true);
            if ($user['pass'] === $password) {
                session_start();
                $_SESSION['loggedInEmail'] = $email;
                $response['success'] = true;
                $response['user'] = $user;
                $redis->del($rateKey);
            } else {
                $response['message'] = "Invalid password.";
                $response['errorField'] = 'password';
            }
        } else {
            $sql = "SELECT * FROM task1 WHERE email = :email";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                if ($user['pass'] === $password) {
                    session_start();
                    $_SESSION['loggedInEmail'] = $email;
                    $response['success'] = true;
                    $response['user'] = $user;

                    $redis->set($userKey, json_encode($user));
                    $redis->expire($userKey, 3600);
                    $redis->del($rateKey);
                } else {
                    $response['message'] = "Invalid password.";
                    $response['errorField'] = 'password';
                }
            } else {
                $response['message'] = "Invalid email.";
                $response['errorField'] = 'email';
            }
        }
    }
} else {
    $response['message'] = "Please provide both email and password.";
}

echo json_encode($response);
?>