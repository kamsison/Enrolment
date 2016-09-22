<?php

// dashboard
$app->get('/dashboard', $isAuthenticated, function() use ($app) {
    $totalSuspected = \um\models\File::getFileTotalByStatus(2);
    $totalPositive = \um\models\File::getFileTotalByStatus(4);
    $totalDpf = \um\models\File::join('dpfs', function($join) {
            $join->on('dpfs.id', '=', 'files.dpf_id');
        })->where('status', '!=', 5)->count();
    $totalCaf = \um\models\File::join('dpfs', function($join) {
            $join->on('dpfs.id', '=', 'files.dpf_id');
        })->join('cafs', function($join) {
            $join->on('cafs.dpf_id', '=', 'dpfs.id');
        })->where('action_status', '!=', 6)->count();

    // weekly timeline
    $timeLine = [];
    $today = \Carbon\Carbon::now('Asia/Manila');
    for ($i = 0; $i < 7; $i++) {
        $day = $today->copy()->subDay($i);
        $timeLine[$i]['day'] = $day;
        $timeLine[$i]['value'][0] = \um\models\File::getFileTotalByStatus(1, $day) . ' total Duplicate files';
        $timeLine[$i]['value'][1] = \um\models\File::getFileTotalByStatus(2, $day) . ' total Suspected files';
        $timeLine[$i]['value'][2] = \um\models\File::getFileTotalByStatus(3, $day) . ' total WIF files';
        $timeLine[$i]['value'][3] = \um\models\File::getFileTotalByStatus(4, $day) . ' total Positive files';
        $timeLine[$i]['value'][4] = \um\models\File::getFileTotalByStatus(5, $day) . ' total False Positive files';
        $timeLine[$i]['value'][5] = \um\models\File::getFileTotalByStatus(6, $day) . ' total Securely Deleted files';
        $timeLine[$i]['value'][6] = \um\models\File::getFileTotalByStatus(7, $day) . ' total Recycle Bin files';
        $timeLine[$i]['value'][7] = \um\models\File::getFileTotalByStatus(8, $day) . ' total No Access files';

        $dateFrom = $day->copy()->startOfDay();
        $dateTo = $day->copy()->endOfDay();

        $timeLine[$i]['value'][8] = \um\models\File::join('dpfs', function($join) {
                    $join->on('dpfs.id', '=', 'files.dpf_id');
                })->where('status', '!=', 5)
                ->whereBetween('dpfs.created_at', [$dateFrom, $dateTo])->count() . ' total files under DPF';

        $timeLine[$i]['value'][9] = \um\models\File::join('dpfs', function($join) {
                    $join->on('dpfs.id', '=', 'files.dpf_id');
                })->join('cafs', function($join) {
                    $join->on('cafs.dpf_id', '=', 'dpfs.id');
                })->where('action_status', '!=', 6)
                ->whereBetween('cafs.created_at', [$dateFrom, $dateTo])->count() . ' total files under CAF';
    }

    $dashboardChart = \um\models\Base::getDashboardChart();

    $app->render('users/dashboard.twig', compact('totalSuspected', 'totalPositive', 'totalDpf', 'totalCaf', 'timeLine', 'dashboardChart'));
});

// chart
$app->get('/dashboard/chart/data', function() use ($app) {
    $i          = 0;
    $newData    = [];
    $dateFrom   = $app->request->get('dateFrom');
    $dateFrom   = \Carbon\Carbon::parse($dateFrom)->startOfDay();
    $dateTo     = $app->request->get('dateTo');
    $dateTo     = \Carbon\Carbon::parse($dateTo)->endOfDay();

    $statuses = [
        1 => 'Duplicate',
        2 => 'Suspected',
        4 => 'Positive',
        6 => 'Deleted'
    ];

    foreach ($statuses as $key => $status) {
        $newData[$i]['name'] = $status;

        $data = [];
        $files = \um\models\File::getFileCountByStatusAndDateRange($key, $dateFrom, $dateTo);

        foreach ($files as $file) {
            $score = $file->file_count;
            $date = \Carbon\Carbon::parse($file->file_date)->startOfDay()->timestamp;
            $data[] = [
                'x'     => $date * 1000, // epoch 13-digits
                'y'     => floatval(number_format($score, 2)),
            ];
        }

        $newData[$i++]['data'] = \um\models\Base::getSortedSurvey($data);
    }

    echo json_encode($newData);

})->name('dashboardChartData');

