<?php

// Autoload files using the Composer autoloader.
require_once __DIR__ . '/../vendor/autoload.php';


$entry = new ArcepApiBoxTester\Controller\Tester();
$entry->MainLoader();
