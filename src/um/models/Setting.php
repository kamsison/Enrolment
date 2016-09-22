<?php namespace um\models;

class Setting extends Base {

    public $timestamps = false;

    /**
     * Retrieve a system setting via code.
     *
     * @param $code
     * @return mixed
     */
    public static function getByCode($code) {
        return static::where('code', '=', $code)->first();
    }
}