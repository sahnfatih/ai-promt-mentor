@php($title = 'Sistem Logları - Admin')

<x-layouts.app :title="$title">
  <div class="max-w-6xl mx-auto p-4 lg:p-6 space-y-4">
    <div class="flex items-center justify-between gap-3">
      <div>
        <h1 class="text-lg lg:text-xl font-semibold">Sistem Logları</h1>
        <p class="text-xs text-slate-400">Uygulama hatalarını ve log kayıtlarını görüntüle</p>
      </div>
      <a
        href="{{ route('admin.index') }}"
        class="px-3 py-1.5 rounded-xl border border-slate-700/80 text-[11px] hover:border-emerald-400 hover:text-emerald-300 transition"
      >
        ← Dashboard
      </a>
    </div>

    <!-- API Error Stats -->
    <div class="bg-surface rounded-2xl border border-slate-800/80 p-4 lg:p-6">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-sm font-semibold">Son 24 Saat API Hataları</h2>
          <p class="text-[11px] text-slate-400 mt-1">Gemini API'den gelen hata sayısı</p>
        </div>
        <div class="text-2xl font-semibold text-rose-300">{{ $apiErrors }}</div>
      </div>
    </div>

    <!-- Log Viewer -->
    <div class="bg-surface rounded-2xl border border-slate-800/80 shadow-xl shadow-black/40 overflow-hidden">
      <div class="px-4 py-3 border-b border-slate-800/80 flex items-center justify-between">
        <h2 class="text-sm font-semibold">Laravel Log Dosyası (Son 50 Satır)</h2>
        <span class="text-[10px] text-slate-500">Sadece görüntüleme</span>
      </div>
      <div class="max-h-[calc(100vh-20rem)] overflow-y-auto">
        @if(count($logs) > 0)
        <pre class="p-4 text-[10px] lg:text-xs font-mono text-slate-300 whitespace-pre-wrap">@foreach($logs as $log){{ $log }}@endforeach</pre>
        @else
        <div class="p-6 text-center text-[11px] text-slate-500">
          Log dosyası bulunamadı veya boş.
        </div>
        @endif
      </div>
    </div>
  </div>
</x-layouts.app>
