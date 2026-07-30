<?php

namespace App\Services\ClientPortal;

use App\Enums\ActivityVisibility;
use App\Enums\ClientMessageSenderType;
use App\Models\ClientCommunication;
use App\Models\ClientUser;
use App\Models\Project;
use App\Models\User;
use App\Notifications\ClientPortalAlertNotification;
use App\Services\Projects\ProjectActivityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClientCommunicationService
{
    public function __construct(
        private readonly ClientPortalAccessService $accessService,
        private readonly ProjectActivityService $activityService
    ) {
    }

    public function sendFromClient(
        Project $project,
        ClientUser $clientUser,
        string $message,
        ?int $replyToId = null
    ): ClientCommunication {
        $this->accessService->accessFor(
            $clientUser,
            $project,
            'communicate'
        );

        return DB::transaction(
            function () use (
                $project,
                $clientUser,
                $message,
                $replyToId
            ): ClientCommunication {
                if ($replyToId) {
                    ClientCommunication::query()
                        ->where(
                            'project_id',
                            $project->id
                        )
                        ->findOrFail(
                            $replyToId
                        );
                }

                $communication =
                    ClientCommunication::create([
                        'project_id' =>
                            $project->id,

                        'client_user_id' =>
                            $clientUser->id,

                        'user_id' => null,

                        'reply_to_id' =>
                            $replyToId,

                        'sender_type' =>
                            ClientMessageSenderType::Client
                                ->value,

                        'message' =>
                            $message,

                        'client_read_at' =>
                            now(),
                    ]);

                $this->activityService
                    ->logCustom(
                        project: $project,

                        event:
                            'client_message_sent',

                        title:
                            "Client message from {$clientUser->name}",

                        description:
                            Str::limit(
                                $message,
                                500
                            ),

                        subject:
                            $communication,

                        metadata: [
                            'client_user_id' =>
                                $clientUser->id,
                        ],

                        visibility:
                            ActivityVisibility::Team,

                        actorId: null
                    );

                return $communication;
            }
        );
    }

    public function sendFromInternalUser(
        Project $project,
        User $user,
        string $message,
        ?int $replyToId = null
    ): ClientCommunication {
        return DB::transaction(
            function () use (
                $project,
                $user,
                $message,
                $replyToId
            ): ClientCommunication {
                $communication =
                    ClientCommunication::create([
                        'project_id' =>
                            $project->id,

                        'client_user_id' =>
                            null,

                        'user_id' =>
                            $user->id,

                        'reply_to_id' =>
                            $replyToId,

                        'sender_type' =>
                            ClientMessageSenderType::InternalUser
                                ->value,

                        'message' =>
                            $message,

                        'internal_read_at' =>
                            now(),
                    ]);

                $recipients =
                    $project->clientUsers()
                        ->wherePivot(
                            'is_active',
                            true
                        )
                        ->wherePivot(
                            'can_communicate',
                            true
                        )
                        ->get();

                foreach ($recipients as $recipient) {
                    $recipient->notify(
                        new ClientPortalAlertNotification(
                            title:
                                "New message for {$project->name}",

                            message:
                                Str::limit(
                                    $message,
                                    180
                                ),

                            url:
                                route(
                                    'client.communications.index',
                                    $project
                                )
                        )
                    );
                }

                return $communication;
            }
        );
    }
}