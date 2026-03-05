<?php

namespace App\Http\Controllers\Api;
use App\Http\Traits\CanLoadRelationships;

use App\Http\Resources\EventResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;


class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    use CanLoadRelationships;

    private array $relations = ['user', 'attendees', 'attendees.user'];

    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->middleware('throttle:60,1')
            ->only(['store', 'update', 'destroy']);
        $this->authorizeResource(Event::class, 'event');
    }
    public function index()
    {

        $query = $this->loadRelationships(Event::query());

        return EventResource::collection(
            $query->latest()->paginate()
            );
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        $event = Event::create([
            ...$validated,
            'user_id' => $request->user()->id,
        ]);

        return new EventResource($this->loadRelationships($event));
    }

public function show(Event $event)
{

        return new EventResource( $this->loadRelationships($event));
}

public function update(Request $request, Event $event)
{
   // $this->authorize('update-event', $event);

    $validated = $request->validate([
        'name' => 'sometimes|string|max:255',
        'description' => 'nullable|string',
        'start_time' => 'sometimes|date',
        'end_time' => 'sometimes|date|after:start_time',
    ]);

    $event->update($validated);

    return new EventResource( $this->loadRelationships($event));
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();


        return response()->json([
            'message'=> 'Event deleted successfully'
            ]);
    }
}
