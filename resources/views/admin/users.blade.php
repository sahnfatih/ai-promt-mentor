@php($title = 'Admin - Kullanıcılar')

<x-layouts.app :title="$title">
  <div class="max-w-7xl mx-auto p-4 lg:p-6 space-y-4">
    <div class="flex items-center justify-between gap-3">
      <div>
        <h1 class="text-lg lg:text-xl font-semibold">Kullanıcılar</h1>
        <p class="text-xs text-slate-400">Tüm kullanıcıları görüntüle ve yönet</p>
      </div>
      <a
        href="{{ route('admin.index') }}"
        class="px-3 py-1.5 rounded-xl border border-slate-700/80 text-[11px] hover:border-emerald-400 hover:text-emerald-300 transition"
      >
        ← Dashboard
      </a>
    </div>

    <!-- Filters & Search -->
    <div class="bg-surface rounded-2xl border border-slate-800/80 p-4">
      <form method="GET" action="{{ route('admin.users') }}" class="flex flex-col md:flex-row gap-3">
        <input
          type="text"
          name="search"
          value="{{ $search }}"
          placeholder="İsim veya e-posta ile ara..."
          class="flex-1 rounded-xl border border-slate-700/80 bg-slate-950/70 px-3 py-2 text-xs focus:outline-none focus:border-emerald-400"
        />
        <select
          name="filter"
          class="rounded-xl border border-slate-700/80 bg-slate-950/70 px-3 py-2 text-xs focus:outline-none focus:border-emerald-400"
        >
          <option value="">Tümü</option>
          <option value="admins" {{ $filter === 'admins' ? 'selected' : '' }}>Adminler</option>
          <option value="banned" {{ $filter === 'banned' ? 'selected' : '' }}>Yasaklılar</option>
          <option value="recent" {{ $filter === 'recent' ? 'selected' : '' }}>Son 7 Gün</option>
          <option value="active" {{ $filter === 'active' ? 'selected' : '' }}>Aktif Kullanıcılar</option>
        </select>
        <button
          type="submit"
          class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-900 font-medium text-xs transition"
        >
          Filtrele
        </button>
      </form>
    </div>

    @if(session('status'))
    <div class="bg-emerald-950/60 border border-emerald-800 rounded-xl px-4 py-2 text-[11px] text-emerald-200">
      {{ session('status') }}
    </div>
    @endif

    <div class="bg-surface rounded-2xl border border-slate-800/80 shadow-xl shadow-black/40 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full text-xs lg:text-sm">
          <thead class="bg-slate-900/60 border-b border-slate-800/80">
            <tr>
              <th class="text-left px-4 py-2 text-slate-400 font-medium">ID</th>
              <th class="text-left px-4 py-2 text-slate-400 font-medium">İsim</th>
              <th class="text-left px-4 py-2 text-slate-400 font-medium">E-posta</th>
              <th class="text-left px-4 py-2 text-slate-400 font-medium">Sohbet</th>
              <th class="text-left px-4 py-2 text-slate-400 font-medium">Durum</th>
              <th class="text-left px-4 py-2 text-slate-400 font-medium">Son Giriş</th>
              <th class="text-left px-4 py-2 text-slate-400 font-medium">Kayıt</th>
              <th class="text-left px-4 py-2 text-slate-400 font-medium">İşlemler</th>
            </tr>
          </thead>
          <tbody>
            @foreach($users as $user)
            <tr class="border-b border-slate-900/80 hover:bg-slate-900/40 transition">
              <td class="px-4 py-2 text-slate-300">{{ $user->id }}</td>
              <td class="px-4 py-2">
                <a
                  href="{{ route('admin.users.show', $user) }}"
                  class="text-slate-100 hover:text-emerald-300 transition"
                >
                  {{ $user->name }}
                </a>
              </td>
              <td class="px-4 py-2 text-slate-300">{{ $user->email }}</td>
              <td class="px-4 py-2 text-slate-300">{{ $user->chats_count }}</td>
              <td class="px-4 py-2">
                <div class="flex items-center gap-1 flex-wrap">
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
                  @if(!$user->is_admin && !$user->is_banned)
                  <span class="text-[10px] text-slate-500">Normal</span>
                  @endif
                </div>
              </td>
              <td class="px-4 py-2 text-slate-300 text-[11px]">
                {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Hiç giriş yapmamış' }}
              </td>
              <td class="px-4 py-2 text-slate-300 text-[11px]">
                {{ $user->created_at->format('d.m.Y') }}
              </td>
              <td class="px-4 py-2">
                <div class="flex items-center gap-1 flex-wrap">
                  <form
                    action="{{ route('admin.users.toggle-admin', $user) }}"
                    method="POST"
                    class="inline"
                  >
                    @csrf
                    <button
                      type="submit"
                      class="text-[10px] px-2 py-0.5 rounded border border-slate-700/80 hover:border-emerald-400 hover:text-emerald-300 transition"
                    >
                      {{ $user->is_admin ? 'Admin Al' : 'Admin Yap' }}
                    </button>
                  </form>
                  <form
                    action="{{ route('admin.users.toggle-ban', $user) }}"
                    method="POST"
                    class="inline"
                  >
                    @csrf
                    <button
                      type="submit"
                      class="text-[10px] px-2 py-0.5 rounded border border-slate-700/80 hover:border-rose-400 hover:text-rose-300 transition"
                    >
                      {{ $user->is_banned ? 'Yasak Kaldır' : 'Yasakla' }}
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="px-4 py-3 border-t border-slate-800/80 text-[11px] text-slate-400">
        {{ $users->links() }}
      </div>
    </div>
  </div>
</x-layouts.app>
