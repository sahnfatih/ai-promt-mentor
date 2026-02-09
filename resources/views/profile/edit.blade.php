@php($title = 'Profilini Düzenle - AI Prompt Mentor')

<x-layouts.app :title="$title">
  <div class="flex items-center justify-center py-10">
    <div
      class="w-full max-w-lg bg-surface rounded-2xl border border-slate-800/80 shadow-xl shadow-black/40 p-6"
    >
      <h1 class="text-lg font-semibold mb-1">Profilini Düzenle</h1>
      <p class="text-xs text-slate-400 mb-5">
        Hesap bilgilerini güncelle ve istersen şifreni değiştir.
      </p>

      @if(session('status'))
      <div
        class="mb-4 text-[11px] text-emerald-200 bg-emerald-950/60 border border-emerald-800 rounded-xl px-3 py-2"
      >
        {{ session('status') }}
      </div>
      @endif

      @if($errors->any())
      <div
        class="mb-4 text-[11px] text-rose-200 bg-rose-950/60 border border-rose-800 rounded-xl px-3 py-2"
      >
        {{ $errors->first() }}
      </div>
      @endif

      <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
        @csrf
        <div class="space-y-1 text-xs">
          <label for="name" class="text-slate-300">Ad Soyad</label>
          <input
            type="text"
            id="name"
            name="name"
            value="{{ old('name', $user->name) }}"
            required
            class="w-full rounded-xl border border-slate-700/80 bg-slate-950/70 px-3 py-2 text-xs focus:outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/40"
          />
        </div>

        <div class="space-y-1 text-xs">
          <label for="email" class="text-slate-300">E-posta</label>
          <input
            type="email"
            id="email"
            name="email"
            value="{{ old('email', $user->email) }}"
            required
            class="w-full rounded-xl border border-slate-700/80 bg-slate-950/70 px-3 py-2 text-xs focus:outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/40"
          />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-1 text-xs">
            <label for="password" class="text-slate-300"
              >Yeni Şifre <span class="text-slate-500">(opsiyonel)</span></label
            >
            <input
              type="password"
              id="password"
              name="password"
              class="w-full rounded-xl border border-slate-700/80 bg-slate-950/70 px-3 py-2 text-xs focus:outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/40"
            />
          </div>
          <div class="space-y-1 text-xs">
            <label for="password_confirmation" class="text-slate-300"
              >Yeni Şifre Tekrar</label
            >
            <input
              type="password"
              id="password_confirmation"
              name="password_confirmation"
              class="w-full rounded-xl border border-slate-700/80 bg-slate-950/70 px-3 py-2 text-xs focus:outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/40"
            />
          </div>
        </div>

        <div class="flex items-center justify-between pt-2">
          <a
            href="{{ route('dashboard') }}"
            class="text-[11px] text-slate-400 hover:text-slate-200"
          >
            ← Dashboard'a geri dön
          </a>

          <button
            type="submit"
            class="inline-flex items-center justify-center rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-900 font-medium text-xs py-2.5 px-4 transition"
          >
            Kaydet
          </button>
        </div>
      </form>
    </div>
  </div>
</x-layouts.app>

