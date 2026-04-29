<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';

    protected $fillable = [
        'recepient',
        'name',
        'email',
        'user_id',
        'message',
        'subject',
        'reply',
        'responder_id',
        'status',
        'responder_name',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }
}
