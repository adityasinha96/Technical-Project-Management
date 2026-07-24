<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketCommentRequest;
use App\Http\Requests\UpdateTicketCommentRequest;
use App\Models\ProjectTicket;
use App\Models\TicketComment;
use App\Services\Attachments\ProjectAttachmentService;
use App\Services\Projects\ProjectActivityService;
use App\Services\Tickets\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketCommentController extends Controller
{
    public function __construct(
        private readonly TicketService $ticketService,
        private readonly ProjectAttachmentService $attachmentService,
        private readonly ProjectActivityService $activityService
    ) {
    }

    public function store(
        StoreTicketCommentRequest $request,
        ProjectTicket $ticket
    ): RedirectResponse {
        DB::transaction(
            function () use (
                $request,
                $ticket
            ): void {
                $comment = $this
                    ->ticketService
                    ->addComment(
                        ticket: $ticket,
                        user: $request->user(),
                        data:
                            $request->validated()
                    );

                if (
                    $request->hasFile(
                        'attachments'
                    )
                ) {
                    $this->attachmentService
                        ->storeMany(
                            project:
                                $ticket->project,

                            attachable:
                                $comment,

                            files:
                                $request->file(
                                    'attachments'
                                ),

                            uploadedBy:
                                $request->user(),

                            category:
                                'ticket_comment'
                        );
                }
            }
        );

        return back()->with(
            'success',
            'Discussion added successfully.'
        );
    }

    public function update(
        UpdateTicketCommentRequest $request,
        ProjectTicket $ticket,
        TicketComment $ticketComment
    ): RedirectResponse {
        abort_unless(
            $ticketComment
                ->project_ticket_id
            === $ticket->id,
            404
        );

        abort_unless(
            $ticketComment
                ->canBeManagedBy(
                    $request->user()
                ),
            403
        );

        $oldMessage =
            $ticketComment->message;

        DB::transaction(
            function () use (
                $request,
                $ticket,
                $ticketComment
            ): void {
                $ticketComment->update([
                    'comment_type' =>
                        $request->validated(
                            'comment_type'
                        ),

                    'message' =>
                        $request->validated(
                            'message'
                        ),

                    'edited_by' =>
                        $request->user()->id,

                    'edited_at' => now(),
                ]);

                if (
                    $request->hasFile(
                        'attachments'
                    )
                ) {
                    $this->attachmentService
                        ->storeMany(
                            project:
                                $ticket->project,

                            attachable:
                                $ticketComment,

                            files:
                                $request->file(
                                    'attachments'
                                ),

                            uploadedBy:
                                $request->user(),

                            category:
                                'ticket_comment'
                        );
                }
            }
        );

        $this->activityService->logCustom(
            project: $ticket->project,
            event: 'ticket_comment_updated',

            title:
                "Discussion updated on {$ticket->ticket_number}",

            subject: $ticketComment,

            oldValues: [
                'message' =>
                    Str::limit(
                        $oldMessage,
                        500
                    ),
            ],

            newValues: [
                'message' =>
                    Str::limit(
                        $ticketComment
                            ->message,
                        500
                    ),
            ],

            actorId:
                $request->user()->id
        );

        return back()->with(
            'success',
            'Discussion updated.'
        );
    }

    public function destroy(
        ProjectTicket $ticket,
        TicketComment $ticketComment
    ): RedirectResponse {
        abort_unless(
            $ticketComment
                ->project_ticket_id
            === $ticket->id,
            404
        );

        abort_unless(
            $ticketComment
                ->canBeManagedBy(
                    request()->user()
                ),
            403
        );

        $this->attachmentService
            ->deleteForAttachable(
                $ticketComment,
                request()->user()
            );

        $ticketComment->delete();

        $this->activityService->logCustom(
            project: $ticket->project,
            event: 'ticket_comment_deleted',

            title:
                "Discussion deleted from {$ticket->ticket_number}",

            subject: $ticket,

            actorId:
                request()->user()->id
        );

        return back()->with(
            'success',
            'Discussion deleted.'
        );
    }
}