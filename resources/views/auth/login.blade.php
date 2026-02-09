@php($title = 'Giriş Yap - AI Prompt Mentor')

<x-layouts.app :title="$title">
  <div class="flex items-center justify-center py-10">
    <div
      class="w-full max-w-md bg-surface rounded-2xl border border-slate-800/80 shadow-xl shadow-black/40 p-6"
    >
      <h1 class="text-lg font-semibold mb-1">Giriş Yap</h1>
      <p class="text-xs text-slate-400 mb-4">
        Hesabınla giriş yap veya Google ile devam et.
      </p>

      {{-- Google ile giriş --}}
      <div class="mb-4">
        <a
          href="{{ route('auth.google.redirect') }}"
          class="w-full inline-flex items-center justify-center gap-2 rounded-xl border border-slate-700/80 bg-slate-950/70 hover:bg-slate-900/80 text-xs text-slate-100 py-2.5 transition"
        >
          <span
            class="h-4 w-4 rounded-full bg-white flex items-center justify-center text-[10px] text-slate-900 font-bold"
          >
            G
          </span>
          <span>Google ile devam et</span>
        </a>
      </div>

      <div
        class="flex items-center gap-2 my-3 text-[10px] text-slate-500"
      >
        <div class="h-px flex-1 bg-slate-800"></div>
        <span>veya e-posta ile giriş yap</span>
        <div class="h-px flex-1 bg-slate-800"></div>
      </div>

      @if($errors->any())
      <div
        class="mb-4 text-[11px] text-rose-200 bg-rose-950/60 border border-rose-800 rounded-xl px-3 py-2"
      >
        {{ $errors->first() }}
      </div>
      @endif

      <form method="POST" action="{{ url('/login') }}" class="space-y-4">
        @csrf
        <div class="space-y-1 text-xs">
          <label for="email" class="text-slate-300">E-posta</label>
          <input
            type="email"
            id="email"
            name="email"
            value="{{ old('email') }}"
            required
            autofocus
            class="w-full rounded-xl border border-slate-700/80 bg-slate-950/70 px-3 py-2 text-xs focus:outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/40"
          />
        </div>
        <div class="space-y-1 text-xs">
          <label for="password" class="text-slate-300">Şifre</label>
          <input
            type="password"
            id="password"
            name="password"
            required
            class="w-full rounded-xl border border-slate-700/80 bg-slate-950/70 px-3 py-2 text-xs focus:outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/40"
          />
        </div>
        <div class="flex items-center justify-between text-[11px]">
          <label class="inline-flex items-center gap-1 text-slate-400">
            <input
              type="checkbox"
              name="remember"
              class="h-3 w-3 rounded border-slate-700 bg-slate-950 text-emerald-500 focus:ring-emerald-500/60"
            />
            <span>Beni hatırla</span>
          </label>
          <a
            href="{{ route('register') }}"
            class="text-emerald-300 hover:text-emerald-200"
          >
            Hesabın yok mu?
          </a>
        </div>
        <button
          type="submit"
          class="w-full mt-2 inline-flex items-center justify-center rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-900 font-medium text-xs py-2.5 transition"
        >
          Giriş Yap
        </button>
      </form>
    </div>
  </div>
</x-layouts.app>

