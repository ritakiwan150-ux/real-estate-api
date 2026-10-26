<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function index(Request $request)
    {
        $inquiries = Inquiry::query()
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->input('status'));
            })
            ->with('property:id,title_ar,title_en,slug')
            ->latest('id')
            ->paginate(20);

        return response()->json($inquiries);
    }

    public function updateStatus(Request $request, Inquiry $inquiry)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:new,contacted,closed'],
        ]);

        $inquiry->status = $validated['status'];
        $inquiry->save();

        return response()->json([
            'message' => 'Inquiry status updated.',
            'inquiry' => $inquiry->fresh(),
        ]);
    }
}
