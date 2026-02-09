<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChatApiController extends Controller
{
    public function handle(Request $request)
    {
        $data = $request->validate([
            'chat_id' => ['required', 'integer', 'exists:chats,id'],
            'message' => ['required', 'string'],
            'template' => ['nullable', 'string', 'in:portrait,cityscape,product,cinematic'],
        ]);

        $user = Auth::user();
        $chat = Chat::where('id', $data['chat_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $userMessage = $data['message'];
        $template = $data['template'] ?? null;

        // Kaydet: user mesajı
        Message::create([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
            'role' => 'user',
            'content' => $userMessage,
        ]);

        // İlk taslak mesajıysa sohbet başlığını güncelle
        if ($chat->title === 'Yeni Sohbet') {
            $firstUserMessageCount = $chat->messages()->where('role', 'user')->count();
            if ($firstUserMessageCount === 1) {
                $chat->title = Str::limit($userMessage, 50);
                $chat->save();
            }
        }

        $history = $chat->messages()->get();

        $geminiResponse = $this->callGemini($history, $userMessage, $template);

        // Asistan mesajını da kaydet
        Message::create([
            'chat_id' => $chat->id,
            'user_id' => null,
            'role' => 'assistant',
            'content' => $geminiResponse['assistantMessage'] ?? '',
            'metadata' => [
                'currentPrompt' => $geminiResponse['currentPrompt'] ?? '',
                'realismScore' => $geminiResponse['realismScore'] ?? 0,
                'negativePrompt' => $geminiResponse['negativePrompt'] ?? '',
                'isFinal' => $geminiResponse['isFinal'] ?? false,
            ],
        ]);

        return response()->json($geminiResponse);
    }

    protected function callGemini($history, string $userMessage, ?string $template = null): array
    {
        $apiKey = config('services.gemini.key');

        if (! $apiKey) {
            return [
                'assistantMessage' => 'Sunucu tarafında Gemini API anahtarı ayarlı değil.',
                'nextQuestion' => '',
                'currentPrompt' => '',
                'realismScore' => 0,
                'isFinal' => false,
                'negativePrompt' => '',
            ];
        }

        $systemInstruction = config('services.gemini.system_instruction');

        // Template desteği ekle
        if ($template && count($history) <= 2) {
            $templateInstructions = [
                'portrait' => 'Focus on portrait photography: soft lighting, warm tones, shallow depth of field, professional composition.',
                'cityscape' => 'Focus on cityscape photography: architectural details, cinematic composition, day or night scenes, urban atmosphere.',
                'product' => 'Focus on product photography: clean studio lighting, minimal background, professional composition, commercial quality.',
                'cinematic' => 'Focus on cinematic scene creation: dramatic lighting, film-like composition, storytelling elements, movie quality.',
            ];

            if (isset($templateInstructions[$template])) {
                $systemInstruction .= "\n\nTemplate Context: {$templateInstructions[$template]}";
            }
        }

        $contents = [];

        if ($systemInstruction) {
            $contents[] = [
                'role' => 'user',
                'parts' => [
                    ['text' => $systemInstruction],
                ],
            ];
        }

        foreach ($history as $msg) {
            $contents[] = [
                'role' => $msg->role === 'assistant' ? 'model' : 'user',
                'parts' => [
                    ['text' => $msg->content],
                ],
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [
                ['text' => $userMessage],
            ],
        ];

        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

        $payload = [
            'contents' => $contents,
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($endpoint.'?key='.$apiKey, $payload);

        if (! $response->successful()) {
            $statusCode = $response->status();
            $errorMessage = 'Gemini isteğinde bir hata oluştu.';
            $retryAfter = null;
            
            if ($statusCode === 429) {
                // Retry-After header'ını kontrol et (saniye cinsinden)
                $retryAfterHeader = $response->header('Retry-After');
                if ($retryAfterHeader) {
                    $retryAfter = (int) $retryAfterHeader;
                }
                
                // Rate limit açıklaması
                $waitTime = $retryAfter ? "{$retryAfter} saniye" : "birkaç saniye";
                $errorMessage = "Çok fazla istek gönderildi (Rate Limit).\n\n";
                $errorMessage .= "🔴 Ücretsiz Gemini API limiti aşıldı.\n";
                $errorMessage .= "⏱️ Lütfen {$waitTime} bekleyip tekrar deneyin.\n\n";
                $errorMessage .= "💡 İpucu: Gemini API'nin ücretsiz versiyonu dakikada ~15-60 istek sınırına sahiptir. ";
                $errorMessage .= "Daha fazla kullanım için Google Cloud Console'dan API kotanızı kontrol edebilir veya ücretli plana geçebilirsiniz.";
            } elseif ($statusCode === 400) {
                $errorMessage = 'Geçersiz istek. Lütfen mesajınızı kontrol edin.';
            } elseif ($statusCode === 500 || $statusCode === 503) {
                $errorMessage = 'Gemini servisi şu anda kullanılamıyor. Lütfen daha sonra tekrar deneyin.';
            }
            
            return [
                'assistantMessage' => $errorMessage,
                'nextQuestion' => '',
                'currentPrompt' => '',
                'realismScore' => 0,
                'isFinal' => false,
                'negativePrompt' => '',
                'retryAfter' => $retryAfter,
                'errorCode' => $statusCode,
            ];
        }

        $text = $response->json('candidates.0.content.parts.0.text') ?? '';

        try {
            $cleaned = trim(str_replace(['```json', '```'], '', $text));
            $parsed = json_decode($cleaned, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            return [
                'assistantMessage' => 'Teknik bir hata oluştu, lütfen tekrar dener misin?',
                'nextQuestion' => '',
                'currentPrompt' => '',
                'realismScore' => 0,
                'isFinal' => false,
                'negativePrompt' => '',
            ];
        }

        return [
            'assistantMessage' => (string) ($parsed['assistantMessage'] ?? ''),
            'nextQuestion' => (string) ($parsed['nextQuestion'] ?? ''),
            'currentPrompt' => (string) ($parsed['currentPrompt'] ?? ''),
            'realismScore' => (int) ($parsed['realismScore'] ?? 0),
            'isFinal' => (bool) ($parsed['isFinal'] ?? false),
            'negativePrompt' => (string) ($parsed['negativePrompt'] ?? ''),
        ];
    }

    public function generateVariations(Request $request)
    {
        $data = $request->validate([
            'chat_id' => ['required', 'integer', 'exists:chats,id'],
            'base_prompt' => ['required', 'string'],
        ]);

        $user = Auth::user();
        $chat = Chat::where('id', $data['chat_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $apiKey = config('services.gemini.key');
        if (! $apiKey) {
            return response()->json(['error' => 'API key not configured'], 500);
        }

        $variationPrompt = <<<TXT
Aşağıdaki profesyonel görsel promptunu, aynı sahneyi koruyarak ama farklı teknik parametrelerle (ışık, lens, hava durumu, renk paleti) 3 farklı varyasyon üret.

Base Prompt: {$data['base_prompt']}

Her varyasyon için şu JSON formatında cevap ver:
{
  "variations": [
    {
      "title": "Varyasyon 1 başlığı (kısa açıklama)",
      "prompt": "Tam İngilizce prompt",
      "negativePrompt": "Negatif prompt",
      "changes": "Bu varyasyonda değişenler: ışık, lens vb."
    },
    {
      "title": "Varyasyon 2 başlığı",
      "prompt": "Tam İngilizce prompt",
      "negativePrompt": "Negatif prompt",
      "changes": "Bu varyasyonda değişenler"
    },
    {
      "title": "Varyasyon 3 başlığı",
      "prompt": "Tam İngilizce prompt",
      "negativePrompt": "Negatif prompt",
      "changes": "Bu varyasyonda değişenler"
    }
  ]
}

Sadece JSON döndür, başka metin ekleme.
TXT;

        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($endpoint.'?key='.$apiKey, [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [['text' => $variationPrompt]],
                ],
            ],
        ]);

        if (! $response->successful()) {
            return response()->json(['error' => 'Variation generation failed'], 500);
        }

        $text = $response->json('candidates.0.content.parts.0.text') ?? '';

        try {
            $cleaned = trim(str_replace(['```json', '```'], '', $text));
            $parsed = json_decode($cleaned, true, 512, JSON_THROW_ON_ERROR);
            return response()->json($parsed);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Failed to parse variations'], 500);
        }
    }
}

