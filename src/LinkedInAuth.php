<?php
namespace Php\LinkedInAuth;
require_once __DIR__ . '/../vendor/autoload.php';

use Exception;
use Php\Helpers\CurlHelper;
use Php\Social\Social;
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
            'grant_type' => 'authorization_code', # To Request, 3-legged token using which we can post onbehalf of the user, we need to specify the grant_type as authorization_code
            'code' => $code,
            'redirect_uri' => $linkedInConfig['redirect_url'],
            'client_id' => $linkedInConfig['client_id'],
            'client_secret' => $linkedInConfig['client_secret'],
        ];

        $response = CurlHelper::call($url, 'POST', http_build_query($data));
        
        $responseArray = json_decode($response, true);

        $filename = __DIR__ . '/../storage/' . getenv('USER_UUID') . '.json';

        // Check if the access token file exists
        if (file_exists($filename)) {
            $data = json_decode(file_get_contents($filename), true);

            // Check if there is already an access token for LinkedIn
            if (!empty($data) && array_key_exists('linkedin', $data) && isset($data['linkedin']['access_token'])) {
                return $data['linkedin']['access_token'];
            } else {
                // Save new access token to file
                Social::saveAccessTokenToFile('linkedin', $responseArray['access_token'], getenv('USER_UUID'));

                // Wait for 7 seconds before making new API calls
                // sleep(7);
                return $responseArray['access_token'];
            }
            
        } else {
                throw new Exception('Access Token not received. Response: ' . json_encode($responseArray));
            }
    }

    public function getLinkedInUser() {
        $accessToken = Social::getAccessTokenFromFile('linkedin', getenv('USER_UUID'));

        $url = 'https://api.linkedin.com/v2/userinfo';
    
        $curl = curl_init($url);
        
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
    
        $response = curl_exec($curl);
    
        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new Exception('cURL error: ' . $error);
        }
    
        curl_close($curl);
    
        $responseArray = json_decode($response, true);
        if (isset($responseArray)) {
            return $responseArray;
        } else {
            throw new Exception('Failed to retrieve user information. Response: ' . json_encode($responseArray));
        }
    }
    
    
}