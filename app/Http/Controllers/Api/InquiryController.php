<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\Property;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function store(Request $request, Property $property)
    {
        // Hidden (pending / rejected / archived) listings must not receive inquiries.
        abort_unless($property->status === 'published', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        // The route is public, so resolve the sanctum user manually:
        // if a valid Bearer token was sent, link the inquiry to that client.
        $client = $request->user('sanctum');

        $inquiry = $property->inquiries()->create([
            ...$validated,
            'client_id' => $client?->id,
        ]);

        return response()->json([
            'inquiry' => $inquiry,
        ], 201);
    }

    public function index(Request $request)
    {
        $inquiries = Inquiry::query()
            ->whereHas('property', function ($q) use ($request) {
                $q->where('owner_id', $request->user()->id);
            })
            ->with('property:id,title_ar,title_en,slug')
            ->latest('id')
            ->get();

        return response()->json([
            'inquiries' => $inquiries,
        ]);
    }

    /**
     * Inquiries sent by the authenticated client.
     */
    public function myInquiries(Request $request)
    {
        $inquiries = Inquiry::query()
            ->where('client_id', $request->user()->id)
            ->with('property:id,title_ar,title_en,slug')
            ->latest('id')
            ->get();

        return response()->json([
            'inquiries' => $inquiries,
        ]);
    }
}
