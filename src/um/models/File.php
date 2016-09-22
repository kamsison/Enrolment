<?php namespace um\models;

use Carbon\Carbon;

class File extends Base {

    protected $fillable = [
        'batch_id',
        'user_id',
        'path',
        'match_count',
        'created',
        'modified',
        'owner',
        'suspected_owner',
        'table_text',
        'samples',
        'status',
        'dpf_id',
        'image_path',
        'image_name',
        'is_beginning'
    ];

    /**
     * Gets status array.
     *
     * @return array - key is type and value is status
     * value is classification e.g. 1-Duplicate, 2-Suspected, 3-WIP, 4-Positive, 5-False Positive, 6-Securely Deleted, 7-Recycle Bin
     */
    public static function getStatus() {
        return [
            1 => 'Duplicate',
            2 => 'Suspected',
            3 => 'WIP',
            4 => 'Positive',
            5 => 'False Positive',
            6 => 'Securely Deleted',
            7 => 'Recycle Bin',
            8 => 'File Not Found',
            9 => 'No Access',
        ];
    }

    /**
     * Gets status array.
     *
     * @return array - key is type and value is status
     * value is classification e.g. 1-Duplicate, 2-Suspected, 3-WIP, 4-Positive, 5-False Positive, 6-Securely Deleted, 7-Recycle Bin
     */
    public static function getStatusByAlias() {
        // aliases are case-sensitive
        return [
            'Duplicate files'   => 1,
            'WIP'               => 3,
            'Positive files'    => 4,
            'FALSE positive'    => 5,
            'Recycle Bin'       => 7,
            'File not found'    => 8,
            'No Access'         => 9
        ];
    }

    /**
     * Gets current status
     *
     * @return string
     */
    public function getCurrentStatus() {
        $fileTag = FileTag::where('file_id', '=', $this->id)->orderBy('created_at', 'DESC')->first();

        $tag = '';
        if ($fileTag) {
            $tag = $fileTag->status;
        }

        return $tag;
    }

    /**
     * Gets current status created_at
     *
     * @return string
     */
    public function getCurrentStatusCreatedAtDate() {
        $fileTag = FileTag::where('file_id', '=', $this->id)->orderBy('created_at', 'DESC')->first();

        $tagCreatedAt = '';
        if ($fileTag) {
            $tagCreatedAt = $fileTag->created_at;
        }

        return $tagCreatedAt;
    }

    /**
     * Gets current notes
     *
     * @return string
     */
    public function getCurrentNotes() {
        $fileTag = FileTag::where('file_id', '=', $this->id)->orderBy('created_at', 'DESC')->first();

        $tag = '';
        if ($fileTag) {
            $tag = $fileTag->notes;
        }

        return $tag;
    }

    /**
     * Gets file tags
     *
     * @return FileTag object
     */
    public function getFileTags() {
        $fileTags = FileTag::where('file_id', '=', $this->id)->orderBy('created_at', 'desc')->get();

        if ($fileTags) {
            return $fileTags;
        } else {
            return '';
        }
    }

    /**
     * Gets cards
     *
     * @return Card object
     */
    public function getCards() {
        return Card::where('file_id', '=', $this->id)->get();
    }

    /**
     * Gets custom formatted date
     *
     * @return string
     */
    public function getShortCreatedDate() {
        return date('m/d/Y H:i', strtotime($this->created));
    }

    /**
     * Gets custom formatted date
     *
     * @return string
     */
    public function getShortModifiedDate() {
        return date('m/d/Y H:i', strtotime($this->modified));
    }

    /**
     * Gets custom formatted table
     *
     * @return string
     */
    public function getFormattedTable() {
        return $this->table_text ? '<a href="javascript:void(0);" class="link-details fa fa-info-circle" rel="tooltip" title="Table: ' . $this->table_text . '"></a>' : '';
    }

    /**
     * Gets custom formatted samples
     *
     * @return string
     */
    public function getFormattedSamples() {
        return $this->samples ? '<a href="javascript:void(0);" class="link-details fa fa-info-circle" rel="tooltip" title="Samples: ' . $this->samples . '"></a>' : '';
    }

    /**
     * Gets custom formatted cards
     *
     * @return string
     */
    public function getFormattedCards() {
        $concatCard = '';
        $cards = Card::where('file_id', '=', $this->id)->get();

        foreach ($cards as $card) {
            $concatCard .= '<span class="label label-primary" style="white-space: pre-line;">' . $card->value . '<span class="badge btn-info" style="background-color: #5bc0de !important">x' . $card->count . '</span></span>&nbsp;';
        }

        return $concatCard ? $concatCard : '';
    }

