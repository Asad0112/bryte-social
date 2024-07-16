<?php
namespace Php\Social;

require_once __DIR__ . '/../vendor/autoload.php';

use Exception;
use Php\Helpers\CurlHelper;
use Php\SocialBrytead\Bryteads;

class TwitterAuth {
    private function generateCodeVerifier($length = 128) {
        return bin2hex(random_bytes($length / 2));
    }

    private function generateCodeChallenge($codeVerifier) {
        return rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
    }

   public function getLoginUrl() {
        $twitterConfig = Bryteads::getTwitterClient();
        $codeVerifier = $this->generateCodeVerifier();
        $codeChallenge = $this->generateCodeChallenge($codeVerifier);

        $_SESSION['code_verifier'] = $codeVerifier;

        $params = [
            'response_type' => 'code',
            'client_id' => $twitterConfig['client_id'],
            'redirect_uri' => $twitterConfig['redirect_url'],
            'scope' => $twitterConfig['scope'],
            'state' => bin2hex(random_bytes(8)),
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256'
        ];

        $url = 'https://twitter.com/i/oauth2/authorize?' . http_build_query($params);
        return $url;
    }

    public function handleCallback($code) {
        $twitterConfig = Bryteads::getTwitterClient();
        
        session_start();
        $url = $twitterConfig['api_base_url'].'/oauth2/token';
        
        $codeVerifier = $_SESSION['code_verifier'];

        
        $data = [
            'code' => $code,
            'grant_type' => 'authorization_code',
            'client_id' => $twitterConfig['client_id'],
            'client_secret' => $twitterConfig['client_secret'],
            'redirect_uri' => $twitterConfig['redirect_url'],
            'code_verifier' => $codeVerifier
        ];
    
        $authHeader = base64_encode($twitterConfig['client_id'] . ':' . $twitterConfig['client_secret']);

        $response = CurlHelper::call($url, 'POST', http_build_query($data), null, $authHeader);
        
        $responseArray = json_decode($response, true);

        if (isset($responseArray['access_token'])) {
            $_SESSION['twitter_access_token'] = $responseArray['access_token'];
            $_SESSION['twitter_refresh_token'] = $responseArray['refresh_token'];
            $_SESSION['token_expires_at'] = time() + $responseArray['expires_in'];

            // Save the updated access token to a JSON file with a UUID
            Social::saveAccessTokenToFile('twitter', $_SESSION['twitter_access_token'], getenv('USER_UUID'));

            return $responseArray['access_token'];
        } else {
            throw new Exception('Access Token not received. Response: ' . json_encode($responseArray));
        }
    }

    public function makeApiRequest($url, $method = 'GET', $data = null) {
        session_start();
        $accessToken = Social::getAccessTokenFromFile('twitter', getenv('USER_UUID'));

        $response = CurlHelper::call($url, $method, json_encode($data), $accessToken);
        return json_decode($response, true);
    }
    
    public static function refreshAccessToken() {
        session_start();
        $twitterConfig = Bryteads::getTwitterClient();
        $url = $twitterConfig['api_base_url'].'/oauth2/token';
        $refreshToken = $_SESSION['twitter_refresh_token'];

        $data = [
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
            'client_id' => $twitterConfig['client_id'],
        ];

        $authHeader = base64_encode($twitterConfig['client_id'] . ':' . $twitterConfig['client_secret']);
        
        $response = CurlHelper::call($url, 'POST', http_build_query($data), null, $authHeader);

        $responseArray = json_decode($response, true);

        if (isset($responseArray['access_token'])) {
            $_SESSION['twitter_access_token'] = $responseArray['access_token'];
            $_SESSION['twitter_refresh_token'] = $responseArray['refresh_token'];
            
            $filename = __DIR__ . '/../storage/' . getenv('USER_UUID') . '.json';

            if (!file_exists($filename)) {
                throw new Exception('Access token file does not exist.');
            }

            // Save the updated access token to a JSON file with a UUID - Test Pending
            Social::saveAccessTokenToFile('twitter', $_SESSION['twitter_access_token'], getenv('USER_UUID'));
            
            return $responseArray['access_token'];
        } else {
            throw new Exception('Access Token not received. Response: ' . json_encode($responseArray));
        }
    }
}
?>
