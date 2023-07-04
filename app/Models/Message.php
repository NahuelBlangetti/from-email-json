<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_id',
        'from',
        'to',
        'subject',
        'body',
        'attachments',
        'read',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}
