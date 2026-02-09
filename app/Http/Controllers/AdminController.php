<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'users' => User::count(),
            'chats' => Chat::count(),
            'messages' => Message::count(),
            'admins' => User::where('is_admin', true)->count(),
            'banned_users' => User::where('is_banned', true)->count(),
        ];

        // Son 7 günlük istatistikler
        $dailyStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dailyStats[$date] = [
                'users' => User::whereDate('created_at', $date)->count(),
                'chats' => Chat::whereDate('created_at', $date)->count(),
                'messages' => Message::whereDate('created_at', $date)->count(),
            ];
        }

        $latestUsers = User::latest()->take(5)->get();
        $latestChats = Chat::with('user')->latest()->take(5)->get();
        $topUsers = User::withCount('chats')
            ->orderBy('chats_count', 'desc')
            ->take(10)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'dailyStats' => $dailyStats,
            'latestUsers' => $latestUsers,
            'latestChats' => $latestChats,
            'topUsers' => $topUsers,
        ]);
    }

    public function users(Request $request)
    {
        $query = User::withCount('chats');

        // Arama
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtreler
        if ($request->has('filter')) {
            $filter = $request->get('filter');
            if ($filter === 'admins') {
                $query->where('is_admin', true);
            } elseif ($filter === 'banned') {
                $query->where('is_banned', true);
            } elseif ($filter === 'recent') {
                $query->where('created_at', '>=', now()->subDays(7));
            } elseif ($filter === 'active') {
                $query->where('last_login_at', '>=', now()->subDays(7));
            }
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.users', [
            'users' => $users,
            'search' => $request->get('search', ''),
            'filter' => $request->get('filter', ''),
        ]);
    }

    public function showUser(User $user)
    {
        $user->loadCount('chats');

        $messagesCount = Message::whereIn('chat_id', $user->chats()->pluck('id'))->count();

        $chats = $user->chats()
            ->withCount('messages')
            ->latest()
            ->paginate(10);

        return view('admin.user-detail', [
            'user' => $user,
            'chats' => $chats,
            'messagesCount' => $messagesCount,
        ]);
    }

    public function showChat(Chat $chat)
    {
        $chat->load(['user', 'messages.user']);

        return view('admin.chat-detail', [
            'chat' => $chat,
        ]);
    }

    public function toggleAdmin(User $user)
    {
        $user->is_admin = !$user->is_admin;
        $user->save();

        return back()->with('status', $user->is_admin
            ? 'Kullanıcı admin yapıldı.'
            : 'Kullanıcının admin yetkisi kaldırıldı.');
    }

    public function toggleBan(User $user)
    {
        $user->is_banned = !$user->is_banned;
        $user->save();

        return back()->with('status', $user->is_banned
            ? 'Kullanıcı yasaklandı.'
            : 'Kullanıcının yasağı kaldırıldı.');
    }

    public function resetPassword(User $user)
    {
        $newPassword = str()->random(12);
        $user->password = Hash::make($newPassword);
        $user->save();

        return back()->with('status', "Yeni şifre: {$newPassword} (Kullanıcıya bildirmeyi unutma!)");
    }

    public function deleteChat(Chat $chat)
    {
        $chat->delete();

        return back()->with('status', 'Sohbet başarıyla silindi.');
    }

    public function settings()
    {
        $settings = [
            'gemini_model' => config('services.gemini.model', 'gemini-2.5-flash'),
            'max_chats_per_user' => config('app.max_chats_per_user', 100),
            'rate_limit_per_minute' => config('app.rate_limit_per_minute', 30),
        ];

        return view('admin.settings', [
            'settings' => $settings,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'gemini_model' => ['required', 'string'],
            'max_chats_per_user' => ['required', 'integer', 'min:1'],
            'rate_limit_per_minute' => ['required', 'integer', 'min:1'],
        ]);

        // Not: Gerçek bir uygulamada bu ayarları database'de saklamak daha mantıklı
        // Şimdilik sadece görüntüleme yapıyoruz

        return back()->with('status', 'Ayarlar güncellendi (Not: Bu özellik henüz tam olarak aktif değil).');
    }

    public function logs()
    {
        $logFile = storage_path('logs/laravel.log');
        $logs = [];

        if (file_exists($logFile)) {
            $lines = file($logFile);
            $logs = array_slice($lines, -50); // Son 50 satır
            $logs = array_reverse($logs);
        }

        // Son 24 saatteki Gemini API hatalarını say
        $apiErrors = Message::where('role', 'assistant')
            ->where(function ($query) {
                $query->where('content', 'like', '%hata%')
                    ->orWhere('content', 'like', '%error%')
                    ->orWhere('content', 'like', '%Hata%')
                    ->orWhere('content', 'like', '%Error%');
            })
            ->where('created_at', '>=', now()->subDay())
            ->count();

        return view('admin.logs', [
            'logs' => $logs,
            'apiErrors' => $apiErrors,
        ]);
    }
}
