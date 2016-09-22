<?php

// Defines session/cookie
$app->add(new \Slim\Middleware\SessionCookie(array(
    'expires' => '1 month',
    'path' => '/',
    'domain' => null,
    'secure' => false,
    'httponly' => false,
    'name' => 'um',
    'secret' => 'wxY9MOSptFLfGPGyoDrJ7M6sgZH81pw9330GMNSu0JYA5w93bS1bnQhxvRHJzb',
    'cipher' => MCRYPT_RIJNDAEL_256,
    'cipher_mode' => MCRYPT_MODE_CBC
)));