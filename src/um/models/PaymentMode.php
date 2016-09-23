<?php
/**
 * Created by PhpStorm.
 * User: kharl
 * Date: 22/09/2016
 * Time: 4:07 PM
 */

namespace um\models;
use Carbon\Carbon;

class PaymentMode extends Base
{
    protected  $fillable = [
        'description'
    ];
    public function payment(){
        return $this->hasMany('um\models\Payment');
    }
}