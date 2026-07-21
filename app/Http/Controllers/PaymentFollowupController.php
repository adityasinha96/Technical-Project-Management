<?php

namespace App\Http\Controllers;

use App\Enums\PaymentFollowupStatus;
use App\Http\Requests\StorePaymentFollowupRequest;
use App\Http\Requests\UpdatePaymentFollowupRequest;
use App\Models\PaymentFollowup;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;

class PaymentFollowupController extends Controller
{
    public function store(
        StorePaymentFollowupRequest $request,
        Project $project
    ): RedirectResponse {
        $data = $this->normaliseData(
            $request->validated(),
            $request->user()->id
        );

        $project->paymentFollowups()->create([
            ...$data,
            'client_id' => $project->client_id,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('projects.show', [
                'project' => $project,
                'tab' => 'payments',
            ])
            ->with(
                'success',
                'Payment follow-up created successfully.'
            );
    }

    public function update(
        UpdatePaymentFollowupRequest $request,
        Project $project,
        PaymentFollowup $paymentFollowup
    ): RedirectResponse {
        abort_unless(
            $paymentFollowup->project_id ===
            $project->id,
            404
        );

        $paymentFollowup->update(
            $this->normaliseData(
                $request->validated(),
                $request->user()->id
            )
        );

        return redirect()
            ->route('projects.show', [
                'project' => $project,
                'tab' => 'payments',
            ])
            ->with(
                'success',
                'Payment follow-up updated successfully.'
            );
    }

    public function destroy(
        Project $project,
        PaymentFollowup $paymentFollowup
    ): RedirectResponse {
        abort_unless(
            $paymentFollowup->project_id ===
            $project->id,
            404
        );

        $paymentFollowup->delete();

        return back()->with(
            'success',
            'Payment follow-up removed.'
        );
    }

    private function normaliseData(
        array $data,
        int $userId
    ): array {
        $status = PaymentFollowupStatus::from(
            $data['status']
        );

        if ($status->isClosed()) {
            $data['completed_at'] = now();
            $data['completed_by'] = $userId;
            $data['next_followup_at'] = null;
        } else {
            $data['completed_at'] = null;
            $data['completed_by'] = null;
        }

        return $data;
    }
}