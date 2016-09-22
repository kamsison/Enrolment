<?php namespace um\models;

class Card extends Base {

    protected $fillable = [
        'file_id',
        'value',
        'count'
    ];

    public $timestamps = false;
}