    /**
     * Gets formatted current status
     *
     * @return string
     */
    public function getFormattedCurrentStatus() {
        $fileTag = FileTag::where('file_id', '=', $this->id)->orderBy('created_at', 'DESC')->first();

        if ($fileTag) {
            if ($fileTag->status == 1) {
                return '<span class="label label-info" style="white-space: pre-line;">Duplicate</span>';
            } elseif ($fileTag->status == 2) {
                return '<span class="label label-warning" style="white-space: pre-line;">Suspected</span>';
            } elseif ($fileTag->status == 3) {
                return '<span class="label label-info" style="white-space: pre-line;">WIP</span>';
            } elseif ($fileTag->status == 4) {
                return '<span class="label label-danger" style="white-space: pre-line;">Positive</span>';
            } elseif ($fileTag->status == 5) {
                return '<span class="label label-info" style="white-space: pre-line;">False Positive</span>';
            } elseif ($fileTag->status == 6) {
                return '<span class="label label-success" style="white-space: pre-line;">Securely Deleted</span>';
            } elseif ($fileTag->status == 7) {
                return '<span class="label label-default" style="white-space: pre-line;">Recycle Bin</span>';
            } elseif ($fileTag->status == 8) {
                return '<span class="label label-default" style="white-space: pre-line;">File Not Found</span>';
            } elseif ($fileTag->status == 9) {
                return '<span class="label label-default" style="white-space: pre-line;">No Access</span>';
            } else {
                return '<span class="label label-default" style="white-space: pre-line;">Undefined</span>';
            }
        } else {
            return '';
        }
    }

    /**
     * Gets formatted status
     * @param $status
     * @return string
     */
    private static function getFormattedStatus($status) {

        if ($status == 1) {
            return '<span class="label label-info" style="white-space: pre-line;">Duplicate</span>';
        } elseif ($status == 2) {
            return '<span class="label label-warning" style="white-space: pre-line;">Suspected</span>';
        } elseif ($status == 3) {
            return '<span class="label label-info" style="white-space: pre-line;">WIP</span>';
        } elseif ($status == 4) {
            return '<span class="label label-danger" style="white-space: pre-line;">Positive</span>';
        } elseif ($status == 5) {
            return '<span class="label label-info" style="white-space: pre-line;">False Positive</span>';
        } elseif ($status == 6) {
            return '<span class="label label-success" style="white-space: pre-line;">Securely Deleted</span>';
        } elseif ($status == 7) {
            return '<span class="label label-default" style="white-space: pre-line;">Recycle Bin</span>';
        } elseif ($status == 8) {
            return '<span class="label label-default" style="white-space: pre-line;">File not found</span>';
        } elseif ($status == 9) {
            return '<span class="label label-default" style="white-space: pre-line;">No Access</span>';
        } else {
            return '<span class="label label-default" style="white-space: pre-line;">Undefined</span>';
        }
    }

    /**
     * Gets new files by comparing last date modified and path
     *
     * @param $worksheets
     * @return array
     */
    public static function getNewFiles($worksheets)
    {
        $newFiles = [];
        // $newSchedules['errors']['users'] contains errors of user data

        $headerOffset = Setting::getByCode('file_upload_excel_header_offset')->value;

        // heading info labels
        $labels = [];
        $labels['path']     = 0;
        $labels['match']    = 1;
        $labels['created']  = 2;
        $labels['modified'] = 3;
        $labels['owner']    = 4;
        $labels['table']    = 5;

        // heading card labels start at col 6
        $i = 6;
        while ($worksheets[$headerOffset][$i] != 'Samples') {
            $labels['cards'][$i]['value'] = preg_replace('/\s*/', '', strtolower($worksheets[$headerOffset][$i]));
            $i++;
        }

        $labels['samples']  = $i;

        $i = 0;
        $batch_id = substr(md5(uniqid(mt_rand(), true)), 0, 8);

        foreach($worksheets as $worksheet) {
            // continues to loop if rows are header offsets
            if ($i <= $headerOffset) {
                $i++;
                continue;
            }

            $status     = 2; // sets default status to Suspected
            $path       = $worksheet[$labels['path']];
            $match      = $worksheet[$labels['match']];
            $created    = Carbon::createFromFormat('M, d Y H:i', $worksheet[$labels['created']]);
            $modified   = Carbon::createFromFormat('M, d Y H:i', $worksheet[$labels['modified']]);
            $owner      = $worksheet[$labels['owner']];
            $table      = $worksheet[$labels['table']];
            $samples    = $worksheet[$labels['samples']];

            if (File::where('path', '=', $path)->where('modified', '=', $modified)->count())
                $status = 1; // if found, sets status to Duplicate

            $newFiles[$i] = [
                'path'      => $path,
                'match'     => $match,
                'created'   => $created,
                'modified'  => $modified,
                'owner'     => $owner,
                'table'     => $table,
                'samples'   => $samples,
                'status'    => $status,
                'batch_id'  => $batch_id
            ];

            foreach ($labels['cards'] as $key => $label) {
                $count = $worksheet[$key];
                if (isset($count) && $count > 0) {
                    $newFiles[$i]['cards'][] = [
                        'value' => $label['value'],
                        'count' => $count
                    ];
                }
            }

            $i++;
        }

        return $newFiles;
    }

