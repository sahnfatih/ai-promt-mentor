@php($title = 'Dashboard - AI Prompt Mentor')

<x-layouts.app :title="$title">
  <div
    class="h-[calc(100vh-3.5rem)] flex flex-col lg:flex-row gap-4 lg:gap-6 p-4 lg:p-6 max-w-7xl mx-auto"
    x-data
  >
    <!-- Sidebar: Chats -->
    <aside
      class="w-full lg:w-60 xl:w-72 bg-surface rounded-2xl border border-slate-800/80 shadow-xl shadow-black/40 flex flex-col"
    >
      <div
        class="px-4 py-3 border-b border-slate-800/80 flex items-center justify-between gap-2"
      >
        <h2 class="text-xs font-semibold text-slate-200 tracking-wide">
          Sohbetler
        </h2>
        <form action="{{ route('chats.store') }}" method="POST" class="inline">
          @csrf
          <button
            type="submit"
            class="text-[10px] px-2.5 py-1.5 rounded-lg border border-slate-700/80 hover:border-emerald-400 hover:text-emerald-300 transition"
            title="Yeni sohbet başlat"
          >
            + Yeni
          </button>
        </form>
      </div>
      <div class="flex-1 overflow-y-auto text-xs" id="chats-list">
        @forelse($chats as $chat)
        <div
          class="group relative px-4 py-2.5 border-b border-slate-900/80 hover:bg-slate-900/60 transition {{ $activeChat && $chat->id === $activeChat->id ? 'bg-slate-900/80' : '' }}"
          data-chat-id="{{ $chat->id }}"
          data-chat-token="{{ $chat->token }}"
        >
          <a
            href="{{ route('chats.show', $chat) }}"
            class="block pr-8"
          >
            <div class="flex items-center justify-between gap-2">
              <span class="truncate text-slate-100 chat-title" data-chat-id="{{ $chat->id }}">
                {{ $chat->title ?? 'Yeni Sohbet' }}
              </span>
            </div>
            <p class="mt-0.5 text-[10px] text-slate-500">
              {{ $chat->created_at->diffForHumans() }}
            </p>
          </a>

          <!-- Action buttons (show on hover) -->
          <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
            <button
              type="button"
              class="chat-edit-btn p-1 rounded hover:bg-slate-800 text-slate-400 hover:text-emerald-300 transition"
              data-chat-id="{{ $chat->id }}"
              data-chat-title="{{ $chat->title ?? 'Yeni Sohbet' }}"
              title="Sohbeti yeniden adlandır"
            >
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </button>
            <button
              type="button"
              class="chat-delete-btn p-1 rounded hover:bg-slate-800 text-slate-400 hover:text-rose-300 transition"
              data-chat-id="{{ $chat->id }}"
              data-chat-token="{{ $chat->token }}"
              data-chat-title="{{ $chat->title ?? 'Yeni Sohbet' }}"
              title="Sohbeti sil"
            >
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
        @empty
        <div class="px-4 py-8 text-center text-[11px] text-slate-500">
          Henüz sohbetiniz yok. Yeni bir sohbet başlatmak için "+ Yeni" butonuna tıklayın.
        </div>
        @endforelse
      </div>
    </aside>

    <!-- Edit Chat Modal -->
    <div
      id="edit-chat-modal"
      class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
    >
      <div class="bg-surface rounded-2xl border border-slate-800/80 shadow-xl shadow-black/40 w-full max-w-md p-6">
        <h3 class="text-sm font-semibold mb-4">Sohbeti Yeniden Adlandır</h3>
        <form id="edit-chat-form" method="POST">
          @csrf
          @method('PUT')
          <input
            type="text"
            id="edit-chat-title-input"
            name="title"
            required
            maxlength="255"
            class="w-full rounded-xl border border-slate-700/80 bg-slate-950/70 px-3 py-2 text-xs focus:outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/40 mb-4"
            placeholder="Sohbet başlığı"
          />
          <div class="flex items-center justify-end gap-2">
            <button
              type="button"
              id="cancel-edit-btn"
              class="px-4 py-2 rounded-xl border border-slate-700/80 text-xs hover:border-slate-600 transition"
            >
              İptal
            </button>
            <button
              type="submit"
              class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-900 font-medium text-xs transition"
            >
              Kaydet
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      id="delete-chat-modal"
      class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
    >
      <div class="bg-surface rounded-2xl border border-slate-800/80 shadow-xl shadow-black/40 w-full max-w-md p-6">
        <h3 class="text-sm font-semibold mb-2">Sohbeti Sil</h3>
        <p class="text-xs text-slate-400 mb-4">
          "<span id="delete-chat-title"></span>" sohbetini silmek istediğinize emin misiniz? Bu işlem geri alınamaz.
        </p>
        <form id="delete-chat-form" method="POST">
          @csrf
          @method('DELETE')
          <div class="flex items-center justify-end gap-2">
            <button
              type="button"
              id="cancel-delete-btn"
              class="px-4 py-2 rounded-xl border border-slate-700/80 text-xs hover:border-slate-600 transition"
            >
              İptal
            </button>
            <button
              type="submit"
              class="px-4 py-2 rounded-xl bg-rose-500 hover:bg-rose-400 text-white font-medium text-xs transition"
            >
              Sil
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Variations Modal -->
    <div
      id="variations-modal"
      class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
    >
      <div class="bg-surface rounded-2xl border border-slate-800/80 shadow-xl shadow-black/40 w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col">
        <div class="px-6 py-4 border-b border-slate-800/80 flex items-center justify-between">
          <h3 class="text-sm font-semibold">Prompt Varyasyonları</h3>
          <button
            type="button"
            id="close-variations-modal"
            class="text-slate-400 hover:text-slate-200 transition"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div id="variations-content" class="flex-1 overflow-y-auto p-6 space-y-4">
          <!-- Variations will be populated here -->
        </div>
      </div>
    </div>

    @if($activeChat)
    <!-- Left: Chat / Mentor Panel -->
    <section
      class="flex-1 flex flex-col bg-surface rounded-2xl border border-slate-800/80 shadow-xl shadow-black/40 overflow-hidden min-h-0"
      data-chat-id="{{ $activeChat->id }}"
    >
      <header
        class="px-5 lg:px-6 py-4 border-b border-slate-800/80 flex flex-col gap-3"
      >
        <div class="flex items-center justify-between gap-3">
          <div>
            <h1 class="text-lg lg:text-xl font-semibold tracking-tight">
              AI Prompt Mentor
            </h1>
            <p class="text-xs lg:text-sm text-slate-400">
              Sanat yönetmeni gibi konuşan görsel prompt asistanı
            </p>
          </div>
        </div>

        <!-- Preset Templates -->
        <div id="preset-templates-container" class="hidden">
          <div class="flex flex-wrap gap-2 mb-3">
            <button
              type="button"
              class="preset-template-btn px-3 py-1.5 rounded-lg border border-slate-700/80 bg-slate-900/60 hover:bg-slate-800/80 hover:border-emerald-400 text-[10px] lg:text-[11px] text-slate-300 hover:text-emerald-300 transition"
              data-template="portrait"
            >
              📸 Portre Fotoğraf
            </button>
            <button
              type="button"
              class="preset-template-btn px-3 py-1.5 rounded-lg border border-slate-700/80 bg-slate-900/60 hover:bg-slate-800/80 hover:border-emerald-400 text-[10px] lg:text-[11px] text-slate-300 hover:text-emerald-300 transition"
              data-template="cityscape"
            >
              🏙️ Şehir Manzarası
            </button>
            <button
              type="button"
              class="preset-template-btn px-3 py-1.5 rounded-lg border border-slate-700/80 bg-slate-900/60 hover:bg-slate-800/80 hover:border-emerald-400 text-[10px] lg:text-[11px] text-slate-300 hover:text-emerald-300 transition"
              data-template="product"
            >
              📦 Ürün Çekimi
            </button>
            <button
              type="button"
              class="preset-template-btn px-3 py-1.5 rounded-lg border border-slate-700/80 bg-slate-900/60 hover:bg-slate-800/80 hover:border-emerald-400 text-[10px] lg:text-[11px] text-slate-300 hover:text-emerald-300 transition"
              data-template="cinematic"
            >
              🎬 Film Sahnesi
            </button>
          </div>
        </div>

        <!-- Stepper (Clickable) -->
        <div
          class="flex items-center gap-3 text-[10px] lg:text-[11px] text-slate-400"
        >
          <div class="flex items-center gap-2 flex-1">
            <button
              type="button"
              data-stepper-stage="1"
              class="stepper-stage-btn flex-1 flex items-center gap-1 border border-slate-700/80 rounded-full px-2 py-1 hover:bg-slate-900/60 transition cursor-pointer"
              title="Taslak aşamasına dön"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-slate-500"></span>
              <span>Taslak</span>
            </button>
            <button
              type="button"
              data-stepper-stage="2"
              class="stepper-stage-btn flex-1 flex items-center gap-1 border border-slate-800 rounded-full px-2 py-1 opacity-70 hover:opacity-100 hover:bg-slate-900/60 transition cursor-pointer"
              title="Kompozisyon aşamasına dön"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-slate-700"></span>
              <span>Kompozisyon</span>
            </button>
            <button
              type="button"
              data-stepper-stage="3"
              class="stepper-stage-btn flex-1 flex items-center gap-1 border border-slate-800 rounded-full px-2 py-1 opacity-70 hover:opacity-100 hover:bg-slate-900/60 transition cursor-pointer"
              title="Teknik Detay aşamasına dön"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-slate-700"></span>
              <span>Teknik Detay</span>
            </button>
            <button
              type="button"
              data-stepper-stage="4"
              class="stepper-stage-btn flex-1 flex items-center gap-1 border border-slate-800 rounded-full px-2 py-1 opacity-70 hover:opacity-100 hover:bg-slate-900/60 transition cursor-pointer"
              title="Final aşamasına dön"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-slate-700"></span>
              <span>Final</span>
            </button>
          </div>
        </div>
      </header>

      <!-- Chat Area -->
      <div
        id="chat-container"
        class="flex-1 overflow-y-auto px-4 lg:px-6 py-4 space-y-4 text-sm lg:text-[15px] scroll-smooth min-h-0"
        style="max-height: calc(100vh - 20rem);"
      >
        @foreach($activeChat->messages as $message)
          @if($message->role === 'user')
          <div class="flex justify-end">
            <div
              class="max-w-[80%] rounded-2xl rounded-br-sm bg-emerald-500 text-slate-950 px-3.5 py-2.5 text-xs lg:text-sm whitespace-pre-wrap shadow-md shadow-emerald-500/30"
            >
              {{ $message->content }}
            </div>
          </div>
          @else
          <div class="flex justify-start">
            <div
              class="max-w-[85%] rounded-2xl rounded-bl-sm bg-slate-900/80 border border-slate-700/70 px-3.5 py-2.5 text-xs lg:text-sm text-slate-100 whitespace-pre-wrap shadow-md shadow-black/40"
            >
              {{ $message->content }}
            </div>
          </div>
          @endif
        @endforeach
      </div>

      <!-- Input Area -->
      <div
        class="border-t border-slate-800/80 bg-surfaceAlt/80 backdrop-blur flex flex-col gap-2 p-3 lg:p-4"
      >
        <label
          for="user-input"
          class="text-[11px] lg:text-xs text-slate-400 flex items-center justify-between"
        >
          <span>İlk fikrini yaz: Örn. "ormanda bir aslan"</span>
          <span
            id="loading-indicator"
            class="hidden items-center gap-1 text-emerald-300"
          >
            <span
              class="h-1 w-1 bg-emerald-300 rounded-full animate-pulse"
            ></span>
            <span class="text-[10px] lg:text-[11px]"
              >Sanat yönetmeni düşünüyor...</span
            >
          </span>
        </label>

        <div
          id="preset-container"
          class="hidden mt-1 mb-1 flex-wrap gap-2 text-[11px] lg:text-[11px]"
        ></div>

        <div class="flex items-end gap-2">
          <textarea
            id="user-input"
            rows="2"
            class="flex-1 resize-none text-sm lg:text-[15px] rounded-xl border border-slate-700/80 bg-slate-900/60 focus:bg-slate-900/90 focus:border-emerald-500/70 focus:ring-2 focus:ring-emerald-500/40 outline-none placeholder:text-slate-500 px-3.5 py-2.5"
            placeholder='Basit fikrini yaz: "Gece şehrinde neon ışıklı yağmurlu sokak"'
          ></textarea>
          <button
            id="send-btn"
            class="shrink-0 inline-flex items-center justify-center rounded-xl bg-emerald-500 hover:bg-emerald-400 active:bg-emerald-500 text-slate-900 font-medium text-xs lg:text-sm px-3.5 lg:px-4 py-2.5 transition disabled:opacity-40 disabled:cursor-not-allowed"
          >
            Gönder
          </button>
        </div>
        <p class="text-[10px] lg:text-[11px] text-slate-500">
          İpucu: Araç, eksik parametreleri (ışık, kamera açısı, lens, doku,
          arka plan vb.) tek tek sorarak profesyonel bir İngilizce prompt
          oluşturur.
        </p>
      </div>
    </section>

    <!-- Right: Realism Meter & Prompt Preview -->
    <aside
      class="w-full lg:w-[380px] xl:w-[420px] flex flex-col gap-4 lg:gap-5 lg:sticky lg:top-4 self-start max-h-[calc(100vh-5rem)] overflow-y-auto"
    >
      <!-- Realism Meter -->
      <div
        class="bg-surface rounded-2xl border border-slate-800/80 shadow-xl shadow-black/40 p-4 lg:p-5"
      >
        <div class="flex items-center justify-between mb-3">
          <div>
            <h2 class="text-sm lg:text-[15px] font-semibold">
              Realism Meter
            </h2>
            <p class="text-[11px] lg:text-xs text-slate-400">
              Prompt teknikleştikçe gerçekçilik artar.
            </p>
          </div>
          <span
            id="realism-label"
            class="text-xs lg:text-sm font-mono text-emerald-300"
            >0%</span
          >
        </div>
        <div
          class="relative h-2.5 rounded-full bg-slate-900 overflow-hidden mb-2"
        >
          <div
            id="realism-bar"
            class="absolute inset-y-0 left-0 w-[0%] bg-gradient-to-r from-emerald-400 via-emerald-500 to-emerald-300 shadow-[0_0_15px_rgba(16,185,129,0.8)] transition-all duration-500"
          ></div>
        </div>
        <p
          id="realism-helper-text"
          class="text-[11px] lg:text-xs text-slate-400 mt-1"
        >
          Basit fikrini gir, birlikte profesyonel bir fotoğraf prodüksiyonu
          haline getirelim.
        </p>
      </div>

      <!-- Live Prompt Preview -->
      <div
        class="bg-surface rounded-2xl border border-slate-800/80 shadow-xl shadow-black/40 flex-1 flex flex-col p-4 lg:p-5"
      >
        <div class="flex items-center justify-between gap-2 mb-3">
          <div>
            <h2 class="text-sm lg:text-[15px] font-semibold">
              Prompt Builder Preview
            </h2>
            <p class="text-[11px] lg:text-xs text-slate-400">
              Gemini tarafından inşa edilen profesyonel İngilizce prompt.
            </p>
          </div>
          <div class="flex items-center gap-2">
            <button
              id="generate-variations-btn"
              class="hidden shrink-0 text-[10px] lg:text-[11px] px-2.5 py-1.5 rounded-lg border border-purple-500/60 text-purple-300 hover:bg-purple-500/10 transition"
              title="3 varyasyon üret"
            >
              ✨ Varyasyonlar
            </button>
            <button
              id="copy-final-btn"
              class="hidden shrink-0 text-[11px] lg:text-xs px-2.5 py-1.5 rounded-lg border border-emerald-500/60 text-emerald-300 hover:bg-emerald-500/10 transition"
            >
              Promptu Kopyala
            </button>
          </div>
        </div>

        <!-- Tabs -->
        <div class="flex items-center gap-1 mb-2 border-b border-slate-800/80">
          <button
            type="button"
            class="prompt-tab-btn active px-3 py-1.5 text-[10px] lg:text-[11px] text-emerald-300 border-b-2 border-emerald-400 transition"
            data-tab="prompt"
          >
            Prompt
          </button>
          <button
            type="button"
            class="prompt-tab-btn px-3 py-1.5 text-[10px] lg:text-[11px] text-slate-400 hover:text-slate-300 border-b-2 border-transparent transition"
            data-tab="changes"
          >
            Değişiklikler
          </button>
        </div>

        <!-- Prompt Tab Content -->
        <div
          id="prompt-tab-content"
          class="tab-content flex-1 flex flex-col"
        >
          <div
            class="flex-1 rounded-xl border border-slate-800/80 bg-slate-950/60 px-3.5 py-3.5 text-[11px] lg:text-xs text-slate-200 font-mono whitespace-pre-wrap leading-relaxed overflow-y-auto"
            id="prompt-preview"
          >
            Burada profesyonel promptun canlı olarak oluşacak. Önce fikrini yaz,
            sonra eksik detayları birlikte dolduralım.
          </div>
          <p
            id="final-helper-text"
            class="mt-2 text-[10px] lg:text-[11px] text-slate-500"
          >
            Gerçekçilik %90+ seviyesine ulaştığında kopyalanabilir final prompt
            burada hazır olacak.
          </p>
        </div>

        <!-- Changes Tab Content -->
        <div
          id="changes-tab-content"
          class="tab-content hidden flex-1 flex flex-col"
        >
          <div
            class="flex-1 rounded-xl border border-slate-800/80 bg-slate-950/60 px-3.5 py-3.5 text-[11px] lg:text-xs text-slate-200 leading-relaxed overflow-y-auto"
            id="changes-preview"
          >
            <div class="text-slate-400 text-[10px] mb-2">Bu turda eklenenler:</div>
            <div id="changes-list" class="space-y-1"></div>
          </div>
        </div>
      </div>

      <!-- Session Summary Card (shown when final) -->
      <div
        id="session-summary-card"
        class="hidden bg-surface rounded-2xl border border-emerald-800/50 shadow-xl shadow-black/40 p-4 lg:p-5"
      >
        <div class="flex items-center justify-between mb-3">
          <h2 class="text-sm lg:text-[15px] font-semibold text-emerald-300">
            📋 Session Özeti
          </h2>
        </div>
        <div id="session-summary-content" class="space-y-2 text-[11px] lg:text-xs">
          <!-- Will be populated by JS -->
        </div>
      </div>

      <!-- Negative Prompt Preview -->
      <div
        class="bg-surface rounded-2xl border border-rose-900/70 shadow-xl shadow-black/40 flex flex-col p-4 lg:p-5"
      >
        <div class="flex items-center justify-between gap-2 mb-3">
          <div>
            <h2 class="text-sm lg:text-[15px] font-semibold text-rose-200">
              Negative Prompt
            </h2>
            <p class="text-[11px] lg:text-xs text-slate-400">
              Görselde özellikle <span class="text-rose-300">olmamasını</span>
              istediğimiz unsurlar.
            </p>
          </div>
          <button
            id="copy-negative-btn"
            class="hidden shrink-0 text-[11px] lg:text-xs px-2.5 py-1.5 rounded-lg border border-rose-500/70 text-rose-200 hover:bg-rose-500/10 transition"
          >
            Negatif Promptu Kopyala
          </button>
        </div>
        <div
          id="negative-prompt-preview"
          class="min-h-[72px] rounded-xl border border-rose-900 bg-slate-950/60 px-3.5 py-3.5 text-[11px] lg:text-xs text-slate-200 font-mono whitespace-pre-wrap leading-relaxed overflow-y-auto"
        >
          Bozuk anatomi, plastik cilt, watermark, logo, aşırı gürültü gibi
          istenmeyen detaylar burada derlenir.
        </div>
      </div>
    </aside>
    @endif
  </div>

  @push('scripts')
  <script type="module" src="{{ asset('js/prompt-mentor.js') }}"></script>
  @endpush
</x-layouts.app>

