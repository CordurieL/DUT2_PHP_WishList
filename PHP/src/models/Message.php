<?php
declare(strict_types=1);

namespace mywishlist\models;

class Message extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'message';
    protected $primaryKey = 'no_mess' ;
    public $timestamps = false ;
}
