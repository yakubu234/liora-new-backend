<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventType extends Model
{
    protected $table = 'event_type';

    protected $fillable = [
        'name',
        'status',
    ];
}
