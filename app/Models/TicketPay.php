<?php
namespace App\Models;

class TicketPay extends Base
{
    protected $table = "ticket_pay";

    protected $fillable = ["id","user_id","pay_phone","amount","created_at","updated_at"];

    protected $hidden = ["updated_at"];
}
