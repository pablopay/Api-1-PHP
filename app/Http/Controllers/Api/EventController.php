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

    public function index()
    {

        $query = $this->loadRelationships(Event::query());

        return EventResource::collection(
            $query->latest()->paginate()
            );
    }


    /**
     * Store a newly created resource in storage.
     */public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'start_time' => 'required|date',
        'end_time' => 'required|date|after:start_time',
    ]);

    $event = Event::create([
        ...$validated,
        'user_id' => 1, // mejor auth()->id()
    ]);

    return new EventResource($ $this->loadRelationships($event));
}

public function show(Event $event)
{

        return new EventResource( $this->loadRelationships($event));
}

public function update(Request $request, Event $event)
{
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
