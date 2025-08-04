<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MessageController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Para contestar mesaje con open AI
Route::post('/webhook/whatsapp', [MessageController::class, 'incomingMessage']);

//Trae los mensajes ordenados por fecha
Route::get('/conversations/{phone}', [MessageController::class, 'getConversation']);

// Lista todos los teléfonos únicos que han conversado
Route::get('/conversations', [MessageController::class, 'listPhones']);

//Resumir conversacion
Route::get('/conversations/{phone}/summary', [MessageController::class, 'getConversationSummary']);


// Para enviar manualmente un mensaje a un número 
Route::post('/send', [MessageController::class, 'sendWhatsApp']);

