<?php

$app->get('/settings', function() use ($app) {
    $settings = \um\models\Setting::all();
    $app->render('users/settings/index.twig', compact('settings'));
});

$app->post('/settings', function() use ($app) {
    $data = $app->request->post();
    foreach($data as $label => $value) {
        $setting = \um\models\Setting::where('code', '=', $label)->first();
        $setting->value = $value;
        $setting->save();
    }

    return $app->redirect('/Enrolment/settings');
});