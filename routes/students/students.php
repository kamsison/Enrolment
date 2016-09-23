<?php
/**
 * Created by PhpStorm.
 * User: UM
 * Date: 9/22/2016
 * Time: 4:43 PM
 */

$app->get('/students', function() use ($app) {
    $students = \um\models\PersonalInfo::all();
    $app->render('students/index.twig', compact('students'));
})->name('getStudents');

$app->get('/students/:id/edit', function($id) use ($app) {
    $student = \um\models\PersonalInfo::find($id);
    $app->render('students/edit.twig', compact('student'));
})->name('getStudentsIdEdit');

$app->get('/students/add', function() use ($app) {
      $app->render('students/add.twig');
})->name('getStudentsAdd');

$app->get('/students/:id/delete', function($id) use ($app) {
    \um\models\PersonalInfo::find($id)->delete();
    $app->redirect('/Enrolment/students');
})->name('getStudentsIdDelete');


// Add Record
$app->post('/students/update', function() use ($app) {
    $id = $app->request->post('id');
    if ($id) {
        $student = \um\models\PersonalInfo::find($id);
    } else {
        $student = \um\models\PersonalInfo::create([]);
    }
    $student->first_name = $app->request->post('first_name');
    $student->middle_name = $app->request->post('middle_name');
    $student->last_name = $app->request->post('last_name');
    $student->birth_date = $app->request->post('birth_date');
    $student->gender = $app->request->post('gender');
    $student->address = $app->request->post('address');
    $student->civil_status_id = $app->request->post('civil_status_id');
    $student->course_id = $app->request->post('course_id');
    $student->year_level = $app->request->post('year_level');
    $student->save();
    $app->redirect("/Enrolment/students");
})->name('postStudentsStudents');
// end post

/*$pass = $app->request->post('password');
if (!empty($pass)) {
    $user->password = password_hash($app->request->post('password'), PASSWORD_BCRYPT);
}*/


