<?php
namespace Php\Social;
require_once __DIR__ . '/../vendor/autoload.php';

use Exception;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Php\FacebookAuth\FacebookAuth;
use Php\SocialBrytead\Bryteads;

class Social
{
    public static function post($platform, $post, $uuid) {
        try {
            if($platform == 'facebook') {
                $fb = Bryteads::getFacebookClient();
    
                $accessToken = self::getAccessTokenFromFile('facebook',$uuid);
                $response = $fb->post('/me/feed', ['message' => $post], $accessToken);
                return $response->getGraphNode();
            } else if($platform == 'twitter') {
                $twitterAuth = new TwitterAuth();
                $twitterConfig = Bryteads::getTwitterClient();
                $url = $twitterConfig['api_base_url'].'/tweets';
                $data = [
                    'text' => $post
                ];
                return $twitterAuth->makeApiRequest($url, 'POST', $data);
            }
        } catch(Exception $e) {
            echo 'Exception: ' . $e->getMessage(). ' '. $e->getLine(). ' '. $e->getFile();
            exit;
        }
    }

    public static function fetchPosts($platform,$uuid) {
        try {
            if($platform == 'facebook') {
                $fb = Bryteads::getFacebookClient();
                $accessToken = self::getAccessTokenFromFile('facebook',$uuid);
                $response = $fb->get('/me/posts', $accessToken);
                return $response->getDecodedBody(); // This will return the posts in an associative array
            }
        } catch(Exception $e) {
            echo 'Exception: ' . $e->getMessage(). ' '. $e->getLine(). ' '. $e->getFile();
            exit;
        }
    }

    public static function fetchUserDetails($platform, $uuid) {
        try {
            if($platform == 'facebook') {
                $fb = Bryteads::getFacebookClient();
                $accessToken = self::getAccessTokenFromFile('facebook',$uuid);
                $response = $fb->get('/me', $accessToken);
                return $response->getDecodedBody();
            } else if($platform == 'twitter') {
                $twitterAuth = new TwitterAuth();
                $twitterConfig =  Bryteads::getTwitterClient();
                $url = $twitterConfig['api_base_url'].'/users/me';
                $userProfile = $twitterAuth->makeApiRequest($url);
                return $userProfile['data'];
            }
        } catch(Exception $e) {
            echo 'Exception: ' . $e->getMessage(). ' '. $e->getLine(). ' '. $e->getFile();
            exit;
        }
    }

    public static function saveAccessTokenToFile($platform, $accessToken, $uuid) {
        $storageDir = __DIR__ . '/../storage';
        $filename = $storageDir . '/' . $uuid . '.json';
    
        if (!is_dir($storageDir)) {
            $createDirectory = mkdir($storageDir, 0775, true);
            if (!$createDirectory) {
                throw new Exception('Failed to create storage directory.');
            }
        }

        $existingData = [];
    
        if (file_exists($filename)) {
            $existingData = json_decode(file_get_contents($filename), true);
            if ($existingData === null) {
                $existingData = [];
            }
        } else {
            $existingData = [];
        }
    
        if (!isset($existingData[$platform])) {
            $existingData[$platform] = [
                'access_token' => []
            ];
        }
    
        // Check if the access token already exists before adding it
        if ($accessToken !== null && !in_array($accessToken, $existingData[$platform]['access_token'])) {
            $existingData[$platform]['access_token'][] = $accessToken;
        }
            
        if (false === file_put_contents($filename, json_encode($existingData, JSON_PRETTY_PRINT))) {
            $error = error_get_last();
            throw new Exception('Failed to write access token to file: ' . $error['message']);
        }
    }

    public static function getAccessTokenFromFile($platform, $uuid) {
        $filename = __DIR__ . '/../storage/' . $uuid . '.json';

        if (!file_exists($filename)) {
            throw new Exception('Access token file does not exist.');
        }

        $data = json_decode(file_get_contents($filename), true);

        if (array_key_exists($platform, $data) && isset($data[$platform]['access_token'])) {
            return $data[$platform]['access_token'][count($data[$platform]['access_token']) -1 ]; // getting the latest access token for now.
        } else {
            throw new Exception('Access token not found in file.');
        }
    }
    
}
        