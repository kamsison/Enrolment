<?php

/**
 * Created by PhpStorm.
 * User: ICT
 * Date: 9/22/2016
 * Time: 4:27 PM
 */

$app->get('/assessments',$isAuthenticated, function() use ($app) {
    $assessments = \um\models\Assessment::all();
    $app->render('assessments/index.twig', compact('assessments'));
})->name('usersId');

$app->get('/assessments/add',$isAuthenticated, function() use ($app) {
    $app->render('assessments/add.twig');
})->name('usersId');

// for posting data
$app->post('/assessments/add',$isAuthenticated, function() use ($app) {
    if ($id = $app->request->post('id')) {
        $assessment = \um\models\Assessment::find($id);
    } else {
        $assessment = \um\models\Assessment::create([]);
    }

    $assessment->course = $app->request->post('course');
    $assessment->year_level = $app->request->post('year_level');
    $assessment->tuition = $app->request->post('tuition');
    $assessment->fix_charges = $app->request->post('fix_charges');
    $assessment->payola = $app->request->post('payola');

    $assessment->save();
    $app->redirect("/Enrolment/assessment");
})->name('usersId');
// end post

//delete
$app->get('/assessments/:id/delete',$isAuthenticated, function($id) use ($app) {
    \um\models\Assessment::find($id)->delete();

    $app->redirect("/Enrolment/assessment");
})->name('getAssessmentIdDelete');
// end delete

// edit
$app->get('/assessments/:id/edit',$isAuthenticated, function($id) use ($app) {
    $assessment = \um\models\Assessment::find($id);

    $app->render('assessments/add.twig', compact('assessment'));
})->name('getAssessmentIdEdit');
// end edit



$app->get('/assessments/:id',$isAuthenticated, function($id) use ($app) {
    $user = \um\models\Assessment::find($id);
    $app->render('assessments/edit.twig', compact('user'));
})->name('usersId');


