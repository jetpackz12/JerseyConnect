<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'order_number',
    'design_request_id',
    'user_id',
    'template_name',
    'template_image',
    'team_name',
    'primary_color',
    'secondary_color',
    'accent_color',
    'font_style',
    'quantity',
    'unit_price',
    'status',
    'shipping_fee',
])]
class Order extends Model
{
    public const STATUS_FLOW = [
        'processing',
        'in_production',
        'ready_for_delivery',
        'shipped',
        'delivered',
        'completed',
    ];

    protected $appends = ['template_image_url'];

    public function getTemplateImageUrlAttribute(): ?string
    {
        return $this->template_image ? Storage::disk('public')->url($this->template_image) : null;
    }

    public function designRequest()
    {
        return $this->belongsTo(DesignRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->hasOne(Address::class);
    }

    public function courierReceipt()
    {
        return $this->hasOne(CourierReceipt::class);
    }

    public function nextAllowedStatuses(): array
    {
        $idx = array_search($this->status, self::STATUS_FLOW, true);
        return $idx === false ? [] : array_slice(self::STATUS_FLOW, $idx + 1);
    }

    public static function generateOrderNumber(): string
    {
        $year = now()->year;
        $count = self::where('order_number', 'like', "ORD-{$year}-%")->count();
        return sprintf('ORD-%d-%04d', $year, $count + 1);
    }
    
    public function messageThread()
    {
        return $this->designRequest?->messageThread;
    }
}
