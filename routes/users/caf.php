<?php

// lists caf
$app->get('/caf/status/:status', function($status) use ($app) {
    $app->render('users/caf/list.twig', compact('status'));
})->name('cafStatusStatus');

$app->get('/caf/data/:status', function($status) use ($app) {
    $draw = $app->request->get('draw') ?: 1;
    $perPage = $app->request->get('length') ?: 25;
    $skip = $app->request->get('start');

    $cafs = \um\models\Caf::select('users.*', 'cafs.id AS caf_id', 'cafs.action_status AS caf_action_status', 'cafs.status AS caf_status', 'cafs.dpf_id AS dpf_id')
        ->join('users', function($join) {
            $join->on('cafs.user_id', '=', 'users.id');
        });

    if ($status != 'all') {
        $cafs = $cafs->where('cafs.action_status', '=', $status);
    }

    if (isset($_SESSION['user']) && $_SESSION['user']['type'] == 1) {
        $cafs = $cafs->where('cafs.user_id', '=', $_SESSION['user']['id']);
    }

    $recordsTotal = $cafs->count();
    $cafs->take($perPage);

    if (!empty($app->request->get('search')['value'])) {
        $search = $app->request->get('search')['value'];
        $cafs->where(function ($query) use ($search) {
            $query->orWhere('acquire_id', 'like', "%$search%")
                ->orWhere('last_name', 'like', "%$search%")
                ->orWhere('first_name', 'like', "%$search%")
                ->orWhere('middle_name', 'like', "%$search%");
        });
    }

    $recordsFiltered = $cafs->count();
    if ($skip > 0) {
        $cafs->skip($skip);
    }

    $cafs = $cafs->orderBy('cafs.created_at', 'desc')->get();

    $skip++;
    $data = [];
    foreach ($cafs as $caf) {
        $action = '';
        if ($status != 'all') {
            $action = '<span><a href="/Enrolment/caf/' . $caf->caf_id . '/edit">Respond</a></span>&nbsp;</span>&nbsp;|&nbsp;';
        }
        $action .= '<a href="/Enrolment/caf/' . $caf->caf_id . '/print" target="_blank">Print PDF</a>';

        $data[] = [
            $skip++,
            '<span><a href="/Enrolment/caf/' . $caf->caf_id . '/edit">' . $caf->caf_id . '</a></span>',
            '<span><a href="/Enrolment/dpf/' . $caf->dpf_id . '/edit">' . $caf->dpf_id . '</a></span>',
            $caf->acquire_id,
            $caf->last_name,
            $caf->first_name,
            $caf->middle_name,
            $caf->designation,
            $caf->department,
            $caf->unit,
            $caf->getStatus()[$caf->caf_status],
            $caf->getFormattedStatus()[$caf->caf_action_status],
            $action
        ];
    }
    $app->response->header('Content-Type', 'application/json');
    $app->response->body(json_encode(compact('draw', 'recordsTotal', 'recordsFiltered', 'data')));
})->name('cafDataStatus');

$app->get('/caf/:id/edit', function($id) use ($app) {
    $caf = \um\models\Caf::find($id);
    $user = \um\models\User::find($caf->user_id);

    $app->render('users/caf/edit.twig', compact('caf', 'user'));
});

$app->post('/caf/edit', function() use ($app) {
    if ($id = $app->request->post('id')) {
        $caf = \um\models\Caf::find($id);

        $actionStatus = $app->request->post('action-status');
        var_dump('lee');
        var_dump($actionStatus);
        if (isset($actionStatus))
            $caf->action_status = $actionStatus;

        $copy = $app->request->post('copy');
        if (isset($copy))
            $caf->copy = 1;
        else
            $caf->copy = 0;

        $destruct = $app->request->post('destruct');
        if (isset($destruct))
            $caf->destruct = 1;
        else
            $caf->destruct = 0;

        $employeeNotes = $app->request->post('employee-notes');
        if (isset($employeeNotes)) {
            $caf->employee_notes = $employeeNotes;
            $caf->responded_at = \Carbon\Carbon::now('Asia/Manila');
        }

        $hrNotes = $app->request->post('hr-notes');
        if (isset($hrNotes))
            $caf->hr_notes = $hrNotes;

        $itNotes = $app->request->post('it-notes');
        if (isset($itNotes))
            $caf->it_notes = $itNotes;

        $adminNotes = $app->request->post('admin-notes');
        if (isset($adminNotes))
            $caf->admin_notes = $adminNotes;

        $cause = $app->request->post('cause');
        if (isset($cause))
            $caf->cause = $cause;

        if ($caf->save()) {
            if ($actionStatus == 6) {
                $files = \um\models\User::getActiveFiles($caf->user_id, $caf->dpf_id);

                foreach ($files as $file) {
                    $fileTag = \um\models\FileTag::create([]);
                    $fileTag->file_id   = $file->id;
                    $fileTag->user_id   = $_SESSION['user']['id'];;
                    $fileTag->status    = 6; // Securely Deleted
                    $fileTag->notes     = 'System generated from HR\'s respond to mark as Securely Deleted.';
                    $fileTag->save();
                }
            }
        }
    }

    $app->redirect("/Enrolment/caf/status/all");
});

$app->get('/caf/:id/delete', function($id) use ($app) {
    \um\models\Caf::find($id)->delete();
    $app->redirect("/Enrolment/caf/status/all");
})->name('cafIdDelete');

$app->get('/caf/:id/print', function($id) use ($app) {
    $caf = \um\models\Caf::find($id);

    $app->response->headers->set("Content-Type", "application/pdf");
    return \um\reports\Caf::caf($caf);
})->name('cafIdPrint');