// table
$app->get('/dashboard/data', function() use ($app) {
    $draw       = $app->request->get('draw') ?: 1;
    $perPage    = $app->request->get('length') ?: 25;
    $skip       = $app->request->get('start');
    $dateFrom   = $app->request->get('dateFrom');
    $dateFrom   = \Carbon\Carbon::parse($dateFrom)->startOfDay();
    $dateTo     = $app->request->get('dateTo');
    $dateTo     = \Carbon\Carbon::parse($dateTo)->endOfDay();

    $query      = "SELECT CONCAT(DAY(file_tags.created_at), '-', MONTHNAME(file_tags.created_at))  AS file_date,
            SUM(CASE WHEN file_tags.status = 1 THEN 1 ELSE 0 END) AS duplicate_count,
            SUM(CASE WHEN file_tags.status = 2 THEN 1 ELSE 0 END) AS suspected_count,
            SUM(CASE WHEN file_tags.status = 3 THEN 1 ELSE 0 END) AS wip_count,
            SUM(CASE WHEN file_tags.status = 4 THEN 1 ELSE 0 END) AS positive_count,
            SUM(CASE WHEN file_tags.status = 6 THEN 1 ELSE 0 END) AS deleted_count
            FROM `files` INNER JOIN `file_tags` on `files`.`id` = `file_tags`.`file_id`
            WHERE
              file_tags.id IN
             (SELECT currentFileTag.id
              FROM files
              INNER JOIN
                (SELECT t1.id, t1.file_id, t1.status FROM file_tags AS t1 WHERE t1.id = (SELECT t2.id FROM file_tags AS t2 WHERE t2.file_id = t1.file_id ORDER BY t2.created_at DESC LIMIT 1))
              AS currentFileTag ON currentFileTag.file_id = files.id
             )
            AND `file_tags`.`created_at` BETWEEN '$dateFrom' AND '$dateTo'
            GROUP BY YEAR(`file_tags`.`created_at`), MONTH(`file_tags`.`created_at`), DAY(`file_tags`.`created_at`)
            ORDER BY file_tags.created_at DESC";

    $tableData = \um\models\File::hydrateRaw($query);

    $recordsTotal = count($tableData);

    $tableData->take($perPage);

    $recordsFiltered = count($tableData);

    if ($skip > 0) {
        $tableData->skip($skip);
    }

    $data = [];
    foreach ($tableData as $value) {
        $data[] = [
            $value->file_date,
            $value->duplicate_count,
            $value->suspected_count,
            $value->wip_count,
            $value->positive_count,
            $value->deleted_count
        ];
    }
    $app->response->header('Content-Type', 'application/json');
    $app->response->body(json_encode(compact('draw', 'recordsTotal', 'recordsFiltered', 'data')));
})->name('dashboardData');

// searches
$app->post('/search', function() use ($app) {
    $search = $app->request->post('search');

    if (!empty($search))
        $app->render('users/list.twig', compact('search'));
    else
        $app->render('users/list.twig');
});

// list of files
$app->get('/files/list', function() use ($app) {
    $app->render('users/list.twig');
});

$app->get('/users/files/data/:search', function($search) use ($app) {
    $draw = $app->request->get('draw') ?: 1;
    $perPage = $app->request->get('length') ?: 25;
    $skip = $app->request->get('start');

    $recordsTotal = \um\models\File::count();
    $files = \um\models\File::take($perPage);

    if (!empty($app->request->get('search')['value'])) {
        $search = $app->request->get('search')['value'];
        $files->where(function ($query) use ($search) {
            $query->orWhere('path', 'like', "%$search%")
                ->orWhere('owner', 'like', "%$search%")
                ->orWhere('table_text', 'like', "%$search%")
                ->orWhere('samples', 'like', "%$search%")
                ->orWhere('batch_id', 'like', "%$search%");
        });
    } elseif (!preg_match('/^\s+$/', $search) && $search != 'null') {
        $files->where(function ($query) use ($search) {
            $query->orWhere('path', 'like', "%$search%")
                ->orWhere('owner', 'like', "%$search%")
                ->orWhere('table_text', 'like', "%$search%")
                ->orWhere('samples', 'like', "%$search%")
                ->orWhere('batch_id', 'like', "%$search%");
        });
    }

    $recordsFiltered = $files->count();
    if ($skip > 0) {
        $files->skip($skip);
    }
    $files = $files->orderBy('modified', 'desc')->get();

    $i = 1;
    $data = [];
    foreach ($files as $file) {
        $data[] = [
            $i,
            $file->batch_id,
            $file->path,
            $file->match_count,
            $file->getShortCreatedDate(),
            $file->getShortModifiedDate(),
            $file->owner,
            $file->getFormattedTable(),
            $file->getFormattedSamples(),
            $file->getFormattedCards(),
            $file->getFormattedCurrentStatus()
        ];

        $i++;
    }
    $app->response->header('Content-Type', 'application/json');
    $app->response->body(json_encode(compact('draw', 'recordsTotal', 'recordsFiltered', 'data', 'search')));
})->name('usersFilesData');

// statuses
$app->get('/files/status/:status', function($status) use ($app) {
    $title = \um\models\File::getStatus()[$status];
    $app->render('users/files.twig', compact('status', 'title'));
});

