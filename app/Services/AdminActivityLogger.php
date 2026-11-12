<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminActivityLogger
{
    public function record(string $action, string $entityType, ?int $entityId, string $summary): void
    {
        DB::table('admin_activity_logs')->insert([
            'admin_id' => Auth::guard('admin')->id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'summary' => $summary,
            'created_at' => now(),
        ]);
    }
}
