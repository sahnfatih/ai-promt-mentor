@php($title = 'Kullanıcı Detayı - Admin')

<x-layouts.app :title="$title">
  <div class="max-w-6xl mx-auto p-4 lg:p-6 space-y-4">
    <div class="flex items-center justify-between gap-3">
      <div>
        <h1 class="text-lg lg:text-xl font-semibold">{{ $user->name }}</h1>
        <p class="text-xs text-slate-400">{{ $user->email }}</p>
      </div>
      <a
        href="{{ route('admin.users') }}"
        class="px-3 py-1.5 rounded-xl border border-slate-700/80 text-[11px] hover:border-emerald-400 hover:text-emerald-300 transition"
      >
        ← Kullanıcı Listesi
      </a>
    </div>

    @if(session('status'))
    <div class="bg-emerald-950/60 border border-emerald-800 rounded-xl px-4 py-2 text-[11px] text-emerald-200">
      {{ session('status') }}
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <!-- User Info -->
      <div class="bg-surface rounded-2xl border border-slate-800/80 p-4 lg:p-6 space-y-4">
        <h2 class="text-sm font-semibold">Kullanıcı Bilgileri</h2>
        <div class="space-y-2 text-xs">
          <div>
            <div class="text-slate-400">ID</div>
            <div class="text-slate-100">{{ $user->id }}</div>
          </div>
          <div>
            <div class="text-slate-400">E-posta</div>
            <div class="text-slate-100">{{ $user->email }}</div>
          </div>
          <div>
            <div class="text-slate-400">Kayıt Tarihi</div>
            <div class="text-slate-100">{{ $user->created_at->format('d.m.Y H:i') }}</div>
          </div>
          <div>
            <div class="text-slate-400">Son Giriş</div>
            <div class="text-slate-100">
              {{ $user->last_login_at ? $user->last_login_at->format('d.m.Y H:i') : 'Hiç giriş yapmamış' }}
            </div>
          </div>
          <div>
            <div class="text-slate-400">Durum</div>
            <div class="flex items-center gap-1 flex-wrap mt-1">
              @if($user->is_admin)
              <span
                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] bg-emerald-500/10 text-emerald-300 border border-emerald-500/60"
                >Admin</span
              >
              @endif
              @if($user->is_banned)
              <span
                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] bg-rose-500/10 text-rose-300 border border-rose-500/60"
                >Yasaklı</span
              >
              @endif
            </div>
          </div>
        </div>

        <div class="pt-4 border-t border-slate-800/80 space-y-2">
          <h3 class="text-xs font-semibold text-slate-300">İstatistikler</h3>
          <div class="grid grid-cols-2 gap-2 text-xs">
            <div>
              <div class="text-slate-400">Toplam Sohbet</div>
              <div class="text-emerald-300 font-semibold">{{ $user->chats_count }}</div>
            </div>
            <div>
              <div class="text-slate-400">Toplam Mesaj</div>
              <div class="text-slate-100 font-semibold">{{ $messagesCount ?? 0 }}</div>
            </div>
          </div>
        </div>

        <div class="pt-4 border-t border-slate-800/80 space-y-2">
          <h3 class="text-xs font-semibold text-slate-300">İşlemler</h3>
          <div class="flex flex-col gap-2">
            <form action="{{ route('admin.users.toggle-admin', $user) }}" method="POST">
              @csrf
              <button
                type="submit"
                class="w-full text-left px-3 py-1.5 rounded-lg border border-slate-700/80 hover:border-emerald-400 hover:text-emerald-300 text-[11px] transition"
              >
                {{ $user->is_admin ? 'Admin Yetkisini Kaldır' : 'Admin Yap' }}
              </button>
            </form>
            <form action="{{ route('admin.users.toggle-ban', $user) }}" method="POST">
              @csrf
              <button
                type="submit"
                class="w-full text-left px-3 py-1.5 rounded-lg border border-slate-700/80 hover:border-rose-400 hover:text-rose-300 text-[11px] transition"
              >
                {{ $user->is_banned ? 'Yasağı Kaldır' : 'Yasakla' }}
              </button>
            </form>
            <form action="{{ route('admin.users.reset-password', $user) }}" method="POST">
              @csrf
              <button
                type="submit"
                class="w-full text-left px-3 py-1.5 rounded-lg border border-slate-700/80 hover:border-yellow-400 hover:text-yellow-300 text-[11px] transition"
              >
                Şifre Sıfırla
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- Chats -->
      <div class="md:col-span-2 bg-surface rounded-2xl border border-slate-800/80 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-800/80">
          <h2 class="text-sm font-semibold">Sohbetler ({{ $chats->total() }})</h2>
        </div>
        <div class="divide-y divide-slate-900/80">
          @forelse($chats as $chat)
          <a
            href="{{ route('admin.chats.show', $chat) }}"
            class="block px-4 py-3 hover:bg-slate-900/60 transition"
          >
            <div class="flex items-center justify-between gap-3">
              <div class="min-w-0 flex-1">
                <div class="text-sm text-slate-100 truncate">
                  {{ $chat->title ?? 'Sohbet #'.$chat->id }}
                </div>
                <div class="text-[11px] text-slate-400 mt-0.5">
                  {{ $chat->messages_count }} mesaj · {{ $chat->created_at->diffForHumans() }}
                </div>
              </div>
              <div class="text-[11px] text-slate-500">
                {{ $chat->created_at->format('d.m.Y') }}
              </div>
            </div>
          </a>
          @empty
          <div class="px-4 py-6 text-center text-[11px] text-slate-500">
            Bu kullanıcının henüz sohbeti yok.
          </div>
          @endforelse
        </div>
        <div class="px-4 py-3 border-t border-slate-800/80">
          {{ $chats->links() }}
        </div>
      </div>
    </div>
  </div>
</x-layouts.app>
