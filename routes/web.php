<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

Route::get('/', [ChatController::class, 'index'])->name('chat.index');
Route::post('/api/chat/ask', [ChatController::class, 'ask'])->name('api.chat.ask');
Route::post('/api/chat/refresh', [ChatController::class, 'refresh'])->name('api.chat.refresh');
Route::get('/api/chat/units', [ChatController::class, 'units'])->name('api.chat.units');
Route::get('/api/chat/unit/{unitName}', [ChatController::class, 'unit'])->name('api.chat.unit');
