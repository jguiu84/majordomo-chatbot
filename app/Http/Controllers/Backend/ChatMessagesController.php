<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChatMessagesController extends Controller
{
    public function index()
    {
        return view('backend.chatmessages.index');
    }

    public function create()
    {
        return view('backend.chatmessages.create');
    }


    public function store(Request $request)
    {
        
    }

    public function edit(Request $request)
    {
        return view('backend.chatmessages.edit');
    }


    public function update(Request $request)
    {

    }

    public function destroy(Request $request)
    {

    }
}
