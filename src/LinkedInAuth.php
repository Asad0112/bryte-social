<?php
namespace Php\LinkedInAuth;
require_once __DIR__ . '/../vendor/autoload.php';

use Exception;
use Php\Helpers\CurlHelper;
use Php\SocialBrytead\Bryteads;

class LinkedInAuth
{
    public function getLoginUrl() {
        $linkedInConfig = Bryteads::getLinkedInClient();
        $params = [
            'response_type' => 'code',
            'client_id' => $linkedInConfig['client_id'],
            'redirect_uri' => $linkedInConfig['redirect_url'],
            'scope' =>  $linkedInConfig['scope'],
            'state' => bin2hex(random_bytes(8))
        ];

        return $linkedInConfig['api_base_url'].'/authorization?' . http_build_query($params);
    }

    public function handleCallback($code) {
        $linkedInConfig = Bryteads::getLinkedInClient();
        $url = $linkedInConfig['api_base_url'].'/accessToken';
        $data = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $linkedInConfig['redirect_url'],
            'client_id' => $linkedInConfig['client_id'],
            'client_secret' => $linkedInConfig['client_secret'],
        ];

        $response = CurlHelper::call($url, 'POST', http_build_query($data));
        
        $responseArray = json_decode($response, true);

        if (isset($responseArray['access_token'])) {
            $_SESSION['linkedin_access_token'] = $responseArray['access_token'];
            return $responseArray['access_token'];
        } else {
            throw new Exception('Access Token not received. Response: ' . json_encode($responseArray));
        }
    }
}