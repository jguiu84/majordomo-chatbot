<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Backend;
use App\Http\Controllers;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/', [Controllers\WebController::class, 'index'])->name('backend');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/chat/{botid}', [Controllers\ChatController::class, 'index'])->name('chat');


    Route::get('/backend', [Backend\IndexController::class, 'index'])->name('backend');
    Route::get('/backend/bots', [Backend\BotsController::class, 'index'])->name('backend.bots');
    Route::get('/backend/bots/new', [Backend\BotsController::class, 'create'])->name('backend.bots.create');
    Route::post('/backend/bots/new', [Backend\BotsController::class, 'store'])->name('backend.bots.store');
    Route::get('/backend/bots/{id}', [Backend\BotsController::class, 'edit'])->name('backend.bots.edit');
    Route::patch('/backend/bots/{id}', [Backend\BotsController::class, 'update'])->name('backend.bots.update');

    Route::get('/backend/openai/files/{botid}', [Backend\OpenaiBotFilesController::class, 'index'])->name('backend.bots.openai.files');
    Route::patch('/backend/openai/files/{botid}', [Backend\OpenaiBotFilesController::class, 'update'])->name('backend.bots.openai.files.update');
    Route::get('/backend/openai/files/{botid}/{id}', [Backend\OpenaiBotFilesController::class, 'delete'])->name('backend.bots.openai.files.delete');

    Route::get('/backend/openai/{botid}', [Backend\OpenaiBotController::class, 'config'])->name('backend.bots.openai.config');

    Route::patch('/backend/openai/{botid}', [Backend\OpenaiBotController::class, 'update'])->name('backend.bots.openai.update');

    
});


Route::get('test', function(){
    $message = App\Models\ChatMessages::inRandomOrder()->first();


    \App\Events\MessageSent::dispatch($message);
});

require __DIR__.'/auth.php';
