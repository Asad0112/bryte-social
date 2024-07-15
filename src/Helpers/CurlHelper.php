<?php
namespace Php\Helpers;
use Exception;
use Php\Social\TwitterAuth;

require_once __DIR__ . '/../../vendor/autoload.php';

class CurlHelper
{
    public static function call($url, $method = 'GET', $data, $accessToken = null, $authHeader = null) {
        $curl = curl_init($url);
        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if (!is_null($accessToken)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ]);
        } else {
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: Basic ' . $authHeader
            ]);
        }

        $response = curl_exec($curl);
        $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new Exception('cURL error: ' . $error);
        }

        if ($httpStatus === 401) { // Access token expired
            $accessToken = TwitterAuth::refreshAccessToken();
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ]);
            $response = curl_exec($curl);
            $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        }
        else if ($httpStatus !== 200 && $httpStatus !== 201) { // Consider 201 as a successful status for POST requests
            throw new Exception('HTTP request failed with status ' . $httpStatus . '. Response: ' . $response);
        }
        curl_close($curl);
        return $response;
    }
}