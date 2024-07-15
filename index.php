<?php
error_reporting(E_ERROR);  // Only show errors
ini_set('display_errors', 1);  // Show errors

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables from .env file
require_once __DIR__ . '/loadEnv.php';
loadEnv(__DIR__ . '/.env');

use Php\Router\Router;
use Php\Controllers\FacebookController;
use Php\Controllers\TwitterController;

$router = new Router();

// Define routes
$router->add('facebook/login', [new FacebookController(), 'handleRequest']);

$router->add('twitter/login', [new TwitterController(), 'handleRequest']);
$router->add('twitter/callback', [new TwitterController(), 'handleCallback']);

// Dispatch the request to the appropriate handler
$router->dispatch();


