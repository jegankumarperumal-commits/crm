<?php

namespace App\Enums;

enum MeetingType: string
{
    case InPerson = 'in_person';
    case VideoCall = 'video_call';
    case PhoneCall = 'phone_call';

    public function label(): string
    {
        return match ($this) {
            self::InPerson => 'In Person',
            self::VideoCall => 'Video Call',
            self::PhoneCall => 'Phone Call',
        };
    }
}
