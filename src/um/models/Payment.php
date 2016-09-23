<?php
/**
 * Created by PhpStorm.
 * User: kharl
 * Date: 22/09/2016
 * Time: 3:58 PM
 */
namespace um\models;
use Carbon\Carbon;

class Payment extends Base {
    protected $fillable = [
        'personal_info_id',
        'amount',
        'payment_mode_id',
        'reference_number',
        'period_id',
        'fee_id'
    ];

    public function Student(){
        //return $this->hasOne('app\personal_info');
    }

    public function paymentMode(){
        return $this->hasOne('um\models\PaymentMode', 'payment_mode_id');
    }

    public static function getMode()
    {
        return [
            0 => 'undefined',
            1 => 'active',
            2 => 'deleted'
        ];
    }
}