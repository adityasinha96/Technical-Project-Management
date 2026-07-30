<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PermissionChangeLog;
use Illuminate\Contracts\View\View;

class PermissionHistoryController extends Controller
{
    public function index(): View
    {
        $permissionChanges =
            PermissionChangeLog::query()
                ->latest('id')
                ->paginate(50)
                ->withQueryString();

        return view(
            'admin.security.permissions.index',
            compact('permissionChanges')
        );
    }
}
