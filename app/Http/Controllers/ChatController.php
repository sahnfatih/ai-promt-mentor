<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->is_banned) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Hesabınız yasaklanmış.',
            ]);
        }

        $chats = $user->chats()->latest()->get();

        $activeChat = $chats->first();

        if (! $activeChat) {
            $activeChat = Chat::create([
                'user_id' => $user->id,
                'title' => 'Yeni Sohbet',
            ]);
            $chats = $user->chats()->latest()->get();
        }

        return view('dashboard', [
            'user' => $user,
            'chats' => $chats,
            'activeChat' => $activeChat->load('messages'),
        ]);
    }

    public function show(Chat $chat)
    {
        $user = Auth::user();

        if ($user->is_banned) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Hesabınız yasaklanmış.',
            ]);
        }

        abort_unless($chat->user_id === $user->id, 403);

        $chats = $user->chats()->latest()->get();

        return view('dashboard', [
            'user' => $user,
            'chats' => $chats,
            'activeChat' => $chat->load('messages'),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $chat = Chat::create([
            'user_id' => $user->id,
            'title' => $request->input('title') ?: 'Yeni Sohbet',
        ]);

        return redirect()->route('chats.show', $chat);
    }

    public function update(Request $request, Chat $chat)
    {
        $user = Auth::user();
        abort_unless($chat->user_id === $user->id, 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $chat->title = $validated['title'];
        $chat->save();

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'chat' => [
                    'id' => $chat->id,
                    'title' => $chat->title,
                    'token' => $chat->token,
                ],
            ]);
        }

        return redirect()->route('chats.show', $chat);
    }

    public function destroy(Chat $chat)
    {
        $user = Auth::user();
        abort_unless($chat->user_id === $user->id, 403);

        $chat->delete();

        return redirect()->route('dashboard');
    }
}

