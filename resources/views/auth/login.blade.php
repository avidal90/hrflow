<x-layouts.public :title="$title">
    <div class="flex min-h-[calc(100vh-4rem)] items-center justify-center bg-slate-50 px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-500 text-xl font-bold text-white shadow-lg shadow-amber-200">
                    HR
                </div>
                <h1 class="mt-6 text-2xl font-semibold text-slate-900">{{ $heading }}</h1>
                <p class="mt-2 text-sm text-slate-600">{{ $description }}</p>
            </div>

            <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
                <form class="flex flex-col gap-5" method="POST" action="{{ $form_action }}">
                    @csrf

                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-slate-700" for="email">Email</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="tu@empresa.com"
                            class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20"
                        >
                        @error('email')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-slate-700" for="password">Contrasena</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20"
                        >
                        @error('password')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex items-center gap-2.5 text-sm text-slate-600">
                        <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300">
                        Mantener sesion iniciada
                    </label>

                    <button type="submit" class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 active:bg-slate-950">
                        Entrar
                    </button>
                </form>
            </div>

            <p class="mt-8 text-center text-xs text-slate-500">
                {!! $footer_html !!}
            </p>
        </div>
    </div>
</x-layouts.public>