<?php

namespace App\Http\Controllers;

use App\Models\GcashSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class GcashSettingController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Gcash', [
            'gcash' => GcashSetting::current(),
        ]);
    }

    public function updateDetails(Request $request)
    {
        $validated = $request->validate([
            'account_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'regex:/^09\d{9}$/', 'size:11'],
            'instructions' => ['nullable', 'string', 'max:2000'],
        ]);

        GcashSetting::current()->update($validated);

        return back()->with('success', 'GCash details updated.');
    }

    public function updateQr(Request $request)
    {
        $request->validate([
            'qr_image' => ['required', 'image', 'max:4096'], // 4MB
        ]);

        $gcash = GcashSetting::current();

        if ($gcash->qr_image_path) {
            Storage::disk('public')->delete($gcash->qr_image_path);
        }

        $path = $request->file('qr_image')->store('gcash', 'public');

        $gcash->update(['qr_image_path' => $path]);

        return back()->with('success', 'GCash QR code updated.');
    }
}
