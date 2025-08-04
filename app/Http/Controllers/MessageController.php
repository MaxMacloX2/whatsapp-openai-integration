<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

// use OpenAI; 

use App\Services\OpenAIService;

use App\Models\Message;



class MessageController extends Controller
{
    //Enviar un mensaje
    public function sendWhatsApp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'From' => 'required|string|min:10',
            'Body' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        $from = "whatsapp:" . $request->input('From');
        $body = $request->input('Body');

        try {
            // Enviar mensaje
            $sid = env('TWILIO_SID');
            $token = env('TWILIO_AUTH_TOKEN');
            $twilio = new Client($sid, $token);

            $message = $twilio->messages->create(
                $from,
                [
                    "from" => env('TWILIO_WHATSAPP_FROM'),  //"whatsapp:+14155238886", // Sandbox
                    "body" => $body
                ]
            );

            // Registrar historial
            DB::table('historial_de_conversacion')->insert([
                'telefono' => $from,
                'mensaje' => $body,
                'rol' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            
            return response()->json([
                'status' => 'success',
                'message_sid' => $message->sid,
                'telefono' => $from,
                'mensaje' => $body,
            ]);

        } catch (\Exception $e) {
            // ✅ 5️⃣ Manejar error
            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo enviar el mensaje.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    // Recibimiento de mensaje
    public function incomingMessage(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'From' => 'required|string|min:10',
            'Body' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Datos inválidos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $from = $request->input('From');
        $body = $request->input('Body');

        try {
            // Registrar mensaje
            DB::table('historial_de_conversacion')->insert([
                'telefono' => $from,
                'mensaje' => $body,
                'rol' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Generar respuesta
            $openAI = new OpenAIService();
            $responseText = $openAI->generateReply($body);

            // Registrar mensaje del bot
            DB::table('historial_de_conversacion')->insert([
                'telefono' => $from,
                'mensaje' => $responseText,
                'rol' => 'bot',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // El envio de mensaje
            $sid = env('TWILIO_SID');
            $token = env('TWILIO_AUTH_TOKEN');
            $twilio = new Client($sid, $token);

            $twilio->messages->create(
                $from,
                [
                    "from" => env('TWILIO_WHATSAPP_FROM'),//"whatsapp:+14155238886",
                    "body" => $responseText
                ]
            );

            return response($responseText, 200);

        } catch (\Exception $e) {
            // Mosatrar mensaje de error 
            \Log::error('Webhook error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error procesando el mensaje.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

   

    //Trae los mensajes
    public function getConversation($phone)
    {
        
        $validator = Validator::make(['phone' => $phone], [
            'phone' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Teléfono inválido.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            
            $messages = DB::table('historial_de_conversacion')
                ->where('telefono', "whatsapp:" . $phone)
                ->orderBy('created_at', 'asc')
                ->get();

            if ($messages->isEmpty()) {
                return response()->json([
                    'status' => 'ok',
                    'message' => 'No hay mensajes para este teléfono.',
                    'telefono' => $phone,
                    'mensajes' => [],
                ], 200);
            }

            
            $formatted = $messages->map(function ($msg) {
                return [
                    'telefono' => $msg->telefono,
                    'mensaje'  => $msg->mensaje,
                    'rol'      => $msg->rol,
                    'timestamp' => $msg->created_at,
                ];
            });

            return response()->json([
                'status' => 'ok',
                'telefono' => $phone,
                'mensajes' => $formatted,
            ], 200);

        } catch (\Exception $e) {
            
            \Log::error('Error getConversation: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Error obteniendo la conversación.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Traer lista de telefonos
    public function listPhones()
    {
        try {
            
            $phones = DB::table('historial_de_conversacion')
                ->select('telefono')
                ->distinct()
                ->get();

            // Quitar el whatsapp
            $cleanPhones = $phones->map(function ($item) {
                return str_replace('whatsapp:', '', $item->telefono);
            });

            // Si no hay teléfonos
            if ($cleanPhones->isEmpty()) {
                return response()->json([
                    'status' => 'ok',
                    'message' => 'No hay teléfonos registrados.',
                    'telefonos_unicos' => [],
                ], 200);
            }

            
            return response()->json([
                'status' => 'ok',
                'telefonos_unicos' => $cleanPhones,
            ], 200);

        } catch (\Exception $e) {
            
            \Log::error('Error listPhones: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Error obteniendo teléfonos.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    // Resumir una conversacion
    public function getConversationSummary($phone)
    {
        // Validación
        $validator = Validator::make(['phone' => $phone], [
            'phone' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Número de teléfono inválido.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Buscar mensajes
            $messages = Message::where('telefono', "whatsapp:" . $phone)
                ->orderBy('created_at', 'asc')
                ->get();

            if ($messages->isEmpty()) {
                return response()->json([
                    'status' => 'ok',
                    'message' => 'No hay mensajes para este teléfono.',
                    'telefono' => $phone,
                    'resumen' => '',
                ], 200);
            }

            
            $conversation = "";
            foreach ($messages as $msg) {
                $conversation .= strtoupper($msg->rol) . ": " . $msg->mensaje . "\n";
            }

            
            $openAI = new OpenAIService();
            $summary = $openAI->summarizeConversation($conversation);

            return response()->json([
                'status' => 'ok',
                'telefono' => $phone,
                'resumen' => $summary,
            ], 200);

        } catch (\Exception $e) {
            
            Log::error('Error getConversationSummary: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Error generando resumen de conversación.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
