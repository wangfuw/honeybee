<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Base
{
    use HasFactory;

    protected $table = 'area';

    protected $fillable = [
        'id','code','pcode','name','level'
    ];

    protected $hidden = [];

    // 模型文件
    public function children() {
        return $this->hasMany(get_class($this), 'pcode' ,'code');
    }

    public function allChildren() {
        return $this->children()->with( 'allChildren' );
    }
}

