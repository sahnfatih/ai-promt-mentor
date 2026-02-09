@php($title = 'Sohbet Detayı - Admin')

<x-layouts.app :title="$title">
  <div class="max-w-5xl mx-auto p-4 lg:p-6 space-y-4">
    <div class="flex items-center justify-between gap-3">
      <div>
        <h1 class="text-lg lg:text-xl font-semibold">
          {{ $chat->title ?? 'Sohbet #'.$chat->id }}
        </h1>
        <p class="text-xs text-slate-400">
          Kullanıcı: {{ $chat->user->name }} · {{ $chat->created_at->format('d.m.Y H:i') }}
        </p>
      </div>
      <div class="flex items-center gap-2">
        <a
          href="{{ route('admin.users.show', $chat->user) }}"
          class="px-3 py-1.5 rounded-xl border border-slate-700/80 text-[11px] hover:border-emerald-400 hover:text-emerald-300 transition"
        >
          Kullanıcıya Git
        </a>
        <form
          action="{{ route('admin.chats.delete', $chat) }}"
          method="POST"
          onsubmit="return confirm('Bu sohbeti silmek istediğinize emin misiniz?');"
        >
          @csrf
          @method('DELETE')
          <button
            type="submit"
            class="px-3 py-1.5 rounded-xl border border-slate-700/80 text-[11px] hover:border-rose-400 hover:text-rose-300 transition"
          >
            Sohbeti Sil
          </button>
        </form>
      </div>
    </div>

    @if(session('status'))
    <div class="bg-emerald-950/60 border border-emerald-800 rounded-xl px-4 py-2 text-[11px] text-emerald-200">
      {{ session('status') }}
    </div>
    @endif

    <div class="bg-surface rounded-2xl border border-slate-800/80 shadow-xl shadow-black/40 overflow-hidden">
      <div class="px-4 py-3 border-b border-slate-800/80 bg-slate-900/40">
        <div class="flex items-center justify-between text-xs">
          <span class="text-slate-400">Toplam {{ $chat->messages->count() }} mesaj</span>
          <span class="text-slate-400">Sadece görüntüleme modu</span>
        </div>
      </div>

      <div class="max-h-[calc(100vh-20rem)] overflow-y-auto p-4 lg:p-6 space-y-4">
        @foreach($chat->messages as $message)
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
          <div class="text-[10px] text-slate-500 text-center">
            {{ $message->created_at->format('d.m.Y H:i:s') }}
          </div>
        @endforeach
      </div>
    </div>
  </div>
</x-layouts.app>
