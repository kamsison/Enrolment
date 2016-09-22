<?php namespace um\models;

class Dpf extends Base {

    protected $fillable = [
        'user_id',
        'count',
        'status',
        'employee_notes',
        'hr_notes'
    ];

    /**
     * Gets user
     *
     * @return User object
     */
    public function getUser() {
        $user = User::find($this->user_id);

        return $user;
    }

    /**
     * Gets count DPF
     *
     * @return int
     */
    public function getCountDpf() {
        return Dpf::where('user_id', '=', $this->user_id)->count();
    }

    /**
     * Gets count DPF
     *
     * @return int
     */
    public function getCountCaf() {
        return Caf::where('user_id', '=', $this->user_id)->count();
    }

    /**
     * Gets custom formatted date
     *
     * @return string
     */
    public function getShortCreatedAt() {
        return date('F d, Y', strtotime($this->created_at));
    }

    /**
     * Gets status array.
     *
     * @return array - key is type and value is status
     */
    public static function getStatus() {
        return [
            1 => 'For Response',
            2 => 'Responded',
            3 => 'Lifted',
            4 => 'For Admin Hearing',
            5 => 'For CAF'
        ];
    }

    /**
     * Gets formatted status
     *
     * @return string
     */
    public static function getFormattedStatus() {
        return [
            1 => '<span class="label label-info" style="white-space: pre-line;">For Response</span>',
            2 => '<span class="label label-success" style="white-space: pre-line;">Responded</span>',
            3 => '<span class="label label-default" style="white-space: pre-line;">Lifted</span>',
            4 => '<span class="label label-info" style="white-space: pre-line;">For Admin Hearing</span>',
            5 => '<span class="label label-danger" style="white-space: pre-line;">For CAF</span>'
        ];
    }

    /**
     * Gets total of DPF by user type
     *
     * @return object
     */
    public static function getDpfTodoTotal() {

        $dpfs = Dpf::select('users.*', 'dpfs.id AS dpf_id', 'dpfs.status AS dpf_status', 'dpfs.created_at AS dpf_created_at')
            ->join('users', function($join) {
                $join->on('dpfs.user_id', '=', 'users.id');
            });

        if (isset($_SESSION['user']) && $_SESSION['user']['type'] == 1) {
            $dpfs = $dpfs->where('dpfs.user_id', '=', $_SESSION['user']['id'])
                ->where('dpfs.status', '=', 1);
        } else {
            $dpfs = $dpfs->where('dpfs.status', '=', 2)
                ->orWhere('dpfs.status', '=', 4);
        }

        return $dpfs->orderBy('dpfs.created_at', 'DESC')->get();
    }
}