<?php namespace um\models;

use Carbon\Carbon;

class User extends Base {

    protected $fillable = [
        'email',
        'password',
        'type',
        'acquire_id',
        'department',
        'unit',
        'last_name',
        'first_name',
        'middle_name',
        'hire_date',
        'designation',
        'birth_date',
        'status'
    ];

    /**
     * Gets formatted user name
     *
     * @return string
     */
    public function getCompleteName() {
        return $this->last_name . ', ' . $this->first_name . ' ' . $this->middle_name;
    }

    /**
     * Gets custom formatted date
     *
     * @return string
     */
    public function getShortHireDate() {
        return $this->hire_date > 0 ? date('m/d/Y', strtotime($this->hire_date)) : '-';
    }

    /**
     * Gets custom formatted date
     *
     * @return string
     */
    public function getShortBirthDate() {
        return $this->birth_date > 0 ? date('m/d/Y', strtotime($this->birth_date)) : '-';
    }

    /**
     * Gets dpf count
     *
     * @return integer
     */
    public function getDpfCount() {
        return Dpf::where('user_id', '=', $this->id)->count();
    }

    /**
     * Gets files
     *
     * @return File object
     */
    public function getFiles() {
        $files = File::where('suspected_owner', '=', $this->id)
            ->where('dpf_id', '=', 0)
            ->orderBy('batch_id')
            ->orderBy('created_at', 'desc')->get();

        if ($files) {
            return $files;
        } else {
            return '';
        }
    }

    /**
     * Gets files
     *
     * @return File object
     */
    public static function getActiveFiles($user_id, $dpf_id) {
        $files = File::where('suspected_owner', '=', $user_id)
            ->where('dpf_id', '=', $dpf_id)
            ->orderBy('batch_id')
            ->orderBy('created_at', 'desc')->get();

        if ($files) {
            return $files;
        } else {
            return '';
        }
    }

    /**
     * Gets new users by comparing email, designation and supervisor_email
     *
     * @param $worksheets
     * @return array
     */
    public static function getNewUsersByRoster($worksheets)
    {
        $newUsers = [];

        $i = 0;
        foreach($worksheets as $worksheet) {
            if ($i++ == 0) continue;

            $department     = $worksheet[0];
            $unit           = $worksheet[1];
            $acquireId      = $worksheet[3];
            $lastName       = $worksheet[4];
            $firstName      = $worksheet[5];
            $middleName     = $worksheet[6];
            $emailAddress   = $worksheet[8];
            $hireDate       = preg_match("/^(January|February|March|April|May|June|July|August|September|October|November|December) (0[1-9]|[1-2][0-9]|3[0-1]), [0-9]{4}$/", $worksheet[9]) ? Carbon::createFromFormat('F j, Y', $worksheet[9]) : 'blank';
            $designation    = $worksheet[12];

            // gets all active employees
            $newUsers[] = [
                'department'    => $department,
                'unit'          => $unit,
                'acquire_id'    => $acquireId,
                'last_name'     => $lastName,
                'first_name'    => $firstName,
                'middle_name'   => $middleName,
                'email_address' => $emailAddress,
                'hire_date'     => $hireDate,
                'designation'   => $designation,
            ];
        }

        return $newUsers;
    }

    /**
     * Gets type array.
     *
     * @return array - key is type and value is type
     * value is classification e.g. admin, hr, checker
     */
    public static function getType()
    {
        return [
            0 => 'undefined',
            1 => 'normal',
            2 => 'admin',
            3 => 'hr',
            4 => 'superadmin'
        ];
    }

    /**
     * Gets status array.
     *
     * @return array - key is type and value is status
     */
    public static function getStatus()
    {
        return [
            0 => 'undefined',
            1 => 'active',
            2 => 'deleted'
        ];
    }

    /**
     * Gets count DPF
     *
     * @return int
     */
    public function getCountDpf() {
        return Dpf::where('user_id', '=', $this->id)->count();
    }

    /**
     * Gets count CAF
     *
     * @return int
     */
    public function getCountCaf() {
        return Caf::where('user_id', '=', $this->id)->count();
    }
}