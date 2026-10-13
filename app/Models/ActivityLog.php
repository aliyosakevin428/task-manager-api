<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];
}
