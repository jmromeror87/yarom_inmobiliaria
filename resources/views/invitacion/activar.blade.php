<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Activar cuenta — YarOM</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center p-4" style="font-family:'Inter',system-ui,sans-serif;">

<div class="w-full max-w-md">

  {{-- Logo --}}
  <div class="text-center mb-8">
    <div class="inline-flex items-center gap-3 mb-2">
      <div style="width:42px;height:42px;background:linear-gradient(135deg,#0F172A,#2563EB);border-radius:12px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(14,1,163,0.25);">
        <svg viewBox="0 0 32 32" fill="none" width="22" height="22"><path d="M4 28V14l12-9 12 9v14H20v-7h-8v7H4z" fill="#fff"/></svg>
      </div>
      <div class="text-left">
        <div class="text-xl font-black tracking-tight text-slate-900">YAROM <span class="text-red-600">INMO</span>BILIARIA</div>
        <div class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Serviarrendar S.A.S</div>
      </div>
    </div>
  </div>

  {{-- Card --}}
  <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">

    {{-- Header card --}}
    <div style="background:linear-gradient(135deg,#0A192F,#1e3a5f);padding:28px 32px;">
      <div class="text-white text-xl font-bold mb-1">Activa tu cuenta</div>
      <div class="text-slate-400 text-sm">Hola <strong class="text-white">{{ $user->name }}</strong>, crea tu contraseña para acceder al sistema.</div>
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('invitacion.store', $token) }}" class="p-8 space-y-5">
      @csrf

      @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
          {{ session('error') }}
        </div>
      @endif

      {{-- Email (readonly) --}}
      <div>
        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-widest mb-2">Correo electrónico</label>
        <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500 font-medium">
          {{ $user->email }}
        </div>
      </div>

      {{-- Password --}}
      <div>
        <label for="password" class="block text-xs font-semibold text-slate-600 uppercase tracking-widest mb-2">
          Nueva contraseña
        </label>
        <input
          type="password"
          id="password"
          name="password"
          required
          minlength="8"
          placeholder="Mínimo 8 caracteres"
          class="w-full rounded-xl border @error('password') border-red-400 bg-red-50 @else border-slate-200 @enderror px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
        >
        @error('password')
          <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
      </div>

      {{-- Confirm password --}}
      <div>
        <label for="password_confirmation" class="block text-xs font-semibold text-slate-600 uppercase tracking-widest mb-2">
          Confirmar contraseña
        </label>
        <input
          type="password"
          id="password_confirmation"
          name="password_confirmation"
          required
          placeholder="Repite la contraseña"
          class="w-full rounded-xl border @error('password_confirmation') border-red-400 bg-red-50 @else border-slate-200 @enderror px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
        >
        @error('password_confirmation')
          <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
      </div>

      {{-- Indicador de fortaleza --}}
      <div id="strength-bar" class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
        <div id="strength-fill" class="h-full rounded-full transition-all duration-300 w-0"></div>
      </div>
      <p id="strength-label" class="text-xs text-slate-400 -mt-3"></p>

      <button type="submit"
        class="w-full py-3 rounded-xl text-white text-sm font-bold tracking-wide transition hover:opacity-90 active:scale-95"
        style="background:linear-gradient(135deg,#0E01A3,#2563EB);">
        Activar cuenta y entrar →
      </button>
    </form>

  </div>

  <p class="text-center text-xs text-slate-400 mt-6">
    ¿Problemas? Contacta al administrador del sistema.
  </p>

</div>

<script>
const pwd = document.getElementById('password');
const fill = document.getElementById('strength-fill');
const label = document.getElementById('strength-label');

pwd.addEventListener('input', function() {
  const v = this.value;
  let score = 0;
  if (v.length >= 8)  score++;
  if (v.length >= 12) score++;
  if (/[A-Z]/.test(v)) score++;
  if (/[0-9]/.test(v)) score++;
  if (/[^A-Za-z0-9]/.test(v)) score++;

  const map = [
    { w: '20%',  color: '#ef4444', text: 'Muy débil' },
    { w: '40%',  color: '#f97316', text: 'Débil' },
    { w: '60%',  color: '#eab308', text: 'Regular' },
    { w: '80%',  color: '#22c55e', text: 'Buena' },
    { w: '100%', color: '#10b981', text: '¡Excelente!' },
  ];
  const s = map[Math.max(0, score - 1)] || map[0];
  fill.style.width = v.length ? s.w : '0';
  fill.style.background = s.color;
  label.textContent = v.length ? s.text : '';
  label.style.color = s.color;
});
</script>

</body>
</html>
