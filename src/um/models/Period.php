<?php
/**
 * Created by PhpStorm.
 * User: kharl
 * Date: 22/09/2016
 * Time: 4:09 PM
 */

namespace um\models;
use Carbon\Carbon;

class Period extends Base
{
    protected $fillable = [
        'school_year',
        'semester'
    ];
}