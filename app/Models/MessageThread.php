<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'design_request_id',
    'client_last_read_at',
    'admin_last_read_at',
])]
class MessageThread extends Model
{
    protected $casts = [
        'client_last_read_at' => 'datetime',
        'admin_last_read_at'  => 'datetime',
    ];

    public function designRequest(): BelongsTo
    {
        return $this->belongsTo(DesignRequest::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'design_request_id', 'design_request_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function latestMessage(): HasMany
    {
        return $this->hasMany(Message::class)->latest();
    }

    protected function serializeDate($date)
    {
        return $date->toIso8601String();
    }
}
