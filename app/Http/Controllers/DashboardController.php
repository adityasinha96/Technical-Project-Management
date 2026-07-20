<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'users' => User::query()->count(),
            'active_users' => User::query()
                ->where('status', 'active')
                ->count(),
            'roles' => Role::query()->count(),
            'permissions' => Permission::query()->count(),
            'settings' => SystemSetting::query()->count(),
        ];

        return view('dashboard', compact('stats'));
    }
}