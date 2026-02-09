@php($title = 'Admin Dashboard - AI Prompt Mentor')

<x-layouts.app :title="$title">
  <div class="max-w-7xl mx-auto p-4 lg:p-6 space-y-6">
    <div class="flex items-center justify-between gap-3">
      <div>
        <h1 class="text-lg lg:text-2xl font-semibold">Admin Dashboard</h1>
        <p class="text-xs lg:text-sm text-slate-400">
          Sistem genel istatistikleri ve son aktiviteleri buradan takip edebilirsin.
        </p>
      </div>
      <div class="flex items-center gap-2">
        <a
          href="{{ route('admin.users') }}"
          class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl border border-slate-700/80 text-[11px] lg:text-xs hover:border-emerald-400 hover:text-emerald-300 transition"
        >
          Kullanıcılar
        </a>
        <a
          href="{{ route('admin.settings') }}"
          class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl border border-slate-700/80 text-[11px] lg:text-xs hover:border-emerald-400 hover:text-emerald-300 transition"
        >
          Ayarlar
        </a>
        <a
          href="{{ route('admin.logs') }}"
          class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl border border-slate-700/80 text-[11px] lg:text-xs hover:border-emerald-400 hover:text-emerald-300 transition"
        >
          Loglar
        </a>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 lg:gap-4">
      <div class="bg-surface rounded-2xl border border-slate-800/80 px-4 py-3">
        <div class="text-[11px] text-slate-400 mb-1">Toplam Kullanıcı</div>
        <div class="text-xl lg:text-2xl font-semibold text-emerald-300">
          {{ $stats['users'] }}
        </div>
      </div>
      <div class="bg-surface rounded-2xl border border-slate-800/80 px-4 py-3">
        <div class="text-[11px] text-slate-400 mb-1">Toplam Sohbet</div>
        <div class="text-xl lg:text-2xl font-semibold text-slate-100">
          {{ $stats['chats'] }}
        </div>
      </div>
      <div class="bg-surface rounded-2xl border border-slate-800/80 px-4 py-3">
        <div class="text-[11px] text-slate-400 mb-1">Toplam Mesaj</div>
        <div class="text-xl lg:text-2xl font-semibold text-slate-100">
          {{ $stats['messages'] }}
        </div>
      </div>
      <div class="bg-surface rounded-2xl border border-slate-800/80 px-4 py-3">
        <div class="text-[11px] text-slate-400 mb-1">Admin Sayısı</div>
        <div class="text-xl lg:text-2xl font-semibold text-slate-100">
          {{ $stats['admins'] }}
        </div>
      </div>
      <div class="bg-surface rounded-2xl border border-slate-800/80 px-4 py-3">
        <div class="text-[11px] text-slate-400 mb-1">Yasaklı Kullanıcı</div>
        <div class="text-xl lg:text-2xl font-semibold text-rose-300">
          {{ $stats['banned_users'] }}
        </div>
      </div>
    </div>

    <!-- Daily Stats Chart -->
    <div class="bg-surface rounded-2xl border border-slate-800/80 shadow-xl shadow-black/40 p-4 lg:p-6">
      <h2 class="text-sm lg:text-base font-semibold mb-4">Son 7 Günlük Aktivite</h2>
      <div class="grid grid-cols-7 gap-2 text-[10px] lg:text-xs">
        @foreach($dailyStats as $date => $dayStats)
        <div class="flex flex-col items-center gap-1">
          <div class="text-slate-400">{{ \Carbon\Carbon::parse($date)->format('d.m') }}</div>
          <div class="w-full bg-slate-900 rounded-lg p-2 space-y-1">
            <div class="text-emerald-300">{{ $dayStats['users'] }} K</div>
            <div class="text-slate-300">{{ $dayStats['chats'] }} S</div>
            <div class="text-slate-400">{{ $dayStats['messages'] }} M</div>
          </div>
        </div>
        @endforeach
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 lg:gap-6">
      <!-- Latest users -->
      <div class="bg-surface rounded-2xl border border-slate-800/80 shadow-xl shadow-black/40">
        <div class="px-4 py-3 border-b border-slate-800/80 flex items-center justify-between">
          <h2 class="text-xs lg:text-sm font-semibold text-slate-100">
            Son Kayıt Olanlar
          </h2>
        </div>
        <div class="divide-y divide-slate-900/80 text-xs lg:text-sm">
          @forelse($latestUsers as $user)
          <a
            href="{{ route('admin.users.show', $user) }}"
            class="block px-4 py-2.5 flex items-center justify-between gap-3 hover:bg-slate-900/60 transition"
          >
            <div>
              <div class="text-slate-100">{{ $user->name }}</div>
              <div class="text-[11px] text-slate-400">{{ $user->email }}</div>
            </div>
            <div class="text-[11px] text-slate-500">
              {{ $user->created_at->diffForHumans() }}
            </div>
          </a>
          @empty
          <div class="px-4 py-3 text-[11px] text-slate-500">
            Henüz kullanıcı bulunmuyor.
          </div>
          @endforelse
        </div>
      </div>

      <!-- Latest chats -->
      <div class="bg-surface rounded-2xl border border-slate-800/80 shadow-xl shadow-black/40">
        <div class="px-4 py-3 border-b border-slate-800/80 flex items-center justify-between">
          <h2 class="text-xs lg:text-sm font-semibold text-slate-100">
            Son Sohbetler
          </h2>
        </div>
        <div class="divide-y divide-slate-900/80 text-xs lg:text-sm">
          @forelse($latestChats as $chat)
          <a
            href="{{ route('admin.chats.show', $chat) }}"
            class="block px-4 py-2.5 flex items-center justify-between gap-3 hover:bg-slate-900/60 transition"
          >
            <div class="min-w-0">
              <div class="text-slate-100 truncate">
                {{ $chat->title ?? 'Sohbet #'.$chat->id }}
              </div>
              <div class="text-[11px] text-slate-400 truncate">
                {{ $chat->user?->name ?? 'Bilinmeyen kullanıcı' }}
              </div>
            </div>
            <div class="text-[11px] text-slate-500 text-right">
              {{ $chat->created_at->diffForHumans() }}
            </div>
          </a>
          @empty
          <div class="px-4 py-3 text-[11px] text-slate-500">
            Henüz sohbet bulunmuyor.
          </div>
          @endforelse
        </div>
      </div>

      <!-- Top users -->
      <div class="bg-surface rounded-2xl border border-slate-800/80 shadow-xl shadow-black/40">
        <div class="px-4 py-3 border-b border-slate-800/80 flex items-center justify-between">
          <h2 class="text-xs lg:text-sm font-semibold text-slate-100">
            En Aktif Kullanıcılar
          </h2>
        </div>
        <div class="divide-y divide-slate-900/80 text-xs lg:text-sm">
          @forelse($topUsers as $user)
          <a
            href="{{ route('admin.users.show', $user) }}"
            class="block px-4 py-2.5 flex items-center justify-between gap-3 hover:bg-slate-900/60 transition"
          >
            <div>
              <div class="text-slate-100">{{ $user->name }}</div>
              <div class="text-[11px] text-slate-400">{{ $user->email }}</div>
            </div>
            <div class="text-[11px] text-emerald-300 font-semibold">
              {{ $user->chats_count }} sohbet
            </div>
          </a>
          @empty
          <div class="px-4 py-3 text-[11px] text-slate-500">
            Henüz kullanıcı bulunmuyor.
          </div>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</x-layouts.app>
