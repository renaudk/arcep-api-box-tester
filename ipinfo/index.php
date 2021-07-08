<?php

// Autoload files using the Composer autoloader.
require_once __DIR__ . '/../vendor/autoload.php';

use ArcepApiBoxTester\Model\IpTools;

if(file_exists('../ipinfo.json')) {
    $config = json_decode(file_get_contents('../ipinfo.json'));
}

$output = [];
$cors = false;
$corsAllowed = false;
if(isset($_SERVER['HTTP_ORIGIN'])) {
    $cors = true;
    if(isset($config->OriginRestrictionRegex)) {
        if(preg_match($config->OriginRestrictionRegex,$_SERVER['HTTP_ORIGIN'])) {
            $corsAllowed = true;
        }
    } else {
        $corsAllowed = true;
    }
}
if($cors) {
    if($corsAllowed) {
        // Set CORS headers
        header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
        header('Access-Control-Allow-Method: GET, OPTIONS');
    } else {
        $error = true;
    }
} else {
    if (!isset($config->AllowNonCORSRequests) || !$config->AllowNonCORSRequests) {
        $error = true;
    }
}

header('Content-Type: application/json');

if(!isset($error)) {
    $output['IPAddress'] = IpTools::getClientIP();
    if (IpTools::isValidIPv4($output['IPAddress'])) $output['IPVersion'] = 4;
    if (IpTools::isValidIPv6($output['IPAddress'])) $output['IPVersion'] = 6;
    echo json_encode($output);
} else {
    echo json_encode(["Status"=>"Forbidden"]);
}