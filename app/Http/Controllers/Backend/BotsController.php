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
        dd($bots);
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
        
    }

    public function edit(Request $request)
    {
        return view('backend.bots.edit');
    }


    public function update(Request $request)
    {

    }

    public function destroy(Request $request)
    {

    }

}
