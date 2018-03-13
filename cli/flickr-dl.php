<?php
// Autoload
require_once(__DIR__.'/../vendor/autoload.php');

use f2face\FlickrDL\FlickrDL;

// Photo save path
define('SAVE_PATH', __DIR__. '/downloads');

$flickr_dl = new FlickrDL();
$flickr_dl->refreshApiKey();
$result = $flickr_dl->getBest($argv[1]);

$f = fopen(rtrim(SAVE_PATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($result->source), 'w');
fwrite($f, file_get_contents($result->source));
fclose($f);
?>