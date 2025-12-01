<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoardStage extends Model
{
    protected $fillable = [
        'boardable_type',
        'boardable_id',
        'name',
        'color',
        'position',
        'type',
    ];


    public function boardable()
    {
        return $this->morphTo();
    }
}
