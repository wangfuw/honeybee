<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notice extends Base
{
    use HasFactory;

    protected $table = 'notice';

    protected $fillable = [
        'id',
        'title',
        'text',
        'type',
        'created_at',
        'updated_at',
    ];


    public function getNotices()
    {

    }
}
