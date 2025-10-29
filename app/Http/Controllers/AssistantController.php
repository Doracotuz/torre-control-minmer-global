<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class AssistantController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate(['message' => 'required|string|max:500']);
        $userInput = $request->input('message');
        
        $userName = auth()->user() ? auth()->user()->name : 'Usuario';

        $messageCount = $request->session()->get('assistant_message_count', 0);
        $messageCount++;
        $request->session()->put('assistant_message_count', $messageCount);

        if ($messageCount > 5) {
            $whatsappNumber = "5215536583392";
            $whatsappMessage = urlencode("Hola, me gustaría recibir asistencia para la plataforma \"Control Tower - Minmer Global\"");
            $whatsappLink = "https://wa.me/{$whatsappNumber}?text={$whatsappMessage}";

            return response()->json([
                'type' => 'escalation',
                'reply' => 'He intentado ayudarte con la información disponible. Para una asistencia más personalizada, por favor contacta a un miembro de nuestro equipo de soporte.',
                'whatsapp_link' => $whatsappLink
            ]);
        }
        try {
            $assistantId = env('OPENAI_ASSISTANT_ID');
            $threadId = $request->session()->get('assistant_thread_id');

            if (!$threadId) {
                $thread = OpenAI::threads()->create([]);
                $threadId = $thread->id;
                $request->session()->put('assistant_thread_id', $threadId);
            }

            $userMessageWithContext = "Mi nombre es {$userName}. Mi pregunta es: {$userInput}";

            OpenAI::threads()->messages()->create($threadId, [
                'role' => 'user',
                'content' => $userMessageWithContext,
            ]);

            $run = OpenAI::threads()->runs()->create($threadId, [
                'assistant_id' => $assistantId,
            ]);

            do {
                sleep(1);
                $run = OpenAI::threads()->runs()->retrieve($threadId, $run->id);
            } while ($run->status !== 'completed' && $run->status !== 'failed');
            
            if ($run->status === 'failed') {
                throw new \Exception('La ejecución del asistente falló.');
            }

            $messages = OpenAI::threads()->messages()->list($threadId, ['limit' => 1]);
            $reply = $messages->data[0]->content[0]->text->value;

            return response()->json(['type' => 'ai_reply', 'reply' => $reply]);

        } catch (\Exception $e) {
            Log::error('Error con OpenAI Assistant: ' . $e->getMessage());
            return response()->json(['error' => 'Lo siento, no puedo responder en este momento.'], 500);
        }
    }

    public function resetChat(Request $request)
    {
        $request->session()->forget(['assistant_thread_id', 'assistant_message_count']);
        return response()->json(['status' => 'ok']);
    }

}