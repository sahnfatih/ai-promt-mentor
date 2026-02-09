<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\ChatApiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Google OAuth
Route::get('/auth/google/redirect', function () {
    return Socialite::driver('google')->redirect();
})->name('auth.google.redirect');

Route::get('/auth/google/callback', function () {
    $googleUser = Socialite::driver('google')->user();

    $user = User::updateOrCreate(
        ['email' => $googleUser->getEmail()],
        [
            'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: $googleUser->getEmail(),
            'password' => bcrypt(str()->random(40)),
        ],
    );

    Auth::login($user, true);

    return redirect()->route('dashboard');
})->name('auth.google.callback');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [ChatController::class, 'index'])->name('dashboard');
    Route::get('/chats/{chat:token}', [ChatController::class, 'show'])->name('chats.show');
    Route::post('/chats', [ChatController::class, 'store'])->name('chats.store');
    Route::put('/chats/{chat:token}', [ChatController::class, 'update'])->name('chats.update');
    Route::delete('/chats/{chat:token}', [ChatController::class, 'destroy'])->name('chats.destroy');

    Route::post('/prompt-mentor', [ChatApiController::class, 'handle'])->name('prompt-mentor.handle');
    Route::post('/prompt-mentor/variations', [ChatApiController::class, 'generateVariations'])->name('prompt-mentor.variations');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'can:isAdmin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.index');

    // Kullanıcı yönetimi
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/admin/users/{user}', [AdminController::class, 'showUser'])->name('admin.users.show');
    Route::post('/admin/users/{user}/toggle-admin', [AdminController::class, 'toggleAdmin'])->name('admin.users.toggle-admin');
    Route::post('/admin/users/{user}/toggle-ban', [AdminController::class, 'toggleBan'])->name('admin.users.toggle-ban');
    Route::post('/admin/users/{user}/reset-password', [AdminController::class, 'resetPassword'])->name('admin.users.reset-password');

    // Sohbet yönetimi
    Route::get('/admin/chats/{chat:token}', [AdminController::class, 'showChat'])->name('admin.chats.show');
    Route::delete('/admin/chats/{chat:token}', [AdminController::class, 'deleteChat'])->name('admin.chats.delete');

    // Sistem ayarları
    Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/admin/settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');

    // Loglar
    Route::get('/admin/logs', [AdminController::class, 'logs'])->name('admin.logs');
});
