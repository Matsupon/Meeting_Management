<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Support\Facades\Storage;
use App\Services\IcalExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EventController extends Controller
{
    public function __construct(
        protected EventService $eventService,
        protected IcalExportService $icalService
    ) {}

    /**
     * GET /api/events — return all events
     */
    public function index()
    {
        $events = $this->eventService->getAll();
        return response()->json(['status' => 'success', 'data' => $events]);
    }

    /**
     * GET /api/events/ical — export events as iCal
     */
    public function ical(): Response
    {
        $content = $this->icalService->generate();
        return response($content, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="events.ics"',
        ]);
    }

    /**
     * POST /api/events — create or update an event
     */
    public function store(StoreEventRequest $request)
    {
        $validated = $request->validated();
        $id = $validated['id'] ?? null;

        if (!empty($id)) {
            $event = Event::find($id);
            if (!$event) {
                return response()->json(['status' => 'error', 'message' => 'Event not found'], 404);
            }
            $this->eventService->update($event, $validated);
        } else {
            $this->eventService->create($validated);
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
        $this->eventService->delete($event);
        return response()->json(['status' => 'success']);
    }

}
