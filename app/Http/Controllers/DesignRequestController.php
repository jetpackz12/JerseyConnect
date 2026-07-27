<?php

namespace App\Http\Controllers;

use App\Models\DesignRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Inertia\Inertia;
use Inertia\Response;

class DesignRequestController extends Controller
{
    /**
     * The customer's own requests only — never another user's.
     */
    public function index(): Response
    {
        $data = DesignRequest::query()
            ->where('user_id', Auth::id())
            ->with('template:id,image')
            ->latest()
            ->get()
            ->map(function (DesignRequest $designRequest) {
                $array = $designRequest->toArray();
                $array['original_template_image'] = $designRequest->template?->image_url;
                unset($array['template']);
 
                return $array;
            });

        return Inertia::render('Client/Design', [
            'data' => $data,
        ]);
    }

    public function pay(Request $request, DesignRequest $designRequest)
    {
        $this->authorizeOwner($designRequest);

        if ($designRequest->status !== 'waiting_for_down_payment') {
            return redirect()->back()->with('error', 'This request is not awaiting a down payment.');
        }

        $validated = $request->validate([
            'gcash_number'     => ['required', 'regex:/^09\d{9}$/', 'size:11'],
            'reference_number' => ['required', 'string', 'max:50'],
            'proof_image'      => ['required', 'image', 'max:4096'],
        ]);

        $proofPath = $request->file('proof_image')->store('design-requests/proofs', 'public');

        $designRequest->update([
            'gcash_number'     => $validated['gcash_number'],
            'reference_number' => $validated['reference_number'],
            'proof_image'      => $proofPath,
            'status'           => 'pending_down_payment_review',
        ]);

        return redirect()
            ->back()
            ->with('success', 'Payment proof submitted — we\'ll review it shortly.');
    }

    public function cancel(DesignRequest $designRequest)
    {
        $this->authorizeOwner($designRequest);

        $nonCancellable = ['revision_requested', 'waiting_for_down_payment', 'pending_down_payment_review', 'approved'];

        if (in_array($designRequest->status, $nonCancellable, true)) {
            return redirect()->back()->with('error', 'This request can no longer be cancelled.');
        }

        $designRequest->update(['status' => 'cancelled']);

        return redirect()->back()->with('success', 'Design request cancelled.');
    }

    private function authorizeOwner(DesignRequest $designRequest): void
    {
        if ($designRequest->user_id !== Auth::id()) {
            throw new HttpException(403, 'This is not your design request.');
        }
    }
}
