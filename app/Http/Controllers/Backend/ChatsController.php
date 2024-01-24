<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChatsController extends Controller
{
    public function index()
    {
        return view('backend.chats.index');
    }

    public function create()
    {
        return view('backend.chats.create');
    }


    public function store(Request $request)
    {
        
    }

    public function edit(Request $request)
    {
        return view('backend.chats.edit');
    }


    public function update(Request $request)
    {

    }

    public function destroy(Request $request)
    {

    }
}
