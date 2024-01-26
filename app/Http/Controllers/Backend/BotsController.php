<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bots;

class BotsController extends Controller
{
    
    public function index()
    {
        $bots = Bots::paginate(20);

        return view('backend.bots.index', [
            'bots' => $bots
        ]);
    }

    public function create()
    {
        return view('backend.bots.create');
    }


    public function store(Request $request)
    {
        $bot = Bots::create([
            'name' => $request->name,
            'description' => $request->description,
            'ai_type' => $request->ai_type //not editable
        ]);
        return redirect()->route('backend.bots');
    }

    public function edit(Request $request)
    {

        $bot = Bots::where('id', $request->id)->first();
        
        return view('backend.bots.edit', ['bot' => $bot]);
    }


    public function update(Request $request)
    {
        $bot = Bots::where('id', $request->id)->first();
        $bot->name = $request->name;
        $bot->description = $request->description;
        $bot->ai_type = $request->ai_type;
        $bot->update();

        return redirect()->route('backend.bots.edit', ['id' => $bot->id]);
    }

    public function destroy(Request $request)
    {

    }

}
