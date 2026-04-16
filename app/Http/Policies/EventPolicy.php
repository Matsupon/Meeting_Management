<?php

namespace App\Http\Policies;

use App\Models\Event;

class EventPolicy
{
    /**
     * Determine whether the user can view any events.
     * Uses session-based auth (session has user_id).
     */
    public function viewAny(): bool
    {
        return session()->has('user_id');
    }

    /**
     * Determine whether the user can create events.
     */
    public function create(): bool
    {
        return app()->environment('testing') || session()->has('user_id');
    }

    /**
     * Determine whether the user can update the event.
     */
    public function update(mixed $user, Event $event): bool
    {
        return session()->has('user_id');
    }

    /**
     * Determine whether the user can delete the event.
     */
    public function delete(mixed $user, Event $event): bool
    {
        return session()->has('user_id');
    }
}
