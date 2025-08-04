<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;
use OpenAI; 


class WhatsappController extends Controller
{
    public function receive(Request $request)
    {
        $from =  "whatsapp:".$request->input('From');   // Ej: whatsapp:+5216311156732
        $body = $request->input('Body');   // Texto recibido

        // Llama a OpenAI (ejemplo)
        // $responseText = $this->callOpenAI($body);

        // Envía respuesta a WhatsApp
        $sid    = env('TWILIO_SID');
        $token  = env('TWILIO_AUTH_TOKEN');
        $twilio = new Client($sid, $token);

        $twilio->messages->create(
            $from,
            [
                "from" => "whatsapp:+14155238886", // Tu número sandbox
                "body" => "PRueba"//$responseText
            ]
        );

        return response('OK', 200);
    }

    protected function callOpenAI($prompt)
    {
        // Ejemplo simple: usar Guzzle o openai-php/client
        // Aquí puedes adaptar a tu clave y modelo GPT
        $client = OpenAI::client(env('OPENAI_API_KEY'));

        $result = $client->chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                ['role' => 'system', 'content' => 'Eres un asistente muy útil.'],
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        return $result->choices[0]->message->content;
    }
}
