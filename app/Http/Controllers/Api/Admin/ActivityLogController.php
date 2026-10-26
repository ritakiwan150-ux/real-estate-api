<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function index()
    {
        $logs = ActivityLog::query()
            ->with('admin:id,name')
            ->latest('id')
            ->paginate(20);

        return response()->json($logs);
    }
}
