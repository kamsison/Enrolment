<?php

$app->get('/assets', function() use ($app) {
    echo \Munee\Dispatcher::run(new \Munee\Request());
});
