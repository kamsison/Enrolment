<?php namespace um\models;

class Caf extends Base {

    protected $fillable = [
        'dpf_id',
        'user_id',
        'status',
        'employee_notes',
        'responded_at',
        'hr_notes',
        'it_notes',
        'admin_notes',
        'action_status',
        'cause',
        'copy',
        'destruct'
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
     * Gets dpf
     *
     * @return Dpf object
     */
    public function getDpf() {
        $dpf = Dpf::find($this->dpf_id);

        return $dpf;
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
     * Gets custom formatted date
     *
     * @return string
     */
    public function getShortRespondedAt() {
        if ($this->responded_at > 0) {
            return date('F d, Y', strtotime($this->responded_at));
        } else {
            return 0;
        }
    }

    /**
     * Gets status array.
     *
     * @return array - key is type and value is status
     */
    public static function getStatus()
    {
        return [
            1 => 'Final Warning',
            2 => 'Last Final Warning',
            3 => 'For Termination'
        ];
    }

    /**
     * Gets status array.
     *
     * @return array - key is type and value is action status
     */
    public static function getActionStatus()
    {
        return [
            0 => 'Pending',
            1 => 'For Respond',
            2 => 'Responded',
            3 => 'For Secure Deletion',
            4 => 'For Approval',
            5 => 'Approved',
            6 => 'Securely Deleted'
        ];
    }

    /**
     * Gets formatted status
     *
     * @return string
     */
    public static function getFormattedStatus() {
        return [
            1 => '<span class="label label-info" style="white-space: pre-line;">For Respond</span>',
            2 => '<span class="label label-success" style="white-space: pre-line;">Responded</span>',
            3 => '<span class="label label-info" style="white-space: pre-line;">For Secure Deletion</span>',
            4 => '<span class="label label-info" style="white-space: pre-line;">For Approval</span>',
            5 => '<span class="label label-default" style="white-space: pre-line;">Approved</span>',
            6 => '<span class="label label-success" style="white-space: pre-line;">Securely Deleted</span>'
        ];
    }

    /**
     * Gets status array.
     *
     * @return array - key is type and value is cause
     */
    public static function getCause()
    {
        return [
            1 => 'negligence of duty',
            2 => 'misconduct',
            3 => 'fraud'
        ];
    }

    /**
     * Gets total of CAF by user type
     *
     * @return object
     */
    public static function getCafTodoTotal() {

        $cafs = Caf::select('cafs.created_at AS caf_created_at');

        if (isset($_SESSION['user']) && $_SESSION['user']['type'] == 1) {
            $cafs = $cafs->join('users', function($join) {
                    $join->on('cafs.user_id', '=', 'users.id');
                })->where('cafs.user_id', '=', $_SESSION['user']['id'])
                ->where('cafs.action_status', '=', 1);
        } elseif (isset($_SESSION['user']) && $_SESSION['user']['type'] == 4) {
            $cafs = $cafs->where('cafs.action_status', '=', 4);
        } else {
            $cafs = $cafs->where('cafs.action_status', '=', 2)
                ->orWhere('cafs.action_status', '=', 5);
        }

        return $cafs->orderBy('cafs.action_status')
            ->orderBy('cafs.created_at', 'DESC')->get();
    }

    /**
     * Gets total of CAF by user type
     *
     * @return object
     */
    public static function getCafMarkSecurelyDeletedTotal() {

        return Caf::where('cafs.action_status', '=', 5)
            ->orderBy('cafs.created_at', 'DESC')->get();
    }
}