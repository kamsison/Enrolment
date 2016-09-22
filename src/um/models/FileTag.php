<?php namespace um\models;

class FileTag extends Base {

    protected $table = 'file_tags';

    protected $fillable = [
        'user_id',
        'file_id',
        'status',
        'notes'
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
     * Gets custom formatted date
     *
     * @return string
     */
    public function getShortCreatedDate() {
        return date('m/d/Y H:i', strtotime($this->created_at));
    }

    /**
     * Gets formatted current status
     *
     * @return string
     */
    public function getFormattedCurrentStatus() {
        $tag = FileTag::find($this->id)->status;

        if ($tag == 1) {
            return '<span class="label label-warning" style="white-space: pre-line;">Duplicate</span>';
        } elseif ($tag == 2) {
            return '<span class="label label-danger" style="white-space: pre-line;">Suspected</span>';
        } elseif ($tag == 3) {
            return '<span class="label label-info" style="white-space: pre-line;">Work In Progress</span>';
        } elseif ($tag == 4) {
            return '<span class="label label-primary" style="white-space: pre-line;">Positive</span>';
        } elseif ($tag == 5) {
            return '<span class="label label-warning" style="white-space: pre-line;">False Positive</span>';
        } elseif ($tag == 6) {
            return '<span class="label label-danger" style="white-space: pre-line;">Securely Deleted</span>';
        } elseif ($tag == 7) {
            return '<span class="label label-default" style="white-space: pre-line;">Recycle Bin</span>';
        } elseif ($tag == 8) {
            return '<span class="label label-default" style="white-space: pre-line;">File Not Found</span>';
        } elseif ($tag == 9) {
            return '<span class="label label-default" style="white-space: pre-line;">No Access</span>';
        } else {
            return '<span class="label label-success" style="white-space: pre-line;">Undefined</span>';
        }
    }
}