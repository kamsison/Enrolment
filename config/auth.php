<?php

$isAuthenticated = function () use ($app) {
    if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
        return true;
    }
    return $app->redirect('/Enrolment/users/login');
};

$isLoggedUser = function($route) use ($app) {
    $params = $route->getParams();
    if (isset($_SESSION['user'])) {
        $position = \um\models\User::getPosition()[$_SESSION['user']['designation']];
        if ($position == 'agent' && $params['id'] != $_SESSION['user']['id']) {
            $app->halt(403, 'You do not have access to this account.');
        } elseif ($position == 'tl' && \um\models\UserSupervisor::where('user_id', '=', $params['id'])->where('status', '=', 1)->first()->email != $_SESSION['user']['email']) {
            $app->halt(403, 'You do not have access to this record.');
        } elseif ($position == 'manager' && \um\models\UserManager::where('user_id', '=', $params['id'])->where('status', '=', 1)->first()->email != $_SESSION['user']['email']) {
            $app->halt(403, 'You do not have access to this record.');
        }
    } else {
        $app->halt(403, 'You do not have access to this account.');
    }
    return true;
};

