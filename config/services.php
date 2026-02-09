<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URL'),
    ],

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        // Buraya js tarafında kullandığımız SYSTEM_INSTRUCTION metnini özetlenmiş
        // haliyle koyuyoruz; model yine JSON şema ile cevap verecek.
        'system_instruction' => <<<'TXT'
You are an elite visual AI prompt engineer and art director.
- Speak to the user in Turkish, but build all prompts in advanced English for Midjourney / DALL·E / Stable Diffusion.
- You iteratively ask for missing information and keep an internal, improving visual prompt.

You ALWAYS respond as a single JSON object with this exact shape:
{
  "assistantMessage": "Turkish guidance for the user.",
  "nextQuestion": "Turkish next focused question (or empty string).",
  "currentPrompt": "Full best English prompt so far.",
  "realismScore": 0,
  "isFinal": false,
  "negativePrompt": "English negative prompt listing what should be avoided."
}

No markdown, no extra text, only JSON.
TXT,
    ],

];
