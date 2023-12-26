<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
 
        $data = Event::all();

        foreach ($data as $event) {
            $event->backgroundColor = $event->color;
            $event->borderColor = $event->color;
            unset($event->color);
            

                    // Validate start and end times
            $start = Carbon::parse($event->start);
            $end = Carbon::parse($event->end);

            if ($start->greaterThanOrEqualTo($end)) {
                continue; 
            }
            // Adjust timezone using Carbon
            $event->start = Carbon::createFromFormat('Y-m-d H:i:s', $event->start, 'UTC')
                ->setTimezone('Asia/Jakarta')
                ->toIso8601String();
    
            $event->end = Carbon::createFromFormat('Y-m-d H:i:s', $event->end, 'UTC')
                ->setTimezone('Asia/Jakarta')
                ->toIso8601String();
        }


        // for ($i = 0; $i < count($data); $i++) {
        //     $data[$i]->backgroundColor = $data[$i]->color;
        //     $data[$i]->borderColor = $data[$i]->color;
        //     unset($data[$i]->color);
        //     // dd($data[$i]->start->shiftTimezone('Asia/Jakarta')->toISOString());
        //     $data[$i]->start = $data[$i]->start->shiftTimezone("Asia/Jakarta")->toISOString();
        //     $data[$i]->end = $data[$i]->end->shiftTimezone("Asia/Jakarta")->toISOString();
        // }

        return response()->json($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => "required|string",
            'start' => "required|date",
            'end' => "required|date|after:start",
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'color' => "nullable|string"
        ]);

        if ($request->start_time) {
            // buat iso format
            $data['start'] = explode('T', $data['start'])[0] . 'T' . $data['start_time'] . ':00.000Z';
            unset($data['start_time']);
        }

        if ($request->end_time) {
            // buat iso format
            $data['end'] = explode('T', $data['end'])[0] . 'T' . $data['end_time'] . ':00.000Z';
            unset($data['end_time']);
        }

        if (strtotime($data['end']) <= strtotime($data['start'])) {
            return redirect()->back()->with('error', 'End time must be later than start time.');
        }
    
        Event::create($data);

        return response()->redirectToRoute('calendar');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function show(Event $event)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function edit(Event $event)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Event $event)
    {
        $data = $request->validate([
            'title' => "string",
            'start' => "date",
            'end' => "date",
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'color' => "nullable|string"
        ]);
        //dump($data);
        if ($request->start && $request->start_time) {
            // buat iso format
            $data['start'] = explode('T', $data['start'])[0] . 'T' . $data['start_time'] . ':00.000Z';
            unset($data['start_time']);
        }

        if ($request->end && $request->end_time) {
            // buat iso format
            $data['end'] = explode('T', $data['end'])[0] . 'T' . $data['end_time'] . ':00.000Z';
            unset($data['end_time']);
        }

        $event->update($data);

        return response()->redirectToRoute('calendar');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return response()->redirectToRoute('calendar');
    }
}
