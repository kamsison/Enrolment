<?php

$app->get('/files/import', function() use ($app) {
    $app->render('users/import/index.twig');
})->name('filesImportIndex');

$app->post('/files/import', function() use ($app) {
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

                $reader = new SpreadsheetReader($file);
                $sheets = $reader->Sheets();

                foreach ($sheets as $index => $name)
                {
                    $reader->ChangeSheet($index);

                    $worksheets = [];

                    foreach ($reader as $row)
                        $worksheets[] = $row;

                    $newFiles = \um\models\File::getNewFiles($worksheets);
                }
            } catch(Exception $e) {
                $error = 'Error occurred on retrieving worksheet: ' . $e->getMessage() . ' at line ' . $e->getLine();
                return $app->render('users/import/index.twig', compact('error'));
            }

            return $app->render('users/import/view.twig', compact('newFiles'));
        }
    } else {
        $error = 'No file selected.';
    }

    $app->render('users/import/index.twig', compact('error'));
});

$app->post('/files/import/beginning', function() use ($app) {
    if (isset($_FILES["file"])) {
        if ($_FILES["file"]["error"] > 0) {
            $errorBeginning = $_FILES["file"]["error"];
        } else {
            if (file_exists($_FILES["file"]["name"])) {
                unlink($_FILES["file"]["name"]);
            }
            $file = $_FILES["file"]["tmp_name"];

            try {
                require('vendor/spreadsheet-reader/SpreadsheetReader.php');

                $reader = new SpreadsheetReader($file, false, 'application/xlsx');
                $sheets = $reader->Sheets();

                foreach ($sheets as $index => $name) {
                    if ($name == 'Sheet7') {
                        $reader->ChangeSheet($index);

                        $worksheets = [];

                        foreach ($reader as $row)
                            $worksheets[] = $row;
                    } else {
                        continue;
                    }
                }

                $newFiles = \um\models\File::getBeginningFiles($worksheets);

            } catch(Exception $e) {
                $errorBeginning = 'Error occurred on retrieving worksheet: ' . $e->getMessage() . ' at line ' . $e->getLine();
                return $app->render('users/import/index.twig', compact('errorBeginning'));
            }

            return $app->render('users/import/view.twig', compact('newFiles'));
        }
    } else {
        $errorBeginning = 'No file selected.';
    }

    $app->render('users/import/index.twig', compact('errorBeginning'));
});

$app->post('/files/import/create', function() use ($app) {
    $countFiles = (int) $app->request->post('total-new-files');
    $newFiles = $app->request->post();

    for ($i = 1; $i <= $countFiles; $i++) {
        if (isset($newFiles['chk-' . (int) $i])) {
            $file = \um\models\File::create([]);
            $file->batch_id     = $newFiles['batch-id-' . (int) $i];
            $file->user_id      = $_SESSION['user']['id'];
            $file->path         = $newFiles['path-' . (int) $i];
            $file->match_count  = $newFiles['match-' . (int) $i];
            $file->created      = $newFiles['created-' . (int) $i];
            $file->modified     = $newFiles['modified-' . (int) $i];
            $file->owner        = $newFiles['owner-' . (int) $i];
            $file->table_text   = $newFiles['table-' . (int) $i];
            $file->samples      = $newFiles['samples-' . (int) $i];

            if (isset($newFiles['is-beginning-' . (int) $i])) {
                $file->is_beginning = $newFiles['is-beginning-' . (int) $i];
            }

            if ($file->save()) {
                // saves card
                $countCards = (int) $app->request->post('total-cards-' . (int) $i);
                for ($j = 1; $j <= $countCards; $j++) {
                    $card = \um\models\Card::create([]);
                    $card->file_id  = $file->id;
                    $card->value    = $newFiles['card-value-' . (int) $i . '-' . (int) $j];
                    $card->count    = $newFiles['card-count-' . (int) $i . '-' . (int) $j];
                    $card->save();
                }

                // saves tag
                $fileTag = \um\models\FileTag::create([]);
                $fileTag->file_id   = $file->id;
                $fileTag->user_id   = $_SESSION['user']['id'];
                $fileTag->status    = $newFiles['status-' . (int) $i];
                $fileTag->notes     = 'Default status from import';
                $fileTag->save();
            }
        }
    }

    $app->render('users/list.twig');
});

