<?php
namespace Php\SocialBrytead;
use Facebook\Facebook;
use Abraham\TwitterOAuth\TwitterOAuth;

require_once __DIR__ . '/../vendor/autoload.php';

class Bryteads
{
    public static function getFacebookClient(){

        $config = include __DIR__ . '/../src/config/services.php';


        $facebookConfig = $config['facebook'];
        $appId = $facebookConfig['app_id'];
        $appSecret = $facebookConfig['app_secret'];

        return new Facebook([
            'app_id' => $appId,
            'app_secret' => $appSecret,
            'default_graph_version' => 'v20.0',
        ]);
    }

    public static function getTwitterClient(){
        $config = include __DIR__ . '/../src/config/services.php';          
        
        $twitterConfig = $config['twitter'];
        
        return $twitterConfig;
    }       
}
