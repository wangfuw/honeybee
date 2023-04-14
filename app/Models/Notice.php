<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notice extends Base
{
    use HasFactory;
    use Illuminate\Database\Eloquent\SoftDeletes;

    protected $table = 'notice';

    protected $fillable = [
        'id',
        'title',
        'text',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $hidden = [
        'deleted_at'
    ];

    public function getNotices()
    {

    }
}
