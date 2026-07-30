<?php

namespace App\Services\ClientPortal;

use App\Enums\ActivityVisibility;
use App\Enums\TicketCommentType;
use App\Enums\TicketCommentVisibility;
use App\Enums\TicketSource;
use App\Enums\TicketStatus;
use App\Models\ClientUser;
use App\Models\Project;
use App\Models\ProjectTicket;
use App\Services\Projects\ProjectActivityService;
use App\Services\Tickets\TicketSlaService;
use Illuminate\Support\Facades\DB;

class ClientTicketService
{
    public function __construct(
        private readonly ClientPortalAccessService $accessService,
        private readonly TicketSlaService $slaService,
        private readonly ProjectActivityService $activityService
    ) {
    }

    public function create(
        Project $project,
        ClientUser $clientUser,
        array $data
    ): ProjectTicket {
        $this->accessService->accessFor(
            clientUser: $clientUser,
            project: $project,
            capability: 'tickets'
        );

        return DB::transaction(
            function () use (
                $project,
                $clientUser,
                $data
            ): ProjectTicket {
                $ticket =
                    ProjectTicket::create([
                        'project_id' =>
                            $project->id,

                        'client_id' =>
                            $project->client_id,

                        'type' =>
                            $data['type'],

                        'source' =>
                            TicketSource::Website
                                ->value,

                        'priority' =>
                            $data['priority'],

                        'status' =>
                            TicketStatus::Open
                                ->value,

                        'subject' =>
                            $data['subject'],

                        'description' =>
                            $data['description'],

                        'client_visible' =>
                            true,

                        'client_can_reply' =>
                            true,

                        'submitted_by_client_user_id' =>
                            $clientUser->id,

                        'last_activity_at' =>
                            now(),
                    ]);

                $ticket->forceFill([
                    'ticket_number' => sprintf(
                        'TKT-%s-%05d',
                        now()->format('Y'),
                        $ticket->id
                    ),

                    ...$this->slaService
                        ->initialAttributes(
                            $ticket->priority
                        ),
                ])->saveQuietly();

                $ticket->statusHistories()
                    ->create([
                        'from_status' =>
                            null,

                        'to_status' =>
                            TicketStatus::Open
                                ->value,

                        'changed_by' =>
                            null,

                        'reason' =>
                            'Ticket submitted through client portal.',

                        'metadata' => [
                            'client_user_id' =>
                                $clientUser->id,

                            'client_user_name' =>
                                $clientUser->name,
                        ],

                        'changed_at' =>
                            now(),
                    ]);

                $this->activityService
                    ->logCustom(
                        project:
                            $project,

                        event:
                            'client_ticket_created',

                        title:
                            "{$ticket->ticket_number} submitted by client",

                        description:
                            $ticket->subject,

                        subject:
                            $ticket,

                        metadata: [
                            'client_user_id' =>
                                $clientUser->id,

                            'client_user_name' =>
                                $clientUser->name,
                        ],

                        visibility:
                            ActivityVisibility::Team,

                        actorId: null
                    );

                return $ticket;
            }
        );
    }

    public function addComment(
        ProjectTicket $ticket,
        ClientUser $clientUser,
        string $message
    ) {
        $this->accessService->accessFor(
            clientUser:
                $clientUser,

            project:
                $ticket->project,

            capability:
                'tickets'
        );

        abort_unless(
            $ticket->client_visible
            && $ticket->client_can_reply,
            403
        );

        return DB::transaction(
            function () use (
                $ticket,
                $clientUser,
                $message
            ) {
                $comment =
                    $ticket->comments()
                        ->create([
                            'comment_type' =>
                                TicketCommentType::Discussion
                                    ->value,

                            'visibility' =>
                                TicketCommentVisibility::Client
                                    ->value,

                            'message' =>
                                $message,

                            'created_by' =>
                                null,

                            'client_user_id' =>
                                $clientUser->id,
                        ]);

                $ticket->forceFill([
                    'client_last_replied_at' =>
                        now(),

                    'last_reply_at' =>
                        now(),

                    'last_activity_at' =>
                        now(),
                ])->saveQuietly();

                return $comment;
            }
        );
    }
}