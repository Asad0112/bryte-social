<?php
namespace Php\Controllers;

use Exception;
use Php\LinkedInAuth\LinkedInAuth;

class LinkedInController {

    public function handleRequest() {
        session_start();
        $linkedinAuth = new LinkedInAuth();

        if (isset($_GET['code'])) {
            try {
                $accessToken = $linkedinAuth->handleCallback($_GET['code']);
                echo "Access Token: " . $accessToken . '<br><br>';
            } catch (Exception $e) {
                echo 'Exception: ' . $e->getMessage();
            }
        } else {
            $loginUrl = $linkedinAuth->getLoginUrl();
            echo '<a href="' . htmlspecialchars($loginUrl) . '">Login with LinkedIn</a>';
        }
    }
}