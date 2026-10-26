<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\ActivityLogger;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->with('roles')
            ->when($request->filled('role'), function ($q) use ($request) {
                $q->whereHas('roles', fn($r) => $r->where('name', $request->input('role')));
            })
            ->latest()
            ->paginate(15);

        return response()->json($users);
    }

    public function toggleStatus(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'You cannot deactivate your own account.',
            ], 422);
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        ActivityLogger::log(
            $request->user(),
            $user->is_active ? 'activated_user' : 'deactivated_user',
            $user,
            ($user->is_active ? 'Activated' : 'Deactivated') . " user #{$user->id}"
        );

        return response()->json([
            'message' => $user->is_active ? 'User activated.' : 'User deactivated.',
            'user' => $user->fresh(),
        ]);
    }

    public function assignRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => ['required', 'in:client,owner,admin'],
        ]);

        if ($user->id === $request->user()->id && $validated['role'] !== 'admin') {
            return response()->json([
                'message' => 'You cannot remove your own admin role.',
            ], 422);
        }

        $user->syncRoles([$validated['role']]);

        return response()->json([
            'message' => 'Role updated successfully.',
            'user' => $user->fresh()->load('roles'),
        ]);
    }

    public function verifyBroker(Request $request, User $user)
    {
        if (empty($user->broker_license_number)) {
            return response()->json([
                'message' => 'This user has no broker license number to verify.',
            ], 422);
        }

        $user->broker_verification_status = 'verified';
        $user->save();

        ActivityLogger::log($request->user(), 'verified_broker', $user, "Verified broker license for user #{$user->id}");

        return response()->json([
            'message' => 'Broker license verified successfully.',
            'user' => $user->fresh(),
        ]);
    }

    public function rejectBroker(Request $request, User $user)
    {
        if (empty($user->broker_license_number)) {
            return response()->json([
                'message' => 'This user has no broker license number to reject.',
            ], 422);
        }

        $user->broker_verification_status = 'rejected';
        $user->save();

        ActivityLogger::log($request->user(), 'rejected_broker', $user, "Rejected broker license for user #{$user->id}");

        return response()->json([
            'message' => 'Broker license rejected.',
            'user' => $user->fresh(),
        ]);
    }
}
