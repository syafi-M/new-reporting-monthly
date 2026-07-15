<x-app-layout title="Rating Pekerjaan" subtitle="Daftar penilaian pekerjaan">
    <div class="flex min-h-screen bg-slate-50">
        @include('components.user-sidebar')

        <main class="flex-1 overflow-y-auto p-3 md:p-6">
            <div class="mx-auto max-w-7xl space-y-4">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <p class="text-sm text-slate-500">Total Rating</p>
                        <p class="mt-1 text-2xl font-bold text-slate-900">{{ $summary['total'] ?? 0 }}</p>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <p class="text-sm text-slate-500">Rata-rata</p>
                        <p class="mt-1 text-2xl font-bold text-slate-900">{{ $summary['avg_rate'] ?? 0 }} / 5</p>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <p class="text-sm text-slate-500">Rating Rendah</p>
                        <p class="mt-1 text-2xl font-bold text-slate-900">{{ $summary['low_rate'] ?? 0 }}</p>
                    </div>
                </div>

                <section class="rounded-lg bg-white p-4 shadow-sm md:p-5">
                    <form method="GET" action="{{ route('user-rating-image.index') }}" class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-4">
                        <input name="search" value="{{ request('search') }}" class="input input-bordered input-sm w-full rounded-sm" placeholder="Cari nama, email, komentar">

                        <select name="rate" class="select select-bordered select-sm w-full rounded-sm">
                            <option value="">Semua rating</option>
                            @for ($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}" @selected((string) request('rate') === (string) $i)>{{ $i }} bintang</option>
                            @endfor
                        </select>

                        <select name="sort" class="select select-bordered select-sm w-full rounded-sm">
                            <option value="">Terbaru</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>Terlama</option>
                            <option value="highest" @selected(request('sort') === 'highest')>Rating tertinggi</option>
                            <option value="lowest" @selected(request('sort') === 'lowest')>Rating terendah</option>
                        </select>

                        <div class="flex gap-2">
                            <button class="btn btn-sm rounded-sm bg-blue-600 text-white hover:bg-blue-700" type="submit">Filter</button>
                            <a class="btn btn-sm rounded-sm" href="{{ route('user-rating-image.index') }}">Reset</a>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="table table-zebra w-full text-xs md:text-sm">
                            <thead>
                                <tr>
                                    <th class="p-2 md:p-3">#</th>
                                    <th class="p-2 md:p-3">Nama</th>
                                    <th class="hidden p-2 md:table-cell md:p-3">Email</th>
                                    <th class="p-2 text-center md:p-3">Rating</th>
                                    <th class="hidden p-2 lg:table-cell md:p-3">Komentar</th>
                                    <th class="p-2 md:p-3">Tanggal</th>
                                    <th class="p-2 text-center md:p-3">Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rates as $index => $rate)
                                    <tr class="hover">
                                        <td class="p-2 md:p-3">{{ $rates->firstItem() + $index }}</td>
                                        <td class="p-2 md:p-3">
                                            <div class="font-semibold text-slate-900">{{ $rate->name ?? '-' }}</div>
                                            <div class="text-xs text-slate-500 md:hidden">{{ $rate->email ?? '-' }}</div>
                                        </td>
                                        <td class="hidden p-2 md:table-cell md:p-3">{{ $rate->email ?? '-' }}</td>
                                        <td class="p-2 text-center md:p-3">
                                            <div class="flex justify-center gap-0.5 text-amber-500" aria-label="Rating {{ $rate->rate ?? 0 }} dari 5">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <i class="{{ $i <= (int) ($rate->rate ?? 0) ? 'ri-star-fill' : 'ri-star-line' }}"></i>
                                                @endfor
                                            </div>
                                        </td>
                                        <td class="hidden p-2 lg:table-cell md:p-3">
                                            <div class="max-w-sm truncate" title="{{ $rate->comment ?: '-' }}">
                                                {{ $rate->comment ?: '-' }}
                                            </div>
                                        </td>
                                        <td class="p-2 md:p-3">{{ optional($rate->created_at)->format('d M Y H:i') ?? '-' }}</td>
                                        <td class="p-2 text-center md:p-3">
                                            <button type="button"
                                                class="btn-show-images btn btn-xs rounded-sm border-0 bg-indigo-50 text-indigo-700 hover:bg-indigo-600 hover:text-white"
                                                data-upload-id="{{ $rate->upload_image_id ?? '-' }}"
                                                data-before="{{ $rate->uploadImage?->img_before ? asset('storage/' . $rate->uploadImage->img_before) : '' }}"
                                                data-progress="{{ $rate->uploadImage?->img_proccess ? asset('storage/' . $rate->uploadImage->img_proccess) : '' }}"
                                                data-after="{{ $rate->uploadImage?->img_final ? asset('storage/' . $rate->uploadImage->img_final) : '' }}"
                                                data-note="{{ $rate->uploadImage?->note ?? '-' }}"
                                                data-foto-rating="{{ $rate->image_path_rate ? asset('storage/' . $rate->image_path_rate) : '' }}">
                                                Detail
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="p-8 text-center text-sm text-slate-500">
                                            Belum ada data rating.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    </div>

                    @if ($rates->hasPages())
                        <div class="mt-4">{{ $rates->links() }}</div>
                    @endif
                </section>
            </div>
        </main>
    </div>

    <dialog id="ratingDetailModal" class="modal">
        <div class="modal-box w-11/12 max-w-3xl p-4 md:p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold">Detail #<span id="uploadImageIdLabel">-</span></h3>
                    <p class="mt-1 line-clamp-2 text-xs text-slate-600">Catatan: <span id="uploadImageNote">-</span></p>
                </div>
                <button type="button" class="btn btn-xs" id="closeRatingDetailModalTop">Tutup</button>
            </div>

            <div class="mt-3 grid grid-cols-2 gap-3 md:grid-cols-4">
                <div>
                    <p class="mb-1 text-xs font-semibold">Before</p>
                    <img id="uploadImageBefore" src="https://placehold.co/600x400?text=No+Image" class="aspect-[4/3] w-full rounded-lg border object-cover">
                </div>
                <div>
                    <p class="mb-1 text-xs font-semibold">Progress</p>
                    <img id="uploadImageProgress" src="https://placehold.co/600x400?text=No+Image" class="aspect-[4/3] w-full rounded-lg border object-cover">
                </div>
                <div>
                    <p class="mb-1 text-xs font-semibold">After</p>
                    <img id="uploadImageAfter" src="https://placehold.co/600x400?text=No+Image" class="aspect-[4/3] w-full rounded-lg border object-cover">
                </div>
                <div>
                    <p class="mb-1 text-xs font-semibold">Pengulas</p>
                    <img id="uploadImageRate" src="https://placehold.co/600x400?text=No+Image" class="aspect-[4/3] w-full rounded-lg border object-cover">
                </div>
            </div>

            <div class="modal-action mt-3">
                <button type="button" class="btn" id="closeRatingDetailModal">Tutup</button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop"><button>close</button></form>
    </dialog>

    @push('scripts')
        <script>
            $(function() {
                const modal = document.getElementById('ratingDetailModal');
                const placeholder = 'https://placehold.co/600x400?text=No+Image';

                $(document).on('click', '.btn-show-images', function() {
                    $('#uploadImageIdLabel').text($(this).data('upload-id') || '-');
                    $('#uploadImageNote').text($(this).data('note') || '-');
                    $('#uploadImageBefore').attr('src', $(this).data('before') || placeholder);
                    $('#uploadImageProgress').attr('src', $(this).data('progress') || placeholder);
                    $('#uploadImageAfter').attr('src', $(this).data('after') || placeholder);
                    $('#uploadImageRate').attr('src', $(this).data('foto-rating') || placeholder);
                    modal.showModal();
                });

                $('#closeRatingDetailModal, #closeRatingDetailModalTop').on('click', function() {
                    modal.close();
                });

                $('#ratingDetailModal img').on('error', function() {
                    this.src = placeholder;
                });
            });
        </script>
    @endpush
</x-app-layout>
