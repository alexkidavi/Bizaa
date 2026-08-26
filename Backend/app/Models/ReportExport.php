<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportExport extends Model
{
    protected $fillable = [
        'business_id',
        'user_id',
        'report_type',
        'format',
        'params',
        'status',
        'file_path',
        'error_message',
        'completed_at',
    ];

    protected $casts = [
        'params'       => 'array',
        'completed_at' => 'datetime',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