    /**
     * Gets beginning files by comparing last date modified and path
     *
     * @param $worksheets
     * @return array
     */
    public static function getBeginningFiles($worksheets)
    {
        $newFiles = [];
        // $newSchedules['errors']['users'] contains errors of user data

        $headerOffset = Setting::getByCode('file_upload_excel_header_offset')->value;

        // heading info labels
        $labels = [];
        $labels['status']   = 0;
        $labels['modified'] = 4;
        $labels['path']     = 5;
        $labels['match']    = 6;
        $labels['owner']    = 7;

        $i = 0;
        $batch_id = substr(md5(uniqid(mt_rand(), true)), 0, 8);

        foreach($worksheets as $worksheet) {
            // continues to loop if rows are header offsets
            if ($i <= $headerOffset) {
                $i++;
                continue;
            }

            // heading card labels start at col 8 having 10 cards to save
            $samples = '';
            $cards = [];
            for ($j = 0, $k = 0; $j < 10; $j++, $k = $k + 3) {
                $cards[] = [
                    'value' => preg_replace('/\s*/', '', strtolower($worksheet[$k + 8])),
                    'count' => $worksheet[$k + 9]
                ];

                if ($worksheet[$k + 10] != null || $worksheet[$k + 10] != '')
                    $samples = $samples . ' ' . $worksheet[$k + 10];
            }

            $status     = File::getStatusByAlias()[$worksheet[$labels['status']]];
            $modified   = Carbon::parse($worksheet[$labels['modified']]);
            $path       = $worksheet[$labels['path']];
            $match      = $worksheet[$labels['match']];
            $owner      = $worksheet[$labels['owner']];
            $samples    = preg_replace('#\s+#',',',trim($samples));

            $newFiles[$i] = [
                'path'      => $path,
                'match'     => $match,
                'modified'  => $modified,
                'owner'     => $owner,
                'samples'   => $samples,
                'status'    => $status,
                'statusStr' => self::getFormattedStatus($status),
                'batch_id'  => $batch_id,
                'isBegin'   => 1
            ];

            foreach ($cards as $key => $label) {
                $value = $label['value'];
                $count = $label['count'];
                if (isset($count) && $count > 0) {
                    $newFiles[$i]['cards'][] = [
                        'value' => $value,
                        'count' => $count
                    ];
                }
            }

            $i++;
        }

        return $newFiles;
    }

    /**
     * Gets total of recent files by status
     *
     * @param $status
     * @return object
     */
    public static function getNewFileTotalByStatus($status) {
        $query = "SELECT id, status, files.created_at AS created_at
            FROM files
            INNER JOIN
     	      (SELECT f1.file_id, f1.status
               FROM file_tags AS f1
               WHERE f1.id = (SELECT f2.id FROM file_tags AS f2 WHERE f2.file_id = f1.file_id ORDER BY f2.created_at DESC LIMIT 1)
              ) AS curStat ON curStat.file_id = files.id
            WHERE curStat.status = $status
            AND files.created_at = (SELECT MAX(files.created_at) FROM files INNER JOIN file_tags ON file_tags.file_id = files.id WHERE file_tags.status = $status)";

        return File::hydrateRaw($query);
    }

    /**
     * Gets total of files by status
     *
     * @param $status
     * @param $date
     * @return object
     */
    public static function getFileTotalByStatus($status, $date = null) {

        $files = File::join('file_tags', function($join) {
            $join->on('files.id', '=', 'file_tags.file_id');
        })->whereRaw('file_tags.id IN
           (SELECT currentFileTag.id FROM files
            INNER JOIN
                (SELECT t1.id, t1.file_id, t1.status
                 FROM file_tags AS t1
                 WHERE t1.id =
                   (SELECT t2.id FROM file_tags AS t2
                    WHERE t2.file_id = t1.file_id
                    ORDER BY t2.updated_at DESC LIMIT 1
                   )
                ) AS currentFileTag ON currentFileTag.file_id = files.id
                  WHERE status = ' . $status .
           ')');

        if ($date != null) {
            $dateFrom = Carbon::parse($date)->startOfDay();
            $dateTo = Carbon::parse($date)->endOfDay();
            $files = $files->whereBetween('file_tags.updated_at', [$dateFrom, $dateTo]);
        }

        return $files->count();

    }

    /**
     * Gets count of files by date range
     *
     * @param $status
     * @param $dateTo
     * @param $dateFrom
     *
     * @return object
     */
    public static function getFileCountByStatusAndDateRange($status, $dateFrom, $dateTo) {
        $dateFrom = Carbon::parse($dateFrom)->startOfDay();
        $dateTo = Carbon::parse($dateTo)->endOfDay();

        $query = "SELECT file_tags.created_at AS file_date,
           SUM(CASE WHEN file_tags.status = $status THEN 1 ELSE 0 END) AS file_count
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

        return File::hydrateRaw($query);
    }
}