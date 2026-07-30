<?php

namespace App\Http\Controllers\Client;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ClientPortal\ClientPortalAccessService;
use Illuminate\Contracts\View\View;

class ClientPaymentController extends Controller
{
    public function __construct(
        private readonly ClientPortalAccessService $accessService
    ) {
    }

    public function index(
        Project $project
    ): View {
        $this->accessService->accessFor(
            auth('client')->user(),
            $project,
            'financials'
        );

        $payments =
            $project->payments()
                ->where(
                    'status',
                    PaymentStatus::Cleared
                        ->value
                )
                ->whereNull('voided_at')
                ->latest('payment_date')
                ->paginate(30);

        return view(
            'client.projects.payments',
            compact(
                'project',
                'payments'
            )
        );
    }
}