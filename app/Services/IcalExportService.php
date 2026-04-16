<?php

namespace App\Services;

use App\Models\Event;
use Carbon\Carbon;

class IcalExportService
{
    /**
     * Generate iCal content for all events.
     */
    public function generate(): string
    {
        $events = Event::where('user_id', session('user_id'))->orderBy('date')->get();
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Meeting Management System//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
        ];

        foreach ($events as $event) {
            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:' . $event->id . '@meeting-management';
            $lines[] = 'DTSTAMP:' . now()->format('Ymd\THis\Z');
            $lines[] = 'DTSTART;VALUE=DATE:' . str_replace('-', '', $event->date);
            if ($event->end_date) {
                $end = Carbon::parse($event->end_date)->addDay();
                $lines[] = 'DTEND;VALUE=DATE:' . $end->format('Ymd');
            } else {
                // RFC 5545: for all-day events, DTEND is exclusive (next day)
                $lines[] = 'DTEND;VALUE=DATE:' . Carbon::parse($event->date)->addDay()->format('Ymd');
            }
            $lines[] = 'SUMMARY:' . $this->escape($event->title);
            if ($event->description) {
                $lines[] = 'DESCRIPTION:' . $this->escape($event->description);
            }
            if ($event->location) {
                $lines[] = 'LOCATION:' . $this->escape($event->location);
            }
            if ($event->recurrence_rule) {
                $rrule = $event->recurrence_rule;
                if ($event->recurrence_end) {
                    $until = Carbon::parse($event->recurrence_end)->format('Ymd');
                    $rrule = rtrim($rrule, ';') . ';UNTIL=' . $until;
                }
                $lines[] = 'RRULE:' . $rrule;
            }
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';
        return implode("\r\n", $lines);
    }

    private function escape(string $str): string
    {
        return str_replace(["\r\n", "\n", ',', ';', '\\'], ['\n', '\n', '\,', '\;', '\\\\'], $str);
    }
}
