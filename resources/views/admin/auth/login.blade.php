<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin-Login · Antiquariat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #1c1917; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <div class="text-4xl mb-3">🏛️</div>
            <h1 class="text-xl font-bold text-stone-100">Antiquariat Admin</h1>
            <p class="text-stone-400 text-sm mt-1">Bitte anmelden</p>
        </div>

        <div class="bg-stone-800 rounded-2xl border border-stone-700 p-6 shadow-2xl">

            @if(session('error'))
            <div class="bg-red-900/50 border border-red-700 text-red-300 text-sm rounded-lg px-4 py-3 mb-4">
                {{ session('error') }}
            </div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-stone-300 mb-1.5">E-Mail</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           required autofocus autocomplete="email"
                           class="w-full px-3 py-2.5 bg-stone-700 border border-stone-600 text-stone-100 rounded-lg focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 placeholder-stone-400 text-sm
                           @error('email') border-red-500 @enderror"
                           placeholder="admin@example.com">
                    @error('email')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-300 mb-1.5">Passwort</label>
                    <input type="password" name="password" required autocomplete="current-password"
                           class="w-full px-3 py-2.5 bg-stone-700 border border-stone-600 text-stone-100 rounded-lg focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 text-sm"
                           placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" value="1"
                               class="accent-amber-500">
                        <span class="text-sm text-stone-400">Angemeldet bleiben</span>
                    </label>
                </div>

                <button type="submit"
                        class="w-full py-2.5 bg-amber-500 hover:bg-amber-400 text-stone-900 font-bold rounded-lg transition-colors mt-2">
                    Anmelden
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-stone-600 mt-6">
            Nur für autorisierte Administratoren
        </p>
    </div>

</body>
</html>
