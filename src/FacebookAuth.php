<?php
namespace Php\FacebookAuth;
require_once __DIR__ . '/../vendor/autoload.php';

use Exception;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Php\Social\Social;
use Php\SocialBrytead\Bryteads;

class FacebookAuth
{
    private $helper;
    private $permissions = ['email', 'public_profile'];  //permission will be added here once we get the app reviewed.

    public function __construct()
    {
        $fb = Bryteads::getFacebookClient();
        $this->helper = $fb->getRedirectLoginHelper();
    }

    public function getLoginUrl($redirectUrl)
    {
        $scopes = array_map('trim', explode(',', getenv('FACEBOOK_SCOPE')));
        return $this->helper->getLoginUrl($redirectUrl, $scopes);
    }

    public function handleCallback()
    {
        try {
            $accessToken = $this->helper->getAccessToken();
            if (!isset($accessToken)) {
                throw new Exception('Access Token not received.');
            }

            $_SESSION['fb_access_token'] = (string) $accessToken;

            // Save the access token to a JSON file with a UUID
            Social::saveAccessTokenToFile('facebook', $_SESSION['fb_access_token'], getenv('USER_UUID'));

            return $_SESSION['fb_access_token'];
        } catch(FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        } catch(Exception $e) {
            echo 'Error: ' . $e->getMessage();
            exit;
        }
    }

    public function getUserProfile()
    {
        try {
            $fb = Bryteads::getFacebookClient();
            $response = $fb->get('/me?fields=id,name,email', $_SESSION['fb_access_token']);
            $user = $response->getGraphUser();
            return $user;
        } catch(FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }

    public static function getAccessTokenFromFile($uuid) {
        $filename = __DIR__ . '/../storage/' . $uuid . '.json';

        if (!file_exists($filename)) {
            throw new Exception('Access token file does not exist.');
        }

        $data = json_decode(file_get_contents($filename), true);

        if (isset($data['facebook']['access_token'])) {
            return $data['facebook']['access_token'][0]; // getting the first access token for now.
        } else {
            throw new Exception('Access token not found in file.');
        }
    }
}
