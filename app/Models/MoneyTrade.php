<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoneyTrade extends Base
{
    use HasFactory;

    protected $table = 'money_trade';

    protected $fillable = [
        'id','from_id','to_id','num','type','created_at','updated_at'
    ];
    public function fromUser()
    {
        return $this->hasOne(User::class, 'id', 'from_id');
    }

    public function toUser()
    {
        return $this->hasOne(User::class, 'id', 'from_id');
    }

    public function tradeList($condition,$size){
        return$this->with(['fromUser' => function ($query) {
            return $query->select("id","phone");
        }, 'toUser' => function ($query) {
            return $query->select("id","phone");
        }])->where($condition)
            ->orderByDesc("id")
            ->paginate($size);
    }
}
