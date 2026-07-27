<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'site', 'status'])]
class Courier extends Model
{
    public function courierReceipts()
    {
        return $this->hasMany(CourierReceipt::class);
    }
}
