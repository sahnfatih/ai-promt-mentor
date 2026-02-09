<!DOCTYPE html>
<html lang="tr" class="dark">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ?? 'AI Prompt Mentor' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              background: "#050816",
              surface: "#0f172a",
              surfaceAlt: "#020617",
              accent: {
                DEFAULT: "#22c55e",
                soft: "rgba(34, 197, 94, 0.12)",
              },
            },
          },
        },
      };
    </script>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
  </head>
  <body class="bg-background text-slate-100 min-h-screen">
    <div class="min-h-screen flex flex-col">
      <nav
        class="h-14 border-b border-slate-800/80 bg-surfaceAlt/90 backdrop-blur flex items-center justify-between px-4 lg:px-8"
      >
        <div class="flex items-center gap-2">
          <span
            class="h-7 w-7 rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-xs font-bold text-slate-950"
            >PM</span
          >
          <div class="leading-tight">
            <div class="text-xs uppercase tracking-[0.2em] text-slate-500">
              AI Prompt Mentor
            </div>
            <div class="text-[11px] text-slate-400">
              Profesyonel görsel prompt asistanı
            </div>
          </div>
        </div>
        <div class="flex items-center gap-3 text-xs">
          @auth
          <span class="text-slate-300 hidden sm:inline">
            {{ auth()->user()->name }}
            @if(auth()->user()->is_admin)
            <span class="ml-1 text-emerald-400">(Admin)</span>
            @endif
          </span>
          @if(auth()->user()->is_admin)
          <a
            href="{{ route('admin.users') }}"
            class="px-3 py-1.5 rounded-lg border border-slate-700/80 text-[11px] hover:border-emerald-400 hover:text-emerald-300 transition"
            >Kullanıcılar</a
          >
          @endif
          <form
            action="{{ route('logout') }}"
            method="POST"
            class="inline"
          >
            @csrf
            <button
              type="submit"
              class="px-3 py-1.5 rounded-lg border border-slate-700/80 text-[11px] hover:border-rose-500 hover:text-rose-300 transition"
            >
              Çıkış
            </button>
          </form>
          @endauth @guest
          <a
            href="{{ route('login') }}"
            class="px-3 py-1.5 rounded-lg border border-slate-700/80 text-[11px] hover:border-emerald-400 hover:text-emerald-300 transition"
            >Giriş</a
          >
          <a
            href="{{ route('register') }}"
            class="px-3 py-1.5 rounded-lg border border-slate-700/80 text-[11px] hover:border-emerald-400 hover:text-emerald-300 transition"
            >Kayıt Ol</a
          >
          @endguest
        </div>
      </nav>

      <main class="flex-1">
        @isset($slot)
          {{ $slot }}
        @else
          @yield('content')
        @endisset
      </main>
    </div>

    @stack('scripts')
  </body>
</html>

