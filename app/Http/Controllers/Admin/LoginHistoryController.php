<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginEvent;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LoginHistoryController extends Controller
{
    public function index(
        Request $request
    ): View {
        $loginEvents =
            LoginEvent::query()
                ->latest('id')
                ->paginate(50)
                ->withQueryString();

        return view(
            'admin.security.login-history.index',
            compact('loginEvents')
        );
    }
}
