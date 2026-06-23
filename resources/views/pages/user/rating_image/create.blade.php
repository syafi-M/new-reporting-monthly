<x-guest-layout>
    <div class="relative min-h-screen overflow-hidden bg-gradient-to-br from-indigo-50 via-white to-purple-50">
        <div class="absolute -top-20 -left-16 h-72 w-72 rounded-full bg-indigo-200/40 blur-3xl"></div>
        <div class="absolute -bottom-20 -right-10 h-72 w-72 rounded-full bg-purple-200/40 blur-3xl"></div>

        <div class="relative z-10 mx-auto flex min-h-screen w-full max-w-7xl items-center justify-center px-4 py-6 md:px-6 md:py-10">
            <div class="grid w-full max-w-6xl grid-cols-1 overflow-hidden rounded-3xl border border-white/60 bg-white/80 shadow-2xl backdrop-blur lg:grid-cols-12">
                <div class="flex flex-col justify-between bg-gradient-to-br from-indigo-600 to-indigo-800 p-7 text-white md:p-10 lg:col-span-5">
                    <div>
                        <p class="mb-4 inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-medium">
                            <i class="ri-customer-service-2-line"></i>
                            Feedback Pelanggan
                        </p>
                        <h1 class="text-3xl font-bold leading-tight md:text-4xl">
                            Bantu kami jadi lebih baik
                        </h1>
                        <p class="mt-4 text-sm text-indigo-100 md:text-base">
                            Isi form singkat ini untuk memberi penilaian layanan kami.
                        </p>
                    </div>

                    <div class="mt-6 grid gap-3 text-sm md:mt-8">
                        <div class="flex items-start gap-3 rounded-xl bg-white/10 p-3">
                            <i class="ri-shield-check-line mt-0.5 text-lg"></i>
                            <p>Data Anda aman dan hanya dipakai untuk evaluasi layanan.</p>
                        </div>
                        <div class="flex items-start gap-3 rounded-xl bg-white/10 p-3">
                            <i class="ri-time-line mt-0.5 text-lg"></i>
                            <p>Isi form ini cuma butuh sekitar 1 menit.</p>
                        </div>
                    </div>
                </div>

                <div class="p-5 md:p-8 lg:col-span-7 lg:p-10">
                    <div class="mb-5 md:mb-6">
                        <h2 class="text-2xl font-bold leading-tight text-slate-900 md:text-3xl">Form Ulasan Pekerjaan</h2>
                        <p class="mt-1 inline-flex rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 md:text-sm">
                            Area {{ $nValue ?? '-' }}
                        </p>
                        <p class="mt-1 text-sm text-slate-500">Terima kasih telah meluangkan waktu untuk memberi penilaian.</p>
                    </div>

                    @if (session('success'))
                        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            <ul class="list-inside list-disc space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-5 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 md:p-5">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-slate-800">Preview Gambar Terkait</h3>
                            <span class="text-xs text-slate-500">Area {{ $nValue ?? '-' }}</span>
                        </div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <div>
                                <p class="mb-1 text-xs font-medium text-slate-600">Before</p>
                                <img
                                    src="{{ $uploadPreview?->img_before ? asset('storage/' . $uploadPreview->img_before) : 'https://placehold.co/600x400?text=No+Image' }}"
                                    class="aspect-[4/3] w-full rounded-lg border border-slate-200 object-cover"
                                    alt="Before image preview"
                                    onerror="this.onerror=null;this.src='https://placehold.co/600x400?text=No+Image';">
                            </div>
                            <div>
                                <p class="mb-1 text-xs font-medium text-slate-600">Progress</p>
                                <img
                                    src="{{ $uploadPreview?->img_proccess ? asset('storage/' . $uploadPreview->img_proccess) : 'https://placehold.co/600x400?text=No+Image' }}"
                                    class="aspect-[4/3] w-full rounded-lg border border-slate-200 object-cover"
                                    alt="Progress image preview"
                                    onerror="this.onerror=null;this.src='https://placehold.co/600x400?text=No+Image';">
                            </div>
                            <div>
                                <p class="mb-1 text-xs font-medium text-slate-600">After</p>
                                <img
                                    src="{{ $uploadPreview?->img_final ? asset('storage/' . $uploadPreview->img_final) : 'https://placehold.co/600x400?text=No+Image' }}"
                                    class="aspect-[4/3] w-full rounded-lg border border-slate-200 object-cover"
                                    alt="After image preview"
                                    onerror="this.onerror=null;this.src='https://placehold.co/600x400?text=No+Image';">
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('rating-pekerjaan.store') }}" method="POST" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-4 md:p-5" enctype="multipart/form-data">
                        @csrf

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="form-control">
                                <label for="name" class="mb-2 block text-sm font-medium text-slate-700">Nama</label>
                                <input id="name" name="name" type="text" value="{{ old('name') }}"
                                    placeholder="Contoh: Budi Santoso"
                                    class="input input-bordered w-full rounded-xl border-slate-300 bg-white focus:border-indigo-500 focus:outline-none" />
                            </div>

                            <div class="form-control">
                                <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email</label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}"
                                    placeholder="nama@email.com"
                                    class="input input-bordered w-full rounded-xl border-slate-300 bg-white focus:border-indigo-500 focus:outline-none @error('email') input-error @enderror"/>
                            </div>
                        </div>

                        <div class="form-control">
                            <label class="mb-2 block text-sm font-medium text-slate-700">Rating Pelayanan</label>
                            <div class="rating rating-lg gap-1">
                                @for ($i = 1; $i <= 5; $i++)
                                    <input type="radio" name="rate" value="{{ $i }}" aria-label="{{ $i }} star"
                                        class="mask mask-star-2 bg-amber-400"
                                        {{ $i === 1 ? 'required' : '' }}
                                        {{ (int) old('rate', 0) === $i ? 'checked' : '' }} />
                                @endfor
                            </div>
                            <p class="mt-1 text-xs text-slate-500">Pilih bintang dari 1 sampai 5.</p>
                        </div>

                        <div class="form-control w-full">
                            <!-- Label -->
                            <div class="mb-2 flex items-center justify-between">
                                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 002-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Bukti Keluhan / Saran
                                </label>
                                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-medium text-slate-400">Opsional</span>
                            </div>

                            <!-- Dropzone Card -->
                            <label for="image_path_rate"
                                class="relative flex flex-col items-center justify-center w-full min-h-[140px] rounded-2xl cursor-pointer transition-all duration-200 group overflow-hidden
                                    @error('image_path_rate') border-2 border-red-300 bg-red-50/40 hover:bg-red-50/70 @else border-2 border-dashed border-indigo-200/70 bg-gradient-to-br from-indigo-50/40 via-white to-purple-50/40 hover:border-indigo-400 hover:shadow-md hover:shadow-indigo-100/50 @enderror">

                                <!-- Input File (Hidden) -->
                                <input
                                    id="image_path_rate"
                                    name="image_path_rate"
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    onchange="previewImage(this)"
                                />

                                <!-- Placeholder State -->
                                <div id="upload-placeholder" class="flex flex-col items-center justify-center text-center py-6 px-4 space-y-3">
                                    <div class="relative">
                                        <div class="absolute inset-0 rounded-2xl bg-indigo-400/20 blur-lg group-hover:bg-indigo-400/30 transition-all"></div>
                                        <div class="relative p-3.5 bg-white rounded-2xl shadow-sm border border-indigo-100/60 group-hover:scale-105 group-hover:shadow-md transition-all duration-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-sm">
                                            <span class="font-semibold text-indigo-600">Klik untuk unggah</span>
                                            <span class="text-slate-400"> gambar</span>
                                        </p>
                                        <p class="text-[11px] text-slate-400 tracking-wide">PNG, JPG, atau WEBP — Maks. 2MB</p>
                                    </div>
                                </div>

                                <!-- Preview State -->
                                <div id="upload-preview" class="hidden absolute inset-0 p-2.5 flex items-center justify-center overflow-hidden">
                                    <img id="preview-img" src="" alt="Preview" class="w-full h-full object-contain rounded-xl">
                                    <!-- Overlay gradient -->
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent rounded-xl pointer-events-none"></div>
                                    <!-- Remove button -->
                                    <button type="button" onclick="removeImage(event)"
                                        class="absolute top-4 right-4 p-2 bg-white/90 hover:bg-white text-red-500 hover:text-red-600 rounded-full shadow-lg backdrop-blur-sm transition-all duration-150 hover:scale-110">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                    <!-- File info bar -->
                                    <div class="absolute bottom-4 left-4 right-4 flex items-center gap-2">
                                        <div class="flex items-center gap-2 rounded-lg bg-white/90 backdrop-blur-sm px-3 py-1.5 shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span id="file-name" class="text-xs font-medium text-slate-700 truncate max-w-[200px]">gambar.jpg</span>
                                        </div>
                                    </div>
                                </div>
                            </label>

                            <!-- Validation Error -->
                            @error('image_path_rate')
                                <div class="mt-1.5 flex items-center gap-1.5 text-xs font-medium text-red-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                    </svg>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>


                        <div class="form-control">
                            <label for="comment" class="mb-2 block text-sm font-medium text-slate-700">Komentar</label>
                            <textarea id="comment" name="comment" rows="4" placeholder="Tulis pengalaman Anda..." required
                                class="textarea textarea-bordered w-full rounded-xl border-slate-300 bg-white focus:border-indigo-500 focus:outline-none">{{ old('comment') }}</textarea>
                        </div>

                        <input type="hidden" name="n" value="{{ $nValue }}">

                        <button type="submit"
                            class="btn h-12 w-full rounded-xl border-none bg-indigo-600 text-base font-semibold text-white hover:bg-indigo-700">
                            <i class="ri-send-plane-line"></i>
                            Kirim Ulasan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
    function previewImage(input) {
        const file = input.files[0];
        const placeholder = document.getElementById('upload-placeholder');
        const previewContainer = document.getElementById('upload-preview');
        const previewImg = document.getElementById('preview-img');
        const fileName = document.getElementById('file-name');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                fileName.textContent = file.name;
                placeholder.classList.add('hidden');
                previewContainer.classList.remove('hidden');
            }
            reader.readAsDataURL(file);
        }
    }

    function removeImage(event) {
        event.preventDefault(); // Mencegah pemicu klik pada label
        const input = document.getElementById('image_path_rate');
        const placeholder = document.getElementById('upload-placeholder');
        const previewContainer = document.getElementById('upload-preview');
        const previewImg = document.getElementById('preview-img');

        input.value = ''; // Reset file input
        previewImg.src = '';
        previewContainer.classList.add('hidden');
        placeholder.classList.remove('hidden');
    }
    </script>
</x-guest-layout>
