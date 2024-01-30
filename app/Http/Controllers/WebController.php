<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bots;
class WebController extends Controller
{
    public function index(){
        $bots = Bots::paginate(20);

        return view('web.index', [
            'bots' => $bots
        ]);
    }
}
