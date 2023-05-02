<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoneyTrade extends Base
{
    use HasFactory;

    protected $table = 'money_trade';

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
            return $query->select("id","phone as from_phone");
        }, 'toUser' => function ($query) {
            return $query->select("id","phone as to_phone");
        }])->where($condition)
            ->orderByDesc("id")
            ->paginate($size);
    }
}
