<x-guest-layout>
    <div class="relative min-h-screen overflow-hidden bg-gradient-to-br from-indigo-50 via-white to-purple-50">
        <div class="absolute -top-20 -left-16 h-72 w-72 rounded-full bg-indigo-200/40 blur-3xl"></div>
        <div class="absolute -bottom-20 -right-10 h-72 w-72 rounded-full bg-purple-200/40 blur-3xl"></div>

        <div class="relative z-10 mx-auto flex min-h-screen w-full max-w-3xl items-center justify-center px-4 py-6 md:px-6 md:py-10">
            <div class="w-full overflow-hidden rounded-3xl border border-white/60 bg-white/80 p-8 text-center shadow-2xl backdrop-blur md:p-12">
                <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <h1 class="text-3xl font-bold text-slate-900 md:text-4xl">Terima Kasih!</h1>
                <p class="mx-auto mt-4 max-w-md text-base text-slate-600 md:text-lg">
                    Penilaian Anda telah berhasil kami terima. Feedback Anda sangat berarti untuk membantu kami meningkatkan kualitas layanan.
                </p>

                <div class="mt-8 grid gap-3 text-sm sm:grid-cols-3">
                    <div class="rounded-xl bg-indigo-50 p-4">
                        <i class="ri-shield-check-line text-2xl text-indigo-500"></i>
                        <p class="mt-2 font-medium text-slate-700">Data Aman</p>
                        <p class="mt-1 text-xs text-slate-500">Informasi Anda terjaga</p>
                    </div>
                    <div class="rounded-xl bg-purple-50 p-4">
                        <i class="ri-time-line text-2xl text-purple-500"></i>
                        <p class="mt-2 font-medium text-slate-700">Tersimpan</p>
                        <p class="mt-1 text-xs text-slate-500">Rating tercatat di sistem</p>
                    </div>
                    <div class="rounded-xl bg-emerald-50 p-4">
                        <i class="ri-heart-line text-2xl text-emerald-500"></i>
                        <p class="mt-2 font-medium text-slate-700">Dihargai</p>
                        <p class="mt-1 text-xs text-slate-500">Pendapat Anda penting</p>
                    </div>
                </div>

                <div class="mt-8">
                    <a href="{{ url('/') }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700">
                        <i class="ri-home-4-line"></i>
                        Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
