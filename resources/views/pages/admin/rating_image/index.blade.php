<x-app-layout title="Rating Gambar" subtitle="Monitoring penilaian gambar dari user">
    <div class="admin-shell flex min-h-screen bg-slate-50">
        @include('components.sidebar-component')

        <div class="admin-content flex-1 p-3 overflow-y-auto md:p-6">
            <div class="container px-3 py-6 mx-auto md:px-4 md:py-8">
                <div class="grid grid-cols-1 gap-3 mb-4 md:grid-cols-3 md:gap-4 md:mb-6">
                    <div class="shadow-sm card bg-base-100">
                        <div class="card-body p-4 md:p-5">
                            <p class="text-xs md:text-sm text-base-content/60">Total Penilaian</p>
                            <h3 class="text-xl font-bold md:text-2xl">{{ $summary['total'] ?? 0 }}</h3>
                        </div>
                    </div>
                    <div class="shadow-sm card bg-base-100">
                        <div class="card-body p-4 md:p-5">
                            <p class="text-xs md:text-sm text-base-content/60">Rata-rata Rating</p>
                            <h3 class="text-xl font-bold md:text-2xl">{{ $summary['avg_rate'] ?? 0 }} <span class="text-base">/ 5</span></h3>
                        </div>
                    </div>
                    <div class="shadow-sm card bg-base-100">
                        <div class="card-body p-4 md:p-5">
                            <p class="text-xs md:text-sm text-base-content/60">Perlu Ditinjau (<=2)</p>
                            <h3 class="text-xl font-bold md:text-2xl">{{ $summary['low_rate'] ?? 0 }}</h3>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-xl card admin-panel">
                    <div class="card-body p-4 md:p-5">
                        @if (session('success'))
                            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                                {{ session('success') }}
                            </div>
                        @endif

                        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between md:gap-4 mb-4">
                            <form method="GET" action="{{ route('admin-rating-image.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                <div class="form-control">
                                    <label for="search" class="label">
                                        <span class="text-xs md:text-sm label-text">Pencarian</span>
                                    </label>
                                    <input id="search" name="search" type="text" value="{{ request('search') }}"
                                        placeholder="Nama / Email / Komentar"
                                        class="input input-bordered input-xs md:input-sm rounded-sm w-full" />
                                </div>

                                <div class="form-control">
                                    <label for="rate" class="label">
                                        <span class="text-xs md:text-sm label-text">Filter Rating</span>
                                    </label>
                                    <select id="rate" name="rate"
                                        class="select select-bordered select-xs md:select-sm rounded-sm w-full">
                                        <option value="">Semua Rating</option>
                                        @for ($i = 5; $i >= 1; $i--)
                                            <option value="{{ $i }}" {{ (string) request('rate') === (string) $i ? 'selected' : '' }}>
                                                {{ $i }} Bintang
                                            </option>
                                        @endfor
                                    </select>
                                </div>

                                <div class="form-control">
                                    <label for="sort" class="label">
                                        <span class="text-xs md:text-sm label-text">Urutkan</span>
                                    </label>
                                    <select id="sort" name="sort"
                                        class="select select-bordered select-xs md:select-sm rounded-sm w-full">
                                        <option value="">Terbaru</option>
                                        <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Terlama</option>
                                        <option value="highest" {{ request('sort') === 'highest' ? 'selected' : '' }}>Rating Tertinggi</option>
                                        <option value="lowest" {{ request('sort') === 'lowest' ? 'selected' : '' }}>Rating Terendah</option>
                                    </select>
                                </div>

                                <div class="form-control">
                                    <label class="label"><span class="text-xs md:text-sm label-text">&nbsp;</span></label>
                                    <div class="flex gap-2">
                                        <button type="submit"
                                            class="btn btn-xs md:btn-sm rounded-sm border-0 bg-blue-500/20 text-blue-600 hover:bg-blue-600 hover:text-white">
                                            Filter
                                        </button>
                                        <a href="{{ url()->current() }}"
                                            class="btn btn-xs md:btn-sm rounded-sm border-0 bg-red-500/20 text-red-600 hover:bg-red-600 hover:text-white">
                                            Reset
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="table w-full text-xs table-zebra md:text-sm">
                                <thead>
                                    <tr>
                                        <th class="p-2 md:p-3">#</th>
                                        <th class="p-2 md:p-3">Nama</th>
                                        <th class="hidden p-2 md:p-3 sm:table-cell">Email</th>
                                        <th class="p-2 md:p-3 text-center">Rating</th>
                                        <th class="hidden p-2 md:p-3 lg:table-cell">Komentar</th>
                                        <th class="hidden p-2 md:p-3 md:table-cell">Upload Image ID</th>
                                        <th class="p-2 md:p-3">Tanggal</th>
                                        <th class="p-2 text-center md:p-3">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($rates as $index => $rate)
                                        <tr class="hover">
                                            <td class="p-2 md:p-3">{{ $rates->firstItem() + $index }}</td>
                                            <td class="p-2 md:p-3">
                                                <div class="font-medium">{{ $rate->name ?? '-' }}</div>
                                            </td>
                                            <td class="hidden p-2 md:p-3 sm:table-cell">{{ $rate->email ?? '-' }}</td>
                                            <td class="p-2 md:p-3 text-center">
                                                <span class="badge badge-warning badge-sm">
                                                    {{ $rate->rate ?? 0 }} / 5
                                                </span>
                                            </td>
                                            <td class="hidden p-2 md:p-3 lg:table-cell">
                                                <div class="max-w-xs truncate" title="{{ $rate->comment ?? '-' }}">
                                                    {{ $rate->comment ?? '-' }}
                                                </div>
                                            </td>
                                            <td class="hidden p-2 md:p-3 md:table-cell">
                                                @if ($rate->upload_image_id)
                                                    <button type="button"
                                                        class="btn btn-xs md:btn-sm rounded-sm border-0 bg-indigo-500/20 text-indigo-600 hover:bg-indigo-600 hover:text-white btn-show-images"
                                                        data-upload-id="{{ $rate->upload_image_id }}"
                                                        data-before="{{ $rate->uploadImage?->img_before ? asset('storage/' . $rate->uploadImage->img_before) : '' }}"
                                                        data-progress="{{ $rate->uploadImage?->img_proccess ? asset('storage/' . $rate->uploadImage->img_proccess) : '' }}"
                                                        data-after="{{ $rate->uploadImage?->img_final ? asset('storage/' . $rate->uploadImage->img_final) : '' }}"
                                                        data-note="{{ $rate->uploadImage?->note ?? '-' }}"
                                                        data-foto-rating="{{ $rate->image_path_rate ? asset('storage/' . $rate->image_path_rate) : '' }}">
                                                        #{{ $rate->upload_image_id }} (klik untuk melihat gambar)
                                                    </button>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="p-2 md:p-3">{{ optional($rate->created_at)->format('d M Y H:i') ?? '-' }}</td>
                                            <td class="p-2 md:p-3">
                                                <div class="flex justify-center gap-2">
                                                    <button type="button"
                                                        class="btn btn-xs md:btn-sm rounded-sm border-0 bg-yellow-500/20 text-yellow-700 hover:bg-yellow-600 hover:text-white btn-edit-rate"
                                                        data-id="{{ $rate->id }}"
                                                        data-name="{{ $rate->name }}"
                                                        data-email="{{ $rate->email }}"
                                                        data-rate="{{ $rate->rate }}"
                                                        data-comment="{{ $rate->comment }}">
                                                        Edit
                                                    </button>
                                                    <button type="button"
                                                        class="btn btn-xs md:btn-sm rounded-sm border-0 bg-red-500/20 text-red-700 hover:bg-red-700 hover:text-white btn-delete-rate"
                                                        data-id="{{ $rate->id }}"
                                                        data-name="{{ $rate->name ?? 'Rating' }}">
                                                        Hapus
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="p-8 text-center text-base-content/60">
                                                Belum ada data rating gambar.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 text-xs md:text-sm text-base-content/60">
                            5 bintang: {{ $summary['five_star'] ?? 0 }} data.
                        </div>
                        @if ($rates->hasPages())
                            <div class="mt-3">
                                {{ $rates->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <dialog id="editRateModal" class="modal">
        <div class="w-11/12 max-w-lg modal-box">
            <h3 class="text-lg font-bold">Edit Rating</h3>
            <form id="editRateForm" method="POST" class="mt-4 space-y-3">
                @csrf
                @method('PUT')

                <div class="form-control">
                    <label class="label"><span class="label-text">Nama</span></label>
                    <input id="edit_name" name="name" type="text" class="input input-bordered w-full" required>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Email</span></label>
                    <input id="edit_email" name="email" type="email" class="input input-bordered w-full" required>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Rating</span></label>
                    <select id="edit_rate" name="rate" class="select select-bordered w-full" required>
                        @for ($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Komentar</span></label>
                    <textarea id="edit_comment" name="comment" class="textarea textarea-bordered w-full" rows="4"></textarea>
                </div>

                <div class="modal-action">
                    <button type="button" class="btn btn-ghost" id="closeEditModal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop"><button>close</button></form>
    </dialog>

    <dialog id="deleteRateModal" class="modal">
        <div class="w-11/12 max-w-md modal-box">
            <h3 class="text-lg font-bold text-red-700">Hapus Rating</h3>
            <p class="py-3 text-sm">Yakin ingin menghapus data <span id="deleteRateName" class="font-semibold"></span>?</p>
            <form id="deleteRateForm" method="POST" class="modal-action">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-ghost min-w-[100px]" id="closeDeleteModal">Batal</button>
                <button type="submit" class="btn btn-error text-white min-w-[100px]">Hapus</button>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop"><button>close</button></form>
    </dialog>

    <dialog id="uploadImageModal" class="modal">
        <div class="w-11/12 max-w-4xl modal-box">
            <h3 class="text-lg font-bold">Detail Upload Image #<span id="uploadImageIdLabel"></span></h3>
            <p class="mt-1 text-sm text-base-content/70">Keterangan: <span id="uploadImageNote">-</span></p>

            <div class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-3">
                <div>
                    <p class="mb-2 text-sm font-semibold">Before</p>
                    <img id="uploadImageBefore" src="https://placehold.co/600x400?text=No+Image" class="w-full rounded-lg border border-base-300 object-cover aspect-[4/3]">
                </div>
                <div>
                    <p class="mb-2 text-sm font-semibold">Progress</p>
                    <img id="uploadImageProgress" src="https://placehold.co/600x400?text=No+Image" class="w-full rounded-lg border border-base-300 object-cover aspect-[4/3]">
                </div>
                <div>
                    <p class="mb-2 text-sm font-semibold">After</p>
                    <img id="uploadImageAfter" src="https://placehold.co/600x400?text=No+Image" class="w-full rounded-lg border border-base-300 object-cover aspect-[4/3]">
                </div>
                <div class="md:col-span-3 flex flex-col justify-center mx-[25%]">
                    <p class="mb-2 text-sm font-semibold">Foto Dari Pengulas</p>
                    <img id="uploadImageRate" src="https://placehold.co/600x400?text=No+Image" class="w-full rounded-lg border border-base-300 object-cover aspect-[4/3]">
                </div>
            </div>

            <div class="modal-action">
                <button type="button" class="btn btn-ghost" id="closeUploadImageModal">Tutup</button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop"><button>close</button></form>
    </dialog>

    @push('scripts')
        <script>
            $(function() {
                const editModal = document.getElementById('editRateModal');
                const deleteModal = document.getElementById('deleteRateModal');
                const imageModal = document.getElementById('uploadImageModal');
                const placeholder = 'https://placehold.co/600x400?text=No+Image';
                const updateUrlTemplate = `{{ route('admin-rating-image.update', ['admin_rating_image' => '__ID__']) }}`;
                const deleteUrlTemplate = `{{ route('admin-rating-image.destroy', ['admin_rating_image' => '__ID__']) }}`;

                $(document).on('click', '.btn-edit-rate', function() {
                    const id = $(this).data('id');
                    $('#editRateForm').attr('action', updateUrlTemplate.replace('__ID__', id));
                    $('#edit_name').val($(this).data('name') || '');
                    $('#edit_email').val($(this).data('email') || '');
                    $('#edit_rate').val($(this).data('rate') || 1);
                    $('#edit_comment').val($(this).data('comment') || '');
                    editModal.showModal();
                });

                $(document).on('click', '.btn-delete-rate', function() {
                    const id = $(this).data('id');
                    $('#deleteRateForm').attr('action', deleteUrlTemplate.replace('__ID__', id));
                    $('#deleteRateName').text($(this).data('name') || 'rating ini');
                    deleteModal.showModal();
                });

                $(document).on('click', '.btn-show-images', function() {
                    const before = $(this).data('before') || placeholder;
                    const progress = $(this).data('progress') || placeholder;
                    const after = $(this).data('after') || placeholder;
                    const uploadId = $(this).data('upload-id') || '-';
                    const note = $(this).data('note') || '-';
                    const fotoRating = $(this).data('foto-rating') || placeholder;

                    $('#uploadImageIdLabel').text(uploadId);
                    $('#uploadImageNote').text(note);
                    $('#uploadImageBefore').attr('src', before);
                    $('#uploadImageProgress').attr('src', progress);
                    $('#uploadImageAfter').attr('src', after);
                    $('#uploadImageRate').attr('src', fotoRating);
                    
                    imageModal.showModal();
                });

                ['#uploadImageBefore', '#uploadImageProgress', '#uploadImageAfter', '#uploadImageRate'].forEach(function(selector) {
                    $(selector).on('error', function() {
                        this.src = placeholder;
                    });
                });

                $('#closeEditModal').on('click', function() {
                    editModal.close();
                });
                $('#closeDeleteModal').on('click', function() {
                    deleteModal.close();
                });
                $('#closeUploadImageModal').on('click', function() {
                    imageModal.close();
                });
            });
        </script>
    @endpush
</x-app-layout>
