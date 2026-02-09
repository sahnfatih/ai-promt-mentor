@php($title = 'Sistem Ayarları - Admin')

<x-layouts.app :title="$title">
  <div class="max-w-4xl mx-auto p-4 lg:p-6 space-y-4">
    <div class="flex items-center justify-between gap-3">
      <div>
        <h1 class="text-lg lg:text-xl font-semibold">Sistem Ayarları</h1>
        <p class="text-xs text-slate-400">Uygulama konfigürasyonunu yönet</p>
      </div>
      <a
        href="{{ route('admin.index') }}"
        class="px-3 py-1.5 rounded-xl border border-slate-700/80 text-[11px] hover:border-emerald-400 hover:text-emerald-300 transition"
      >
        ← Dashboard
      </a>
    </div>

    @if(session('status'))
    <div class="bg-emerald-950/60 border border-emerald-800 rounded-xl px-4 py-2 text-[11px] text-emerald-200">
      {{ session('status') }}
    </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4">
      @csrf

      <div class="bg-surface rounded-2xl border border-slate-800/80 p-4 lg:p-6 space-y-4">
        <h2 class="text-sm font-semibold">Gemini API Ayarları</h2>
        
        <div class="space-y-2">
          <label class="text-xs text-slate-300">Gemini Model</label>
          <select
            name="gemini_model"
            class="w-full rounded-xl border border-slate-700/80 bg-slate-950/70 px-3 py-2 text-xs focus:outline-none focus:border-emerald-400"
          >
            <option value="gemini-2.5-flash" {{ $settings['gemini_model'] === 'gemini-2.5-flash' ? 'selected' : '' }}>
              Gemini 2.5 Flash
            </option>
            <option value="gemini-1.5-pro" {{ $settings['gemini_model'] === 'gemini-1.5-pro' ? 'selected' : '' }}>
              Gemini 1.5 Pro
            </option>
          </select>
          <p class="text-[10px] text-slate-500">
            Kullanılacak Gemini model versiyonu
          </p>
        </div>
      </div>

      <div class="bg-surface rounded-2xl border border-slate-800/80 p-4 lg:p-6 space-y-4">
        <h2 class="text-sm font-semibold">Kullanıcı Limitleri</h2>
        
        <div class="space-y-2">
          <label class="text-xs text-slate-300">Kullanıcı Başına Maksimum Sohbet</label>
          <input
            type="number"
            name="max_chats_per_user"
            value="{{ $settings['max_chats_per_user'] }}"
            min="1"
            class="w-full rounded-xl border border-slate-700/80 bg-slate-950/70 px-3 py-2 text-xs focus:outline-none focus:border-emerald-400"
          />
          <p class="text-[10px] text-slate-500">
            Bir kullanıcının açabileceği maksimum sohbet sayısı
          </p>
        </div>

        <div class="space-y-2">
          <label class="text-xs text-slate-300">Dakikada Maksimum İstek (Rate Limit)</label>
          <input
            type="number"
            name="rate_limit_per_minute"
            value="{{ $settings['rate_limit_per_minute'] }}"
            min="1"
            class="w-full rounded-xl border border-slate-700/80 bg-slate-950/70 px-3 py-2 text-xs focus:outline-none focus:border-emerald-400"
          />
          <p class="text-[10px] text-slate-500">
            Kullanıcı başına dakikada izin verilen maksimum API isteği
          </p>
        </div>
      </div>

      <div class="flex items-center justify-end gap-3">
        <a
          href="{{ route('admin.index') }}"
          class="px-4 py-2 rounded-xl border border-slate-700/80 text-xs hover:border-slate-600 transition"
        >
          İptal
        </a>
        <button
          type="submit"
          class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-900 font-medium text-xs transition"
        >
          Ayarları Kaydet
        </button>
      </div>
    </form>
  </div>
</x-layouts.app>
