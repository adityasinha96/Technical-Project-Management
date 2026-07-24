<?php

namespace App\Enums;

enum TicketType: string
{
    case Bug = 'bug';
    case ChangeRequest = 'change_request';
    case Content = 'content';
    case Deployment = 'deployment';
    case Access = 'access';
    case Billing = 'billing';
    case Support = 'support';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Bug => 'Bug or Technical Issue',
            self::ChangeRequest => 'Change Request',
            self::Content => 'Content Update',
            self::Deployment => 'Deployment Issue',
            self::Access => 'Login or Access Issue',
            self::Billing => 'Payment or Billing',
            self::Support => 'General Support',
            self::Other => 'Other',
        };
    }
}