<?php

// Autoload
require_once __DIR__.'/../vendor/autoload.php';

use f2face\FlickrDL\FlickrDL;
use Commando\Command;

// Photo save path
define('SAVE_PATH', __DIR__.'/downloads');

$cmd = new Command();

$cmd->option()
    ->aka('url')
    ->aka('u')
    ->require()
    ->describedAs('Flickr photo URL')
    ->must(function($url){
        return preg_match('#^https?://(?:www\.)?flickr\.com/.+?$#', $url);
    });

$flickr_dl = new FlickrDL();
$result = $flickr_dl->getBest($cmd['url']);

$f = fopen(rtrim(SAVE_PATH, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.basename($result->source), 'w');
fwrite($f, file_get_contents($result->source));
fclose($f);
