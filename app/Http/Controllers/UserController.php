<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('userInfo')
        ->where('role', 'client')
        ->latest()
        ->get();

        return Inertia::render('Admin/Users', [
            'data' => $users,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $user = User::findOrFail($id);
        $user->update($validated);

        return back()->with('success', 'User status updated successfully.');
    }
}
