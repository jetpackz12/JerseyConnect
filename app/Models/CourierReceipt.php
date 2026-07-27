<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'order_id',
    'courier_id',
    'transaction_number',
    'shipping_fee',
    'date_shipped',
    'remarks',
])]
class CourierReceipt extends Model
{
    protected $casts = ['date_shipped' => 'datetime'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function courier()
    {
        return $this->belongsTo(Courier::class);
    }
}
