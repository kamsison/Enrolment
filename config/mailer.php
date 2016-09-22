<?php

#TODO provides username and password for Mandrill email API
$transport = Swift_SmtpTransport::newInstance('smtp.mandrillapp.com', 465, 'ssl')->setUsername('')->setPassword('');

use Mandrill\Mandrill;

// Only invoked if mode is "production"
$app->configureMode('production', function () use ($app, $transport) {
    $app->container->singleton('mailer', function () use ($transport) {
        // instantiate a client object
        return new Mandrill('');
    });
});

// Only invoked if mode is "production"
$app->configureMode('staging', function () use ($app, $transport) {
    $app->container->singleton('mailer', function () use ($transport) {
        return new Mandrill('');
    });
});

// In development environment all emails are redirected to a single email address.
$app->configureMode('development', function () use ($app, $transport) {
    $app->container->singleton('mailer', function () use ($transport) {
        return new Mandrill('');
    });
});