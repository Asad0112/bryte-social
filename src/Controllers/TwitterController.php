<?php
namespace Php\Controllers;

use Exception;
use Php\Social\Social;
use Php\Social\TwitterAuth;

class TwitterController {
    public function handleRequest() {
        session_start();
        $twitterAuth = new TwitterAuth();

        if (isset($_GET['code'])) {
            try {
                $accessToken = $twitterAuth->handleCallback($_GET['code']);
                echo "Access Token: " . $accessToken . '<br><br>';    

            } catch (Exception $e) {
                echo 'Exception: ' . $e->getMessage(). ' at line number '. $e->getLine(). ' in file '. $e->getFile();
                exit;
            }
        } else {
            $loginUrl = $twitterAuth->getLoginUrl();
            echo '<a href="' . htmlspecialchars($loginUrl) . '">Login with Twitter</a>';
        }
    }
}
?>
