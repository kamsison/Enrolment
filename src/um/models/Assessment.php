<?php namespace um\models;

use Carbon\Carbon;

class Assessment extends Base {

    protected $fillable = [
        'course',
        'year_lvl',
        'tuition',
        'fix_charges',
        'payola',
        'total_assessment'
    ];

}


