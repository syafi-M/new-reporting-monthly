<x-app-layout title="Data Photo Progress" subtitle="Menampilkan Data Photo Yang Sudah Ada">
    <div class="flex min-h-screen pb-10 admin-shell bg-slate-50">
        @include('components.sidebar-component')
        <div class="flex-1 p-3 overflow-y-auto admin-content md:p-6">
            <div class="container px-3 py-6 mx-auto md:px-4 md:py-8">
                <div class="m-3 bg-white shadow-xl md:m-5 card admin-panel">
                    <div class="card-body">
                        <div class="flex flex-col gap-3 px-4 py-3 mb-4 admin-filter-card md:px-5 md:mb-6">
                            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h2 class="text-xl font-semibold md:text-2xl text-slate-900">Data Photo Progress</h2>
                                    <p class="text-xs text-slate-500 md:text-sm">Filter data, pilih foto, lalu export atau hapus sesuai kebutuhan.</p>
                                </div>
                                <span id="activeFilterBadge"
                                    class="hidden px-3 py-1 text-xs font-medium text-blue-700 border border-blue-100 rounded-full w-fit bg-blue-50">
                                    Filter aktif
                                </span>
                            </div>

                            <!-- Filter Section -->
                            <div class="rounded-lg bg-white/45">
                                <div class="flex flex-wrap items-end gap-2 md:gap-3">
                                    <div class="w-full form-control sm:w-52 lg:w-56">
                                        <label for="mitraFilter" class="min-h-0 px-0 py-1 label">
                                            <span class="text-xs font-medium label-text">Mitra</span>
                                        </label>
                                        <select name="mitraFilter" id="mitraFilter"
                                            class="w-full rounded-md select select-bordered select-sm">
                                            <option selected value="">Semua Mitra</option>
                                            @foreach ($client as $cl)
                                                <option value="{{ $cl->id }}">{{ ucwords(strtolower($cl->name)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div id="filterUserContainer" class="w-full form-control sm:w-52 lg:w-56">
                                        <label for="userFilter" class="min-h-0 px-0 py-1 label">
                                            <span class="text-xs font-medium label-text">User</span>
                                        </label>
                                        <select name="userFilter" id="userFilter"
                                            class="w-full rounded-md select select-bordered select-sm" disabled>
                                            <option selected value="">Pilih Mitra Terlebih Dahulu</option>
                                        </select>
                                    </div>
                                    <div class="form-control w-[calc(50%-0.25rem)] sm:w-36">
                                        <label for="monthFilter" class="min-h-0 px-0 py-1 label">
                                            <span class="text-xs font-medium label-text">Bulan</span>
                                        </label>
                                        <select id="monthFilter"
                                            class="w-full rounded-md select select-bordered select-sm">
                                            <option selected value="">Semua Bulan</option>
                                            <option value="1">Januari</option>
                                            <option value="2">Februari</option>
                                            <option value="3">Maret</option>
                                            <option value="4">April</option>
                                            <option value="5">Mei</option>
                                            <option value="6">Juni</option>
                                            <option value="7">Juli</option>
                                            <option value="8">Agustus</option>
                                            <option value="9">September</option>
                                            <option value="10">Oktober</option>
                                            <option value="11">November</option>
                                            <option value="12">Desember</option>
                                        </select>
                                    </div>

                                    <div class="form-control w-[calc(50%-0.25rem)] sm:w-28">
                                        <label for="yearFilter" class="min-h-0 px-0 py-1 label">
                                            <span class="text-xs font-medium label-text">Tahun</span>
                                        </label>
                                        <select id="yearFilter"
                                            class="w-full rounded-md select select-bordered select-sm">
                                            <option selected value="">Semua Tahun</option>
                                            @for ($year = now()->year; $year >= 2024; $year--)
                                                <option value="{{ $year }}">{{ $year }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="flex w-full gap-2 sm:w-auto">
                                        <button id="applyFilter"
                                            class="flex-1 border-0 rounded-md btn btn-sm sm:flex-none bg-blue-500/20 hover:bg-blue-600 hover:text-white">
                                            <i class="text-sm ri-filter-3-line"></i>
                                            Terapkan
                                        </button>
                                        <button id="clearFilter"
                                            class="flex-1 border-0 rounded-md btn btn-sm sm:flex-none bg-red-500/20 hover:bg-red-600 hover:text-white">
                                            <i class="text-sm ri-refresh-line"></i>
                                            Reset
                                        </button>
                                        <button id="generatePdf"
                                            class="flex-1 border-0 rounded-md btn btn-sm sm:flex-none bg-green-500/20 hover:bg-green-600 hover:text-white">
                                            <i class="text-sm ri-download-cloud-2-line"></i><span>PDF</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Selection Controls -->
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <p id="selectionSummary" class="text-xs text-slate-500 md:text-sm">Belum ada data dipilih.</p>
                                <div class="flex flex-wrap gap-2">
                                    <button id="selectAll"
                                        class="border-0 rounded-md btn btn-xs md:btn-sm bg-blue-500/20 hover:bg-blue-600 hover:text-white">
                                        <i class="ri-checkbox-multiple-line"></i>
                                        Select All
                                    </button>
                                    <button id="deselectAll"
                                        class="border-0 rounded-md btn btn-xs md:btn-sm bg-red-500/20 hover:bg-red-600 hover:text-white">
                                        <i class="ri-checkbox-blank-line"></i>
                                        Deselect All
                                    </button>
                                    <button id="deleteSelected"
                                        class="text-red-700 border-0 rounded-md btn btn-xs md:btn-sm bg-red-600/20 hover:bg-red-700 hover:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                                        disabled>
                                        <i class="mr-1 text-xs ri-delete-bin-6-line md:text-sm"></i><span
                                            class="hidden sm:inline">Delete Selected</span>
                                    </button>
                                </div>
                            </div>

                            <div id="pdf-progress-container" class="hidden"
                                style="font-family: Arial, sans-serif; margin-top: 20px;">
                                <h3>PDF Generation Progress</h3>
                                <progress id="pdf-progress" class="w-56 progress progress-success" value="0"
                                    max="100"></progress>
                            </div>

                        </div>

                        <div class="overflow-x-auto">
                            <table class="table w-full text-xs table-zebra md:text-sm">
                                <thead>
                                    <tr>
                                        <th class="p-2 md:p-3">
                                            <input type="checkbox" id="headerCheckbox" class="checkbox checkbox-xs">
                                        </th>
                                        <th class="p-2 md:p-3">ID</th>
                                        <th class="p-2 md:p-3">Nama Mitra</th>
                                        <th class="p-2 md:p-3">Nama User</th>
                                        <th class="hidden p-2 md:p-3 sm:table-cell">Before</th>
                                        <th class="hidden p-2 md:p-3 md:table-cell">Progress</th>
                                        <th class="hidden p-2 md:p-3 lg:table-cell">After</th>
                                        <th class="hidden p-2 md:p-3 md:table-cell">Keterangan</th>
                                        <th class="p-2 text-center md:p-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody">
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <span class="loading loading-spinner loading-lg"></span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div id="pagination" class="flex justify-center mt-4 md:mt-6"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <dialog id="fotoModal" class="modal">
        <div class="w-11/12 max-w-5xl p-0 overflow-hidden modal-box md:w-10/11">
            <div class="flex items-start justify-between gap-3 px-4 py-3 border-b border-base-200 bg-slate-50 md:px-6 md:py-4">
                <div>
                    <h3 class="text-lg font-bold md:text-xl text-slate-900" id="modalTitle">Edit Photo Progress</h3>
                    <p class="mt-1 text-xs text-base-content/60 md:text-sm">Perbarui mitra, catatan, atau unggah gambar pengganti.</p>
                </div>
                <button type="button" class="btn btn-sm btn-circle btn-ghost" id="btnCloseTop" aria-label="Tutup modal">
                    <i class="ri-close-line"></i>
                </button>
            </div>

            <form id="photoForm" method="dialog" class="max-h-[78vh] overflow-hidden">
                <input type="hidden" id="photoId" name="id">
                <input type="hidden" id="formMethod" value="POST">

                <div class="p-4 overflow-y-auto max-h-[calc(78vh-132px)] md:p-6">
                    <div class="grid gap-5 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)]">
                        <div class="space-y-4">
                            <div class="w-full form-control">
                                <label class="py-1 label">
                                    <span class="text-xs font-medium label-text md:text-sm">Nama Mitra <span
                                            class="text-error">*</span></span>
                                </label>
                                <select name="client_id" id="client_id"
                                    class="w-full rounded-md select select-bordered select-sm" required>
                                    <option value="" disabled selected>Select Client</option>
                                    @foreach ($client as $cl)
                                        <option value="{{ $cl->id }}">{{ $cl->name }}</option>
                                    @endforeach
                                </select>
                                <label class="hidden label" id="error-client_id">
                                    <span class="label-text-alt text-error"></span>
                                </label>
                            </div>

                            <div class="w-full form-control">
                                <label class="py-1 label">
                                    <span class="text-xs font-medium label-text md:text-sm">Keterangan</span>
                                </label>
                                <textarea class="w-full rounded-md min-h-32 textarea textarea-bordered textarea-sm" id="note"
                                    name="note" placeholder="Tambahkan catatan progress..."></textarea>
                                <label class="hidden label" id="error-note">
                                    <span class="label-text-alt text-error"></span>
                                </label>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <p class="text-xs font-semibold tracking-wide uppercase text-slate-500">Foto Progress</p>
                            <div class="grid gap-3 md:grid-cols-3">
                                <div class="p-3 border rounded-lg form-control border-slate-200 bg-slate-50/70">
                                    <label class="py-1 label">
                                        <span class="text-xs font-medium label-text md:text-sm">Before</span>
                                    </label>
                                    <div id="current-img_before" class="mb-3"></div>
                                    <input type="file" class="w-full file-input file-input-bordered file-input-sm"
                                        id="img_before" name="img_before" accept="image/*">
                                    <label class="hidden label" id="error-img_before">
                                        <span class="label-text-alt text-error"></span>
                                    </label>
                                </div>

                                <div class="p-3 border rounded-lg form-control border-slate-200 bg-slate-50/70">
                                    <label class="py-1 label">
                                        <span class="text-xs font-medium label-text md:text-sm">Progress</span>
                                    </label>
                                    <div id="current-img_proccess" class="mb-3"></div>
                                    <input type="file" class="w-full file-input file-input-bordered file-input-sm"
                                        id="img_proccess" name="img_proccess" accept="image/*">
                                    <label class="hidden label" id="error-img_proccess">
                                        <span class="label-text-alt text-error"></span>
                                    </label>
                                </div>

                                <div class="p-3 border rounded-lg form-control border-slate-200 bg-slate-50/70">
                                    <label class="py-1 label">
                                        <span class="text-xs font-medium label-text md:text-sm">After</span>
                                    </label>
                                    <div id="current-img_final" class="mb-3"></div>
                                    <input type="file" class="w-full file-input file-input-bordered file-input-sm"
                                        id="img_final" name="img_final" accept="image/*">
                                    <label class="hidden label" id="error-img_final">
                                        <span class="label-text-alt text-error"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sticky bottom-0 px-4 py-3 border-t modal-action border-base-200 bg-base-100 md:px-6 md:py-4">
                    <button type="button" class="btn btn-xs md:btn-sm btn-ghost" id="btnClose">Batal</button>
                    <button type="submit" class="btn btn-xs md:btn-sm btn-primary" id="btnSave">
                        <span class="hidden loading loading-spinner loading-sm" id="btnSpinner"></span>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

    {{-- Delete Confirmation Modal --}}
    <dialog id="deleteConfirmModal" class="modal">
        <div class="w-11/12 max-w-md p-0 overflow-hidden modal-box">
            <div class="px-5 py-4 border-b border-red-100 bg-red-50">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 text-red-700 bg-red-100 rounded-full">
                        <i class="text-xl ri-delete-bin-6-line"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900" id="deleteModalTitle">Hapus Photo Progress</h3>
                        <p class="text-xs text-slate-500">Aksi ini tidak bisa dibatalkan.</p>
                    </div>
                </div>
            </div>
            <div class="px-5 py-4">
                <p class="text-sm text-slate-600" id="deleteModalMessage">Yakin ingin menghapus data ini?</p>
            </div>
            <div class="px-5 py-4 border-t modal-action border-base-200 bg-base-100">
                <button type="button" class="btn btn-sm btn-ghost" id="cancelDelete">Batal</button>
                <button type="button"
                    class="text-white bg-red-600 border-0 rounded-md btn btn-sm min-w-24 hover:bg-red-700"
                    id="confirmDelete">
                    <span class="hidden loading loading-spinner loading-xs" id="deleteSpinner"></span>
                    Hapus
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

    <dialog id="ratingDetailModal" class="modal">
        <div class="w-11/12 max-w-xl overflow-hidden border shadow-2xl modal-box rounded-2xl border-slate-200 bg-white p-0">
            <div class="relative px-5 py-5 border-b sm:px-6 border-slate-200 bg-gradient-to-r from-slate-50 via-blue-50/70 to-cyan-50/60">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] uppercase tracking-[0.18em] text-slate-500 font-semibold">Review Foto</p>
                        <h3 class="mt-1 text-lg font-bold text-slate-900 sm:text-xl">Detail Penilaian</h3>
                    </div>
                    <button type="button" class="btn btn-xs btn-circle border-0 bg-white/80 text-slate-500 hover:bg-white hover:text-slate-800" id="closeRatingDetailTop">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
                <div class="mt-4">
                    <span id="ratingDetailBadge" class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-slate-200 text-slate-700">
                        -
                    </span>
                </div>
            </div>
            <div class="px-5 py-5 space-y-4 sm:px-6">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">Dinilai Oleh</p>
                        <p id="ratingDetailBy" class="mt-1 text-sm font-medium text-slate-800">-</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">Waktu Penilaian</p>
                        <p id="ratingDetailAt" class="mt-1 text-sm font-medium text-slate-800">-</p>
                    </div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white px-3 py-3.5">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">Alasan Penilaian</p>
                    <p id="ratingDetailReason" class="mt-2 text-sm leading-relaxed text-slate-700 whitespace-pre-wrap">-</p>
                </div>
            </div>
            <div class="flex justify-end gap-2 px-5 py-4 border-t sm:px-6 border-slate-200 bg-slate-50/60">
                <button type="button" class="btn btn-sm rounded-lg border-0 bg-slate-200 text-slate-700 hover:bg-slate-300" id="closeRatingDetail">Tutup</button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>Tutup</button>
        </form>
    </dialog>

    {{-- Enhanced Image Preview Modal --}}
    <div id="imagePreviewModal"
        class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-90">
        <div class="relative max-w-6xl max-h-[90vh] w-full">
            <!-- Top controls -->
            <div class="absolute left-0 right-0 z-20 flex justify-center top-4">
                <div class="flex gap-2 p-1 bg-black rounded-full bg-opacity-70">
                    <button id="closeImagePreview"
                        class="p-2 text-white transition-colors rounded-full hover:bg-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 md:w-6 md:h-6" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <button id="rotateImage" class="p-2 text-white transition-colors rounded-full hover:bg-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 md:w-6 md:h-6" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                    <button id="zoomInImage" class="p-2 text-white transition-colors rounded-full hover:bg-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 md:w-6 md:h-6" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                        </svg>
                    </button>
                    <button id="zoomOutImage" class="p-2 text-white transition-colors rounded-full hover:bg-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 md:w-6 md:h-6" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7" />
                        </svg>
                    </button>
                    <button id="downloadImage"
                        class="p-2 text-white transition-colors rounded-full hover:bg-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 md:w-6 md:h-6" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Navigation buttons -->
            <button id="prevImage"
                class="absolute z-10 p-2 text-white transition-all transform -translate-y-1/2 bg-black bg-opacity-50 rounded-full md:p-3 left-2 md:left-4 top-1/2 hover:bg-opacity-70">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 md:w-8 md:h-8" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <button id="nextImage"
                class="absolute z-10 p-2 text-white transition-all transform -translate-y-1/2 bg-black bg-opacity-50 rounded-full md:p-3 right-2 md:right-4 top-1/2 hover:bg-opacity-70">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 md:w-8 md:h-8" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            <!-- Image container -->
            <div class="flex items-center justify-center w-full h-[75vh] md:h-[85vh] overflow-hidden">
                <img id="previewImage" src="" alt="Preview"
                    class="object-contain max-w-full max-h-full transition-transform duration-300">
            </div>

            <!-- Image info -->
            <div id="imageInfo" class="absolute left-0 right-0 text-xs text-center text-white md:text-sm bottom-4">
                <span id="imageCounter"></span>
            </div>
        </div>
    </div>

    <audio id="notify-sound" src="{{ asset('/sound/done.mp3') }}" preload="auto"></audio>

    @push('scripts')
        <script src="{{ asset('js/fotoPages.js') }}"></script>
        <script>
            function askNotificationPermission() {
                if (!("Notification" in window)) {
                    console.warn("This browser does not support notifications.");
                    return;
                }

                if (Notification.permission === "granted") {
                    return;
                }

                if (Notification.permission !== "denied") {
                    Notification.requestPermission().then(permission => {
                        console.log("Notification permission:", permission);
                    });
                }
            }

            askNotificationPermission();

            function showNotification(title, options) {
                if (Notification.permission === "granted") {
                    new Notification(title, options);
                }
            }


            function queueProgress(pageIndex, totalPages) {
                const list = document.getElementById('pdf-progress-list');
                const listItem = document.createElement('li');
                listItem.id = `pdf-page-${pageIndex}`;
                listItem.textContent = `Queued page ${pageIndex} of ${totalPages}`;
                list.appendChild(listItem);
            }

            function setProgress(percent) {
                const bar = document.getElementById('pdf-progress');
                $('#pdf-progress-container').removeClass('hidden');
                if (bar) {
                    bar.value = percent;
                }
            }


            function updateProgress(pageIndex, status) {
                const listItem = document.getElementById(`pdf-page-${pageIndex}`);
                $('#pdf-progress-container').removeClass('hidden');
                if (listItem) {
                    listItem.textContent = `${status} page ${pageIndex}`;
                }
            }

            function clearProgressQueue() {
                const bar = document.getElementById('pdf-progress');
                if (bar) {
                    bar.value = 0;
                }
            }


            $(document).ready(function() {
                let currentPage = 1;
                let currentMonth = '';
                let activeFilters = {
                    month: '',
                    year: '',
                    mitra: '',
                    user: ''
                };
                let currentRequest = null;
                let pendingDelete = {
                    type: null,
                    ids: []
                };
                const modal = document.getElementById('fotoModal');
                const deleteModal = document.getElementById('deleteConfirmModal');
                const imageModal = document.getElementById('imagePreviewModal');
                const pdfViewerModal = document.getElementById('pdfViewerModal');

                // Image preview state
                let imagePreviewState = {
                    images: [],
                    currentIndex: 0,
                    rotation: 0,
                    zoom: 1
                };
                let pdfLibrariesPromise = null;

                const ASSET_URL = "{{ asset('storage') }}";
                const CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                const PLACEHOLDER_IMAGE = 'https://placehold.co/600x400?text=Image+Not+Found';
                const THUMB_PLACEHOLDER =
                    'data:image/svg+xml;charset=UTF-8,%3Csvg xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22 width%3D%2280%22 height%3D%2280%22 viewBox%3D%220 0 80 80%22%3E%3Crect width%3D%2280%22 height%3D%2280%22 rx%3D%228%22 fill%3D%22%23f1f5f9%22%2F%3E%3Cpath d%3D%22M23 52l11-13 8 9 6-7 9 11H23z%22 fill%3D%22%23cbd5e1%22%2F%3E%3Ccircle cx%3D%2254%22 cy%3D%2226%22 r%3D%226%22 fill%3D%22%23cbd5e1%22%2F%3E%3C%2Fsvg%3E';
                let tableImageObserver = null;

                function init() {
                    loadData();
                    setupImagePreviewControls();
                    setupFilterControls();
                    setupCheckboxControls();
                    setupMassDelete();
                    setupDeleteModal();
                    setupPdfGeneration();
                }

                // Load data (defensive)
                function loadData(page = 1, month = null, year = null, mitra = null, user = null) {
                    // Abort previous request if any
                    if (currentRequest && currentRequest.readyState !== 4) {
                        currentRequest.abort();
                    }

                    $('#tableBody').html(`
                        <tr>
                            <td colspan="9" class="py-8 text-center">
                                <span class="loading loading-spinner loading-lg"></span>
                            </td>
                        </tr>
                    `);

                    currentRequest = $.ajax({
                        url: '{{ route('admin.upload.index') }}',
                        type: 'GET',
                        data: {
                            page: page,
                            month,
                            year,
                            mitra,
                            user
                        },
                        dataType: 'json',
                        success: function(response) {
                            // defensive checks
                            if (!response) {
                                console.error('Empty JSON response');
                                Notify('Empty response from server', null, null, 'error');
                                return;
                            }

                            if (response.status) {
                                const paginator = normalizePaginator(response.data);

                                try {
                                    renderTable(paginator.items);
                                } catch (error) {
                                    console.error('Error rendering table:', error);
                                    $('#tableBody').html(
                                        '<tr><td colspan="9" class="py-8 text-center text-error">Error rendering table data.</td></tr>'
                                    );
                                }

                                renderPagination(paginator);
                                currentPage = page;
                            } else {
                                // server returned JSON but with status false
                                $('#tableBody').html(
                                    '<tr><td colspan="9" class="py-8 text-center text-base-content/60">No data</td></tr>'
                                );
                            }
                        },
                        error: function(xhr, textStatus, errorThrown) {
                            if (textStatus === 'abort') {
                                console.log('Previous request aborted');
                                return;
                            }

                            // show useful debugging info
                            {{-- console.error('AJAX error', textStatus, errorThrown); --}}
                            {{-- console.log('Response text:', xhr); --}}
                            Notify('Error loading data', null, null, 'error');

                            // if server returned HTML (login page / error page):
                            // inspect xhr.responseText in devtools
                        }
                    });
                }

                function normalizePaginator(data) {
                    const meta = data?.meta || {};
                    const perPage = Number(data?.per_page || meta.per_page || 0);
                    const total = Number(data?.total || meta.total || 0);

                    return {
                        items: Array.isArray(data?.data) ? data.data : [],
                        currentPage: Number(data?.current_page || meta.current_page || 1),
                        perPage,
                        total,
                        lastPage: Number(data?.last_page || meta.last_page || (perPage && total ?
                            Math.ceil(total / perPage) : 1)),
                    };
                }

                function getSelectedFilters() {
                    return {
                        month: $('#monthFilter').val() || '',
                        year: $('#yearFilter').val() || '',
                        mitra: $('#mitraFilter').val() || '',
                        user: $('#userFilter').val() || ''
                    };
                }

                function setActiveFilters(filters = getSelectedFilters()) {
                    activeFilters = {
                        month: filters.month || '',
                        year: filters.year || '',
                        mitra: filters.mitra || '',
                        user: filters.user || ''
                    };
                }

                function loadDataWithActiveFilters(page = currentPage) {
                    loadData(
                        page,
                        activeFilters.month,
                        activeFilters.year,
                        activeFilters.mitra,
                        activeFilters.user
                    );
                }

                // Render table
                function renderTable(data) {
                    if (data.length === 0) {
                        $('#tableBody').html(
                            '<tr><td colspan="9" class="py-8 text-center text-base-content/60">No data available</td></tr>'
                        );
                        updateDeleteSelectedState();
                        return;
                    }

                    const html = data.map((item, index) => {
                        const rating = item.upload_rating || item.uploadRating || item.fixed_image || item.fixedImage || null;
                        const hasRating = Boolean(rating && rating.rating_value);
                        const ratedBy = rating?.rated_by?.nama_lengkap || rating?.ratedBy?.nama_lengkap || '-';
                        const ratedAt = rating?.rated_at || '-';
                        const ratingReason = rating?.rating_reason || '-';
                        const ratingValue = rating?.rating_value || '-';

                        return `
                        <tr class="hover">
                            <td class="px-2 py-2 md:px-3">
                                <input type="checkbox" class="row-checkbox checkbox checkbox-xs" data-id="${item.id}">
                            </td>
                            <td class="px-2 py-2 md:px-3">${index + 1}</td>
                            <td class="px-2 py-2 md:px-3">
                                <div class="max-w-[100px] md:max-w-xs truncate" title="${item.clients?.name || '-'}">
                                    ${kapitalName(item.clients?.name || '-')}
                                </div>
                            </td>
                            <td class="px-2 py-2 md:px-3">
                                <div class="max-w-[100px] md:max-w-xs truncate" title="${item.user?.nama_lengkap || '-'}">
                                    ${kapitalName(item.user?.nama_lengkap || '-')}
                                </div>
                            </td>
                            <td class="hidden px-2 py-2 md:px-3 sm:table-cell">
                                ${renderImageCell(item.img_before, 'Before')}
                            </td>
                            <td class="hidden px-2 py-2 text-center md:px-3 md:table-cell">
                                ${renderImageCell(item.img_proccess, '-')}
                            </td>
                            <td class="hidden px-2 py-2 text-center md:px-3 lg:table-cell">
                                ${renderImageCell(item.img_final, '-')}
                            </td>
                            <td class="hidden px-2 py-2 md:px-3 md:table-cell">
                                <div class="max-w-[100px] md:max-w-xs truncate" title="${item.note || '-'}">
                                    ${item.note || '-'}
                                </div>
                            </td>
                            <td class="px-2 py-2 md:px-3">
                                <div class="flex justify-center gap-1 md:gap-2">
                                    ${hasRating ? `
                                    <button
                                        class="text-cyan-700 border-0 rounded-sm btn btn-xs md:btn-sm bg-cyan-500/20 hover:bg-cyan-600 hover:text-white btn-rating-detail"
                                        data-rating-value="${String(ratingValue).replace(/"/g, '&quot;')}"
                                        data-rating-reason="${String(ratingReason).replace(/"/g, '&quot;')}"
                                        data-rating-by="${String(ratedBy).replace(/"/g, '&quot;')}"
                                        data-rating-at="${String(ratedAt).replace(/"/g, '&quot;')}">
                                        <i class="text-xs ri-file-list-3-line md:text-sm"></i>
                                    </button>` : `
                                    <button
                                        class="border-0 rounded-sm btn btn-xs md:btn-sm bg-slate-200 text-slate-400 cursor-not-allowed"
                                        disabled
                                        title="Belum dinilai">
                                        <i class="text-xs ri-file-list-3-line md:text-sm"></i>
                                    </button>`}
                                    <button class="text-yellow-600 border-0 rounded-sm btn btn-xs md:btn-sm bg-yellow-500/20 hover:bg-yellow-600 hover:text-white btn-edit" data-id="${item.id}">
                                        <i class="text-xs ri-settings-3-line md:text-sm"></i>
                                    </button>
                                    <button class="text-red-600 border-0 rounded-sm btn btn-xs md:btn-sm bg-red-500/20 hover:bg-red-600 hover:text-white btn-delete" data-id="${item.id}">
                                        <i class="text-xs md:text-sm ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                    }).join('');

                    $('#tableBody').html(html);
                    observeTableImages();
                    updateDeleteSelectedState();
                }

                function observeTableImages() {
                    if (tableImageObserver) {
                        tableImageObserver.disconnect();
                    }

                    const lazyImages = document.querySelectorAll('#tableBody img[data-src]');

                    if (!('IntersectionObserver' in window)) {
                        lazyImages.forEach(loadLazyTableImage);
                        return;
                    }

                    tableImageObserver = new IntersectionObserver((entries, observer) => {
                        entries.forEach((entry) => {
                            if (!entry.isIntersecting) {
                                return;
                            }

                            loadLazyTableImage(entry.target);
                            observer.unobserve(entry.target);
                        });
                    }, {
                        rootMargin: '180px 0px',
                        threshold: 0.01,
                    });

                    lazyImages.forEach((image) => tableImageObserver.observe(image));
                }

                function loadLazyTableImage(image) {
                    if (!image?.dataset?.src) {
                        return;
                    }

                    image.src = image.dataset.src;
                    image.removeAttribute('data-src');
                }

                // Helper function to render image cell
                function renderImageCell(imagePath, label) {
                    if (!imagePath) {
                        return `<img src="https://placehold.co/160x160?text=Kosong"
                             class="object-cover w-16 h-16 opacity-50 md:h-20 md:w-20"
                             width="80"
                             height="80"
                             loading="lazy"
                             decoding="async"
                             alt="Kosong" />`;
                    }

                    const fullUrl = window.location.origin + '/storage/' + imagePath;
                    return `<img src="${THUMB_PLACEHOLDER}"
                             data-src="${fullUrl}"
                             class="object-cover w-16 h-16 transition-opacity cursor-pointer md:h-20 md:min-w-20 hover:opacity-80"
                             width="80"
                             height="80"
                             loading="lazy"
                             decoding="async"
                             fetchpriority="low"
                             onerror="this.onerror=null; this.src='${PLACEHOLDER_IMAGE}';"
                             onclick="showImagePreview('${fullUrl}')" 
                             alt="${label}" />`;
                }

                // Render pagination
                function renderPagination(paginator) {
                    const currentPageNumber = paginator.currentPage;
                    const lastPage = paginator.lastPage;

                    if (!lastPage || lastPage <= 1) {
                        $('#pagination').html('');
                        return;
                    }

                    let html = '<nav class="flex flex-wrap items-center justify-center gap-1" aria-label="Pagination">';

                    // Previous button
                    html += `<button type="button" class="btn btn-xs pagination-btn ${currentPageNumber === 1 ? 'btn-disabled' : ''}" data-page="${currentPageNumber - 1}" aria-label="Halaman sebelumnya">
                        <i class="ri-arrow-left-s-line"></i>
                    </button>`;

                    // Page numbers
                    for (let i = 1; i <= lastPage; i++) {
                        if (i === 1 || i === lastPage || (i >= currentPageNumber - 2 && i <= currentPageNumber +
                                2)) {
                            html +=
                                `<button type="button" class="btn btn-xs pagination-btn ${i === currentPageNumber ? 'btn-active' : ''}" data-page="${i}">${i}</button>`;
                        } else if (i === currentPageNumber - 3 || i === currentPageNumber + 3) {
                            html += '<button type="button" class="btn btn-xs btn-disabled">...</button>';
                        }
                    }

                    // Next button
                    html += `<button type="button" class="btn btn-xs pagination-btn ${currentPageNumber === lastPage ? 'btn-disabled' : ''}" data-page="${currentPageNumber + 1}" aria-label="Halaman berikutnya">
                        <i class="ri-arrow-right-s-line"></i>
                    </button>`;

                    html += '</nav>';
                    $('#pagination').html(html);
                }

                // Setup filter controls
                function setupFilterControls() {
                    // Handle mitra filter change
                    $('#mitraFilter').change(function() {
                        const mitraId = $(this).val();

                        if (mitraId) {
                            // Fetch users for selected mitra
                            $.ajax({
                                url: '{{ route('admin.upload.get-users') }}',
                                type: 'GET',
                                data: {
                                    mitra_id: mitraId
                                },
                                dataType: 'json',
                                beforeSend: function() {
                                    // Show loading state
                                    $('#userFilter').html('<option value="">Loading...</option>');
                                },
                                success: function(response) {
                                    if (response.status) {
                                        populateUserFilter(response.data);
                                    } else {
                                        console.error('Error in response:', response);
                                        $('#userFilter').html(
                                            '<option selected value="">Error loading users</option>'
                                        );
                                        $('#userFilter').prop('disabled', true);
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error('AJAX error:', {
                                        status: status,
                                        error: error,
                                        responseText: xhr.responseText
                                    });

                                    // Reset user filter on error
                                    $('#userFilter').html(
                                        '<option selected value="">Error loading users</option>'
                                    );
                                    $('#userFilter').prop('disabled', true);
                                }
                            });
                        } else {
                            // Reset user filter
                            $('#userFilter').html('<option selected value="">Select Mitra First</option>');
                            $('#userFilter').prop('disabled', true);
                        }
                    });

                    $('#clearFilter').click(function() {
                        $('#monthFilter').val('');
                        $('#yearFilter').val('');
                        $('#mitraFilter').val('');
                        $('#userFilter').html('<option selected value="">Select Mitra First</option>');
                        $('#userFilter').prop('disabled', true);
                        currentMonth = '';
                        setActiveFilters({
                            month: '',
                            year: '',
                            mitra: '',
                            user: ''
                        });
                        updateFilterSummary();
                        loadData(1);
                    });
                }

                $(document).on('click', '.btn-rating-detail', function() {
                    const value = ($(this).data('rating-value') || '-').toString();
                    const reason = ($(this).data('rating-reason') || '-').toString();
                    const by = ($(this).data('rating-by') || '-').toString();
                    const at = ($(this).data('rating-at') || '-').toString();

                    const normalized = value.toLowerCase();
                    const badgeClasses = {
                        'baik': 'bg-emerald-100 text-emerald-700',
                        'cukup': 'bg-amber-100 text-amber-700',
                        'kurang': 'bg-rose-100 text-rose-700'
                    };

                    $('#ratingDetailBadge')
                        .removeClass('bg-slate-200 text-slate-700 bg-emerald-100 text-emerald-700 bg-amber-100 text-amber-700 bg-rose-100 text-rose-700')
                        .addClass(badgeClasses[normalized] || 'bg-slate-200 text-slate-700')
                        .text((value || '-').toUpperCase());
                    $('#ratingDetailReason').text(reason);
                    $('#ratingDetailBy').text(by);
                    if (at !== '-') {
                        const dateObj = new Date(at);
                        const hh = String(dateObj.getHours()).padStart(2, '0');
                        const mm = String(dateObj.getMinutes()).padStart(2, '0');
                        $('#ratingDetailAt').text(`${hh}:${mm}`);
                    } else {
                        $('#ratingDetailAt').text('-');
                    }
                    document.getElementById('ratingDetailModal').showModal();
                });

                $('#closeRatingDetail, #closeRatingDetailTop').on('click', function() {
                    document.getElementById('ratingDetailModal').close();
                });

                function updateFilterSummary() {
                    const filters = [
                        $('#mitraFilter').val(),
                        $('#userFilter').val(),
                        $('#monthFilter').val(),
                        $('#yearFilter').val(),
                    ];
                    const activeCount = filters.filter(Boolean).length;

                    $('#activeFilterBadge')
                        .toggleClass('hidden', activeCount === 0)
                        .text(`${activeCount} filter aktif`);
                }

                // Populate user filter dropdown
                function populateUserFilter(users) {
                    let html = '<option value="">All Users</option>';

                    if (users && users.length > 0) {
                        users.forEach(user => {
                            html += `<option value="${user.id}">${kapitalName(user.nama_lengkap)}</option>`;
                        });
                    } else {
                        html += '<option value="">No users found</option>';
                    }

                    $('#userFilter').html(html);
                    $('#userFilter').prop('disabled', false);
                }

                $('#applyFilter').click(function() {
                    const {
                        month,
                        year,
                        mitra,
                        user
                    } = getSelectedFilters();

                    setActiveFilters({
                        month,
                        year,
                        mitra,
                        user
                    });

                    // Update currentMonth when filter is applied
                    if (month || year) {
                        currentMonth = year + '-' + (month ? month.padStart(2, '0') : '01');
                    } else {
                        currentMonth = '';
                    }

                    updateFilterSummary();
                    loadData(1, month, year, mitra, user);
                });

                // Setup checkbox controls
                function setupCheckboxControls() {
                    // Header checkbox
                    $('#headerCheckbox').change(function() {
                        const isChecked = $(this).prop('checked');
                        $('.row-checkbox').prop('checked', isChecked);
                        updateDeleteSelectedState();
                    });

                    // Select all button
                    $('#selectAll').click(function() {
                        $('.row-checkbox').prop('checked', true);
                        $('#headerCheckbox').prop('checked', true);
                        updateDeleteSelectedState();
                    });

                    // Deselect all button
                    $('#deselectAll').click(function() {
                        $('.row-checkbox').prop('checked', false);
                        $('#headerCheckbox').prop('checked', false);
                        updateDeleteSelectedState();
                    });

                    $(document).on('change', '.row-checkbox', function() {
                        const total = $('.row-checkbox').length;
                        const checked = $('.row-checkbox:checked').length;
                        $('#headerCheckbox').prop('checked', total > 0 && total === checked);
                        updateDeleteSelectedState();
                    });
                }

                function updateDeleteSelectedState() {
                    const checkedCount = $('.row-checkbox:checked').length;
                    const totalCount = $('.row-checkbox').length;
                    $('#deleteSelected').prop('disabled', checkedCount === 0);
                    $('#selectionSummary').text(checkedCount > 0 ?
                        `${checkedCount} dari ${totalCount} data di halaman ini dipilih.` :
                        'Belum ada data dipilih.'
                    );
                }

                function setupMassDelete() {
                    $('#deleteSelected').click(function() {
                        const selectedIds = [];
                        $('.row-checkbox:checked').each(function() {
                            selectedIds.push($(this).data('id'));
                        });

                        if (selectedIds.length === 0) {
                            Notify('Pilih minimal 1 data untuk dihapus.', null, null, 'warning');
                            return;
                        }

                        openDeleteModal('mass', selectedIds);
                    });
                }

                function setupDeleteModal() {
                    $('#cancelDelete').click(function() {
                        deleteModal.close();
                        resetPendingDelete();
                    });

                    $('#confirmDelete').click(function() {
                        if (!pendingDelete.type || pendingDelete.ids.length === 0) {
                            deleteModal.close();
                            return;
                        }

                        setDeleteLoading(true);

                        if (pendingDelete.type === 'mass') {
                            deleteMassUploads(pendingDelete.ids);
                            return;
                        }

                        deleteSingleUpload(pendingDelete.ids[0]);
                    });
                }

                function openDeleteModal(type, ids) {
                    pendingDelete = {
                        type,
                        ids
                    };

                    const total = ids.length;
                    $('#deleteModalTitle').text(type === 'mass' ? 'Hapus Data Terpilih' : 'Hapus Photo Progress');
                    $('#deleteModalMessage').text(type === 'mass' ?
                        `Yakin ingin menghapus ${total} data photo progress terpilih? Semua foto terkait juga akan dihapus.` :
                        'Yakin ingin menghapus data photo progress ini? Semua foto terkait juga akan dihapus.'
                    );
                    setDeleteLoading(false);
                    deleteModal.showModal();
                }

                function resetPendingDelete() {
                    pendingDelete = {
                        type: null,
                        ids: []
                    };
                    setDeleteLoading(false);
                }

                function setDeleteLoading(isLoading) {
                    $('#confirmDelete').prop('disabled', isLoading);
                    $('#cancelDelete').prop('disabled', isLoading);
                    $('#deleteSpinner').toggleClass('hidden', !isLoading);
                }

                function deleteMassUploads(ids) {
                    $.ajax({
                        url: '{{ route('admin.upload.mass-delete') }}',
                        type: 'POST',
                        data: {
                            ids: ids,
                            _token: CSRF_TOKEN
                        },
                        dataType: 'json',
                        success: function(response) {
                            deleteModal.close();
                            resetPendingDelete();
                            loadDataWithActiveFilters(currentPage);
                            $('#headerCheckbox').prop('checked', false);
                            Notify(response.message || 'Selected uploads deleted successfully',
                                null, null, 'success');
                        },
                        error: function(xhr) {
                            const message = xhr.responseJSON?.message ||
                                'Error deleting selected data';
                            Notify(message, null, null, 'error');
                        },
                        complete: function() {
                            setDeleteLoading(false);
                        }
                    });
                }

                // Setup PDF generation
                function setupPdfGeneration() {
                    $('#generatePdf').click(function() {
                        const selectedIds = [];
                        $('.row-checkbox:checked').each(function() {
                            selectedIds.push($(this).data('id'));
                        });

                        if (selectedIds.length === 0) {
                            if (!confirm(
                                    'No records selected. Generate PDF for all records in the current month?'
                                )) {
                                return;
                            }
                        }

                        // Show loading state
                        const $button = $(this);
                        const originalText = $button.html();
                        $button.prop('disabled', true);
                        $button.html(
                            '<span class="loading loading-spinner loading-sm"></span> Generating PDF...');

                        ensurePdfLibrariesLoaded()
                            .then(() => {
                                $.ajax({
                                    url: '{{ route('admin.upload.get-pdf-data') }}',
                                    type: 'GET',
                                    data: {
                                        ids: selectedIds,
                                        month: currentMonth,
                                    },
                                    dataType: 'json',
                                    success: function(response) {
                                        if (response.status) {
                                            updatePdfProgress('Generating PDF...');
                                            generatePdf(response.data, currentMonth, function() {
                                                // Callback when PDF generation is complete
                                                hidePdfProgressOverlay();
                                                $button.prop('disabled', false);
                                                $button.html(originalText);
                                            });
                                        } else {
                                            hidePdfProgressOverlay();
                                            $button.prop('disabled', false);
                                            $button.html(originalText);
                                            Notify('Error generating PDF', null, null, 'error');
                                        }
                                    },
                                    error: function(xhr) {
                                        hidePdfProgressOverlay();
                                        $button.prop('disabled', false);
                                        $button.html(originalText);
                                        Notify('Error getting PDF data', null, null, 'error');
                                    }
                                });
                            })
                            .catch(() => {
                                hidePdfProgressOverlay();
                                $button.prop('disabled', false);
                                $button.html(originalText);
                                Notify('Gagal memuat library PDF. Silakan coba lagi.', null, null, 'error');
                            });
                    });
                }

                function ensurePdfLibrariesLoaded() {
                    if (window.html2canvas) {
                        return Promise.resolve();
                    }

                    if (pdfLibrariesPromise) {
                        return pdfLibrariesPromise;
                    }

                    pdfLibrariesPromise = loadScript('https://cdn.jsdelivr.net/npm/html2canvas-pro@1.5.13/dist/html2canvas-pro.min.js');

                    return pdfLibrariesPromise;
                }

                function loadScript(src) {
                    return new Promise((resolve, reject) => {
                        const existingScript = document.querySelector(`script[src="${src}"]`);

                        if (existingScript) {
                            existingScript.addEventListener('load', resolve, { once: true });
                            existingScript.addEventListener('error', reject, { once: true });
                            return;
                        }

                        const script = document.createElement('script');
                        script.src = src;
                        script.async = true;
                        script.onload = resolve;
                        script.onerror = reject;
                        document.head.appendChild(script);
                    });
                }

                function generatePdf(data, currentMonth, onComplete) {
                    const pages = getFotoPageHtml(data, currentMonth);
                    const totalPages = pages.length;
                    setProgress(0);

                    const images = [];

                    let index = 0;

                    function renderNext() {
                        if (index >= totalPages) {
                            // sudah selesai render semua jadi image
                            setProgress(100);
                            startWorker(images);
                            return;
                        }


                        const pageNum = index + 1;
                        const progressPercent = Math.round((pageNum - 1) / totalPages * 100);

                        // Update bar for starting this page
                        setProgress(progressPercent);

                        const div = document.createElement("div");
                        div.style.position = "absolute";
                        div.style.left = "-9999px";
                        div.style.width = "297mm";
                        div.style.backgroundColor = "white";
                        div.innerHTML = pages[index];
                        document.body.appendChild(div);

                        html2canvas(div, {
                            scale: 2,
                            useCORS: true,
                            allowTaint: true,
                            logging: false,
                        }).then(canvas => {
                            document.body.removeChild(div);

                            const dataUrl = canvas.toDataURL("image/jpeg", 0.8);
                            images.push(dataUrl);

                            // Update after page finished
                            const newPercent = Math.round(pageNum / totalPages * 100);
                            setProgress(newPercent);

                            index++;
                            renderNext();

                        }).catch(err => {
                            console.error(err);
                        });
                    }

                    renderNext();

                    function startWorker(images) {
                        const worker = new Worker("/js/pdf-worker.js");

                        worker.onmessage = function(e) {
                            if (e.data.progress !== undefined) {
                                setProgress(e.data.progress);
                            }

                            if (e.data.done) {
                                const filename = `Photo_Progress_Report_${currentMonth}.pdf`;
                                downloadPdfBlob(e.data.pdfBlob, filename);
                                sendPdfToBackend(e.data.pdfBlob, currentMonth, data[0].clients_id, () => {
                                    if (onComplete) onComplete();

                                    showNotification("PDF Generation Complete", {
                                        body: "Proses pembuatan PDF telah selesai!",
                                        icon: "/favicon.ico"
                                    });
                                    document.getElementById("notify-sound").play().catch(console.warn);

                                });
                                worker.terminate();
                            }

                            if (e.data.error) {
                                console.error("Worker Error:", e.data.error);
                                worker.terminate();
                            }
                        };

                        worker.postMessage({
                            images
                        });
                    }
                }



                // Function to send PDF to backend
                function sendPdfToBackend(pdfBlob, month, clientIds, onComplete) {
                    const formData = new FormData();
                    formData.append('pdf', pdfBlob, `Photo_Progress_Report_${month.replace(/\s+/g, '_')}.pdf`);
                    formData.append('month', month);
                    formData.append('client_ids', clientIds);
                    formData.append('_token', CSRF_TOKEN);
                    
                    $.ajax({
                        url: '{{ route('admin.upload.store-pdf') }}',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            Notify('PDF saved successfully!', null, null, 'success');
                            if (onComplete) onComplete();
                        },
                        error: function(xhr) {
                            Notify('Error saving PDF: ' + (xhr.responseJSON?.message || 'Unknown error'),
                                null, null, 'error');
                            if (onComplete) onComplete();
                        }
                    });
                }

                // Pagination click
                $(document).on('click', '#pagination .pagination-btn', function(e) {
                    e.preventDefault();
                    const page = Number($(this).data('page'));
                    if (page && !$(this).hasClass('btn-disabled') && !$(this).hasClass('btn-active')) {
                        loadDataWithActiveFilters(page);
                    }
                });

                // Close button for edit modal
                $('#btnClose, #btnCloseTop').click(function() {
                    modal.close();
                    resetForm();
                });

                // Edit button
                $(document).on('click', '.btn-edit', function() {
                    const id = $(this).data('id');
                    $.ajax({
                        url: `{{ route('admin.upload.show', ':id') }}`.replace(':id', id),
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            if (response.status) {
                                $('#modalTitle').text('Edit Photo Progress');
                                $('#formMethod').val('PUT');
                                $('#photoId').val(response.data.id);
                                $('#client_id').val(response.data.client_id || response.data.clients_id || '');
                                $('#note').val(response.data.note || '');

                                // Display current images
                                displayCurrentImage('img_before', response.data.img_before);
                                displayCurrentImage('img_proccess', response.data.img_proccess);
                                displayCurrentImage('img_final', response.data.img_final);

                                modal.showModal();
                            }
                        },
                        error: function(xhr) {
                            Notify('Error loading data for edit', null, null, 'error');
                        }
                    });
                });

                // Helper function to display current image
                function displayCurrentImage(field, imagePath) {
                    const container = $(`#current-${field}`);
                    if (imagePath) {
                        const fullUrl = window.location.origin + '/storage/' + imagePath;
                        container.html(
                            `<div class="space-y-2">
                                <button type="button" class="block w-full overflow-hidden bg-white border rounded-md border-base-300" onclick="showImagePreview('${fullUrl}')">
                                    <img src="${fullUrl}" class="object-cover w-full transition-opacity h-28 hover:opacity-80" loading="lazy" decoding="async" onerror="this.onerror=null; this.src='${PLACEHOLDER_IMAGE}';" />
                                </button>
                                <span class="block text-[11px] md:text-xs text-base-content/70">Gambar saat ini</span>
                            </div>`
                        );
                    } else {
                        container.html(
                            `<div class="flex items-center justify-center w-full bg-white border border-dashed rounded-md h-28 border-slate-300 text-slate-400">
                                <span class="text-[11px] md:text-xs">Belum ada gambar</span>
                            </div>`
                        );
                    }
                }

                // Form submission
                $('#photoForm').submit(function(e) {
                    e.preventDefault();
                    clearErrors();

                    const method = $('#formMethod').val();
                    const id = $('#photoId').val();
                    const url = "{{ route('admin.upload.update', ':id') }}".replace(':id', id);

                    // Use FormData for proper file handling
                    const formData = new FormData();
                    formData.append('client_id', $('#client_id').val());
                    formData.append('note', $('#note').val());

                    // Append image files if they exist
                    appendImageIfExists(formData, 'img_before');
                    appendImageIfExists(formData, 'img_proccess');
                    appendImageIfExists(formData, 'img_final');

                    formData.append('_token', CSRF_TOKEN);

                    if (method === 'PUT') {
                        formData.append('_method', 'PUT');
                    }

                    $('#btnSave').prop('disabled', true);
                    $('#btnSpinner').removeClass('hidden');

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        success: function(response) {
                            if (response.status) {
                                modal.close();
                                loadDataWithActiveFilters(currentPage);
                                Notify(response.message, null, null, 'success');
                                resetForm();
                            }
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) {
                                const errors = xhr.responseJSON.errors;
                                displayErrors(errors);
                            } else {
                                Notify('Error saving data', null, null, 'error');
                            }
                        },
                        complete: function() {
                            $('#btnSave').prop('disabled', false);
                            $('#btnSpinner').addClass('hidden');
                        }
                    });
                });

                // Helper function to append image if exists
                function appendImageIfExists(formData, fieldName) {
                    const file = $(`#${fieldName}`)[0].files[0];
                    if (file) {
                        formData.append(fieldName, file);
                    }
                }

                // Delete button
                $(document).on('click', '.btn-delete', function() {
                    const id = $(this).data('id');
                    openDeleteModal('single', [id]);
                });

                function deleteSingleUpload(id) {
                    $.ajax({
                        url: `{{ route('admin.upload.destroy', ':id') }}`.replace(':id', id),
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: CSRF_TOKEN
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status) {
                                deleteModal.close();
                                resetPendingDelete();
                                loadDataWithActiveFilters(currentPage);
                                Notify(response.message, null, null, 'success');
                            }
                        },
                        error: function(xhr) {
                            Notify('Error deleting data', null, null, 'error');
                        },
                        complete: function() {
                            setDeleteLoading(false);
                        }
                    });
                }

                // Image preview functionality
                window.showImagePreview = function(imageSrc) {
                    // Reset preview state
                    imagePreviewState = {
                        images: [imageSrc],
                        currentIndex: 0,
                        rotation: 0,
                        zoom: 1
                    };

                    updatePreviewImage();
                    imageModal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                };

                // Setup image preview controls
                function setupImagePreviewControls() {
                    // Close image preview
                    $('#closeImagePreview').click(closeImagePreview);

                    // Rotate image
                    $('#rotateImage').click(function() {
                        imagePreviewState.rotation = (imagePreviewState.rotation + 90) % 360;
                        updatePreviewImageTransform();
                    });

                    // Zoom in
                    $('#zoomInImage').click(function() {
                        imagePreviewState.zoom = Math.min(imagePreviewState.zoom + 0.25, 3);
                        updatePreviewImageTransform();
                    });

                    // Zoom out
                    $('#zoomOutImage').click(function() {
                        imagePreviewState.zoom = Math.max(imagePreviewState.zoom - 0.25, 0.5);
                        updatePreviewImageTransform();
                    });

                    // Download image
                    $('#downloadImage').click(function() {
                        const link = document.createElement('a');
                        link.href = imagePreviewState.images[imagePreviewState.currentIndex];
                        link.download = 'image.jpg';
                        link.click();
                    });

                    // Previous image
                    $('#prevImage').click(function() {
                        if (imagePreviewState.images.length > 1) {
                            imagePreviewState.currentIndex = (imagePreviewState.currentIndex - 1 +
                                imagePreviewState.images.length) % imagePreviewState.images.length;
                            updatePreviewImage();
                        }
                    });

                    // Next image
                    $('#nextImage').click(function() {
                        if (imagePreviewState.images.length > 1) {
                            imagePreviewState.currentIndex = (imagePreviewState.currentIndex + 1) %
                                imagePreviewState.images.length;
                            updatePreviewImage();
                        }
                    });

                    // Close when clicking outside
                    $(imageModal).click(function(e) {
                        if (e.target === imageModal) {
                            closeImagePreview();
                        }
                    });
                }

                // Update preview image
                function updatePreviewImage() {
                    $('#previewImage')
                        .off('error')
                        .on('error', function() {
                            this.onerror = null;
                            this.src = PLACEHOLDER_IMAGE;
                        })
                        .attr('src', imagePreviewState.images[imagePreviewState.currentIndex]);
                    updatePreviewImageTransform();
                    updateImageCounter();
                    updateNavigationButtons();
                }

                // Update preview image transform
                function updatePreviewImageTransform() {
                    const transform = `rotate(${imagePreviewState.rotation}deg) scale(${imagePreviewState.zoom})`;
                    $('#previewImage').css('transform', transform);
                }

                // Update image counter
                function updateImageCounter() {
                    if (imagePreviewState.images.length > 1) {
                        $('#imageCounter').text(
                            `${imagePreviewState.currentIndex + 1} / ${imagePreviewState.images.length}`);
                        $('#imageInfo').show();
                    } else {
                        $('#imageInfo').hide();
                    }
                }

                // Update navigation buttons
                function updateNavigationButtons() {
                    if (imagePreviewState.images.length > 1) {
                        $('#prevImage, #nextImage').show();
                    } else {
                        $('#prevImage, #nextImage').hide();
                    }
                }

                // Close image preview
                function closeImagePreview() {
                    imageModal.classList.add('hidden');
                    document.body.style.overflow = 'auto';

                    // Reset transform
                    imagePreviewState.rotation = 0;
                    imagePreviewState.zoom = 1;
                }

                function downloadPdfBlob(blob, filename) {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                }

                // Helper functions
                function resetForm() {
                    $('#photoForm')[0].reset();
                    $('#photoId').val('');
                    $('#current-img_before, #current-img_proccess, #current-img_final').html('');
                    clearErrors();
                }

                ['img_before', 'img_proccess', 'img_final'].forEach(function(field) {
                    $(`#${field}`).on('change', function() {
                        const file = this.files[0];
                        if (!file) return;

                        const objectUrl = URL.createObjectURL(file);
                        $(`#current-${field}`).html(
                            `<div class="space-y-2">
                                <button type="button" class="block w-full overflow-hidden bg-white border rounded-md border-primary/40" onclick="showImagePreview('${objectUrl}')">
                                    <img src="${objectUrl}" class="object-cover w-full transition-opacity h-28 hover:opacity-80" />
                                </button>
                                <span class="block text-[11px] md:text-xs text-base-content/70">Preview file baru</span>
                            </div>`
                        );
                    });
                });

                function clearErrors() {
                    $('select, input, textarea').removeClass('input-error select-error textarea-error');
                    $('.label[id^="error-"]').addClass('hidden').find('span').text('');
                }

                function displayErrors(errors) {
                    $.each(errors, function(key, value) {
                        const $field = $(`#${key}`);
                        $field.addClass($field.is('select') ? 'select-error' : ($field.is('textarea') ?
                            'textarea-error' : 'input-error'));
                        $(`#error-${key}`).removeClass('hidden').find('span').text(value[0]);
                    });
                }

                function kapitalName(name) {
                    return name
                        .toLowerCase()
                        .trim()
                        .replace(/\b([a-z])|\'([a-z])/g, match => match.toUpperCase());
                }


                // Update progress message
                function updatePdfProgress(message) {
                    $('#pdfProgressMessage').text(message);
                }

                // Hide PDF generation progress overlay
                function hidePdfProgressOverlay() {
                    $('#pdfProgressOverlay').addClass('hidden');
                }


                // Initial load
                init();
            });
        </script>

        <style>
            @keyframes fade-in {
                from {
                    opacity: 0;
                    transform: translateX(20px);
                }

                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }

            @keyframes fade-out {
                from {
                    opacity: 1;
                    transform: translateX(0);
                }

                to {
                    opacity: 0;
                    transform: translateX(20px);
                }
            }

            .animate-fade-in {
                animation: fade-in 0.3s ease-out;
            }

            .animate-fade-out {
                animation: fade-out 0.3s ease-out;
            }

            /* Image preview modal styles */
            #imagePreviewModal {
                transition: opacity 0.3s ease;
            }

            #previewImage {
                transition: transform 0.3s ease;
            }
        </style>
    @endpush
</x-app-layout>
