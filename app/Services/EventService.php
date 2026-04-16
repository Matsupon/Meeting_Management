<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventAudit;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventService
{
    /**
     * Get all events ordered by created_at desc.
     * Supports optional pagination via ?page= query param.
     */
    public function getAll(bool $paginate = false, int $perPage = 50)
    {
        $query = Event::where('user_id', session('user_id'))->orderBy('created_at', 'desc');
        return $paginate ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Create a new event.
     */
    public function create(array $data): Event
    {
        $event = Event::create([
            'id'             => (string) Str::uuid(),
            'user_id'        => session('user_id'),
            'title'          => $data['title'],
            'date'           => $data['date'],
            'end_date'       => $data['end_date'] ?? null,
            'location'       => $data['location'] ?? null,
            'classification' => $data['classification'] ?? null,
            'description'    => $data['description'] ?? '',
            'status'         => $data['status'] ?? 'upcoming',
            'color'          => $data['color'] ?? '#3b82f6',
            'day_overrides'  => $data['day_overrides'] ?? null,
            'recurrence_rule' => $data['recurrence_rule'] ?? null,
            'recurrence_end' => $data['recurrence_end'] ?? null,
        ]);
        $this->audit($event, 'created', null, $event->toArray());
        return $event;
    }

    /**
     * Update an existing event.
     */
    public function update(Event $event, array $data): Event
    {
        // Capture original values BEFORE any mutations so the audit diff is accurate
        $old = $event->getOriginal();

        $event->title          = $data['title'];
        $event->date           = $data['date'];
        $event->end_date       = $data['end_date'] ?? null;
        $event->location       = $data['location'] ?? null;
        $event->classification = $data['classification'] ?? null;
        $event->description    = $data['description'] ?? '';
        $event->status         = $data['status'] ?? 'upcoming';
        $event->color          = $data['color'] ?? '#3b82f6';
        $event->day_overrides  = $data['day_overrides'] ?? $event->day_overrides;
        $event->recurrence_rule = $data['recurrence_rule'] ?? $event->recurrence_rule;
        $event->recurrence_end  = $data['recurrence_end'] ?? $event->recurrence_end;
        $event->save();
        $this->audit($event, 'updated', $old, $event->toArray());
        return $event;
    }

    /**
     * Delete an event.
     */
    public function delete(Event $event): void
    {
        $old = $event->toArray();
        $this->audit($event, 'deleted', $old, null);
        $event->delete();
    }

    private function audit(Event $event, string $action, ?array $oldValues, ?array $newValues): void
    {
        EventAudit::create([
            'event_id' => $event->id,
            'user_id' => session('user_id'),
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }
}