$app->get('/files/data/:status', function($status) use ($app) {
    $draw = $app->request->get('draw') ?: 1;
    $perPage = $app->request->get('length') ?: 25;
    $skip = $app->request->get('start');

    $files = \um\models\File::join('file_tags', function($join) {
            $join->on('files.id', '=', 'file_tags.file_id');
        })->whereRaw('files.id IN
           (SELECT file_id FROM files
            INNER JOIN
                (SELECT t1.file_id, t1.status
                 FROM file_tags AS t1
                 WHERE t1.id = (SELECT t2.id FROM file_tags AS t2 WHERE t2.file_id = t1.file_id ORDER BY t2.created_at DESC LIMIT 1)
                ) AS currentFileTag ON currentFileTag.file_id = files.id
            WHERE status = ' . $status . ')'
        );

    $recordsTotal = $files->count();
    $files->take($perPage);

    if (!empty($app->request->get('search')['value'])) {
        $search = $app->request->get('search')['value'];
        $files->where(function ($query) use ($search) {
            $query->orWhere('path', 'like', "%$search%")
                ->orWhere('owner', 'like', "%$search%")
                ->orWhere('table_text', 'like', "%$search%")
                ->orWhere('samples', 'like', "%$search%")
                ->orWhere('batch_id', 'like', "%$search%");
        });
    }

    $recordsFiltered = $files->count();
    if ($skip > 0) {
        $files->skip($skip);
    }

    $files = $files->orderBy('modified', 'desc')->distinct()->get(['files.*']);

    $skip++;
    $data = [];
    foreach ($files as $file) {
        if ($file->dpf_id == 0) {
            $action = '<span ><a href="/um/users/investigate/' . $file->id . '/edit">Investigate</a></span>&nbsp;|&nbsp;<span><a onclick="return confirm(\'Are you sure you want to securely delete this item?\');" href="/um/users/investigate/' . $file->id . '/delete">Delete</a></span>';
        } else {
            $action = '<span style="color: darkgray"><small>Investigate</small></span>&nbsp;|&nbsp;<span style="color: darkgray"><small>Delete</small></span>';
            $action .= '<a href="javascript:void(0);" class="link-details fa fa-info-circle" rel="tooltip" title="This file is being used in DPF/CAF"></a>';
        }
        $data[] = [
            $skip++,
            $file->batch_id,
            '<span><a href="/um/users/investigate/' . $file->id . '/view" target="_blank">' . $file->path . '</a></span>',
            $file->match_count,
            $file->getShortCreatedDate(),
            $file->getShortModifiedDate(),
            $file->owner,
            $file->getFormattedTable(),
            $file->getFormattedSamples(),
            $file->getFormattedCards(),
            $file->getFormattedCurrentStatus(),
            $action
        ];
    }
    $app->response->header('Content-Type', 'application/json');
    $app->response->body(json_encode(compact('draw', 'recordsTotal', 'recordsFiltered', 'data')));
})->name('filesData');

$app->get('/users/investigate/:id/edit', function($id) use ($app) {
    $file = \um\models\File::find($id);
    $users = \um\models\User::orderBy('last_name')->orderBy('first_name')->get();

    if ($file->suspected_owner != 0) {
        $userFound = \um\models\User::find($file->suspected_owner);
    } else {
        foreach($users as $user) {
            if (preg_match('/^[0-9]{8}$/', $user->acquire_id) && preg_match('/' . $user->acquire_id . '/', $file->path)) {
                $found = $user->id;
                $userFound = \um\models\User::find($found);
                break;
            }
        }
    }

    $app->render('users/edit.twig', compact('file', 'users', 'found', 'userFound'));
});

$app->post('/users/investigate/update_status', function() use ($app) {
    $id = $app->request->post('suspected_owner');
    $userFound = \um\models\User::find($id);

    header("Content-Type: application/json");
    echo json_encode($userFound->getCountCaf());
    exit;
});

$app->post('/users/investigate/edit', function() use ($app) {
    $id     = $app->request->post('id');
    $status = $app->request->post('status');
    $notes  = $app->request->post('notes');
    $suspectedOwner  = $app->request->post('suspected-owner');

    $file = \um\models\File::find($id);
    $file->suspected_owner = $suspectedOwner;

    if (file_exists($_FILES['image']['tmp_name'])) {
        $image = $_FILES['image']['tmp_name'];
        $info = pathinfo($_FILES['image']['name']);
        $ext = $info['extension'];
        $target = IMPORT_DIR . "/$id.$ext";
        move_uploaded_file($image, $target);

        $file->image_path = IMAGE_DIR;
        $file->image_name = "$id.$ext";
    }

    if ($file->save()) {
        $fileTag = \um\models\FileTag::create([]);
        $fileTag->file_id   = $id;
        $fileTag->user_id   = $_SESSION['user']['id'];;
        $fileTag->status    = $status;
        $fileTag->notes     = $notes;
        $fileTag->save();
    }

    $title = \um\models\File::getStatus()[$status];
    $app->render('users/files.twig', compact('status', 'title'));
});

$app->get('/users/investigate/:id/view', function($id) use ($app) {
    $file = \um\models\File::find($id);
    $app->render('users/view.twig', compact('file'));
});

$app->get('/users/investigate/:id/delete', function($id) use ($app) {
    \um\models\File::find($id)->delete();
    \um\models\FileTag::where('file_id', '=', $id)->delete();
    \um\models\Card::where('file_id', '=', $id)->delete();

    $app->render('users/list.twig');
});
