<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportsInfo extends Model
{
    protected $table = 'imports_info';
    
    protected $fillable = ['name', 'status'];

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
}
