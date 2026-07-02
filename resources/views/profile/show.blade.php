<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Mi perfil</title>
        @if (file_exists(public_path('build/manifest.json')))
            @vite(['resources/css/app.css'])
        @endif
    </head>
    <body class="min-h-screen bg-stone-100 text-stone-900">
        <main class="mx-auto flex min-h-screen max-w-6xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8">
            <section class="overflow-hidden rounded-[2rem] bg-linear-to-br from-amber-100 via-white to-stone-200 shadow-sm ring-1 ring-stone-200">
                <div class="grid gap-6 px-6 py-8 lg:grid-cols-[240px_1fr] lg:px-10">
                    <div class="flex flex-col items-center gap-4 rounded-[1.5rem] bg-white/80 p-6 text-center ring-1 ring-stone-200">
                        @if (filled($user->getFilamentAvatarUrl()))
                            <img
                                src="{{ $user->getFilamentAvatarUrl() }}"
                                alt="Foto de {{ $user->name }}"
                                class="h-32 w-32 rounded-full object-cover ring-4 ring-amber-200"
                            >
                        @else
                            <div class="flex h-32 w-32 items-center justify-center rounded-full bg-amber-500 text-3xl font-semibold text-white ring-4 ring-amber-200">
                                {{ $initials }}
                            </div>
                        @endif

                        <div class="space-y-1">
                            <p class="text-xl font-semibold">{{ $user->name }}</p>
                            <p class="text-sm text-stone-500">{{ $user->job_title ?: 'Empleado' }}</p>
                            <p class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-medium uppercase tracking-[0.2em] text-amber-800">
                                {{ str_replace('_', ' ', $user->employment_status) }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-2">
                        <article class="rounded-[1.5rem] bg-white p-6 shadow-sm ring-1 ring-stone-200 lg:col-span-2">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm font-medium uppercase tracking-[0.2em] text-stone-500">Mi perfil</p>
                                    <h1 class="mt-2 text-3xl font-semibold text-stone-900">Portal del empleado</h1>
                                </div>
                                <div class="rounded-2xl bg-stone-900 px-4 py-3 text-sm text-stone-50">
                                    <p>{{ $user->tenant?->name ?: 'Sin empresa asignada' }}</p>
                                </div>
                            </div>

                            @if (session('status') === 'avatar-updated')
                                <p class="mt-4 rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700 ring-1 ring-emerald-200">
                                    La foto de perfil se ha actualizado correctamente.
                                </p>
                            @endif

                            @if (session('status') === 'password-updated')
                                <p class="mt-4 rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700 ring-1 ring-emerald-200">
                                    Tu contrasena se ha actualizado correctamente.
                                </p>
                            @endif
                        </article>

                        <article class="rounded-[1.5rem] bg-white p-6 shadow-sm ring-1 ring-stone-200 lg:col-span-2">
                            <h2 class="text-lg font-semibold text-stone-900">Datos del empleado</h2>
                            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                <div class="rounded-2xl bg-stone-50 p-4">
                                    <p class="text-xs uppercase tracking-[0.2em] text-stone-500">Email</p>
                                    <p class="mt-2 text-sm font-medium text-stone-900">{{ $user->email }}</p>
                                </div>
                                <div class="rounded-2xl bg-stone-50 p-4">
                                    <p class="text-xs uppercase tracking-[0.2em] text-stone-500">Departamento</p>
                                    <p class="mt-2 text-sm font-medium text-stone-900">{{ $user->department?->name ?: '-' }}</p>
                                </div>
                                <div class="rounded-2xl bg-stone-50 p-4">
                                    <p class="text-xs uppercase tracking-[0.2em] text-stone-500">Codigo</p>
                                    <p class="mt-2 text-sm font-medium text-stone-900">{{ $user->employee_code ?: '-' }}</p>
                                </div>
                                <div class="rounded-2xl bg-stone-50 p-4">
                                    <p class="text-xs uppercase tracking-[0.2em] text-stone-500">Fecha de alta</p>
                                    <p class="mt-2 text-sm font-medium text-stone-900">{{ $user->hire_date?->format('d/m/Y') ?: '-' }}</p>
                                </div>
                            </div>
                        </article>

                        <article class="rounded-[1.5rem] bg-white p-6 shadow-sm ring-1 ring-stone-200">
                            <h2 class="text-lg font-semibold text-stone-900">Actualizar foto</h2>
                            <p class="mt-2 text-sm text-stone-500">Admite JPG, PNG o WEBP hasta 2 MB.</p>

                            <form class="mt-6 flex flex-col gap-4" method="POST" action="{{ route('profile.avatar.update') }}" enctype="multipart/form-data">
                                @csrf
                                <label class="flex cursor-pointer flex-col gap-3 rounded-2xl border border-dashed border-stone-300 bg-stone-50 p-4 text-sm text-stone-600">
                                    <span>Selecciona una imagen de perfil</span>
                                    <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm text-stone-600">
                                </label>
                                @error('avatar')
                                    <p class="text-sm text-rose-600">{{ $message }}</p>
                                @enderror
                                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-stone-900 px-4 py-3 text-sm font-medium text-white transition hover:bg-stone-700">
                                    Guardar foto
                                </button>
                            </form>
                        </article>

                        <article class="rounded-[1.5rem] bg-white p-6 shadow-sm ring-1 ring-stone-200">
                            <h2 class="text-lg font-semibold text-stone-900">Cambiar contrasena</h2>
                            <p class="mt-2 text-sm text-stone-500">Debe incluir al menos 8 caracteres, mayusculas, minusculas, numeros y simbolos.</p>

                            <form class="mt-6 flex flex-col gap-4" method="POST" action="{{ route('profile.password.update') }}">
                                @csrf
                                @method('PUT')
                                <label class="flex flex-col gap-2 text-sm text-stone-700">
                                    <span>Contrasena actual</span>
                                    <input type="password" name="current_password" class="rounded-2xl border border-stone-300 bg-white px-4 py-3 outline-none ring-0 transition focus:border-amber-500">
                                </label>
                                @error('current_password')
                                    <p class="text-sm text-rose-600">{{ $message }}</p>
                                @enderror

                                <label class="flex flex-col gap-2 text-sm text-stone-700">
                                    <span>Nueva contrasena</span>
                                    <input type="password" name="password" class="rounded-2xl border border-stone-300 bg-white px-4 py-3 outline-none ring-0 transition focus:border-amber-500">
                                </label>
                                @error('password')
                                    <p class="text-sm text-rose-600">{{ $message }}</p>
                                @enderror

                                <label class="flex flex-col gap-2 text-sm text-stone-700">
                                    <span>Confirmar nueva contrasena</span>
                                    <input type="password" name="password_confirmation" class="rounded-2xl border border-stone-300 bg-white px-4 py-3 outline-none ring-0 transition focus:border-amber-500">
                                </label>

                                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-amber-500 px-4 py-3 text-sm font-medium text-stone-950 transition hover:bg-amber-400">
                                    Actualizar contrasena
                                </button>
                            </form>
                        </article>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>