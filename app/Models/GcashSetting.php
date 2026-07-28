<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GcashSetting extends Model
{
    protected $fillable = [
        'account_name',
        'account_number',
        'instructions',
        'qr_image_path',
    ];

    protected $appends = ['qr_image_url'];

    public function getQrImageUrlAttribute(): ?string
    {
        return $this->qr_image_path
            ? Storage::disk('public')->url($this->qr_image_path)
            : null;
    }

    /**
     * There's only ever one row. Fetch it (creating a default one if missing)
     * so the rest of the app never has to null-check.
     */
    public static function current(): self
    {
        return static::firstOrCreate([], [
            'account_name' => 'Set your GCash name',
            'account_number' => '0900 000 0000',
            'instructions' => 'Send your payment to the GCash account above, then upload a screenshot of your receipt as proof of payment.',
        ]);
    }
}
