<?php

$app->configureMode('production', function () use ($app) {
    $app->container->singleton('log', function () {
        $log = new \Monolog\Logger('app');
        $log->pushHandler(new \Monolog\Handler\StreamHandler('../um/logs/app-production.log', \Monolog\Logger::INFO));
        return $log;
    });
});

$app->configureMode('staging', function () use ($app) {
    $app->container->singleton('log', function () {
        $log = new \Monolog\Logger('app');
        $log->pushHandler(new \Monolog\Handler\StreamHandler('../um/logs/app-staging.log', \Monolog\Logger::WARNING));
        return $log;
    });
});

$app->configureMode('development', function () use ($app) {
    $app->container->singleton('log', function () {
        $log = new \Monolog\Logger('app');
        $log->pushHandler(new \Monolog\Handler\StreamHandler('../um/logs/app-development.log', \Monolog\Logger::DEBUG));
        return $log;
    });
});

$app->configureMode('test', function () use ($app) {
    $app->container->singleton('log', function () {
        $log = new \Monolog\Logger('app');
        $log->pushHandler(new \Monolog\Handler\StreamHandler('../um/logs/app-test.log', \Monolog\Logger::DEBUG));
        return $log;
    });
});