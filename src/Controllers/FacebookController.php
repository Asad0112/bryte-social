<?php
namespace Php\Controllers;

use Php\FacebookAuth\FacebookAuth;
use Php\Social\Social;

class FacebookController {
    public function handleRequest() {
        session_start();

        $config = require_once __DIR__ . '/../config/services.php';
        $facebookConfig = $config['facebook'];
        $redirectUrl = $facebookConfig['redirect_url'];

        $facebookAuth = new FacebookAuth();

        if (isset($_GET['code'])) {
            $facebookAuth->handleCallback();
            $userProfile = $facebookAuth->getUserProfile();
            echo "Access Token: " . $_SESSION['fb_access_token'] . '<br><br>';
            echo 'User ID: ' . htmlspecialchars($userProfile->getId()) . '<br>';
            echo 'Name: ' . htmlspecialchars($userProfile->getName()) . '<br>';
            echo 'Email: ' . htmlspecialchars($userProfile->getEmail()) . '<br>';

            # For the testing, called this endpoint here to get the posts of the user.(Can be tested only after the app is reviewed).
            $posts = Social::fetchPosts('facebook', getenv('USER_UUID'));
            print_r($posts);
        } else {
            $loginUrl = $facebookAuth->getLoginUrl($redirectUrl);
            echo '<a href="' . htmlspecialchars($loginUrl) . '">Login with Facebook</a>';
        }
    }
}
?>
