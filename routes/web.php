<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\WhatsappController;
use App\Http\Controllers\MessageController;


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

Route::get('/', function () {
    return view('welcome');
});

// Route::post('/webhook/whatsapp', [WhatsappController::class, 'receive']);
Route::post('/send', [MessageController::class, 'sendWhatsApp']);

Route::post('/webhook/whatsapp', [MessageController::class, 'incomingMessage']);

Route::get('/conversations/{phone}', [MessageController::class, 'getConversation']);

Route::get('/conversations', [MessageController::class, 'listPhones']);


Route::get('/conversations/{phone}/summary', [MessageController::class, 'getConversationSummary']);
