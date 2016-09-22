<?php

use Illuminate\Database\Capsule\Manager as Capsule;

date_default_timezone_set('UTC');

$capsule = new Capsule;

// Only invoked if mode is "production"
$app->configureMode('production', function () use ($app, $capsule) {

});

// Only invoked if mode is "staging"
$app->configureMode('staging', function () use ($app, $capsule) {

});

// Only invoked if mode is "development"
$app->configureMode('development', function () use ($app, $capsule) {
    $capsule->addConnection(array(
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'database'  => 'enrolment',
        'username'  => 'root',
        'password'  => '',
        'charset'   => 'utf8',
        'collation' => 'utf8_general_ci',
        'prefix'    => ''
    ));
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
});

// Only invoked if mode is "test"
$app->configureMode('test', function () use ($app) {

});
