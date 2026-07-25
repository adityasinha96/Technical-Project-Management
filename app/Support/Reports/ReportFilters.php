<?php

namespace App\Support\Reports;

use Carbon\CarbonImmutable;

final readonly class ReportFilters
{
    public function __construct(
        public CarbonImmutable $from,
        public CarbonImmutable $to,
        public ?int $projectId = null,
        public ?int $clientId = null,
        public ?int $userId = null,
        public ?string $projectStatus = null,
        public ?string $projectPriority = null,
        public ?string $ticketStatus = null,
        public ?string $ticketPriority = null,
        public int $perPage = 25
    ) {
    }

    public static function fromArray(
        array $data
    ): self {
        [$defaultFrom, $defaultTo] =
            self::currentFinancialYear();

        $from = filled(
            $data['date_from'] ?? null
        )
            ? CarbonImmutable::parse(
                $data['date_from']
            )->startOfDay()
            : $defaultFrom;

        $to = filled(
            $data['date_to'] ?? null
        )
            ? CarbonImmutable::parse(
                $data['date_to']
            )->endOfDay()
            : $defaultTo;

        return new self(
            from: $from,
            to: $to,

            projectId:
                isset($data['project_id'])
                    ? (int) $data['project_id']
                    : null,

            clientId:
                isset($data['client_id'])
                    ? (int) $data['client_id']
                    : null,

            userId:
                isset($data['user_id'])
                    ? (int) $data['user_id']
                    : null,

            projectStatus:
                $data['project_status']
                ?? null,

            projectPriority:
                $data['project_priority']
                ?? null,

            ticketStatus:
                $data['ticket_status']
                ?? null,

            ticketPriority:
                $data['ticket_priority']
                ?? null,

            perPage:
                (int) (
                    $data['per_page']
                    ?? 25
                )
        );
    }

    public static function currentFinancialYear():
        array
    {
        $today = CarbonImmutable::today(
            config(
                'app.timezone',
                'Asia/Kolkata'
            )
        );

        $startYear = $today->month >= 4
            ? $today->year
            : $today->year - 1;

        $from = CarbonImmutable::create(
            $startYear,
            4,
            1,
            0,
            0,
            0,
            $today->timezone
        );

        $financialYearEnd =
            CarbonImmutable::create(
                $startYear + 1,
                3,
                31,
                23,
                59,
                59,
                $today->timezone
            );

        $to = $financialYearEnd->isFuture()
            ? $today->endOfDay()
            : $financialYearEnd;

        return [$from, $to];
    }

    public function toArray(): array
    {
        return [
            'date_from' =>
                $this->from->toDateString(),

            'date_to' =>
                $this->to->toDateString(),

            'project_id' =>
                $this->projectId,

            'client_id' =>
                $this->clientId,

            'user_id' =>
                $this->userId,

            'project_status' =>
                $this->projectStatus,

            'project_priority' =>
                $this->projectPriority,

            'ticket_status' =>
                $this->ticketStatus,

            'ticket_priority' =>
                $this->ticketPriority,

            'per_page' =>
                $this->perPage,
        ];
    }

    public function cacheHash(): string
    {
        return hash(
            'sha256',
            json_encode(
                $this->toArray(),
                JSON_THROW_ON_ERROR
            )
        );
    }

    public function periodLabel(): string
    {
        return $this->from->format('d M Y')
            . ' – '
            . $this->to->format('d M Y');
    }
}