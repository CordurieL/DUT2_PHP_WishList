<?php
declare(strict_types=1);

namespace mywishlist\models;

class Compte extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'compte';
    protected $primaryKey = 'no_compte';
    public $timestamps = false;
}