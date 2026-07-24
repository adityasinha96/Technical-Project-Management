<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTicketSlaPolicyRequest;
use App\Models\TicketSlaPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class TicketSlaPolicyController extends Controller
{
    public function index(): View
    {
        return view(
            'ticket-sla-policies.index',
            [
                'policies' =>
                    TicketSlaPolicy::query()
                        ->get()
                        ->sortBy(
                            fn (
                                TicketSlaPolicy $policy
                            ) =>
                                $policy
                                    ->priority
                                    ->sortOrder()
                        ),
            ]
        );
    }

    public function update(
        UpdateTicketSlaPolicyRequest $request,
        TicketSlaPolicy $ticketSlaPolicy
    ): RedirectResponse {
        $ticketSlaPolicy->update(
            $request->validated()
        );

        return back()->with(
            'success',
            "{$ticketSlaPolicy->priority->label()} SLA policy updated."
        );
    }
}