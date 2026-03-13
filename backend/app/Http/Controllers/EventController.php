<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /**
     * GET /api/events — return all events
     */
    public function index()
    {
        $events = Event::orderBy('created_at', 'desc')->get();
        return response()->json(['status' => 'success', 'data' => $events]);
    }

    /**
     * POST /api/events — create or update an event
     */
    public function store(Request $request)
    {
        $input = $request->json()->all();
        $id = $input['id'] ?? null;

        if (!empty($id)) {
            // Update
            $event = Event::find($id);
            if (!$event) {
                return response()->json(['status' => 'error', 'message' => 'Event not found'], 404);
            }
            $event->title          = $input['title']          ?? $event->title;
            $event->date           = $input['date']           ?? $event->date;
            $event->end_date       = $input['end_date']       ?? $event->end_date;
            $event->location       = $input['location']       ?? $event->location;
            $event->classification = $input['classification'] ?? $event->classification;
            $event->description   = $input['description']    ?? $event->description;
            $event->status         = $input['status']         ?? $event->status;
            $event->color          = $input['color']          ?? $event->color;
            $event->day_overrides  = array_key_exists('day_overrides', $input) ? $input['day_overrides'] : $event->day_overrides;
            $event->save();
        } else {
            // Create
            Event::create([
                'id'             => (string) Str::uuid(),
                'title'          => $input['title']       ?? '',
                'date'           => $input['date']        ?? '',
                'end_date'       => $input['end_date']    ?? null,
                'location'       => $input['location']     ?? null,
                'classification' => $input['classification'] ?? null,
                'description'    => $input['description'] ?? '',
                'status'         => $input['status']       ?? 'upcoming',
                'color'          => $input['color']       ?? '#3b82f6',
                'day_overrides'  => $input['day_overrides'] ?? null,
            ]);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * DELETE /api/events/{id} — delete an event
     */
    public function destroy($id)
    {
        $event = Event::find($id);
        if (!$event) {
            return response()->json(['status' => 'error', 'message' => 'Event not found'], 404);
        }
        $event->delete();
        return response()->json(['status' => 'success']);
    }
}
