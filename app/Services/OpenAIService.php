<?php

namespace App\Services;

use OpenAI;

class OpenAIService
{
    protected $client;

    public function __construct()
    {
        $this->client = OpenAI::client(env('OPENAI_API_KEY'));
    }

    /**
     * Envía un mensaje al modelo de chat de OpenAI (GPT-4o, GPT-4 Turbo, etc.)
     */
    public function generateReply(string $prompt): string
    {
        $result = $this->client->chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                ['role' => 'system', 'content' => 'Eres un asistente muy útil.'],
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        return $result->choices[0]->message->content;
    }

    public function summarizeConversation(string $conversation): string
    {
        $response = $this->client->chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Eres un asistente que resume conversaciones de WhatsApp de forma breve y clara.'
                ],
                [
                    'role' => 'user',
                    'content' => "Resume la siguiente conversación:\n\n" . $conversation
                ],
            ],
        ]);

        return trim($response->choices[0]->message->content);
    }


}
