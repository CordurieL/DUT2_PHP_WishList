<?php
declare(strict_types=1);

namespace mywishlist\models;

class Participation extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'participation';
    protected $primaryKey = 'id_participation' ;
    public $timestamps = false ;

    public function item()
    {
        return $this->belongsTo('mywishlist\models\Item', 'item_id') ;
    }
}
