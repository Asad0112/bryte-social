<?php
error_reporting(E_ERROR);  // Only show errors
ini_set('display_errors', 1);  // Show errors

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables from .env file
require_once __DIR__ . '/loadEnv.php';
loadEnv(__DIR__ . '/.env');

use Php\Router\Router;
use Php\Controllers\FacebookController;
use Php\Controllers\LinkedInController;
use Php\Controllers\TwitterController;

$router = new Router();

# Route for the Facebook Login
$router->add('facebook/login', [new FacebookController(), 'handleRequest']);

# Route for the Twitter Login
$router->add('twitter/login', [new TwitterController(), 'handleRequest']);

# Route for the LinkedIn Login
$router->add('linkedin/login', [new LinkedInController(), 'handleRequest']);

// Dispatch the request to the appropriate handler
$router->dispatch();


