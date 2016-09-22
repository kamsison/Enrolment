<?php

// Prepares view templates
$app->config('templates.path', VIEWS_DIR);

$app->view(new \Slim\Views\Twig());

$app->view->parserOptions = [
    'charset' => 'utf-8',
    'cache' => CACHE_DIR . '/templates',
    'debug' => true,
    'auto_reload' => true,
    'strict_variables' => false,
    'autoescape' => true
];

$app->view->parserExtensions = [
    new \Slim\Views\TwigExtension(),
    new Twig_Extension_Debug()
];
