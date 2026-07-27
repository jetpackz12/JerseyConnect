<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'order_id',
    'recipient_name',
    'contact_number',
    'line1',
    'barangay',
    'city',
    'province',
    'postal_code',
    'latitude',
    'longitude',
])]
class Address extends Model
{
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function isComplete(): bool
    {
        return filled($this->recipient_name)
            && filled($this->contact_number)
            && filled($this->line1)
            && filled($this->city)
            && filled($this->province)
            && filled($this->postal_code);
    }
}
