<?php

require __DIR__ . '/../vendor/autoload.php';

use Pusher\Pusher;

$app_id = "2175455";
$key = "c1756ac2bb163dfeacbf";
$secret = "b221bbcf82a9f4042f5f";

$options = [
    'cluster' => 'ap2',
    'useTLS' => true
];

$pusher = new Pusher(
    $key,
    $secret,
    $app_id,
    $options
);