<?php

// creates dpf
$app->get('/dpf/create', function() use ($app) {
    $users = \um\models\User::join('files', function($join) {
        $join->on('users.id', '=', 'files.suspected_owner');
        })->join('file_tags', function($join) {
            $join->on('files.id', '=', 'file_tags.file_id');
        })->where('file_tags.status', '=', 4)
        ->where('files.dpf_id', '=', 0)->distinct()->get(['users.*']);

    $app->render('users/dpf/view.twig', compact('users'));
})->name('dpfCreate');

$app->post('/dpf/create', function() use ($app) {
    $countUsers = (int) $app->request->post('total-new-users');
    $newDpfs = $app->request->post();

    for ($i = 1; $i <= $countUsers; $i++) {
        if (isset($newDpfs['chk-' . (int) $i])) {

            $dpf = \um\models\Dpf::create([]);
            $dpf->user_id   = $newDpfs['user-id-' . (int) $i];
            $dpf->count     = $newDpfs['count-' . (int) $i] + 1;
            $dpf->status    = 1;

            if ($dpf->save()) {
                // updates dpf flat in file table
                $countFiles = (int) $app->request->post('total-files-' . (int) $i);
                for ($j = 1; $j <= $countFiles; $j++) {
                    $file = \um\models\File::find($newDpfs['file-id-' . (int) $i . '-' . (int) $j]);
                    $file->dpf_id = $dpf->id;

                    $file->save();
                }
            }
        }
    }

    $app->redirect('/um/dpf/create');
});

// lists dpf
$app->get('/dpf/status/:status', function($status) use ($app) {
    $app->render('users/dpf/list.twig', compact('status'));
})->name('dpfStatusStatus');

$app->get('/dpf/data/:status', function($status) use ($app) {
    $draw = $app->request->get('draw') ?: 1;
    $perPage = $app->request->get('length') ?: 25;
    $skip = $app->request->get('start');

    $dpfs = \um\models\Dpf::select('users.*', 'dpfs.id AS dpf_id', 'dpfs.status AS dpf_status')
        ->join('users', function($join) {
            $join->on('dpfs.user_id', '=', 'users.id');
        });

    if ($status == '2') {
        $dpfs = $dpfs->where('dpfs.status', '=', 2)
            ->orWhere('dpfs.status', '=', 4);
    } elseif ($status != 'all') {
        $dpfs = $dpfs->where('dpfs.status', '=', $status);
    }

    if (isset($_SESSION['user']) && $_SESSION['user']['type'] == 1) {
        $dpfs = $dpfs->where('dpfs.user_id', '=', $_SESSION['user']['id']);
    }

    $recordsTotal = $dpfs->count();
    $dpfs->take($perPage);

    if (!empty($app->request->get('search')['value'])) {
        $search = $app->request->get('search')['value'];
        $dpfs->where(function ($query) use ($search) {
            $query->orWhere('acquire_id', 'like', "%$search%")
                ->orWhere('last_name', 'like', "%$search%")
                ->orWhere('first_name', 'like', "%$search%")
                ->orWhere('middle_name', 'like', "%$search%");
        });
    }

    $recordsFiltered = $dpfs->count();
    if ($skip > 0) {
        $dpfs->skip($skip);
    }

    $dpfs = $dpfs->orderBy('dpfs.created_at', 'desc')->get();

    $skip++;
    $data = [];
    foreach ($dpfs as $dpf) {
        $action = '';
        if ($status != 'all') {
            $action = '<span><a href="/um/dpf/' . $dpf->dpf_id . '/edit">Respond</a></span>&nbsp;</span>&nbsp;|&nbsp;';
        }
        $action .= '<a href="/um/dpf/' . $dpf->dpf_id . '/print" target="_blank">Print PDF</a>';

        $data[] = [
            $skip++,
            '<span><a href="/um/dpf/' . $dpf->dpf_id . '/edit">' . $dpf->dpf_id . '</a></span>',
            $dpf->acquire_id,
            $dpf->last_name,
            $dpf->first_name,
            $dpf->middle_name,
            $dpf->designation,
            $dpf->department,
            $dpf->unit,
            $dpf->getFormattedStatus()[$dpf->dpf_status],
            $action
        ];
    }
    $app->response->header('Content-Type', 'application/json');
    $app->response->body(json_encode(compact('draw', 'recordsTotal', 'recordsFiltered', 'data')));
})->name('dpfDataStatus');

$app->get('/dpf/:id/edit', function($id) use ($app) {
    $dpf = \um\models\Dpf::find($id);
    $user = \um\models\User::find($dpf->user_id);
    $caf = \um\models\Caf::where('dpf_id', '=', $id)->first();

    $app->render('users/dpf/edit.twig', compact('dpf', 'user', 'caf'));
});

$app->post('/dpf/edit', function() use ($app) {
    if ($id = $app->request->post('id')) {
        $dpf = \um\models\Dpf::find($id);

        if ($status = $app->request->post('status'))
            $dpf->status = $status;

        if ($employeeNotes = $app->request->post('employee-notes'))
            $dpf->employee_notes = $employeeNotes;

        if ($hrNotes = $app->request->post('hr-notes'))
            $dpf->hr_notes = $hrNotes;

        if ($dpf->save()) {
            $cafStatus = $app->request->post('caf-status');
            if (isset($cafStatus)) {
                if ($cafId = $app->request->post('caf-id')) {
                    $caf = \um\models\Caf::find($cafId);
                    $caf->status    = $cafStatus;
                } else {
                    $caf = \um\models\Caf::create([]);
                    $caf->dpf_id    = $dpf->id;
                    $caf->user_id   = $dpf->user_id;
                    $caf->status    = $cafStatus;
                    $caf->action_status = 2; // Assumed that employee has responded so HR will take action
                }

                $caf->save();
            } elseif ($_SESSION['user']['type'] == 3) {
                if ($cafId = $app->request->post('caf-id')) {
                    \um\models\Caf::find($cafId)->delete();
                }
            }
        }
    }

    $app->redirect("/um/dpf/status/all");
});

$app->get('/dpf/:id/delete', function($id) use ($app) {
    \um\models\Dpf::find($id)->delete();
    $app->redirect("/um/dpf/status/all");
})->name('dpfIdDelete');

$app->get('/dpf/:id/print', function($id) use ($app) {
    $dpf = \um\models\Dpf::find($id);

    $app->response->headers->set("Content-Type", "application/pdf");
    return \um\reports\Pdf::dpf($dpf);
})->name('dpfIdPrint');